<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ProductCsvController as AdminProductCsvController;
use App\Http\Controllers\Api\Admin\LogController as AdminLogController;
use App\Http\Controllers\Api\Admin\AdminLogController as AdminActionLogController;
use App\Http\Controllers\Api\TelegramAuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\InviteController;
use App\Http\Controllers\Api\B2BOrderController;

// Публичные API маршруты
Route::post('/auth/telegram', [AuthController::class, 'telegram']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::post('/orders', [OrderController::class, 'store']);

// Telegram B2B Auth routes
Route::prefix('b2b')->group(function () {
    // Public routes
    Route::post('/auth/telegram', [TelegramAuthController::class, 'authenticate']);
    Route::get('/invites/validate', [InviteController::class, 'validateToken']);
    
    // Protected routes
    Route::middleware('jwt.auth')->group(function () {
        Route::post('/auth/role', [TelegramAuthController::class, 'setRole']);
        Route::get('/auth/me', [TelegramAuthController::class, 'me']);
        
        // Organization routes
        Route::get('/organization', [OrganizationController::class, 'show']);
        Route::post('/organization', [OrganizationController::class, 'store']);
        Route::put('/organization', [OrganizationController::class, 'update']);
        Route::post('/organization/invite', [OrganizationController::class, 'generateInvite']);
        
        // Invite routes
        Route::post('/invites/join', [InviteController::class, 'join']);
        
        // Order routes
        Route::get('/orders', [B2BOrderController::class, 'index']);
        Route::post('/orders', [B2BOrderController::class, 'store']);
        Route::get('/orders/{order}', [B2BOrderController::class, 'show']);
        Route::post('/orders/{order}/items', [B2BOrderController::class, 'addItem']);
        Route::delete('/orders/{order}/items/{item}', [B2BOrderController::class, 'deleteItem']);
        
        // Buyer-only routes
        Route::middleware('role:buyer')->group(function () {
            Route::post('/orders/{order}/submit', [B2BOrderController::class, 'submit']);
        });
    });
});

// Административные API маршруты
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);
    
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::put('/products/{product}', [AdminProductController::class, 'update']);
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy']);
    
    // CSV операции с товарами
    Route::get('/products/csv/export', [AdminProductCsvController::class, 'export']);
    Route::post('/products/csv/import', [AdminProductCsvController::class, 'import']);
    Route::get('/products/csv/template', [AdminProductCsvController::class, 'getTemplate']);
    
        // Логи
        Route::get('/logs', [AdminLogController::class, 'index']);
        Route::delete('/logs', [AdminLogController::class, 'clear']);
        Route::get('/logs/download', [AdminLogController::class, 'download']);
        
        // Заказы
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy']);
        
        // Логи администратора
        Route::get('/admin-logs', [AdminActionLogController::class, 'index']);
});
