@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1>Meus Pedidos</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if ($orders->isEmpty())
                <div class="alert alert-info">
                    Você ainda não possui pedidos. <a href="{{ route('products.index') }}">Comece a comprar</a>.
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Pedido #</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr data-order-id="{{ $order->id }}">
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span
                                                    class="badge status-badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'completed' ? 'success' : ($order->status === 'canceled' ? 'danger' : 'info')) }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                                            <td>
                                                <a href="{{ route('orders.show', $order) }}"
                                                    class="btn btn-sm btn-info">Ver Detalhes</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="{{ asset('js/orderUpdates.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new OrderUpdatesManager({
                userId: {{ Auth::id() }}
            });
        });
    </script>

    <style>
        .table-highlight {
            animation: highlight 3s;
        }

        @keyframes highlight {
            0% {
                background-color: rgba(255, 243, 205, 0.5);
            }

            100% {
                background-color: transparent;
            }
        }

        #toast-container {
            z-index: 1090;
        }
    </style>
@endsection
