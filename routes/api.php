<?php

use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\DeliveryController;
use App\Http\Controllers\Api\V1\Admin\FarmController;
use App\Http\Controllers\Api\V1\Admin\FarmerController;
use App\Http\Controllers\Api\V1\Admin\HarvestController;
use App\Http\Controllers\Api\V1\Admin\InventoryController;
use App\Http\Controllers\Api\V1\Admin\SourcingController;
use App\Http\Controllers\Api\V1\Admin\WarehouseController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\FarmerPortalController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| UTHANO REST API - Version 1
| "From Farm to Family"
|
*/

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'UTHANO API',
        'version' => 'v1',
    ]);
});

Route::prefix('v1')->group(function () {

    // ============================================
    // PUBLIC ROUTES
    // ============================================

    // Auth
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login'])->name('login');

    // Products (public catalog)
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);

    // ============================================
    // AUTHENTICATED ROUTES
    // ============================================

    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // Customer profile
        Route::get('customer/profile', [CustomerController::class, 'profile']);
        Route::put('customer/profile', [CustomerController::class, 'updateProfile']);

        // Customer addresses
        Route::get('customer/addresses', [CustomerController::class, 'addresses']);
        Route::post('customer/addresses', [CustomerController::class, 'storeAddress']);
        Route::put('customer/addresses/{id}', [CustomerController::class, 'updateAddress']);
        Route::delete('customer/addresses/{id}', [CustomerController::class, 'destroyAddress']);

        // Cart
        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart/items', [CartController::class, 'storeItem']);
        Route::put('cart/items/{id}', [CartController::class, 'updateItem']);
        Route::delete('cart/items/{id}', [CartController::class, 'destroyItem']);
        Route::delete('cart', [CartController::class, 'destroy']);

        // Orders
        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);

        // Farmer portal (farmers view their own data)
        Route::prefix('farmer')->middleware('role:farmer')->group(function () {
            Route::get('dashboard', [FarmerPortalController::class, 'dashboard']);
            Route::get('farms', [FarmerPortalController::class, 'farms']);
            Route::get('harvests', [FarmerPortalController::class, 'harvests']);
            Route::get('sourcing-records', [FarmerPortalController::class, 'sourcingRecords']);
            Route::get('earnings', [FarmerPortalController::class, 'earnings']);
        });

        // ============================================
        // ADMIN ROUTES
        // ============================================

        Route::prefix('admin')->middleware('role:admin,staff,warehouse_manager')->group(function () {

            // Dashboard
            Route::get('dashboard', [DashboardController::class, 'summary']);

            // Products (admin CRUD)
            Route::post('products', [ProductController::class, 'store']);
            Route::put('products/{id}', [ProductController::class, 'update']);
            Route::delete('products/{id}', [ProductController::class, 'destroy']);

            // Farmers
            Route::get('farmers', [FarmerController::class, 'index']);
            Route::post('farmers', [FarmerController::class, 'store']);
            Route::get('farmers/{id}', [FarmerController::class, 'show']);
            Route::put('farmers/{id}', [FarmerController::class, 'update']);
            Route::delete('farmers/{id}', [FarmerController::class, 'destroy']);

            // Farms
            Route::get('farms', [FarmController::class, 'index']);
            Route::post('farms', [FarmController::class, 'store']);
            Route::get('farms/{id}', [FarmController::class, 'show']);
            Route::put('farms/{id}', [FarmController::class, 'update']);
            Route::delete('farms/{id}', [FarmController::class, 'destroy']);

            // Harvests
            Route::get('harvests', [HarvestController::class, 'index']);
            Route::post('harvests', [HarvestController::class, 'store']);
            Route::get('harvests/{id}', [HarvestController::class, 'show']);
            Route::put('harvests/{id}', [HarvestController::class, 'update']);
            Route::delete('harvests/{id}', [HarvestController::class, 'destroy']);

            // Sourcing
            Route::get('sourcing-records', [SourcingController::class, 'index']);
            Route::post('sourcing-records', [SourcingController::class, 'store']);
            Route::get('sourcing-records/{id}', [SourcingController::class, 'show']);
            Route::post('sourcing-records/{id}/receive', [SourcingController::class, 'receive']);
            Route::delete('sourcing-records/{id}', [SourcingController::class, 'destroy']);

            // Warehouses
            Route::get('warehouses', [WarehouseController::class, 'index']);
            Route::post('warehouses', [WarehouseController::class, 'store']);
            Route::get('warehouses/{id}', [WarehouseController::class, 'show']);
            Route::put('warehouses/{id}', [WarehouseController::class, 'update']);
            Route::delete('warehouses/{id}', [WarehouseController::class, 'destroy']);

            // Inventory
            Route::get('inventory', [InventoryController::class, 'index']);
            Route::get('inventory/{id}', [InventoryController::class, 'show']);
            Route::get('inventory/{id}/movements', [InventoryController::class, 'movements']);
            Route::post('inventory/{id}/adjust', [InventoryController::class, 'adjust']);
            Route::post('inventory/transfer', [InventoryController::class, 'transfer']);
            Route::get('traceability/{orderItemId}', [InventoryController::class, 'traceability']);

            // Deliveries
            Route::get('deliveries', [DeliveryController::class, 'index']);
            Route::post('deliveries', [DeliveryController::class, 'store']);
            Route::get('deliveries/{id}', [DeliveryController::class, 'show']);
            Route::post('deliveries/{id}/assign', [DeliveryController::class, 'assign']);
            Route::post('deliveries/{id}/status', [DeliveryController::class, 'updateStatus']);

            // Orders (admin)
            Route::post('orders/{id}/confirm', [OrderController::class, 'confirm']);
            Route::post('orders/{id}/status', [OrderController::class, 'updateStatus']);
        });
    });
});