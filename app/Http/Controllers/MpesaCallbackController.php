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

        Log::info('M-Pesa callback received', ['payload' => $request->all()]);

        $mpesa->handleCallback($request->all());

        // Always acknowledge so Safaricom does not retry.
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
