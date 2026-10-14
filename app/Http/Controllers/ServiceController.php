<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $categories = ServiceCategory::withCount('services')->orderBy('sort_order')->get();

        $query = Service::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $services = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('services.index', compact('services', 'categories'));
    }

    public function create()
    {
        $categories = ServiceCategory::where('is_active', true)->orderBy('name')->get();
        return view('services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:service_categories,id'],
            'code' => ['required', 'string', 'max:50', 'unique:services,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'buffer_time_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'requires_room' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['requires_room'] = $request->boolean('requires_room');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['cost_price'] = $validated['cost_price'] ?? 0.00;

        $service = Service::create($validated);

        return redirect()->route('services.index')->with('success', "Service {$service->name} added successfully.");
    }

    public function edit(Service $service)
    {
        $categories = ServiceCategory::where('is_active', true)->orderBy('name')->get();
        return view('services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:service_categories,id'],
            'code' => ['required', 'string', 'max:50', 'unique:services,code,' . $service->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'buffer_time_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'requires_room' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['requires_room'] = $request->boolean('requires_room');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['cost_price'] = $validated['cost_price'] ?? 0.00;

        $service->update($validated);

        return redirect()->route('services.index')->with('success', "Service {$service->name} updated.");
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Service deleted.');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['icon'] = $validated['icon'] ?: 'bi-stars';

        ServiceCategory::create($validated);

        return back()->with('success', 'Service category created successfully.');
    }
}
