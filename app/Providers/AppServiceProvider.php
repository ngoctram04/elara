<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /**
         * SHARE MEGA MENU CATEGORIES
         * Dùng cho toàn bộ frontend
         */
        view()->composer('components.mega-menu', function ($view) {

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
    }
}