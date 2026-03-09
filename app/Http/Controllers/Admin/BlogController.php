<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{

    // ================= LIST + SEARCH + FILTER =================
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


    // ================= CREATE =================
    public function create()
    {
        return view('admin.blogs.create');
    }


    // ================= STORE =================
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        // tạo slug unique
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;

        while (Blog::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['slug'] = $slug;
        $data['is_active'] = 1;

        // upload thumbnail
        if ($request->hasFile('thumbnail')) {

            $data['thumbnail'] = $request
                ->file('thumbnail')
                ->store('blogs', 'public');
        }

        Blog::create($data);

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Thêm bài viết thành công');
    }


    // ================= EDIT =================
    public function edit($id)
    {
        $blog = Blog::findOrFail($id);

        return view('admin.blogs.edit', compact('blog'));
    }


    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048'
        ]);

        // slug unique
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

        // upload thumbnail mới
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


    // ================= DELETE =================
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


    // ================= TOGGLE ACTIVE =================
    public function toggle($id)
    {
        $blog = Blog::findOrFail($id);

        $blog->is_active = !$blog->is_active;
        $blog->save();

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Cập nhật trạng thái bài viết thành công');
    }


    // ================= UPLOAD IMAGE / VIDEO FOR TINYMCE =================
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