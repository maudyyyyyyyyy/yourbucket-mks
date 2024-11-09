<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $categories = Category::query()
                ->withCount('products')
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(); // Ini penting untuk mempertahankan parameter search saat paginasi

            return view('admin.category', compact('categories'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load categories: ' . $e->getMessage());
        }
    }


    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'required|max:255',
                'description' => 'nullable'
            ]);

            Category::create($validated);

            DB::commit();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to create category: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, Category $category)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'required|max:255',
                'description' => 'nullable'
            ]);

            $category->update($validated);

            DB::commit();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to update category: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Category $category)
    {
        try {
            DB::beginTransaction();

            if ($category->products()->exists()) {
                throw new Exception('Cannot delete category with associated products.');
            }

            $category->delete();

            DB::commit();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}