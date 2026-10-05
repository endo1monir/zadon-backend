<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $paymentMethods = PaymentMethod::query()
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search').'%';

                $query->where('name_ar', 'like', $term)
                    ->orWhere('name_en', 'like', $term)
                    ->orWhere('key', 'like', $term);
            }))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->paginate(15)
            ->withQueryString();

        return view('admin.payment-methods.index', [
            'paymentMethods' => $paymentMethods,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function create(): View
    {
        return view('admin.payment-methods.create');
    }

    public function store(PaymentMethodRequest $request): RedirectResponse
    {
        $attributes = $request->safe()->except('icon');

        if ($request->hasFile('icon')) {
            $attributes['icon'] = $this->storeImage($request->file('icon'), 'payment-methods');
        }

        PaymentMethod::create($attributes);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', __('admin.flash.payment_method_created'));
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.edit', ['paymentMethod' => $paymentMethod]);
    }

    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $attributes = $request->safe()->except('icon');

        if ($request->hasFile('icon')) {
            $this->deleteImage($paymentMethod->icon);
            $attributes['icon'] = $this->storeImage($request->file('icon'), 'payment-methods');
        }

        $paymentMethod->update($attributes);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', __('admin.flash.payment_method_updated'));
    }

    public function toggle(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update(['is_active' => ! $paymentMethod->is_active]);

        return back()->with('success', __('admin.flash.payment_method_status_updated'));
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->deleteImage($paymentMethod->icon);

        $paymentMethod->delete();

        return back()->with('success', __('admin.flash.payment_method_deleted'));
    }
}
