<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\Paginator;

use App\Models\Category;
use App\Models\Cart;
use App\Models\Wishlist;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * =======================================
         * FIX PAGINATION (Bootstrap 5)
         * =======================================
         */
        Paginator::useBootstrapFive();


        /**
         * =======================================
         * SHARE MEGA MENU CATEGORIES
         * =======================================
         */
        View::composer('components.mega-menu', function ($view) {

            $categories = Category::whereNull('parent_id')
                ->with([
                    'children' => function ($q) {
                        $q->withCount('products')
                            ->orderBy('name');
                    }
                ])
                ->orderBy('name')
                ->get();

            $view->with('categories', $categories);
        });


        /**
         * =======================================
         * SHARE GLOBAL DATA (FRONTEND)
         * cartCount + favorites
         * =======================================
         */
        View::composer('*', function ($view) {

            $cartCount = 0;
            $favorites = [];

            if (Auth::check()) {

                $userId = Auth::id();

                // Tổng số lượng trong cart
                $cartCount = Cart::where('user_id', $userId)
                    ->sum('quantity');

                // Danh sách product đã yêu thích
                $favorites = Wishlist::where('user_id', $userId)
                    ->pluck('product_id')
                    ->map(fn($id) => (int) $id)
                    ->toArray();
            } else {
                // Cart của khách (session)
                $cart = Session::get('cart', []);
                $cartCount = collect($cart)->sum('quantity');
            }

            $view->with([
                'cartCount' => $cartCount,
                'favorites' => $favorites
            ]);
        });
    }
}