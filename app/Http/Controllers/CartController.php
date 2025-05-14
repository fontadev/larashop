<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cartItems = $this->cartService->getContent();
        $subtotal = $this->cartService->getSubtotal();
        $shipping = $this->cartService->getShipping();
        $discount = $this->cartService->getDiscount();
        $total = $this->cartService->getTotal();
        $couponCode = $this->cartService->getCouponCode();

        return view('modules.cart.index', compact('cartItems', 'subtotal', 'shipping', 'discount', 'total', 'couponCode'));
    }

    public function add(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variation_id' => 'nullable|exists:product_variations,id',
        ]);

        $success = $this->cartService->add(
            $validatedData['product_id'],
            $validatedData['quantity'],
            $validatedData['variation_id'] ?? null
        );

        if ($success) {
            return redirect()->back()->with('success', 'Produto adicionado ao carrinho!');
        }

        return redirect()->back()->with('error', 'Não foi possível adicionar o produto. Estoque insuficiente.');
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'item_id' => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $success = $this->cartService->update(
            $validatedData['item_id'],
            $validatedData['quantity']
        );

        if ($success) {
            return redirect()->route('cart.index')->with('success', 'Carrinho atualizado!');
        }

        return redirect()->route('cart.index')->with('error', 'Não foi possível atualizar o carrinho. Estoque insuficiente.');
    }

    public function remove($itemId)
    {
        $this->cartService->remove($itemId);
        return redirect()->route('cart.index')->with('success', 'Item removido do carrinho!');
    }

    public function clear()
    {
        $this->cartService->clear();
        return redirect()->route('cart.index')->with('success', 'Carrinho esvaziado!');
    }

    public function applyCoupon(Request $request)
    {
        $validatedData = $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $success = $this->cartService->applyCoupon($validatedData['coupon_code']);

        if ($success) {
            return redirect()->route('cart.index')->with('success', 'Cupom aplicado com sucesso!');
        }

        return redirect()->route('cart.index')->with('error', 'Cupom inválido ou não aplicável.');
    }

    public function removeCoupon()
    {
        session()->forget('coupon');
        return redirect()->route('cart.index')->with('success', 'Cupom removido!');
    }

    public function checkCep(Request $request)
    {
        $validatedData = $request->validate([
            'cep' => 'required|string|size:8',
        ]);

        $cep = $validatedData['cep'];
        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        if ($response->successful() && !isset($response['erro'])) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'CEP não encontrado'], 404);
    }
}
