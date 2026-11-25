<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\MpesaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MpesaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.mpesa.callback_token' => 'secret-token', 'services.mpesa.simulate' => false]);
    }

    private function pendingPayment(): Payment
    {
        $student = Student::create([
            'first_name' => 'Test', 'last_name' => 'Student', 'gender' => 'male',
            'admission_number' => 'ADM-T1', 'enrollment_date' => today(),
        ]);

        return Payment::create([
            'student_id' => $student->id, 'amount' => 5000, 'phone' => '254712345678',
            'status' => 'pending', 'checkout_request_id' => 'ws_CO_123',
        ]);
    }

    private function successPayload(): array
    {
        return ['Body' => ['stkCallback' => [
            'MerchantRequestID' => 'm1', 'CheckoutRequestID' => 'ws_CO_123', 'ResultCode' => 0, 'ResultDesc' => 'ok',
            'CallbackMetadata' => ['Item' => [
                ['Name' => 'Amount', 'Value' => 5000],
                ['Name' => 'MpesaReceiptNumber', 'Value' => 'QWE123RTY4'],
                ['Name' => 'TransactionDate', 'Value' => 20261120101500],
            ]],
        ]]];
    }

    public function test_phone_numbers_are_normalised(): void
    {
        $this->assertSame('254712345678', MpesaService::normalizePhone('0712 345 678'));
        $this->assertSame('254112345678', MpesaService::normalizePhone('+254 112 345 678'));
        $this->assertSame('254712345678', MpesaService::normalizePhone('712345678'));
        $this->assertNull(MpesaService::normalizePhone('12345'));
    }

    public function test_successful_callback_completes_the_payment(): void
    {
        $payment = $this->pendingPayment();

        $this->postJson('/mpesa/callback/secret-token', $this->successPayload())
            ->assertOk()->assertJson(['ResultCode' => 0]);

        $payment->refresh();
        $this->assertSame('completed', $payment->status);
        $this->assertSame('QWE123RTY4', $payment->mpesa_receipt);
    }

    public function test_cancelled_callback_marks_payment_cancelled(): void
    {
        $payment = $this->pendingPayment();

        $this->postJson('/mpesa/callback/secret-token', ['Body' => ['stkCallback' => [
            'CheckoutRequestID' => 'ws_CO_123', 'ResultCode' => 1032, 'ResultDesc' => 'Request cancelled by user',
        ]]])->assertOk();

        $this->assertSame('cancelled', $payment->refresh()->status);
    }

    public function test_callback_with_wrong_token_is_rejected(): void
    {
        $payment = $this->pendingPayment();

        $this->postJson('/mpesa/callback/wrong', $this->successPayload())->assertNotFound();
        $this->assertSame('pending', $payment->refresh()->status);
    }

    public function test_duplicate_callbacks_do_not_change_a_finished_payment(): void
    {
        $payment = $this->pendingPayment();

        $this->postJson('/mpesa/callback/secret-token', $this->successPayload());
        $this->postJson('/mpesa/callback/secret-token', ['Body' => ['stkCallback' => [
            'CheckoutRequestID' => 'ws_CO_123', 'ResultCode' => 1, 'ResultDesc' => 'late failure',
        ]]]);

        $this->assertSame('completed', $payment->refresh()->status);
    }

    public function test_only_admins_can_open_the_payments_pages(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($teacher)->get('/payments')->assertForbidden();
        $this->actingAs($admin)->get('/payments')->assertOk();
    }

    public function test_simulated_payment_completes_after_a_few_seconds(): void
    {
        config(['services.mpesa.simulate' => true]);
        $admin = User::factory()->create(['role' => 'admin']);
        $payment = $this->pendingPayment();
        $payment->forceFill(['created_at' => now()->subSeconds(10)])->save();

        $this->actingAs($admin)->getJson("/payments/{$payment->id}/status")
            ->assertOk()->assertJson(['status' => 'completed']);
    }
}
