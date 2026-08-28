<?php

use App\Http\Controllers\Api\V1\Admin\BoxOptionController as AdminBoxOptionController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Api\V1\Admin\CustomerController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\InventoryController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\V1\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\V1\Admin\ShippingMethodController as AdminShippingMethodController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\BuildABoxController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\Customer\AddressController;
use App\Http\Controllers\Api\V1\Customer\ProfileController;
use App\Http\Controllers\Api\V1\Customer\WishlistController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\ShippingMethodController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — versioned under /api/v1
|--------------------------------------------------------------------------
|
| Three trust levels:
|   - public:        no auth, rate-limited
|   - auth:sanctum:  any logged-in user (customer or admin)
|   - admin:         auth:sanctum + the 'admin' role, gated by EnsureUserIsAdmin
*/

Route::prefix('v1')->group(function () {

    // -- Auth ----------------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // -- Public catalogue ------------------------------------------------
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('build-a-box', [BuildABoxController::class, 'options']);
    Route::get('shipping-methods', [ShippingMethodController::class, 'index']);
    Route::get('settings', [SettingController::class, 'index']);
    Route::get('reviews', [ReviewController::class, 'index']);

    // Cart pricing preview — no auth required so a guest can see a live total.
    Route::post('cart/calculate', [CartController::class, 'calculate']);

    // Checkout: guest or authenticated (see StoreOrderRequest / OrderService — user_id is nullable).
    Route::post('checkout', [OrderController::class, 'store']);

    // -- Authenticated (customer) ---------------------------------------
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('reviews', [ReviewController::class, 'store']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::post('orders/{order}/reorder', [OrderController::class, 'reorderPayload']);

        Route::prefix('customer')->group(function () {
            Route::get('profile', [ProfileController::class, 'show']);
            Route::put('profile', [ProfileController::class, 'update']);

            Route::get('addresses', [AddressController::class, 'index']);
            Route::post('addresses', [AddressController::class, 'store']);
            Route::put('addresses/{address}', [AddressController::class, 'update']);
            Route::delete('addresses/{address}', [AddressController::class, 'destroy']);

            Route::get('wishlist', [WishlistController::class, 'index']);
            Route::post('wishlist', [WishlistController::class, 'store']);
            Route::delete('wishlist/{product}', [WishlistController::class, 'destroy']);
        });
    });

    // -- Admin ------------------------------------------------------------
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::apiResource('products', AdminProductController::class);
        Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'deleteImage']);
        Route::post('products/{product}/images/{image}/make-primary', [AdminProductController::class, 'makeImagePrimary']);
        Route::post('products/{product}/images/reorder', [AdminProductController::class, 'reorderImages']);
        Route::post('products/{product}/adjust-stock', [AdminProductController::class, 'adjustStock']);

        Route::apiResource('categories', AdminCategoryController::class)->except(['show']);

        Route::get('customers', [CustomerController::class, 'index']);
        Route::get('customers/{customer}', [CustomerController::class, 'show']);
        Route::put('customers/{customer}/status', [CustomerController::class, 'updateStatus']);

        Route::get('orders', [AdminOrderController::class, 'index']);
        Route::get('orders/{order}', [AdminOrderController::class, 'show']);
        Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);

        Route::apiResource('coupons', AdminCouponController::class);

        Route::get('reviews', [AdminReviewController::class, 'index']);
        Route::put('reviews/{review}/status', [AdminReviewController::class, 'updateStatus']);
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy']);

        Route::get('inventory', [InventoryController::class, 'index']);
        Route::get('inventory/{product}/history', [InventoryController::class, 'history']);

        Route::apiResource('shipping-methods', AdminShippingMethodController::class)->except(['show']);
        Route::apiResource('box-options', AdminBoxOptionController::class)->except(['show']);

        Route::get('settings', [AdminSettingController::class, 'index']);
        Route::put('settings', [AdminSettingController::class, 'update']);
    });
});
