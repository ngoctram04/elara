<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /*
    |------------------------------------------------------------------
    | TẠO SLUG KHÔNG TRÙNG
    |------------------------------------------------------------------
    */
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

    /*
    |------------------------------------------------------------------
    | DANH SÁCH DANH MỤC CHA
    |------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Category::query()
            ->whereNull('parent_id')
            ->withCount('children');

        // 🔍 Tìm kiếm
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // 🔃 Sắp xếp
        match ($request->sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default  => $query->orderBy('created_at', 'desc'),
        };

        $categories = $query->get();

        return view('admin.categories.index', compact('categories'));
    }

    /*
    |------------------------------------------------------------------
    | FORM TẠO DANH MỤC CHA / CON
    |------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $parent = null;

        if ($request->filled('parent_id')) {
            $parent = Category::whereNull('parent_id')
                ->findOrFail($request->parent_id);
        }

        return view('admin.categories.create', compact('parent'));
    }

    /*
    |------------------------------------------------------------------
    | LƯU DANH MỤC CHA / CON
    |------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = Category::create([
            'name'      => $data['name'],
            'slug'      => $this->generateUniqueSlug($data['name']),
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return $category->parent_id
            ? redirect()->route('admin.categories.show', $category->parent_id)
            ->with('success', 'Thêm danh mục nhỏ thành công')
            : redirect()->route('admin.categories.index')
            ->with('success', 'Thêm danh mục lớn thành công');
    }

    /*
    |------------------------------------------------------------------
    | CHI TIẾT DANH MỤC CHA – DANH SÁCH DANH MỤC CON
    |------------------------------------------------------------------
    */
    public function show(Request $request, Category $category)
    {
        // ❗ Chỉ cho xem danh mục CHA
        abort_if($category->parent_id !== null, 404);

        // ✅ THÊM withCount('products') Ở ĐÂY
        $childrenQuery = $category->children()
            ->withCount('products');

        // 🔍 Tìm kiếm
        if ($request->filled('keyword')) {
            $childrenQuery->where('name', 'like', '%' . $request->keyword . '%');
        }

        // 🔃 Sắp xếp (CHUẨN)
        if ($request->sort === 'oldest') {
            $childrenQuery->orderBy('id', 'asc');
        } else {
            $childrenQuery->orderBy('id', 'desc'); // mặc định
        }

        $children = $childrenQuery->get();

        return view('admin.categories.show', compact('category', 'children'));
    }

    /*
    |------------------------------------------------------------------
    | FORM CHỈNH SỬA
    |------------------------------------------------------------------
    */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /*
    |------------------------------------------------------------------
    | CẬP NHẬT DANH MỤC
    |------------------------------------------------------------------
    */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if ($data['name'] !== $category->name) {
            $category->slug = $this->generateUniqueSlug(
                $data['name'],
                $category->id
            );
        }

        $category->update([
            'name' => $data['name'],
        ]);

        return $category->parent_id
            ? redirect()->route('admin.categories.show', $category->parent_id)
            ->with('success', 'Cập nhật danh mục nhỏ thành công')
            : redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục thành công');
    }

    /*
    |------------------------------------------------------------------
    | XÓA DANH MỤC
    |------------------------------------------------------------------
    */
    public function destroy(Category $category)
    {
        // ❌ Không cho xóa danh mục cha khi còn danh mục con
        if ($category->parent_id === null && $category->children()->exists()) {
            return back()->with('error', 'Không thể xóa danh mục đang chứa danh mục nhỏ');
        }

        $parentId = $category->parent_id;
        $category->delete();

        return $parentId
            ? redirect()->route('admin.categories.show', $parentId)
            ->with('success', 'Xóa danh mục nhỏ thành công')
            : redirect()->route('admin.categories.index')
            ->with('success', 'Xóa danh mục thành công');
    }
}