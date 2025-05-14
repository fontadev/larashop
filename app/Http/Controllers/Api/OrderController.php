<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmation;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->orders()->with('items.product', 'items.productVariation');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function show($id, Request $request)
    {
        $order = Order::with([
            'items.product',
            'items.productVariation',
            'user:id,name,email'
        ])->findOrFail($id);

        if ($request->user()->id !== $order->user_id && !$request->user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Você não tem permissão para ver este pedido'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'cep' => 'required|string|size:8',
            'address' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|size:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de validação inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $items = $request->items;
            $subtotal = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variationId = $item['variation_id'] ?? null;

                if ($variationId) {
                    $stock = Stock::where('product_id', $product->id)
                        ->where('product_variation_id', $variationId)
                        ->first();
                } else {
                    $stock = Stock::where('product_id', $product->id)
                        ->whereNull('product_variation_id')
                        ->first();
                }

                if (!$stock || $stock->quantity < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => "Estoque insuficiente para o produto: {$product->name}"
                    ], 422);
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_variation_id' => $variationId,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];
            }

            $shipping = $this->calculateShipping($subtotal);

            $discount = 0;
            $couponCode = $request->coupon_code;

            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();

                if (!$coupon || !$coupon->active || ($coupon->expires_at && $coupon->expires_at->isPast())) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cupom inválido ou expirado'
                    ], 422);
                }

                if ($subtotal < $coupon->min_value) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => "Valor mínimo para este cupom: R$ {$coupon->min_value}"
                    ], 422);
                }

                if ($coupon->type === 'percentage') {
                    $discount = $subtotal * ($coupon->value / 100);
                } else {
                    $discount = min($coupon->value, $subtotal);
                }

                if ($coupon->usage_limit) {
                    $coupon->increment('used_count');
                }
            }

            $total = $subtotal + $shipping - $discount;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'coupon_code' => $couponCode,
                'status' => 'pending',
                'cep' => $request->cep,
                'address' => $request->address,
                'number' => $request->number,
                'complement' => $request->complement,
                'neighborhood' => $request->neighborhood,
                'city' => $request->city,
                'state' => $request->state,
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variation_id' => $item['product_variation_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                Stock::where('product_id', $item['product_id'])
                    ->where('product_variation_id', $item['product_variation_id'])
                    ->decrement('quantity', $item['quantity']);

                Stock::where('product_id', $item['product_id'])
                    ->whereNull('product_variation_id')
                    ->decrement('quantity', $item['quantity']);
            }

            try {
                Mail::to($request->user()->email)->send(new OrderConfirmation($order));
            } catch (\Exception $e) {
                Log::error('Erro ao enviar e-mail de confirmação: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido criado com sucesso',
                'data' => [
                    'order_id' => $order->id,
                    'order' => $order->load('items.product', 'items.productVariation')
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar o pedido',
                'debug' => app()->environment('production') ? null : $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if (!$request->user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Somente administradores podem atualizar o status de pedidos'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,processing,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return response()->json([
                'status' => 'success',
                'message' => 'O pedido já está com este status',
                'data' => $order
            ]);
        }

        DB::beginTransaction();

        try {
            if ($newStatus === 'canceled' && $oldStatus !== 'canceled') {
                foreach ($order->items as $item) {
                    if ($item->product_variation_id) {
                        Stock::where('product_id', $item->product_id)
                            ->where('product_variation_id', $item->product_variation_id)
                            ->increment('quantity', $item->quantity);
                    } else {
                        Stock::where('product_id', $item->product_id)
                            ->whereNull('product_variation_id')
                            ->increment('quantity', $item->quantity);
                    }
                }
            }

            $order->status = $newStatus;
            $order->save();

            event(new OrderStatusUpdated($order));

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status do pedido atualizado com sucesso',
                'data' => $order->load('items.product', 'items.productVariation')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao atualizar o status do pedido',
                'debug' => app()->environment('production') ? null : $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($request->user()->id !== $order->user_id && !$request->user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Você não tem permissão para cancelar este pedido'
            ], 403);
        }

        if ($order->status === 'canceled') {
            return response()->json([
                'status' => 'success',
                'message' => 'Este pedido já está cancelado',
                'data' => $order
            ]);
        }

        if ($order->status === 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Não é possível cancelar um pedido já concluído'
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($order->items as $item) {
                if ($item->product_variation_id) {
                    Stock::where('product_id', $item->product_id)
                        ->where('product_variation_id', $item->product_variation_id)
                        ->increment('quantity', $item->quantity);
                } else {
                    Stock::where('product_id', $item->product_id)
                        ->whereNull('product_variation_id')
                        ->increment('quantity', $item->quantity);
                }
            }

            $order->status = 'canceled';
            $order->save();

            event(new OrderStatusUpdated($order));

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido cancelado com sucesso',
                'data' => $order->load('items.product', 'items.productVariation')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao cancelar o pedido',
                'debug' => app()->environment('production') ? null : $e->getMessage()
            ], 500);
        }
    }

    private function calculateShipping($subtotal)
    {
        if ($subtotal >= 200) {
            return 0;
        } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15;
        }

        return 20;
    }
}
