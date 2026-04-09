<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;
class BlogController extends Controller
{


    public function index(Request $request)
    {
        $query = Blog::query();

        if ($request->keyword) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        if ($request->sort == 'most') {
            $query->orderBy('views', 'desc');
        } elseif ($request->sort == 'least') {
            $query->orderBy('views', 'asc');
        } else {
            $query->latest();
        }

        $blogs = $query->paginate(10)->withQueryString();

        return view('admin.blogs.index', compact('blogs'));
    }


    public function create()
    {
        return view('admin.blogs.create');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        $slug = Str::slug($request->title);

        if (empty($slug)) {
            $slug = 'blog-' . time();
        }

        $originalSlug = $slug;
        $counter = 1;

        while (Blog::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['slug'] = $slug;
        $data['is_active'] = 1;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request
            ->file('thumbnail')
                ->store('blogs', 'public');
        }


        $blog = Blog::create($data);

        User::where('is_active', 1)
        ->where('role', 'customer') 
            ->chunk(100, function ($users) use ($blog) {

                foreach ($users as $user) {
                    try {
                        $user->notify(new SystemNotification([
                            'title' => 'Bài viết mới',
                            'message' => 'Shop vừa đăng: ' . $blog->title,
                            'url' => route('blogs.show', $blog->slug),
                            'type' => 'blog',
                            'meta' => [
                                'blog_id' => $blog->id
                            ]
                        ]));
                    } catch (\Exception $e) {
                        Log::error('Notify error: ' . $e->getMessage());
                    }
                }
            });

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Thêm bài viết thành công');
    }

    public function edit($id)
    {
        $blog = Blog::findOrFail($id);

        return view('admin.blogs.edit', compact('blog'));
    }


    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;

        while (
            Blog::where('slug', $slug)
            ->where('id', '!=', $blog->id)
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['slug'] = $slug;
        if ($request->hasFile('thumbnail')) {

            if ($blog->thumbnail) {
                Storage::disk('public')->delete($blog->thumbnail);
            }

            $data['thumbnail'] = $request
                ->file('thumbnail')
                ->store('blogs', 'public');
        }

        $blog->update($data);

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Cập nhật bài viết thành công');
    }

    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);

        if ($blog->thumbnail) {
            Storage::disk('public')->delete($blog->thumbnail);
        }

        $blog->delete();

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Xóa bài viết thành công');
    }

    public function toggle($id)
    {
        $blog = Blog::findOrFail($id);

        $blog->is_active = !$blog->is_active;
        $blog->save();

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Cập nhật trạng thái bài viết thành công');
    }


    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200|mimes:jpg,jpeg,png,webp,gif,mp4,webm,mov'
        ]);

        $file = $request->file('file');

        $path = $file->store('blogs', 'public');

        return response()->json([
            'location' => asset('storage/' . $path)
        ]);
    }
}