@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1>Carrinho de Compras</h1>
        </div>
    </div>

    @if (empty($cartItems))
        <div class="alert alert-info">
            Seu carrinho está vazio. <a href="{{ route('home') }}">Continue comprando</a>.
        </div>
    @else
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Itens no Carrinho</h5>
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
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cartItems as $itemId => $item)
                                        <tr>
                                            <td>{{ $item['name'] }}</td>
                                            <td>{{ $item['variation_name'] ?? 'N/A' }}</td>
                                            <td>R$ {{ number_format($item['price'], 2, ',', '.') }}</td>
                                            <td>
                                                <form action="{{ route('cart.update') }}"
                                                    method="POST">
                                                    @csrf
                                                    <input type="hidden"
                                                        name="item_id"
                                                        value="{{ $itemId }}">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number"
                                                            name="quantity"
                                                            class="form-control"
                                                            value="{{ $item['quantity'] }}"
                                                            min="1"
                                                            style="max-width: 70px">
                                                        <button type="submit"
                                                            class="btn btn-outline-secondary">Atualizar</button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</td>
                                            <td>
                                                <a href="{{ route('cart.remove', $itemId) }}"
                                                    class="btn btn-sm btn-danger">Remover</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('cart.clear') }}"
                            class="btn btn-warning">Limpar Carrinho</a>
                        <a href="{{ route('home') }}"
                            class="btn btn-secondary">Continuar Comprando</a>
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
                            <span>R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Frete:</span>
                            <span>
                                @if ($shipping > 0)
                                    R$ {{ number_format($shipping, 2, ',', '.') }}
                                @else
                                    Grátis
                                @endif
                            </span>
                        </div>

                        @if ($discount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Desconto:</span>
                                <span>-R$ {{ number_format($discount, 2, ',', '.') }}</span>
                            </div>
                        @endif

                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span><strong>Total:</strong></span>
                            <span><strong>R$ {{ number_format($total, 2, ',', '.') }}</strong></span>
                        </div>

                        @if ($couponCode)
                            <div class="alert alert-success mb-3">
                                Cupom aplicado: {{ $couponCode }}
                                <a href="{{ route('cart.coupon.remove') }}"
                                    class="float-end text-danger">Remover</a>
                            </div>
                        @else
                            <form action="{{ route('cart.coupon') }}"
                                method="POST"
                                class="mb-3">
                                @csrf
                                <div class="input-group">
                                    <input type="text"
                                        name="coupon_code"
                                        class="form-control"
                                        placeholder="Código do cupom">
                                    <button type="submit"
                                        class="btn btn-outline-secondary">Aplicar</button>
                                </div>
                            </form>
                        @endif

                        @auth
                            <a href="{{ route('orders.checkout') }}"
                                class="btn btn-success d-block">Finalizar Compra</a>
                        @else
                            <div class="alert alert-warning mb-3">
                                <a href="{{ route('login') }}">Faça login</a> ou <a
                                    href="{{ route('register') }}">registre-se</a> para finalizar sua compra.
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
