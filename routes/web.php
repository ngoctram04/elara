<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

// Frontend
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShopController;

// Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;

// User profile
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| FRONTEND
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/shop', [ShopController::class, 'index'])
    ->middleware('auth')
    ->name('shop');

/*
|--------------------------------------------------------------------------
| BACKEND – ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'is_admin'])
    ->group(function () {

        /* ================= DASHBOARD ================= */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        /* ================= ADMIN PROFILE ================= */
        Route::get('/profile', [AdminProfileController::class, 'show'])
            ->name('profile.show');

        Route::get('/profile/edit', [AdminProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('/profile', [AdminProfileController::class, 'update'])
            ->name('profile.update');

        /* ================= CATEGORIES ================= */
        Route::resource('categories', CategoryController::class)->only([
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
        ]);

        /* ================= BRANDS ================= */
        Route::resource('brands', BrandController::class)->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
        ]);

        /* ================= PRODUCTS ================= */
        Route::resource('products', ProductController::class)->only([
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
        ]);

        /* =================================================
           PROMOTIONS – QUẢN LÝ KHUYẾN MÃI (TÁCH LOẠI)
        ================================================= */

        // 1️⃣ Danh sách
        Route::get('promotions', [PromotionController::class, 'index'])
            ->name('promotions.index');

        // 2️⃣ Chọn loại khuyến mãi
        Route::get('promotions/create/type', [PromotionController::class, 'chooseType'])
            ->name('promotions.choose');

        // 3️⃣ Tạo khuyến mãi SẢN PHẨM
        Route::get('promotions/create/product', [PromotionController::class, 'createProduct'])
            ->name('promotions.create.product');

        // 4️⃣ Tạo mã giảm giá ĐƠN HÀNG
        Route::get('promotions/create/order', [PromotionController::class, 'createOrder'])
            ->name('promotions.create.order');

        // 5️⃣ Lưu khuyến mãi (POST – dùng chung)
        Route::post('promotions', [PromotionController::class, 'store'])
            ->name('promotions.store');

        // 6️⃣ Redirect edit (tự phân loại theo type)
        Route::get('promotions/{promotion}/edit', [PromotionController::class, 'edit'])
            ->name('promotions.edit');

        // 6.1️⃣ Edit khuyến mãi SẢN PHẨM
        Route::get(
            'promotions/{promotion}/edit/product',
            [PromotionController::class, 'editProduct']
        )->name('promotions.edit.product');

        // 6.2️⃣ Edit khuyến mãi ĐƠN HÀNG
        Route::get(
            'promotions/{promotion}/edit/order',
            [PromotionController::class, 'editOrder']
        )->name('promotions.edit.order');

        // 7️⃣ Cập nhật (PUT – dùng chung)
        Route::put('promotions/{promotion}', [PromotionController::class, 'update'])
            ->name('promotions.update');

        // 8️⃣ Bật / tắt nhanh
        Route::patch(
            'promotions/{promotion}/toggle',
            [PromotionController::class, 'toggle']
        )->name('promotions.toggle');
    });

/*
|--------------------------------------------------------------------------
| USER PROFILE (DEFAULT LARAVEL)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';