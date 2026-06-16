<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminBrandController;
use App\Http\Controllers\Api\Admin\AdminInventoryController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminCustomerController;
use App\Http\Controllers\Api\Admin\AdminCouponController;
use App\Http\Controllers\Api\Admin\AdminReviewController;
use App\Http\Controllers\Api\Admin\AdminReportController;
use App\Http\Controllers\Api\Admin\AdminSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/home', [HomeController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/brands', [CatalogController::class, 'brands']);
Route::get('/filters', [CatalogController::class, 'filters']);

/*
|--------------------------------------------------------------------------
| Authenticated routes (Sanctum token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile/dashboard', [ProfileController::class, 'dashboard']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);

    // Addresses
    Route::apiResource('addresses', AddressController::class)->except('show');

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{cart}', [CartController::class, 'update']);
    Route::delete('/cart/{cart}', [CartController::class, 'destroy']);
    Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{productId}', [WishlistController::class, 'destroy']);
    Route::post('/wishlist/{productId}/move-to-cart', [WishlistController::class, 'moveToCart']);

    // Orders / checkout
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Reviews
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Admin routes (Sanctum + IsAdmin middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);

    // Users
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::put('/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
    Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus']);

    // Products
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::get('/products/{product}', [AdminProductController::class, 'show']);
    Route::put('/products/{product}', [AdminProductController::class, 'update']);
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy']);
    Route::delete('/products/{product}/images/{image}', [AdminProductController::class, 'deleteImage']);

    // Categories
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::get('/categories/all', [AdminCategoryController::class, 'all']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);

    // Brands
    Route::get('/brands', [AdminBrandController::class, 'index']);
    Route::get('/brands/all', [AdminBrandController::class, 'all']);
    Route::post('/brands', [AdminBrandController::class, 'store']);
    Route::put('/brands/{brand}', [AdminBrandController::class, 'update']);
    Route::delete('/brands/{brand}', [AdminBrandController::class, 'destroy']);

    // Inventory
    Route::get('/inventory/low-stock', [AdminInventoryController::class, 'lowStock']);
    Route::get('/inventory/movements', [AdminInventoryController::class, 'movements']);
    Route::post('/inventory/stock-in', [AdminInventoryController::class, 'stockIn']);
    Route::post('/inventory/stock-out', [AdminInventoryController::class, 'stockOut']);
    Route::post('/inventory/adjust', [AdminInventoryController::class, 'adjust']);

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
    Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel']);
    Route::post('/orders/{order}/refund', [AdminOrderController::class, 'refund']);

    // Customers
    Route::get('/customers', [AdminCustomerController::class, 'index']);
    Route::get('/customers/{user}', [AdminCustomerController::class, 'show']);
    Route::get('/customers/{user}/orders', [AdminCustomerController::class, 'orders']);
    Route::post('/customers/{user}/toggle-status', [AdminCustomerController::class, 'toggleStatus']);

    // Coupons
    Route::get('/coupons', [AdminCouponController::class, 'index']);
    Route::post('/coupons', [AdminCouponController::class, 'store']);
    Route::put('/coupons/{coupon}', [AdminCouponController::class, 'update']);
    Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy']);

    // Reviews
    Route::get('/reviews', [AdminReviewController::class, 'index']);
    Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve']);
    Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject']);
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy']);

    // Reports
    Route::get('/reports/sales', [AdminReportController::class, 'sales']);
    Route::get('/reports/products/top', [AdminReportController::class, 'topProducts']);
    Route::get('/reports/products/low-stock', [AdminReportController::class, 'lowStock']);
    Route::get('/reports/customers/top', [AdminReportController::class, 'topCustomers']);
    Route::get('/reports/inventory/value', [AdminReportController::class, 'inventoryValue']);

    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'show']);
    Route::post('/settings', [AdminSettingsController::class, 'update']);

    // Profile (reuse existing)
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);
});
