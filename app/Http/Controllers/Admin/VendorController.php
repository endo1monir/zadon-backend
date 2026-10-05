<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VendorRequest;
use App\Models\Store;
use App\Models\User;
use App\Support\AdminOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $vendors = User::query()
            ->vendor()
            ->with('city:id,name_ar')
            ->with(['stores' => fn ($query) => $query
                ->with(['category:id,name_ar', 'city:id,name_ar'])
                ->withCount('products')
                ->orderBy('id'),
            ])
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search')->trim().'%';

                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhereHas('stores', fn ($query) => $query
                        ->where('name_ar', 'like', $term)
                        ->orWhere('name_en', 'like', $term));
            }))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.vendors.index', [
            'vendors' => $vendors,
            'filters' => $request->only(['search', 'is_active']),
            'notificationTypeOptions' => AdminOptions::notificationTypes(),
        ]);
    }

    public function create(): View
    {
        return view('admin.vendors.create', [
            'categoryOptions' => AdminOptions::categories('store'),
            'cityOptions' => AdminOptions::cities(),
            'statusOptions' => AdminOptions::storeStatuses(),
        ]);
    }

    public function store(VendorRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $vendor = User::create($request->ownerAttributes());
            $vendor->forceFill([
                'code' => null,
                'phone_verified_at' => now(),
                'email_verified_at' => $request->filled('email') ? now() : null,
            ])->save();

            $store = $vendor->stores()->create($request->storeAttributes());
            $this->syncStoreImages($request, $store);
        });

        return redirect()->route('admin.vendors.index')->with('success', __('admin.flash.vendor_created'));
    }

    public function edit(User $vendor): View
    {
        $store = $this->managedStore($vendor);

        return view('admin.vendors.edit', [
            'vendor' => $vendor,
            'store' => $store,
            'categoryOptions' => AdminOptions::categories('store'),
            'cityOptions' => AdminOptions::cities(),
            'statusOptions' => AdminOptions::storeStatuses(),
        ]);
    }

    public function update(VendorRequest $request, User $vendor): RedirectResponse
    {
        DB::transaction(function () use ($request, $vendor): void {
            $vendor->update($request->ownerAttributes());

            $store = $this->managedStore($vendor);

            if ($store === null) {
                $store = $vendor->stores()->create($request->storeAttributes());
            } else {
                $store->update($request->storeAttributes());
            }

            $this->syncStoreImages($request, $store);
        });

        return redirect()->route('admin.vendors.index')->with('success', __('admin.flash.vendor_updated'));
    }

    public function toggle(User $vendor): RedirectResponse
    {
        if ($vendor->is(request()->user())) {
            return redirect()->route('admin.vendors.index')
                ->with('error', __('admin.flash.cannot_deactivate_self'));
        }

        $vendor->update(['is_active' => ! $vendor->is_active]);

        return redirect()->route('admin.vendors.index')->with('success', __('admin.flash.vendor_status_updated'));
    }

    public function destroy(User $vendor): RedirectResponse
    {
        if ($vendor->is(request()->user())) {
            return redirect()->route('admin.vendors.index')
                ->with('error', __('admin.flash.cannot_delete_self'));
        }

        $stores = $vendor->stores()->get();

        if ($stores->contains(fn (Store $store): bool => $store->products()->exists() || $store->orders()->exists())) {
            return redirect()->route('admin.vendors.index')
                ->with('error', __('admin.flash.vendor_in_use'));
        }

        DB::transaction(function () use ($vendor, $stores): void {
            foreach ($stores as $store) {
                $this->deleteImage($store->logo);
                $this->deleteImage($store->cover_image);

                $store->delete();
            }

            $vendor->delete();
        });

        return redirect()->route('admin.vendors.index')->with('success', __('admin.flash.vendor_deleted'));
    }

    /**
     * The vendor store the dashboard edits, mirroring how the vendor API resolves it.
     */
    protected function managedStore(User $vendor): ?Store
    {
        return $vendor->stores()->orderBy('id')->first();
    }

    protected function syncStoreImages(VendorRequest $request, Store $store): void
    {
        if ($request->hasFile('store_logo')) {
            $this->deleteImage($store->logo);
            $store->update(['logo' => $this->storeImage($request->file('store_logo'), 'stores')]);
        }

        if ($request->hasFile('store_cover_image')) {
            $this->deleteImage($store->cover_image);
            $store->update(['cover_image' => $this->storeImage($request->file('store_cover_image'), 'stores')]);
        }
    }
}
