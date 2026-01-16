<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FRONTEND CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShopController;
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;
use App\Http\Controllers\Frontend\CategoryController as FrontendCategoryController;
use App\Http\Controllers\Frontend\CartController;

/*
|--------------------------------------------------------------------------
| USER (FRONTEND PROFILE)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| ADMIN CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\CustomerController;

/*
|--------------------------------------------------------------------------
| FRONTEND – PUBLIC
|--------------------------------------------------------------------------
*/

// 🏠 Trang chủ
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

// 🛍 Trang shop
Route::get('/shop', [ShopController::class, 'index'])
    ->name('shop');

// 📂 Trang danh mục
Route::get('/danh-muc/{slug}', [FrontendCategoryController::class, 'show'])
    ->name('category.show');

// 📦 Chi tiết sản phẩm  ✅ (ROUTE ĐÚNG ĐỂ DÙNG TRONG BLADE)
Route::get('/products/{slug}', [FrontendProductController::class, 'show'])
    ->name('products.show');

/*
|--------------------------------------------------------------------------
| CART
|--------------------------------------------------------------------------
*/

// 🛒 Giỏ hàng
Route::get('/cart', [CartController::class, 'index'])
    ->name('cart.index');

// ➕ Thêm sản phẩm vào giỏ
Route::post('/cart/add', [CartController::class, 'add'])
    ->name('cart.add');

// 🔄 Cập nhật số lượng
Route::post('/cart/update', [CartController::class, 'update'])
    ->name('cart.update');

// ❌ Xóa sản phẩm
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])
    ->name('cart.remove');

/*
|--------------------------------------------------------------------------
| FRONTEND – USER PROFILE (AUTH REQUIRED)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {

        // 👤 Thông tin cá nhân
        Route::get('/', [ProfileController::class, 'edit'])
            ->name('index');

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

        // 📦 Đơn hàng
        Route::get('/orders', function () {
            return view('frontend.profile.orders');
        })->name('orders');

        // 📍 Địa chỉ
        Route::get('/address', function () {
            return view('frontend.profile.address');
        })->name('address');
    });

/*
|--------------------------------------------------------------------------
| BACKEND – ADMIN (AUTH + IS_ADMIN)
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

        /* ================= CUSTOMERS ================= */
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