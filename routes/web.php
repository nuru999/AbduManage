<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\MpesaCallbackController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

// Safaricom -> us (public, protected by secret token in the URL)
Route::post('/mpesa/callback/{token}', MpesaCallbackController::class)->name('mpesa.callback');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin + teachers
    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    });

    // Admin only (declared before the read-only routes so /create wins over /{id})
    Route::middleware('role:admin')->group(function () {
        Route::resource('students', StudentController::class)->except(['index', 'show']);
        Route::resource('teachers', TeacherController::class)->except(['show']);
        Route::resource('classes', SchoolClassController::class)
            ->parameters(['classes' => 'class'])->except(['index', 'show']);

        Route::post('/students/{student}/fees', [FeeController::class, 'store'])->name('fees.store');
        Route::delete('/fees/{fee}', [FeeController::class, 'destroy'])->name('fees.destroy');

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/status', [PaymentController::class, 'status'])->name('payments.status');
    });

    // Read-only for admin + teachers
    Route::middleware('role:admin,teacher')->group(function () {
        Route::resource('students', StudentController::class)->only(['index', 'show']);
        Route::resource('classes', SchoolClassController::class)
            ->parameters(['classes' => 'class'])->only(['index', 'show']);
    });
});
