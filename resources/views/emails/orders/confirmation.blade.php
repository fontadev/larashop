<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Confirmação de Pedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .order-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .order-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .order-items th,
        .order-items td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .order-items th {
            background-color: #f2f2f2;
        }

        .order-total {
            margin-top: 20px;
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Confirmação de Pedido</h1>
        <p>Obrigado por sua compra!</p>
    </div>

    <div class="order-info">
        <p><strong>Pedido #:</strong> {{ $order->id }}</p>
        <p><strong>Data:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
    </div>

    <h3>Itens do Pedido</h3>
    <table class="order-items">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Variação</th>
                <th>Quantidade</th>
                <th>Preço</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->productVariation ? $item->productVariation->name : 'N/A' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="order-total">
        <p><strong>Subtotal:</strong> R$ {{ number_format($order->subtotal, 2, ',', '.') }}</p>
        <p>
            <strong>Frete:</strong>
            @if ($order->shipping > 0)
                R$ {{ number_format($order->shipping, 2, ',', '.') }}
            @else
                Grátis
            @endif
        </p>

        @if ($order->discount > 0)
            <p><strong>Desconto:</strong> -R$ {{ number_format($order->discount, 2, ',', '.') }}</p>
        @endif

        <h3>Total: R$ {{ number_format($order->total, 2, ',', '.') }}</h3>

        @if ($order->coupon_code)
            <p><small>Cupom aplicado: {{ $order->coupon_code }}</small></p>
        @endif
    </div>

    <h3>Endereço de Entrega</h3>
    <div class="order-info">
        <p>
            {{ $order->address }}, {{ $order->number }}
            @if ($order->complement)
                , {{ $order->complement }}
            @endif
        </p>
        <p>{{ $order->neighborhood }}, {{ $order->city }} - {{ $order->state }}</p>
        <p>CEP: {{ $order->cep }}</p>
    </div>

    <div class="footer">
        <p>Este é um e-mail automático, não responda a esta mensagem.</p>
        <p>&copy; {{ date('Y') }} LaraShop. Todos os direitos reservados.</p>
    </div>
</body>

</html>
