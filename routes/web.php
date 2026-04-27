<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — International Payments Portal
|--------------------------------------------------------------------------
*/

// ── Public routes  ──────
Route::get('/', fn () => redirect()->route('login'));

// Customer auth
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

//  Customer portal (authenticated customers only) 
Route::middleware(['auth', 'role:customer', 'session.validate'])->group(function () {
    Route::get('/dashboard',       [PaymentController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/payment',         [PaymentController::class, 'showPayment'])->name('customer.payment');
    Route::post('/payment',        [PaymentController::class, 'submitPayment'])->name('customer.payment.post');
    Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('customer.payment.success');
});

//  Employee portal (authenticated employees only) 
Route::middleware(['auth', 'role:employee', 'session.validate'])->group(function () {
    Route::get('/employee/dashboard',            [EmployeeController::class, 'dashboard'])->name('employee.dashboard');
    Route::post('/employee/transactions/{id}/verify', [EmployeeController::class, 'verify'])->name('employee.verify');
    Route::post('/employee/submit-swift',        [EmployeeController::class, 'submitToSwift'])->name('employee.submit');
});

// Employee login (separate endpoint)
Route::get('/employee/login',  [AuthController::class, 'showEmployeeLogin'])->name('employee.login');
Route::post('/employee/login', [AuthController::class, 'employeeLogin'])->name('employee.login.post');
