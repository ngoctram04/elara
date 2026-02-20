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
use App\Http\Controllers\Frontend\CheckoutController;

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
use App\Http\Controllers\Admin\StockImportController;
/*
|--------------------------------------------------------------------------
| FRONTEND – PUBLIC
|--------------------------------------------------------------------------
*/

// 🏠 Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// 🛍 Shop
Route::get('/products', [ShopController::class, 'index'])->name('shop');

// 📂 Category
Route::get('/category/{slug}', [FrontendCategoryController::class, 'show'])
    ->name('category.show');

// 📦 Product detail
Route::get('/product/{slug}', [FrontendProductController::class, 'show'])
    ->name('products.show');

/*
|--------------------------------------------------------------------------
| CART (PUBLIC – KHÔNG CẦN LOGIN)
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->name('cart.')->group(function () {

    // 📄 Trang giỏ hàng
    Route::get('/', [CartController::class, 'index'])
        ->name('index');

    // ➕ Thêm vào giỏ (form + ajax)
    Route::post('/add', [CartController::class, 'add'])
        ->name('add');

    // ✏️ Cập nhật số lượng (input)
    Route::post('/update', [CartController::class, 'update'])
        ->name('update');

    // ➕➖ Tăng / giảm số lượng (AJAX + −)
    Route::post('/change-qty', [CartController::class, 'changeQty'])
        ->name('changeQty');

    // 🔁 Đổi biến thể trong giỏ
    Route::post('/change-variant', [CartController::class, 'changeVariant'])
        ->name('changeVariant');

    // ❌ Xóa 1 sản phẩm
    Route::delete('/remove/{variantId}', [CartController::class, 'remove'])
        ->name('remove');

    // 🧹 Xóa toàn bộ giỏ
    Route::delete('/clear', [CartController::class, 'clear'])
        ->name('clear');
});

/*
|--------------------------------------------------------------------------
| CHECKOUT (FRONTEND – REQUIRE LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
    ->prefix('checkout')
    ->name('checkout.')
    ->group(function () {

        // 🧾 Trang checkout
        Route::get('/', [CheckoutController::class, 'index'])
            ->name('index');

        // 💳 Đặt hàng
        Route::post('/', [CheckoutController::class, 'store'])
            ->name('store');
    });

/*
|--------------------------------------------------------------------------
| FRONTEND – USER PROFILE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {

        Route::get('/', [ProfileController::class, 'edit'])
            ->name('index');

        Route::patch('/', [ProfileController::class, 'update'])
            ->name('update');

        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])
            ->name('avatar');

        Route::post('/password', [ProfileController::class, 'updatePassword'])
            ->name('password');

        Route::delete('/', [ProfileController::class, 'destroy'])
            ->name('destroy');

        Route::get('/orders', fn() => view('frontend.profile.orders'))
            ->name('orders');

        Route::get('/address', fn() => view('frontend.profile.address'))
            ->name('address');
    });

/*
|--------------------------------------------------------------------------
| BACKEND – ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'check_active', 'is_admin'])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // 👤 Admin profile
        Route::get('/profile', [AdminProfileController::class, 'show'])
            ->name('profile.show');

        Route::get('/profile/edit', [AdminProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('/profile', [AdminProfileController::class, 'update'])
            ->name('profile.update');

        // 👥 Customers
        Route::get('/customers', [CustomerController::class, 'index'])
            ->name('customers.index');

        Route::get('/customers/{user}', [CustomerController::class, 'show'])
            ->name('customers.show');

        Route::post(
            '/customers/{user}/toggle-status',
            [CustomerController::class, 'toggleStatus']
        )->name('customers.toggle-status');

        // 📂 Categories
        Route::resource('categories', CategoryController::class)
            ->except(['update']);

        Route::put(
            'categories/{category}',
            [CategoryController::class, 'update']
        )->name('categories.update');

        // 🏷 Brands
        Route::resource('brands', BrandController::class)
            ->except(['show']);

        // 📦 Products
        Route::resource('products', ProductController::class);

        // 🎯 Promotions
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
    Route::get('/stock-import', [StockImportController::class, 'create'])
        ->name('stock.create');

    Route::post('/stock-import', [StockImportController::class, 'store'])
        ->name('stock.store');

    Route::get('/stock-import/history', [StockImportController::class, 'history'])
    ->name('stock.history');
    });

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';