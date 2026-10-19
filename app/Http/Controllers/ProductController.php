<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::withCount('products')->get();

        $query = Product::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('search')) {
            $search = strtolower(trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(brand) LIKE ?', ["%{$search}%"]);
            });
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $lowStockTotal = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();

        return view('products.index', compact('products', 'categories', 'lowStockTotal'));
    }

    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:retail,backbar_consumable,both'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'retail_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['sku'] = strtoupper($validated['sku']);
        $validated['cost_price'] = $validated['cost_price'] ?? 0.00;
        $validated['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($validated);

        if ($product->stock_quantity > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'restock',
                'quantity' => $product->stock_quantity,
                'unit_cost' => $product->cost_price,
                'reference_number' => 'INITIAL',
                'notes' => 'Initial stock intake upon creation',
            ]);
        }

        return redirect()->route('products.index')->with('success', "Product {$product->name} added to inventory.");
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'barcode' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:retail,backbar_consumable,both'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'retail_price' => ['required', 'numeric', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['sku'] = strtoupper($validated['sku']);
        $validated['cost_price'] = $validated['cost_price'] ?? 0.00;
        $validated['is_active'] = $request->boolean('is_active');

        $product->update($validated);

        return redirect()->route('products.index')->with('success', "Product {$product->name} updated.");
    }

    public function restock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'not_in:0'],
            'type' => ['required', 'string', 'in:restock,adjustment,damage,service_usage'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $quantity = $validated['quantity'];
        // If damage or usage, ensure negative
        if (in_array($validated['type'], ['damage', 'service_usage']) && $quantity > 0) {
            $quantity = -$quantity;
        }

        $newStock = max(0, $product->stock_quantity + $quantity);
        $product->update(['stock_quantity' => $newStock]);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'quantity' => $quantity,
            'unit_cost' => $validated['unit_cost'] ?? $product->cost_price,
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Stock updated for {$product->name}. Current stock: {$newStock} {$product->unit}.");
    }

    public function destroy(Product $product)
    {
        if ($product->invoiceItems()->exists()) {
            return back()->with('error', "Cannot delete product '{$product->name}' because it has historical sales on invoices. Set it to inactive instead.");
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted from inventory.');
    }
}
