<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $categories = Category::query()
            ->with('parent:id,name_ar,name_en')
            ->withCount(['stores', 'products'])
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search').'%';

                $query->where('name_ar', 'like', $term)
                    ->orWhere('name_en', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            }))
            ->when($request->filled('type'), fn ($query) => $query->type($request->string('type')->toString()))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'type', 'is_active']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.categories.create', [
            'selectedType' => $request->string('type')->toString(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $attributes = $request->categoryAttributes();

        if ($request->hasFile('icon')) {
            $attributes['icon'] = $this->storeImage($request->file('icon'), 'categories');
        }

        Category::create($attributes);

        return redirect()->route('admin.categories.index')->with('success', __('admin.flash.category_created'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'selectedType' => $category->type,
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $attributes = $request->categoryAttributes();

        if ($request->hasFile('icon')) {
            $this->deleteImage($category->icon);
            $attributes['icon'] = $this->storeImage($request->file('icon'), 'categories');
        }

        $category->update($attributes);

        return redirect()->route('admin.categories.index')->with('success', __('admin.flash.category_updated'));
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', __('admin.flash.category_status_updated'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return back()->with('error', __('admin.flash.category_has_children'));
        }

        if ($category->stores()->exists() || $category->products()->exists()) {
            return back()->with('error', __('admin.flash.category_in_use'));
        }

        $this->deleteImage($category->icon);

        $category->delete();

        return back()->with('success', __('admin.flash.category_deleted'));
    }
}
