<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->withCount('products')
            ->with(['parent', 'children'])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        // ✅ Get ALL categories for dropdown (including sub-categories)
        $allCategories = Category::with('parent')
            ->orderBy('name')
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'summary_html' => view('seller.partials.category_summary_items', compact('categories'))->render(),
                'table_html' => view('seller.partials.category_rows', compact('categories'))->render(),
                'next_page_url' => $categories->nextPageUrl(),
            ]);
        }

        // ✅ Pass both $categories and $allCategories to view
        return view('seller.categories', compact('categories', 'allCategories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'category_group' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category = Category::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'category_group' => $request->category_group ?? 'Other',
                'tags' => $request->tags ?? ['general'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
                'category' => $category->load(['products', 'parent', 'children'])->loadCount('products'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $category = Category::with(['products', 'parent', 'children'])
            ->withCount('products')
            ->wherePublicIdOrId($id)
            ->first();

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'category' => $category,
            'related_categories' => $category->getRelatedCategoryIds(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::wherePublicIdOrId($id)->first();

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // ✅ Build validation rules dynamically
        $rules = [
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'category_group' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];

        // ✅ Only check unique if name has changed
        if ($request->name !== $category->name) {
            $rules['name'] = 'required|string|max:191|unique:categories,name';
        } else {
            $rules['name'] = 'required|string|max:191';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'category_group' => $request->category_group ?? $category->category_group,
                'tags' => $request->tags ?? $category->tags,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'category' => $category->load(['products', 'parent', 'children'])->loadCount('products'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $category = Category::withCount('products')
            ->wherePublicIdOrId($id)
            ->first();

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        if ($category->products_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category that contains products. Please remove all products first.',
            ], 422);
        }

        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category that has sub-categories. Please remove sub-categories first.',
            ], 422);
        }

        try {
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getHierarchy()
    {
        $categories = Category::with(['children'])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }
}