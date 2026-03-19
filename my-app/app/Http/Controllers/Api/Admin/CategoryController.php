<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /* ===========================
       GET ALL
    =========================== */
    public function index()
    {
        return Category::orderBy('name', 'asc')->get();
    }

    /* ===========================
       GET ONE
    =========================== */
    public function show(Category $category)
    {
        return $category;
    }

    /* ===========================
       CREATE
    =========================== */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'is_active' => 'boolean',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category = Category::create($data);

        return response()->json($category, 201);
    }

    /* ===========================
       UPDATE
    =========================== */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('categories')->ignore($category->id),
            ],
            'slug' => [
                'nullable',
                'string',
                Rule::unique('categories')->ignore($category->id),
            ],
            'is_active' => 'boolean',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return response()->json($category);
    }

    /* ===========================
       DELETE (optional hard delete)
    =========================== */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    /* ===========================
       TOGGLE STATUS
    =========================== */
    public function toggleStatus(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        return response()->json($category);
    }
}
