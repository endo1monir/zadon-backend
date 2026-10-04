<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Support\AdminOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with('store:id,name_ar,name_en')
            ->with('user:id,name,phone')
            ->with('order:id,order_number')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';

                $query->where('customer_name', 'like', $term)
                    ->orWhere('comment', 'like', $term)
                    ->orWhere('store_reply', 'like', $term)
                    ->orWhereHas('store', fn ($query) => $query
                        ->where('name_ar', 'like', $term)
                        ->orWhere('name_en', 'like', $term))
                    ->orWhereHas('order', fn ($query) => $query->where('order_number', 'like', $term));
            }))
            ->when($request->filled('store_id'), fn ($query) => $query->where('store_id', $request->integer('store_id')))
            ->when($request->filled('rating'), fn ($query) => $query->where('rating', $request->integer('rating')))
            ->when($request->filled('published'), fn ($query) => $query->where('published', $request->boolean('published')))
            ->when($request->filled('replied'), fn ($query) => $query->when(
                $request->boolean('replied'),
                fn ($query) => $query->whereNotNull('store_reply'),
                fn ($query) => $query->whereNull('store_reply'),
            ))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'filters' => $request->only(['search', 'store_id', 'rating', 'published', 'replied']),
            'storeOptions' => AdminOptions::stores(),
            'ratingOptions' => AdminOptions::reviewRatings(),
            'summary' => [
                'total' => Review::query()->count(),
                'published' => Review::query()->where('published', true)->count(),
                'unreplied' => Review::query()->whereNull('store_reply')->count(),
                'average' => round((float) Review::query()->avg('rating'), 2),
            ],
        ]);
    }

    public function toggle(Review $review): RedirectResponse
    {
        $review->update(['published' => ! $review->published]);

        return back()->with('success', 'Review visibility updated.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }
}
