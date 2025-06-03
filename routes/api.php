<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvestorAuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UmkmController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\ImageController;

Route::post('/register', [AuthController::class, 'register'])->name('umkm.register');
Route::post('/login', [AuthController::class, 'login'])->name('umkm.login');

Route::get('/images/{path}', [ImageController::class, 'show'])->where('path', '.*');
Route::options('/images/{path}', [ImageController::class, 'options'])->where('path', '.*');

Route::post('/investor/register', [InvestorAuthController::class, 'register'])->name('investor.register');
Route::post('/investor/login', [InvestorAuthController::class, 'login'])->name('investor.login');


Route::middleware(['auth:sanctum', 'ability:role:umkm'])->group(function () {
    Route::get('/user', [AuthController::class, 'user'])->name('umkm.auth.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('umkm.auth.logout');
    
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('umkm.auth.profile.update');

    Route::apiResource('transactions', TransactionController::class)->names('umkm.transactions');
    Route::apiResource('debts', DebtController::class)->names('umkm.debts');
    Route::patch('/debts/{debt}/verify', [DebtController::class, 'verifyAndRecordIncome'])->name('umkm.debts.verify');


    Route::get('/appointments/umkm', [AppointmentController::class, 'indexForUmkm'])->name('umkm.appointments.index');
    Route::put('/appointments/umkm/{appointment}/status', [AppointmentController::class, 'updateStatusForUmkm'])->name('umkm.appointments.updateStatus');
});

Route::middleware(['auth:sanctum', 'ability:role:investor'])->prefix('investor')->group(function () {
    Route::get('/user', [InvestorAuthController::class, 'user'])->name('investor.auth.user');
    Route::post('/logout', [InvestorAuthController::class, 'logout'])->name('investor.auth.logout');


    Route::get('/umkms', [UmkmController::class, 'index'])->name('investor.umkms.list');
    Route::get('/umkms/{umkmId}', [UmkmController::class, 'show'])->name('investor.umkms.show');

    Route::post('/investments', [InvestmentController::class, 'store'])->name('investor.investments.store');
    Route::get('/investments', [InvestmentController::class, 'index'])->name('investor.investments.index');
    Route::put('/investments/{investment}/confirm', [InvestmentController::class, 'confirmInvestment'])->name('investor.investments.confirm');

    Route::post('/appointments', [AppointmentController::class, 'store'])->name('investor.appointments.store');
    Route::get('/appointments', [AppointmentController::class, 'indexForInvestor'])->name('investor.appointments.index');
    Route::put('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatusForInvestor'])->name('investor.appointments.updateStatus');
});
