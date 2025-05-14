@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1>Pedido #{{ $order->id }}</h1>
            <p>
                <strong>Status:</strong>
                <span
                    class="badge order-status-badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'completed' ? 'success' : ($order->status === 'canceled' ? 'danger' : 'info')) }}">
                    {{ ucfirst($order->status) }}
                </span>
            </p>
            <p><strong>Data:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Itens do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Variação</th>
                                    <th>Preço</th>
                                    <th>Quantidade</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->productVariation ? $item->productVariation->name : 'N/A' }}</td>
                                        <td>R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Resumo do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Frete:</span>
                        <span>
                            @if ($order->shipping > 0)
                                R$ {{ number_format($order->shipping, 2, ',', '.') }}
                            @else
                                Grátis
                            @endif
                        </span>
                    </div>

                    @if ($order->discount > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Desconto:</span>
                            <span>-R$ {{ number_format($order->discount, 2, ',', '.') }}</span>
                        </div>
                    @endif

                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span><strong>Total:</strong></span>
                        <span><strong>R$ {{ number_format($order->total, 2, ',', '.') }}</strong></span>
                    </div>

                    @if ($order->coupon_code)
                        <div class="alert alert-success mb-3">
                            Cupom aplicado: {{ $order->coupon_code }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Endereço de Entrega</h5>
                </div>
                <div class="card-body">
                    <p>
                        {{ $order->address }}, {{ $order->number }}
                        @if ($order->complement)
                            , {{ $order->complement }}
                        @endif
                    </p>
                    <p>{{ $order->neighborhood }}, {{ $order->city }} - {{ $order->state }}</p>
                    <p>CEP: {{ $order->cep }}</p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/orderUpdates.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new OrderUpdatesManager({
                userId: {{ Auth::id() }},
                currentOrderId: {{ $order->id }}
            });
        });
    </script>

    <style>
        #toast-container {
            z-index: 1090;
        }
    </style>
@endsection
