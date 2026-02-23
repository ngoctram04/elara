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
use App\Http\Controllers\Frontend\OrderController;

/*
|--------------------------------------------------------------------------
| USER PROFILE
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
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

/*
|--------------------------------------------------------------------------
| FRONTEND – PUBLIC
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Shop
Route::get('/products', [ShopController::class, 'index'])->name('shop');

// Category
Route::get('/category/{slug}', [FrontendCategoryController::class, 'show'])
    ->name('category.show');

// Product detail
Route::get('/product/{slug}', [FrontendProductController::class, 'show'])
    ->name('products.show');


/*
|--------------------------------------------------------------------------
| VNPAY RETURN (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::get('/vnpay-return', [CheckoutController::class, 'vnpayReturn'])
    ->name('vnpay.return');


/*
|--------------------------------------------------------------------------
| CART (KHÔNG CẦN LOGIN)
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->name('cart.')->group(function () {

    Route::get('/', [CartController::class, 'index'])->name('index');

    Route::post('/add', [CartController::class, 'add'])->name('add');

    Route::post('/change-qty', [CartController::class, 'changeQty'])
        ->name('changeQty');

    Route::post('/change-variant', [CartController::class, 'changeVariant'])
        ->name('changeVariant');

    Route::delete('/remove/{variantId}', [CartController::class, 'remove'])
        ->name('remove');

    Route::delete('/clear', [CartController::class, 'clear'])
        ->name('clear');
});


/*
|--------------------------------------------------------------------------
| CHECKOUT (LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
    ->prefix('checkout')
    ->name('checkout.')
    ->group(function () {

        Route::get('/', [CheckoutController::class, 'index'])
            ->name('index');

        Route::post('/', [CheckoutController::class, 'store'])
            ->name('store');

        Route::get('/success/{order}', [CheckoutController::class, 'success'])
            ->name('success');

        Route::post('/cancel/{id}', [CheckoutController::class, 'cancel'])
            ->name('cancel');
    });


/*
|--------------------------------------------------------------------------
| ORDER HISTORY (LOGIN)  ⭐ QUAN TRỌNG
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check_active'])
    ->prefix('orders')
    ->name('orders.')
    ->group(function () {

        // Danh sách đơn của user
        Route::get('/', [OrderController::class, 'index'])
            ->name('history');

        // Chi tiết đơn
        Route::get('/{id}', [OrderController::class, 'show'])
            ->name('show');
    });


/*
|--------------------------------------------------------------------------
| USER PROFILE
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

        Route::get('/address', fn() => view('frontend.profile.address'))
            ->name('address');
    });


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
->name('admin.')
->middleware(['auth', 'check_active', 'is_admin'])
->group(function () {

    /*
        |--------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------
        */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
        |--------------------------------------------------
        | PROFILE
        |--------------------------------------------------
        */
    Route::get('/profile', [AdminProfileController::class, 'show'])
        ->name('profile.show');

    Route::get('/profile/edit', [AdminProfileController::class, 'edit'])
    ->name('profile.edit');

    Route::put('/profile', [AdminProfileController::class, 'update'])
    ->name('profile.update');

    /*
        |--------------------------------------------------
        | CUSTOMERS
        |--------------------------------------------------
        */
    Route::get('/customers', [CustomerController::class, 'index'])
        ->name('customers.index');

    Route::get('/customers/{user}', [CustomerController::class, 'show'])
    ->name('customers.show');

    Route::post(
        '/customers/{user}/toggle-status',
        [CustomerController::class, 'toggleStatus']
    )->name('customers.toggle-status');

    /*
        |--------------------------------------------------
        | CATEGORIES
        |--------------------------------------------------
        */
    Route::resource('categories', CategoryController::class)
        ->except(['update']);

    Route::put(
        'categories/{category}',
        [CategoryController::class, 'update']
    )->name('categories.update');

    /*
        |--------------------------------------------------
        | BRANDS
        |--------------------------------------------------
        */
    Route::resource('brands', BrandController::class)
    ->except(['show']);

    /*
        |--------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------
        */
    Route::resource('products', ProductController::class);

    /*
        |--------------------------------------------------
        | ORDERS  🔥 QUẢN LÝ ĐƠN HÀNG
        |--------------------------------------------------
        */
    Route::prefix('orders')->name('orders.')->group(function () {

        // Danh sách đơn
        Route::get('/', [AdminOrderController::class,
            'index'
        ])
            ->name('index');

        // Chi tiết đơn
        Route::get('/{id}', [AdminOrderController::class, 'show'])
        ->name('show');

        // Cập nhật trạng thái
        Route::post('/update-status/{id}', [AdminOrderController::class, 'updateStatus'])
        ->name('updateStatus');
    });

    /*
        |--------------------------------------------------
        | PROMOTIONS
        |--------------------------------------------------
        */
    Route::resource('promotions', PromotionController::class)
        ->except(['show']);

    /*
        |--------------------------------------------------
        | STOCK IMPORT
        |--------------------------------------------------
        */
    Route::get('/stock-import', [StockImportController::class, 'create'])
    ->name('stock.create');

    Route::post('/stock-import', [StockImportController::class, 'store'])
    ->name('stock.store');

    Route::get('/stock-import/history', [StockImportController::class, 'history'])
    ->name('stock.history');
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';