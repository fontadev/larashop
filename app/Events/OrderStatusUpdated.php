<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        Log::debug("OrderStatusUpdated construído para o pedido #{$order->id} com status {$order->status}");
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('user.' . $this->order->user_id),

            new PrivateChannel('order.' . $this->order->id),

            new Channel('orders'),
        ];

        Log::debug("OrderStatusUpdated para pedido #{$this->order->id} será transmitido nos canais:", [
            'private-user.' . $this->order->user_id,
            'private-order.' . $this->order->id,
            'orders'
        ]);

        return $channels;
    }

    public function broadcastAs(): string
    {
        Log::debug("Nome do evento broadcast definido como: order.updated");
        return 'order.updated';
    }

    public function broadcastWith(): array
    {
        $data = [
            'id' => $this->order->id,
            'status' => $this->order->status,
            'status_label' => ucfirst($this->order->status),
            'updated_at' => $this->order->updated_at->format('d/m/Y H:i'),
        ];

        Log::debug("Dados a serem transmitidos para o pedido #{$this->order->id}:", $data);

        return $data;
    }
}
