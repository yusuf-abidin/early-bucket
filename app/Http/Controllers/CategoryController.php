<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('color')
            ->orderBy('type')
            ->orderBy('order')
            ->get()
            ->groupBy('type')
            ->toArray();

        $colors = Color::select(['id', 'name', 'class'])->get();

        return Inertia::render('admin/category/Index', [
            'categories' => $categories,
            'colors' => $colors
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'nullable|exists:categories,id',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.type' => 'required|string|max:255',
            'categories.*.order' => 'required|integer|min:1',
            'categories.*.color_id' => 'nullable|exists:colors,id',
            'categories.*.isNew' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->categories as $categoryData) {
                if (!empty($categoryData['isNew']) && empty($categoryData['id'])) {
                    // Create new category
                    Category::create([
                        'name' => $categoryData['name'],
                        'type' => $categoryData['type'],
                        'order' => $categoryData['order'],
                        'color_id' => $categoryData['color_id'] ?? null,
                    ]);
                } else if (!empty($categoryData['id'])) {
                    // Update existing category
                    $category = Category::findOrFail($categoryData['id']);
                    $category->update([
                        'name' => $categoryData['name'],
                        'order' => $categoryData['order'],
                        'color_id' => $categoryData['color_id'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect(route('admin.categories.index'))->with('success', 'Categories updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to update categories: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'color_id' => 'nullable|exists:colors,id',
        ]);

        // Get the max order for this type
        $maxOrder = Category::where('type', $request->type)->max('order') ?? 0;

        $category = Category::create([
            'name' => $request->name,
            'type' => $request->type,
            'order' => $maxOrder + 1,
            'color_id' => $request->color_id,
        ]);

        return back()->with('success', 'Category created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:1',
            'color_id' => 'nullable|exists:colors,id',
        ]);

        $data = ['name' => $request->name];

        if ($request->has('color_id')) {
            $data['color_id'] = $request->color_id;
        }

        if ($request->has('order')) {
            $oldOrder = $category->order;
            $newOrder = $request->order;

            DB::transaction(function () use ($category, $oldOrder, $newOrder, $data) {
                if ($oldOrder !== $newOrder) {
                    if ($oldOrder < $newOrder) {
                        // Moving down: decrease order of items between old and new position
                        Category::where('type', $category->type)
                            ->where('order', '>', $oldOrder)
                            ->where('order', '<=', $newOrder)
                            ->decrement('order');
                    } else {
                        // Moving up: increase order of items between new and old position
                        Category::where('type', $category->type)
                            ->where('order', '>=', $newOrder)
                            ->where('order', '<', $oldOrder)
                            ->increment('order');
                    }
                }

                $data['order'] = $newOrder;
                $category->update($data);
            });
        } else {
            $category->update($data);
        }

        return back()->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if (Category::where('type', $category->type)->count() <= 1) {
            return back()->withErrors(['message' => 'Tidak dapat menghapus kategori terakhir']);
        }
        if($category->tasks()->exists()) {
            return back()->withErrors(['message' => 'Tidak dapat menghapus kategori yang masih memiliki pending matter.']);
        }

        if($category->memos()->exists()) {
            return back()->withErrors(['message' => 'Tidak dapat menghapus kategori yang masih memiliki memo.']);
        }

        if($category->performancesAsKomitmenEtape()->exists()) {
            return back()->withErrors(['message' => 'Tidak dapat menghapus kategori yang masih memiliki performance komitmen etape.']);
        }

        if($category->performancesAsKomitmenEomBc()->exists()) {
            return back()->withErrors(['message' => 'Tidak dapat menghapus kategori yang masih memiliki performance komitmen eom bc.']);
        }

        if ($category->performancesAsKomitmenEomBm()->exists()){
            return back()->withErrors(['message' => 'Tidak dapat menghapus kategori yang masih memiliki performance komitmen eom bm.']);
        }

        DB::transaction(function () use ($category) {
            $type = $category->type;
            $order = $category->order;

            // Delete the category
            $category->delete();

            // Reorder remaining categories
            Category::where('type', $type)
                ->where('order', '>', $order)
                ->decrement('order');
        });

        return back()->with('success', 'Category deleted successfully');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:categories,id',
            'orders.*.order' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->orders as $orderData) {
                Category::where('id', $orderData['id'])
                    ->where('type', $request->type)
                    ->update(['order' => $orderData['order']]);
            }
        });

        return back()->with('success', 'Categories reordered successfully');
    }
}
