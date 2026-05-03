<?php

use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\EmployeeLoginController;
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
Route::get('/register',  [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

Route::get('/login',  [CustomerLoginController::class, 'index'])->name('login');
Route::post('/login', [CustomerLoginController::class, 'login'])->name('login.post');

Route::post('/logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');

//  Customer portal (authenticated customers only) 
Route::middleware(['auth', 'role:customer', 'session.validate'])->group(function () {
    Route::get('/dashboard', [PaymentController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/payment', [PaymentController::class, 'showPayment'])->name('customer.payment');
    Route::post('/payment', [PaymentController::class, 'submitPayment'])->name('customer.payment.post');
    Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('customer.payment.success');
});

//  Employee portal (authenticated employees only) 
Route::middleware(['auth', 'role:employee', 'session.validate'])->group(function () {
    Route::get('/employee/dashboard',            [EmployeeController::class, 'dashboard'])->name('employee.dashboard');
    Route::post('/employee/transactions/{id}/verify', [EmployeeController::class, 'verify'])->name('employee.verify');
    Route::post('/employee/submit-swift',        [EmployeeController::class, 'submitToSwift'])->name('employee.submit');
});

// Employee login (separate endpoint)
Route::get('/employee/login',  [EmployeeLoginController::class, 'index'])->name('employee.login');
Route::post('/employee/login', [EmployeeLoginController::class, 'login'])->name('employee.login.post');
