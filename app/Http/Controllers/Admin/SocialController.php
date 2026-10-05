<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SocialRequest;
use App\Models\Social;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $socials = Social::query()
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';

                $query->where('name_ar', 'like', $term)
                    ->orWhere('name_en', 'like', $term)
                    ->orWhere('link', 'like', $term);
            }))
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.socials.index', [
            'socials' => $socials,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): View
    {
        return view('admin.socials.create');
    }

    public function store(SocialRequest $request): RedirectResponse
    {
        $attributes = $request->socialAttributes();

        if ($request->hasFile('icon')) {
            $attributes['icon'] = $this->storeImage($request->file('icon'), 'socials');
        }

        Social::create($attributes);

        return redirect()->route('admin.socials.index')->with('success', __('admin.flash.social_created'));
    }

    public function edit(Social $social): View
    {
        return view('admin.socials.edit', ['social' => $social]);
    }

    public function update(SocialRequest $request, Social $social): RedirectResponse
    {
        $attributes = $request->socialAttributes();

        if ($request->hasFile('icon')) {
            $this->deleteImage($social->icon);
            $attributes['icon'] = $this->storeImage($request->file('icon'), 'socials');
        }

        $social->update($attributes);

        return redirect()->route('admin.socials.index')->with('success', __('admin.flash.social_updated'));
    }

    public function destroy(Social $social): RedirectResponse
    {
        $this->deleteImage($social->icon);

        $social->delete();

        return back()->with('success', __('admin.flash.social_deleted'));
    }
}
