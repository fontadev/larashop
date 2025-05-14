<?php

namespace App\Http\Controllers;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Stock;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleOrderStatus(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|string|in:pending,processing,completed,canceled',
        ]);

        $order = Order::findOrFail($validatedData['order_id']);
        $oldStatus = $order->status;
        $newStatus = $validatedData['status'];

        if ($oldStatus === $newStatus) {
            return response()->json([
                'message' => 'O status do pedido já é ' . $newStatus
            ]);
        }

        if ($newStatus === 'canceled' && $oldStatus !== 'canceled') {
            foreach ($order->items as $item) {
                Stock::where('product_id', $item->product_id)
                    ->where('product_variation_id', $item->product_variation_id)
                    ->increment('quantity', $item->quantity);

                Stock::where('product_id', $item->product_id)
                    ->whereNull('product_variation_id')
                    ->increment('quantity', $item->quantity);
            }
        }

        $order->update(['status' => $newStatus]);

        try {
            event(new OrderStatusUpdated($order));
            Log::info("Evento OrderStatusUpdated disparado para o pedido #{$order->id}");
        } catch (Exception $e) {
            Log::error("Erro ao disparar evento OrderStatusUpdated: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Status do pedido atualizado para ' . $newStatus,
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
    }
}
