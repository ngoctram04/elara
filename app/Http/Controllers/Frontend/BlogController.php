<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Session;

class BlogController extends Controller
{
    public function index()
    {
        $latestBlog = Blog::where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        $discoverBlogsQuery = Blog::where('is_active', 1);

        if ($latestBlog) {
            $discoverBlogsQuery->where('id', '!=', $latestBlog->id);
        }

        $discoverBlogs = $discoverBlogsQuery
            ->orderBy('created_at', 'desc')
            ->get();

        $recentViewedIds = Session::get('recent_viewed_blogs', []);

        $recentViewedBlogs = collect();

        if (!empty($recentViewedIds)) {
            $recentViewedBlogs = Blog::where('is_active', 1)
                ->whereIn('id', $recentViewedIds)
                ->get()
                ->sortBy(function ($blog) use ($recentViewedIds) {
                    return array_search($blog->id, $recentViewedIds);
                })
                ->values()
                ->take(4);
        }

        return view('frontend.blogs.index', compact(
            'latestBlog',
            'discoverBlogs',
            'recentViewedBlogs'
        ));
    }

    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();

        $recentViewed = Session::get('recent_viewed_blogs', []);

        $recentViewed = array_values(array_filter($recentViewed, function ($id) use ($blog) {
            return (int) $id !== (int) $blog->id;
        }));

        array_unshift($recentViewed, $blog->id);

        $recentViewed = array_slice($recentViewed, 0, 12);

        Session::put('recent_viewed_blogs', $recentViewed);

        $blog->timestamps = false;
        $blog->increment('views');
        $blog->refresh();

        $relatedBlogs = Blog::where('id', '!=', $blog->id)
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $popularBlogs = Blog::where('is_active', 1)
            ->where('id', '!=', $blog->id)
            ->orderBy('views', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('frontend.blogs.show', compact(
            'blog',
            'relatedBlogs',
            'popularBlogs'
        ));
    }
}