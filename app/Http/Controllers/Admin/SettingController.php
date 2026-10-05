<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request): View
    {
        $settings = Setting::query()
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search').'%';

                $query->where('key', 'like', $term)->orWhere('value', 'like', $term);
            }))
            ->orderBy('key')
            ->paginate(20)
            ->withQueryString();

        return view('admin.settings.index', [
            'settings' => $settings,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): View
    {
        return view('admin.settings.create');
    }

    public function store(SettingRequest $request): RedirectResponse
    {
        Setting::create($request->validated());

        return redirect()->route('admin.settings.index')->with('success', __('admin.flash.setting_created'));
    }

    public function edit(Setting $setting): View
    {
        return view('admin.settings.edit', ['setting' => $setting]);
    }

    public function update(SettingRequest $request, Setting $setting): RedirectResponse
    {
        $setting->update($request->validated());

        return redirect()->route('admin.settings.index')->with('success', __('admin.flash.setting_updated'));
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $setting->delete();

        return back()->with('success', __('admin.flash.setting_deleted'));
    }
}
