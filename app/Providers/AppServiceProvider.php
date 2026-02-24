<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Category;
use App\Models\Cart;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /**
         * ===============================
         * SHARE MEGA MENU CATEGORIES
         * ===============================
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
         * ===============================
         * SHARE CART COUNT (HEADER)
         * ===============================
         * Dùng cho toàn bộ frontend
         */
        View::composer('*', function ($view) {

            $cartCount = 0;

            // Nếu đã đăng nhập → lấy từ database
            if (Auth::check()) {
                $cartCount = Cart::where('user_id', Auth::id())
                    ->sum('quantity');
            }
            // Nếu chưa đăng nhập → lấy từ session
            else {
                $cart = Session::get('cart', []);
                $cartCount = collect($cart)->sum('quantity');
            }

            $view->with('cartCount', $cartCount);
        });
    }
}