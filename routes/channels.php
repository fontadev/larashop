<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

// Canal privado para o usuário - apenas o próprio usuário pode acessar
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal para acesso a detalhes de pedidos específicos
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    // Verificar se o usuário é dono do pedido ou é administrador
    $order = Order::find($orderId);
    return $order && ((int) $user->id === (int) $order->user_id || $user->is_admin);
});

// Canal para todos os pedidos (somente para administradores)
Broadcast::channel('orders', function ($user) {
    return $user->is_admin;
});
