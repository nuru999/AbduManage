<?php

namespace App\Http\Controllers;

use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    public function __invoke(Request $request, string $token, MpesaService $mpesa): JsonResponse
    {
        $expected = (string) config('services.mpesa.callback_token');

        if ($expected === '' || ! hash_equals($expected, $token)) {
            abort(404);
        }

        $payload = $request->all();
        $payment = $mpesa->handleCallback($payload);

        Log::info('M-Pesa callback received', [
            'payment_id' => $payment?->id,
            'checkout_request_id' => data_get($payload, 'Body.stkCallback.CheckoutRequestID'),
            'result_code' => data_get($payload, 'Body.stkCallback.ResultCode'),
        ]);

        // Always acknowledge so Safaricom does not retry.
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
