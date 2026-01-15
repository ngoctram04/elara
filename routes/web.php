<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

// ================= FRONTEND =================
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShopController;

// ================= USER (FRONTEND PROFILE) =================
use App\Http\Controllers\ProfileController;

// ================= ADMIN =================
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\CustomerController;

/*
|--------------------------------------------------------------------------
| FRONTEND – USER
|--------------------------------------------------------------------------
*/

// 🏠 Trang chủ
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

// 🛍 Trang shop
Route::get('/shop', [ShopController::class, 'index'])
    ->name('shop');

/*
|--------------------------------------------------------------------------
| USER PROFILE (FRONTEND)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {

        // 👤 Thông tin tài khoản
        Route::get('/', [ProfileController::class, 'edit'])
            ->name('index');

        // ✏️ Cập nhật thông tin
        Route::patch('/', [ProfileController::class, 'update'])
            ->name('update');

        // 🖼 Avatar
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])
            ->name('avatar');

        // 🔐 Đổi mật khẩu
        Route::post('/password', [ProfileController::class, 'updatePassword'])
            ->name('password');

        // ❌ Xóa tài khoản
        Route::delete('/', [ProfileController::class, 'destroy'])
            ->name('destroy');

        // 📦 Đơn hàng (tạm)
        Route::get('/orders', function () {
            return view('frontend.profile.orders');
        })->name('orders');

        // 📍 Địa chỉ (tạm)
        Route::get('/address', function () {
            return view('frontend.profile.address');
        })->name('address');
    });

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

        /* ================= CUSTOMERS (CHỈ KHÁCH HÀNG) ================= */

        Route::get('/customers', [CustomerController::class, 'index'])
            ->name('customers.index');

        Route::get('/customers/{user}', [CustomerController::class, 'show'])
            ->name('customers.show');

        Route::post('/customers/{user}/toggle-status', [CustomerController::class, 'toggleStatus'])
            ->name('customers.toggle-status');

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

        /* ================= PROMOTIONS ================= */

        Route::get('promotions', [PromotionController::class, 'index'])
            ->name('promotions.index');

        Route::get('promotions/create/type', [PromotionController::class, 'chooseType'])
            ->name('promotions.choose');

        Route::get('promotions/create/product', [PromotionController::class, 'createProduct'])
            ->name('promotions.create.product');

        Route::get('promotions/create/order', [PromotionController::class, 'createOrder'])
            ->name('promotions.create.order');

        Route::post('promotions', [PromotionController::class, 'store'])
            ->name('promotions.store');

        Route::get('promotions/{promotion}/edit', [PromotionController::class, 'edit'])
            ->name('promotions.edit');

        Route::get('promotions/{promotion}/edit/product', [PromotionController::class, 'editProduct'])
            ->name('promotions.edit.product');

        Route::get('promotions/{promotion}/edit/order', [PromotionController::class, 'editOrder'])
            ->name('promotions.edit.order');

        Route::put('promotions/{promotion}', [PromotionController::class, 'update'])
            ->name('promotions.update');

        Route::patch('promotions/{promotion}/toggle', [PromotionController::class, 'toggle'])
            ->name('promotions.toggle');
    });

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';