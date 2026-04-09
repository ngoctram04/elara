<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (
            Category::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    public function index(Request $request)
    {
        $query = Category::query()
            ->whereNull('parent_id')
            ->withCount('children');

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $numberKeyword = preg_replace('/\D/', '', $keyword);

            $query->where(function ($q) use ($keyword, $numberKeyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhereRaw("CONCAT('DM', LPAD(id, 4, '0')) LIKE ?", ['%' . $keyword . '%']);

                if ($numberKeyword !== '') {
                    $q->orWhere('id', (int) $numberKeyword);
                }
            });
        }

        match ($request->sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default  => $query->orderBy('created_at', 'desc'),
        };

        $categories = $query->paginate(7)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(Request $request)
    {
        $parent = null;

        if ($request->filled('parent_id')) {
            $parent = Category::whereNull('parent_id')
                ->findOrFail($request->parent_id);
        }

        return view('admin.categories.create', compact('parent'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;


        if (!empty($data['parent_id']) && $request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create([
            'name'      => $data['name'],
            'slug'      => $this->generateUniqueSlug($data['name']),
            'parent_id' => $data['parent_id'] ?? null,
            'image'     => $imagePath,
        ]);

        return $category->parent_id
            ? redirect()->route('admin.categories.show', $category->parent_id)
            ->with('success', 'Thêm danh mục nhỏ thành công')
            : redirect()->route('admin.categories.index')
            ->with('success', 'Thêm danh mục lớn thành công');
    }

    public function show(Request $request, Category $category)
    {
        abort_if($category->parent_id !== null, 404);

        $childrenQuery = $category->children()
            ->withCount('products');

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $numberKeyword = preg_replace('/\D/', '', $keyword);

            $childrenQuery->where(function ($q) use ($keyword, $numberKeyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhereRaw("CONCAT('DMC', LPAD(id, 4, '0')) LIKE ?", ['%' . $keyword . '%']);

                if ($numberKeyword !== '') {
                    $q->orWhere('id', (int) $numberKeyword);
                }
            });
        }

        if ($request->sort === 'oldest') {
            $childrenQuery->orderBy('id', 'asc');
        } else {
            $childrenQuery->orderBy('id', 'desc');
        }

        $children = $childrenQuery->paginate(7)->withQueryString();

        return view('admin.categories.show', compact('category', 'children'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $updateData = [
            'name' => $data['name'],
        ];

        if ($data['name'] !== $category->name) {
            $updateData['slug'] = $this->generateUniqueSlug(
                $data['name'],
                $category->id
            );
        }

        if ($category->parent_id) {
            if ($request->hasFile('image')) {
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }

                $updateData['image'] = $request->file('image')->store('categories', 'public');
            }
        } else {
     
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $updateData['image'] = null;
        }

        $category->update($updateData);

        return $category->parent_id
            ? redirect()->route('admin.categories.show', $category->parent_id)
            ->with('success', 'Cập nhật danh mục nhỏ thành công')
            : redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục thành công');
    }

    public function destroy(Category $category)
    {
        if ($category->parent_id === null && $category->children()->exists()) {
            return back()->with('error', 'Không thể xóa danh mục đang chứa danh mục nhỏ');
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'Không thể xóa danh mục đang có sản phẩm');
        }

        $parentId = $category->parent_id;

        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return $parentId
            ? redirect()->route('admin.categories.show', $parentId)
            ->with('success', 'Xóa danh mục nhỏ thành công')
            : redirect()->route('admin.categories.index')
            ->with('success', 'Xóa danh mục thành công');
    }
}