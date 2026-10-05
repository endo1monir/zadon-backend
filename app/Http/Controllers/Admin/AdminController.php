<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Models\User;
use App\Support\AdminOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $admins = User::query()
            ->admin()
            ->with('city:id,name_ar')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search').'%';

                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            }))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.admins.index', [
            'admins' => $admins,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function create(): View
    {
        return view('admin.admins.create', [
            'cityOptions' => AdminOptions::cities(),
        ]);
    }

    public function store(AdminRequest $request): RedirectResponse
    {
        $attributes = $request->adminAttributes();

        if ($request->hasFile('avatar')) {
            $attributes['avatar'] = $this->storeImage($request->file('avatar'), 'admins');
        }

        $admin = User::create($attributes);
        $admin->forceFill([
            'email_verified_at' => now(),
            'phone_verified_at' => $admin->phone ? now() : null,
        ])->save();

        return redirect()->route('admin.admins.index')->with('success', __('admin.flash.admin_created'));
    }

    public function edit(User $admin): View
    {
        return view('admin.admins.edit', [
            'admin' => $admin,
            'cityOptions' => AdminOptions::cities(),
        ]);
    }

    public function update(AdminRequest $request, User $admin): RedirectResponse
    {
        $attributes = $request->adminAttributes();

        if ($request->hasFile('avatar')) {
            $this->deleteImage($admin->avatar);
            $attributes['avatar'] = $this->storeImage($request->file('avatar'), 'admins');
        }

        if ($request->has('is_active') && ! $request->boolean('is_active') && $admin->is($request->user())) {
            return redirect()->route('admin.admins.edit', $admin)
                ->withErrors(['is_active' => __('admin.flash.cannot_deactivate_self')]);
        }

        $admin->update($attributes);

        return redirect()->route('admin.admins.index')->with('success', __('admin.flash.admin_updated'));
    }

    public function toggle(User $admin): RedirectResponse
    {
        if ($admin->is(request()->user())) {
            return redirect()->route('admin.admins.index')
                ->with('error', __('admin.flash.cannot_deactivate_self'));
        }

        $admin->update(['is_active' => ! $admin->is_active]);

        return redirect()->route('admin.admins.index')->with('success', __('admin.flash.admin_status_updated'));
    }

    public function destroy(User $admin): RedirectResponse
    {
        if ($admin->is(request()->user())) {
            return redirect()->route('admin.admins.index')
                ->with('error', __('admin.flash.cannot_delete_self'));
        }

        $this->deleteImage($admin->avatar);

        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', __('admin.flash.admin_deleted'));
    }
}
