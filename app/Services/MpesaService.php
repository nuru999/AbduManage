<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Safaricom Daraja integration (M-Pesa Express / STK Push).
 */
class MpesaService
{
    public function simulate(): bool
    {
        return (bool) config('services.mpesa.simulate');
    }

    /** Normalise 07xx / 01xx / +254... / 254... to 2547XXXXXXXX. */
    public static function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (preg_match('/^0([17]\d{8})$/', $digits, $m)) {
            return '254'.$m[1];
        }
        if (preg_match('/^254([17]\d{8})$/', $digits, $m)) {
            return '254'.$m[1];
        }
        if (preg_match('/^([17]\d{8})$/', $digits, $m)) {
            return '254'.$m[1];
        }

        return null;
    }

    /** Send the STK push prompt to the customer's phone. */
    public function stkPush(Payment $payment): Payment
    {
        if ($this->simulate()) {
            return tap($payment)->update([
                'merchant_request_id' => 'SIM-'.Str::upper(Str::random(8)),
                'checkout_request_id' => 'ws_SIM_'.Str::upper(Str::random(12)),
            ]);
        }

        $this->assertConfigured();
        $timestamp = Carbon::now('Africa/Nairobi')->format('YmdHis');
        $shortcode = config('services.mpesa.shortcode');

        $response = $this->client()->post('/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => $shortcode,
            'Password' => base64_encode($shortcode.config('services.mpesa.passkey').$timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => config('services.mpesa.transaction_type'),
            'Amount' => $payment->amount,
            'PartyA' => $payment->phone,
            'PartyB' => config('services.mpesa.party_b') ?: $shortcode,
            'PhoneNumber' => $payment->phone,
            'CallBackURL' => $this->callbackUrl(),
            'AccountReference' => Str::limit($payment->student->admission_number, 12, ''),
            'TransactionDesc' => 'School fees',
        ]);

        $body = $response->json() ?? [];

        if (! $response->successful() || Arr::get($body, 'ResponseCode') !== '0') {
            Log::warning('M-Pesa STK push rejected', ['payment' => $payment->id, 'body' => $body]);

            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'result_desc' => Str::limit(Arr::get($body, 'errorMessage', Arr::get($body, 'ResponseDescription', 'STK push failed')), 250, ''),
            ]);

            throw new MpesaException($payment->result_desc);
        }

        $payment->update([
            'merchant_request_id' => $body['MerchantRequestID'] ?? null,
            'checkout_request_id' => $body['CheckoutRequestID'] ?? null,
        ]);

        return $payment;
    }

    /**
     * Apply Safaricom's callback payload. Idempotent: a payment that has
     * already left "pending" is never changed again.
     */
    public function handleCallback(array $payload): ?Payment
    {
        $callback = Arr::get($payload, 'Body.stkCallback');

        if (! $callback || empty($callback['CheckoutRequestID'])) {
            return null;
        }

        $payment = Payment::where('checkout_request_id', $callback['CheckoutRequestID'])->first();

        if (! $payment || ! $payment->isPending()) {
            return $payment;
        }

        $code = (int) ($callback['ResultCode'] ?? -1);

        if ($code === 0) {
            $items = collect(Arr::get($callback, 'CallbackMetadata.Item', []))
                ->mapWithKeys(fn ($item) => [$item['Name'] => $item['Value'] ?? null]);

            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'result_code' => 0,
                'result_desc' => $callback['ResultDesc'] ?? 'Success',
                'mpesa_receipt' => $items->get('MpesaReceiptNumber'),
                'paid_at' => $this->parseTransactionDate($items->get('TransactionDate')),
            ]);
        } else {
            $payment->update([
                'status' => $code === 1032 ? Payment::STATUS_CANCELLED : Payment::STATUS_FAILED,
                'result_code' => $code,
                'result_desc' => Str::limit($callback['ResultDesc'] ?? 'Payment failed', 250, ''),
            ]);
        }

        return $payment->refresh();
    }

    /**
     * Called while the browser polls. In simulation mode this completes the
     * payment; otherwise it asks Daraja directly if the callback is late.
     */
    public function refreshStatus(Payment $payment): Payment
    {
        if (! $payment->isPending()) {
            return $payment;
        }

        if ($this->simulate()) {
            if ($payment->created_at->diffInSeconds(now()) >= 5) {
                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'result_code' => 0,
                    'result_desc' => 'Simulated payment',
                    'mpesa_receipt' => 'SIM'.Str::upper(Str::random(7)),
                    'paid_at' => now(),
                ]);
            }

            return $payment->refresh();
        }

        // Give the callback 20s before asking Safaricom ourselves.
        if (! $payment->checkout_request_id || $payment->created_at->diffInSeconds(now()) < 20) {
            return $payment;
        }

        try {
            $timestamp = Carbon::now('Africa/Nairobi')->format('YmdHis');
            $shortcode = config('services.mpesa.shortcode');

            $response = $this->client()->post('/mpesa/stkpushquery/v1/query', [
                'BusinessShortCode' => $shortcode,
                'Password' => base64_encode($shortcode.config('services.mpesa.passkey').$timestamp),
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $payment->checkout_request_id,
            ]);

            $body = $response->json() ?? [];

            // The query API does not return the receipt number, so only record
            // failures here. Success is confirmed by the callback.
            if ($response->successful() && isset($body['ResultCode']) && (int) $body['ResultCode'] !== 0) {
                $code = (int) $body['ResultCode'];
                $payment->update([
                    'status' => $code === 1032 ? Payment::STATUS_CANCELLED : Payment::STATUS_FAILED,
                    'result_code' => $code,
                    'result_desc' => Str::limit($body['ResultDesc'] ?? 'Payment failed', 250, ''),
                ]);
            }
        } catch (\Throwable $e) {
            Log::info('M-Pesa status query failed', ['payment' => $payment->id, 'error' => $e->getMessage()]);
        }

        return $payment->refresh();
    }

    public function callbackUrl(): string
    {
        $path = route('mpesa.callback', ['token' => config('services.mpesa.callback_token')], false);
        $base = rtrim(config('services.mpesa.callback_base_url') ?: config('app.url'), '/');

        return $base.$path;
    }

    private function baseUrl(): string
    {
        return config('services.mpesa.env') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withToken($this->accessToken())
            ->acceptJson()
            ->timeout(30);
    }

    private function accessToken(): string
    {
        $credentials = config('services.mpesa.consumer_key')."\0".config('services.mpesa.consumer_secret');
        $cacheKey = 'mpesa_access_token:'.config('services.mpesa.env').':'.hash('sha256', $credentials);

        return Cache::remember($cacheKey, now()->addMinutes(50), function () {
            $response = Http::baseUrl($this->baseUrl())
                ->withBasicAuth((string) config('services.mpesa.consumer_key'), (string) config('services.mpesa.consumer_secret'))
                ->timeout(30)
                ->get('/oauth/v1/generate', ['grant_type' => 'client_credentials']);

            $token = $response->json('access_token');

            if (! $response->successful() || ! $token) {
                throw new MpesaException('Could not authenticate with M-Pesa. Check your Daraja consumer key and secret.');
            }

            return $token;
        });
    }

    private function assertConfigured(): void
    {
        $required = [
            'MPESA_CONSUMER_KEY' => config('services.mpesa.consumer_key'),
            'MPESA_CONSUMER_SECRET' => config('services.mpesa.consumer_secret'),
            'MPESA_SHORTCODE' => config('services.mpesa.shortcode'),
            'MPESA_PASSKEY' => config('services.mpesa.passkey'),
            'MPESA_CALLBACK_TOKEN' => config('services.mpesa.callback_token'),
        ];
        $missing = array_keys(array_filter($required, fn ($value) => ! is_string($value) || trim($value) === ''));

        if ($missing) {
            throw new MpesaException('M-Pesa is not configured. Set '.implode(', ', $missing).' in .env.');
        }

        if (! in_array(config('services.mpesa.env'), ['sandbox', 'production'], true)) {
            throw new MpesaException('MPESA_ENV must be either sandbox or production.');
        }

        $callback = parse_url($this->callbackUrl());

        if (! is_array($callback)) {
            throw new MpesaException('Set MPESA_CALLBACK_BASE_URL to a public HTTPS URL that Safaricom can reach.');
        }

        $host = strtolower($callback['host'] ?? '');

        if (
            strtolower($callback['scheme'] ?? '') !== 'https'
            || $host === ''
            || in_array($host, ['localhost', '127.0.0.1', '::1'], true)
        ) {
            throw new MpesaException('Set MPESA_CALLBACK_BASE_URL to a public HTTPS URL that Safaricom can reach.');
        }
    }

    private function parseTransactionDate(mixed $value): Carbon
    {
        try {
            return Carbon::createFromFormat('YmdHis', (string) $value, 'Africa/Nairobi')->setTimezone(config('app.timezone'));
        } catch (\Throwable) {
            return now();
        }
    }
}
