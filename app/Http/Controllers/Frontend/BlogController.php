<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;

class BlogController extends Controller
{

    /**
     * Danh sách blog
     */
    public function index(Request $request)
    {

        // chỉ lấy blog đang hiển thị
        $query = Blog::where('is_active', 1);

        // sắp xếp
        if ($request->sort == 'old') {
            $query->orderBy('created_at', 'asc');
        } elseif ($request->sort == 'views') {
            $query->orderBy('views', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $blogs = $query->paginate(9)->withQueryString();

        return view('frontend.blogs.index', compact('blogs'));
    }



    /**
     * Chi tiết blog
     */
    public function show($slug)
    {

        // chỉ cho xem blog đang active
        $blog = Blog::where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();

        // tăng lượt xem
        $blog->increment('views');


        // bài viết liên quan
        $relatedBlogs = Blog::where('id', '!=', $blog->id)
            ->where('is_active', 1)
            ->latest()
            ->limit(3)
            ->get();


        // bài viết phổ biến
        $popularBlogs = Blog::where('is_active', 1)
            ->orderBy('views', 'desc')
            ->limit(5)
            ->get();


        return view('frontend.blogs.show', compact(
            'blog',
            'relatedBlogs',
            'popularBlogs'
        ));
    }
}