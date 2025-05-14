<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Stock;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['variations', 'stocks'])->get();
        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with(['variations', 'variations.stock', 'mainStock'])->findOrFail($id);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

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

        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

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

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);

        $product->delete();
        return response()->json(null, 204);
    }
}
