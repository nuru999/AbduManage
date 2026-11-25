<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Services\MpesaException;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private MpesaService $mpesa) {}

    public function index(Request $request)
    {
        $payments = Payment::with('student')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), function ($query, $term) {
                $query->where(fn ($q) => $q->where('mpesa_receipt', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhereHas('student', fn ($s) => $s->search($term)));
            })
            ->latest()->paginate(15)->withQueryString();

        $totals = [
            'collected' => (int) Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            'month' => (int) Payment::where('status', Payment::STATUS_COMPLETED)->where('paid_at', '>=', now()->startOfMonth())->sum('amount'),
            'pending' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'failed' => Payment::whereIn('status', [Payment::STATUS_FAILED, Payment::STATUS_CANCELLED])->count(),
        ];

        return view('payments.index', compact('payments', 'totals'));
    }

    public function create(Request $request)
    {
        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        $selected = $students->firstWhere('id', (int) $request->query('student'));

        return view('payments.create', [
            'students' => $students,
            'selected' => $selected,
            'simulate' => $this->mpesa->simulate(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'amount' => ['required', 'integer', 'min:1', 'max:250000'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = MpesaService::normalizePhone($data['phone']);

        if (! $phone) {
            return back()->withInput()->withErrors(['phone' => 'Enter a valid Safaricom number, e.g. 0712 345 678.']);
        }

        $payment = Payment::create([
            'student_id' => $data['student_id'],
            'initiated_by' => $request->user()->id,
            'amount' => $data['amount'],
            'phone' => $phone,
            'status' => Payment::STATUS_PENDING,
        ]);

        try {
            $this->mpesa->stkPush($payment);
        } catch (MpesaException $e) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'result_desc' => Str::limit($e->getMessage(), 250, ''),
            ]);

            return back()->withInput()->with('error', 'M-Pesa error: '.$e->getMessage());
        } catch (\Throwable $e) {
            Log::error('STK push crashed', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            $payment->update(['status' => Payment::STATUS_FAILED, 'result_desc' => 'Could not reach M-Pesa']);

            return back()->withInput()->with('error', 'Could not reach M-Pesa. Please try again.');
        }

        return redirect()->route('payments.show', $payment);
    }

    public function show(Payment $payment)
    {
        $payment->load('student.schoolClass', 'initiator');

        return view('payments.show', ['payment' => $payment, 'simulate' => $this->mpesa->simulate()]);
    }

    /** Polled by the payment page while the customer enters their PIN. */
    public function status(Payment $payment): JsonResponse
    {
        $payment = $this->mpesa->refreshStatus($payment);

        return response()->json([
            'status' => $payment->status,
            'receipt' => $payment->mpesa_receipt,
            'message' => $payment->result_desc,
        ]);
    }
}
