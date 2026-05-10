<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\BankTransferController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SettlementController;
use App\Http\Controllers\Api\JournalVoucherController;
use App\Http\Controllers\Api\CustomAccountController;
use App\Http\Controllers\Api\SystemSettingController;

// Public auth routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/password', [AuthController::class, 'changePassword']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // All modules
    Route::apiResource('contacts', ContactController::class);
    Route::apiResource('bank-accounts', BankAccountController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('incomes', IncomeController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('bank-transfers', BankTransferController::class);
    Route::apiResource('loans', LoanController::class);
    Route::get('/items/generate-sku', [ItemController::class, 'generateSku']);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('sales', SaleController::class);
    Route::apiResource('settlements', SettlementController::class);
    Route::apiResource('journal-vouchers', JournalVoucherController::class);
    Route::apiResource('custom-accounts', CustomAccountController::class);
    Route::get('/system-settings', [SystemSettingController::class, 'index']);
    Route::post('/system-settings', [SystemSettingController::class, 'store']);
    Route::put('/system-settings/{systemSetting}', [SystemSettingController::class, 'update']);
    Route::delete('/system-settings/{systemSetting}', [SystemSettingController::class, 'destroy']);
});
