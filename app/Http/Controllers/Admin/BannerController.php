<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BannerController extends Controller
{
    use HandlesUploads;

    public function index(): View
    {
        return view('admin.banners.index', [
            'banners' => Banner::query()->latest('id')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(BannerRequest $request): RedirectResponse
    {
        Banner::create([
            'image' => $this->storeImage($request->file('image'), 'banners'),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner uploaded successfully.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', ['banner' => $banner]);
    }

    public function update(BannerRequest $request, Banner $banner): RedirectResponse
    {
        if ($request->hasFile('image')) {
            $this->deleteImage($banner->image);

            $banner->update(['image' => $this->storeImage($request->file('image'), 'banners')]);
        }

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->deleteImage($banner->image);

        $banner->delete();

        return back()->with('success', 'Banner deleted successfully.');
    }
}
