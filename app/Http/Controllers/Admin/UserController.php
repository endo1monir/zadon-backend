<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use App\Support\AdminOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $users = User::query()
            ->customer()
            ->with('city:id,name_ar')
            ->withCount(['orders', 'addresses'])
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';

                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            }))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $request->only(['search', 'is_active']),
            'notificationTypeOptions' => AdminOptions::notificationTypes(),
        ]);
    }

    public function addresses(User $user): View
    {
        return view('admin.users.addresses', [
            'user' => $user,
            'addresses' => $user->addresses()
                ->orderByDesc('is_default')
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'cityOptions' => AdminOptions::cities(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $attributes = $request->userAttributes();

        if ($request->hasFile('avatar')) {
            $attributes['avatar'] = $this->storeImage($request->file('avatar'), 'avatars');
        }

        $user = User::create($attributes);
        $user->forceFill([
            'code' => null,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ])->save();

        return redirect()->route('admin.users.index')->with('success', __('admin.flash.user_created'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'cityOptions' => AdminOptions::cities(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return redirect()->route('admin.users.index')
                ->with('error', __('admin.flash.cannot_edit_self'));
        }

        $attributes = $request->userAttributes();

        if ($request->hasFile('avatar')) {
            $this->deleteImage($user->avatar);
            $attributes['avatar'] = $this->storeImage($request->file('avatar'), 'avatars');
        }

        $user->update($attributes);

        return redirect()->route('admin.users.index')->with('success', __('admin.flash.user_updated'));
    }

    public function toggle(User $user): RedirectResponse
    {
        if ($user->is(request()->user())) {
            return redirect()->route('admin.users.index')
                ->with('error', __('admin.flash.cannot_deactivate_self'));
        }

        $user->update(['is_active' => ! $user->is_active]);

        return redirect()->route('admin.users.index')->with('success', __('admin.flash.user_status_updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(request()->user())) {
            return redirect()->route('admin.users.index')
                ->with('error', __('admin.flash.cannot_delete_self'));
        }

        $this->deleteImage($user->avatar);

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', __('admin.flash.user_deleted'));
    }
}
