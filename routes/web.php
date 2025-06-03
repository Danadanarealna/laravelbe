<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InvestorController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\InvestmentController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\DebtController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login.form');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('register', [LoginController::class, 'showRegistrationForm'])->name('register.form');
    Route::post('register', [LoginController::class, 'register'])->name('register.submit');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::resource('users', UserController::class);
        Route::resource('investors', InvestorController::class);
        Route::resource('transactions', TransactionController::class);
        
        Route::resource('investments', InvestmentController::class);
        Route::patch('investments/{investment}/confirm', [InvestmentController::class, 'confirm'])->name('investments.confirm');
        
        Route::resource('appointments', AppointmentController::class);
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
        
        Route::resource('debts', DebtController::class);
        Route::patch('debts/{debt}/verify', [DebtController::class, 'verify'])->name('debts.verify');
    });
});

Route::get('admin', function() {
    return redirect()->route('admin.login.form');
})->middleware('guest');


Route::get('/storage/{path}', function ($path) {
    // Check if file exists
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    
    // Get file contents and mime type
    $file = Storage::disk('public')->get($path);
    $mimeType = Storage::disk('public')->mimeType($path);
    
    // Create response with CORS headers
    $response = Response::make($file, 200, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=3600',
    ]);
    
    // Add CORS headers
    $response->header('Access-Control-Allow-Origin', '*');
    $response->header('Access-Control-Allow-Methods', 'GET, HEAD, OPTIONS');
    $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
    $response->header('Access-Control-Allow-Credentials', 'true');
    
    return $response;
})->where('path', '.*')->name('storage.public');

// Handle OPTIONS requests for CORS preflight
Route::options('/storage/{path}', function () {
    return Response::make('', 200, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
        'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Requested-With',
        'Access-Control-Allow-Credentials' => 'true',
    ]);
})->where('path', '.*');