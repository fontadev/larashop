<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Stock;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->middleware('auth');
        $this->cartService = $cartService;
    }

    public function index()
    {
        $orders = User::find(Auth::id())
            ->orders()
            ->with('items.product', 'items.productVariation')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = compact([
            'orders'
        ]);

        return view('modules.orders.index', $data);
    }

    public function checkout()
    {
        $cartItems = $this->cartService->getContent();

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Seu carrinho está vazio.');
        }

        $subtotal = $this->cartService->getSubtotal();
        $shipping = $this->cartService->getShipping();
        $discount = $this->cartService->getDiscount();
        $total = $this->cartService->getTotal();
        $couponCode = $this->cartService->getCouponCode();

        return view('modules.orders.checkout', compact('cartItems', 'subtotal', 'shipping', 'discount', 'total', 'couponCode'));
    }

    public function store(Request $request)
    {
        $cartItems = $this->cartService->getContent();

        $user = Auth::user();

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Seu carrinho está vazio.');
        }

        $validatedData = $request->validate([
            'cep' => 'required|string|size:8',
            'address' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|size:2',
        ]);

        foreach ($cartItems as $itemId => $item) {
            $stock = null;
            if ($item['variation_id']) {
                $stock = Stock::where('product_id', $item['id'])
                    ->where('product_variation_id', $item['variation_id'])
                    ->first();
            } else {
                $stock = Stock::where('product_id', $item['id'])
                    ->whereNull('product_variation_id')
                    ->first();
            }

            if (!$stock || $stock->quantity < $item['quantity']) {
                return redirect()->route('cart.index')
                    ->with('error', "Estoque insuficiente para o produto: {$item['name']}");
            }
        }

        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => $this->cartService->getSubtotal(),
            'shipping' => $this->cartService->getShipping(),
            'discount' => $this->cartService->getDiscount(),
            'total' => $this->cartService->getTotal(),
            'coupon_code' => $this->cartService->getCouponCode(),
            'status' => 'pending',
            'cep' => $validatedData['cep'],
            'address' => $validatedData['address'],
            'number' => $validatedData['number'],
            'complement' => $validatedData['complement'] ?? null,
            'neighborhood' => $validatedData['neighborhood'],
            'city' => $validatedData['city'],
            'state' => $validatedData['state'],
        ]);

        foreach ($cartItems as $itemId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'product_variation_id' => $item['variation_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);

            Stock::where('product_id', $item['id'])
                ->where('product_variation_id', $item['variation_id'])
                ->decrement('quantity', $item['quantity']);

            Stock::where('product_id', $item['id'])
                ->whereNull('product_variation_id')
                ->decrement('quantity', $item['quantity']);
        }

        Mail::to($user->email)->send(new OrderConfirmation($order));

        $this->cartService->clear();

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pedido realizado com sucesso! Um e-mail de confirmação foi enviado.');
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $order->load('items.product', 'items.productVariation');
        return view('modules.orders.show', compact('order'));
    }
}
