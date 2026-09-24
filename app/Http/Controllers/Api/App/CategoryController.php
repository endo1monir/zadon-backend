<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\CategoryIconRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use ResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $type = $request->validate(['type' => ['sometimes', Rule::in(['store', 'product'])]])['type'] ?? null;

        $categories = Category::query()
            ->active()
            ->when($type, fn ($query) => $query->type($type))
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return $this->successReturn([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function uploadIcon(CategoryIconRequest $request, Category $category): JsonResponse
    {
        $icon = $request->file('icon')->store('categories', 'public');

        $category->update(['icon' => $icon]);

        return $this->successReturn([
            'category' => new CategoryResource($category),
        ], 'messages.category_icon_uploaded');
    }
}
