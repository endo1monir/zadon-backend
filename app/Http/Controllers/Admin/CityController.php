<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CityRequest;
use App\Models\City;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request): View
    {
        $cities = City::query()
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request): void {
                $term = '%'.$request->string('search').'%';

                $query->where('name_ar', 'like', $term)->orWhere('name_en', 'like', $term);
            }))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cities.index', [
            'cities' => $cities,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function create(): View
    {
        return view('admin.cities.create');
    }

    public function store(CityRequest $request): RedirectResponse
    {
        City::create($request->validated());

        return redirect()->route('admin.cities.index')->with('success', 'City created successfully.');
    }

    public function edit(City $city): View
    {
        return view('admin.cities.edit', ['city' => $city]);
    }

    public function update(CityRequest $request, City $city): RedirectResponse
    {
        $city->update($request->validated());

        return redirect()->route('admin.cities.index')->with('success', 'City updated successfully.');
    }

    public function toggle(City $city): RedirectResponse
    {
        $city->update(['is_active' => ! $city->is_active]);

        return back()->with('success', 'City status updated.');
    }

    public function destroy(City $city): RedirectResponse
    {
        if ($city->stores()->exists() || $city->users()->exists()) {
            return back()->with('error', 'This city is in use and cannot be deleted.');
        }

        $city->delete();

        return back()->with('success', 'City deleted successfully.');
    }
}
