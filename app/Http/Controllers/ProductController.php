<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Stock;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $products = Product::with(['variations', 'stocks'])->get();
        return view('modules.products.index', compact('products'));
    }

    public function create()
    {
        return view('modules.products.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'variations' => 'nullable|array',
            'variations.*.name' => 'required|string|max:255',
            'variations.*.stock' => 'required|integer|min:0',
        ]);

        $product = Product::create([
            'name' => $validatedData['name'],
            'price' => $validatedData['price'],
            'description' => $validatedData['description'] ?? null,
        ]);

        Stock::create([
            'product_id' => $product->id,
            'quantity' => $validatedData['stock_quantity'],
        ]);

        if (isset($validatedData['variations'])) {
            foreach ($validatedData['variations'] as $variationData) {
                $variation = ProductVariation::create([
                    'product_id' => $product->id,
                    'name' => $variationData['name'],
                ]);

                Stock::create([
                    'product_id' => $product->id,
                    'product_variation_id' => $variation->id,
                    'quantity' => $variationData['stock'],
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Produto criado com sucesso!');
    }

    public function show(Product $product)
    {
        $product->load(['variations', 'variations.stock', 'mainStock']);
        return view('modules.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['variations', 'variations.stock', 'mainStock']);
        return view('modules.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.name' => 'required|string|max:255',
            'variations.*.stock' => 'required|integer|min:0',
        ]);

        $product->update([
            'name' => $validatedData['name'],
            'price' => $validatedData['price'],
            'description' => $validatedData['description'] ?? null,
        ]);

        $mainStock = $product->mainStock;
        if ($mainStock) {
            $mainStock->update(['quantity' => $validatedData['stock_quantity']]);
        } else {
            Stock::create([
                'product_id' => $product->id,
                'quantity' => $validatedData['stock_quantity'],
            ]);
        }

        if (isset($validatedData['variations'])) {
            $existingVariationIds = [];

            foreach ($validatedData['variations'] as $variationData) {
                if (isset($variationData['id'])) {
                    $variation = ProductVariation::find($variationData['id']);
                    $variation->update(['name' => $variationData['name']]);

                    $stock = $variation->stock;
                    if ($stock) {
                        $stock->update(['quantity' => $variationData['stock']]);
                    } else {
                        Stock::create([
                            'product_id' => $product->id,
                            'product_variation_id' => $variation->id,
                            'quantity' => $variationData['stock'],
                        ]);
                    }

                    $existingVariationIds[] = $variation->id;
                } else {
                    $variation = ProductVariation::create([
                        'product_id' => $product->id,
                        'name' => $variationData['name'],
                    ]);

                    Stock::create([
                        'product_id' => $product->id,
                        'product_variation_id' => $variation->id,
                        'quantity' => $variationData['stock'],
                    ]);

                    $existingVariationIds[] = $variation->id;
                }
            }

            $product->variations()->whereNotIn('id', $existingVariationIds)->delete();
        } else {
            $product->variations()->delete();
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Produto excluído com sucesso!');
    }
}
