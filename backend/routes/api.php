<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PromoCodeController;
use App\Http\Controllers\Api\LiveFeedController;
use App\Http\Controllers\Api\StatisticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    
    // Authentication
    Route::post('/auth/steam', [AuthController::class, 'steamLogin']);
    Route::post('/auth/steam/callback', [AuthController::class, 'steamCallback']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    
    // Public data
    Route::get('/cases', [CaseController::class, 'index']);
    Route::get('/cases/{case}', [CaseController::class, 'show']);
    Route::get('/cases/{case}/items', [CaseController::class, 'getItems']);
    
    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/{item}', [ItemController::class, 'show']);
    
    // Live feed
    Route::get('/live-feed', [LiveFeedController::class, 'index']);
    Route::get('/live-feed/recent-openings', [LiveFeedController::class, 'recentOpenings']);
    Route::get('/live-feed/big-wins', [LiveFeedController::class, 'bigWins']);
    
    // Statistics
    Route::get('/statistics/global', [StatisticsController::class, 'global']);
    Route::get('/statistics/cases', [StatisticsController::class, 'cases']);
    
    // Promo codes (public check)
    Route::post('/promo-codes/check', [PromoCodeController::class, 'check']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // User management
        Route::get('/user/profile', [UserController::class, 'profile']);
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::get('/user/statistics', [UserController::class, 'statistics']);
        Route::get('/user/activity', [UserController::class, 'activity']);
        Route::post('/user/update-trade-url', [UserController::class, 'updateTradeUrl']);
        
        // Case opening
        Route::post('/cases/{case}/open', [CaseController::class, 'openCase']);
        Route::post('/cases/{case}/multi-open', [CaseController::class, 'multiOpen']);
        Route::get('/cases/{case}/can-open', [CaseController::class, 'canOpen']);
        Route::get('/cases/{case}/simulate', [CaseController::class, 'simulate']);
        
        // Inventory
        Route::get('/inventory', [InventoryController::class, 'index']);
        Route::get('/inventory/{item}', [InventoryController::class, 'show']);
        Route::post('/inventory/{item}/withdraw', [InventoryController::class, 'withdraw']);
        Route::post('/inventory/{item}/sell', [InventoryController::class, 'sell']);
        Route::get('/inventory/withdrawals', [InventoryController::class, 'withdrawals']);
        Route::get('/inventory/sales', [InventoryController::class, 'sales']);
        
        // Payments
        Route::get('/payments/methods', [PaymentController::class, 'getMethods']);
        Route::post('/payments/deposit', [PaymentController::class, 'createDeposit']);
        Route::get('/payments/deposits', [PaymentController::class, 'getDeposits']);
        Route::post('/payments/withdraw', [PaymentController::class, 'createWithdrawal']);
        Route::get('/payments/withdrawals', [PaymentController::class, 'getWithdrawals']);
        Route::get('/payments/{payment}/status', [PaymentController::class, 'getStatus']);
        
        // Promo codes
        Route::post('/promo-codes/use', [PromoCodeController::class, 'use']);
        Route::get('/promo-codes/history', [PromoCodeController::class, 'history']);
        
        // Case opening history
        Route::get('/history/openings', [CaseController::class, 'getOpenings']);
        Route::get('/history/openings/{opening}', [CaseController::class, 'getOpening']);
        Route::get('/history/openings/{opening}/verify', [CaseController::class, 'verifyOpening']);
        
        // Referrals
        Route::get('/referrals', [UserController::class, 'getReferrals']);
        Route::get('/referrals/statistics', [UserController::class, 'getReferralStatistics']);
        
        // User statistics
        Route::get('/user/openings/statistics', [StatisticsController::class, 'userOpenings']);
        Route::get('/user/profit/statistics', [StatisticsController::class, 'userProfit']);
        
        // Achievements
        Route::get('/achievements', [UserController::class, 'getAchievements']);
        Route::get('/achievements/progress', [UserController::class, 'getAchievementProgress']);
        
    });
    
    // Admin routes
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [StatisticsController::class, 'adminDashboard']);
        
        // User management
        Route::get('/users', [UserController::class, 'adminIndex']);
        Route::get('/users/{user}', [UserController::class, 'adminShow']);
        Route::put('/users/{user}', [UserController::class, 'adminUpdate']);
        Route::post('/users/{user}/ban', [UserController::class, 'ban']);
        Route::post('/users/{user}/unban', [UserController::class, 'unban']);
        Route::post('/users/{user}/balance', [UserController::class, 'adjustBalance']);
        
        // Case management
        Route::apiResource('cases', CaseController::class)->except(['index', 'show']);
        Route::post('/cases/{case}/items', [CaseController::class, 'addItem']);
        Route::put('/cases/{case}/items/{item}', [CaseController::class, 'updateItem']);
        Route::delete('/cases/{case}/items/{item}', [CaseController::class, 'removeItem']);
        
        // Item management
        Route::apiResource('items', ItemController::class)->except(['index', 'show']);
        Route::post('/items/import', [ItemController::class, 'import']);
        Route::post('/items/{item}/update-prices', [ItemController::class, 'updatePrices']);
        
        // Analytics
        Route::get('/analytics/revenue', [StatisticsController::class, 'revenue']);
        Route::get('/analytics/users', [StatisticsController::class, 'users']);
        Route::get('/analytics/cases', [StatisticsController::class, 'caseAnalytics']);
        Route::get('/analytics/items', [StatisticsController::class, 'itemAnalytics']);
        
        // System
        Route::get('/system/status', [StatisticsController::class, 'systemStatus']);
        Route::post('/system/cache/clear', [StatisticsController::class, 'clearCache']);
        
    });
    
});

// Webhooks (no authentication)
Route::prefix('webhooks')->group(function () {
    Route::post('/payment/qiwi', [PaymentController::class, 'qiwiWebhook']);
    Route::post('/payment/yoomoney', [PaymentController::class, 'yoomoneyWebhook']);
    Route::post('/payment/crypto', [PaymentController::class, 'cryptoWebhook']);
    Route::post('/steam/trade', [InventoryController::class, 'steamTradeWebhook']);
});