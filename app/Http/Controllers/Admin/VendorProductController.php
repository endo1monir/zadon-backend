<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Support\AdminOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorProductController extends Controller
{
    use HandlesUploads;

    public function index(Request $request, User $vendor): View
    {
        $store = $this->managedStore($vendor);

        $products = $store->products()
            ->with('category:id,name_ar')
            ->search($request->string('search')->trim()->toString())
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->input('stock') === 'low', fn ($query) => $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock_alert'))
            ->when($request->input('stock') === 'out', fn ($query) => $query->where('stock', 0))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.vendors.products.index', [
            'vendor' => $vendor,
            'store' => $store,
            'products' => $products,
            'filters' => $request->only(['search', 'category_id', 'stock', 'is_active']),
            'categoryOptions' => AdminOptions::categories('product'),
            'summary' => [
                'total' => $store->products()->count(),
                'active' => $store->products()->where('is_active', true)->count(),
                'low' => $store->products()->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock_alert')->count(),
                'out' => $store->products()->where('stock', 0)->count(),
            ],
        ]);
    }

    public function create(User $vendor): View
    {
        return view('admin.vendors.products.create', [
            'vendor' => $vendor,
            'store' => $this->managedStore($vendor),
            'categoryOptions' => AdminOptions::categories('product'),
            'storageTempOptions' => AdminOptions::productStorageTemps(),
        ]);
    }

    public function store(ProductRequest $request, User $vendor): RedirectResponse
    {
        $store = $this->managedStore($vendor);

        $attributes = $request->productAttributes();

        if ($request->hasFile('image')) {
            $attributes['image'] = $this->storeImage($request->file('image'), 'products');
        }

        $store->products()->create($attributes);

        return redirect()->route('admin.vendors.products.index', $vendor)
            ->with('success', __('admin.flash.product_created'));
    }

    public function edit(User $vendor, Product $product): View
    {
        $store = $this->managedStore($vendor);

        return view('admin.vendors.products.edit', [
            'vendor' => $vendor,
            'store' => $store,
            'product' => $this->findProduct($store, $product),
            'categoryOptions' => AdminOptions::categories('product'),
            'storageTempOptions' => AdminOptions::productStorageTemps(),
        ]);
    }

    public function update(ProductRequest $request, User $vendor, Product $product): RedirectResponse
    {
        $store = $this->managedStore($vendor);
        $product = $this->findProduct($store, $product);

        $attributes = $request->productAttributes();

        if ($request->hasFile('image')) {
            $this->deleteImage($product->image);
            $attributes['image'] = $this->storeImage($request->file('image'), 'products');
        }

        $product->update($attributes);

        return redirect()->route('admin.vendors.products.index', $vendor)
            ->with('success', __('admin.flash.product_updated'));
    }

    public function toggle(User $vendor, Product $product): RedirectResponse
    {
        $product = $this->findProduct($this->managedStore($vendor), $product);

        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', __('admin.flash.product_status_updated'));
    }

    public function destroy(User $vendor, Product $product): RedirectResponse
    {
        $product = $this->findProduct($this->managedStore($vendor), $product);

        $this->deleteImage($product->image);

        $product->delete();

        return back()->with('success', __('admin.flash.product_deleted'));
    }

    /**
     * The vendor store the dashboard edits, mirroring how the vendor API resolves it.
     */
    protected function managedStore(User $vendor): Store
    {
        return $vendor->stores()->orderBy('id')->firstOrFail();
    }

    /**
     * Resolve the product inside the vendor's managed store so a product from
     * another store or another vendor is treated as missing.
     */
    protected function findProduct(Store $store, Product $product): Product
    {
        return $store->products()->findOrFail($product->id);
    }
}
