@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1>Finalizar Pedido</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Endereço de Entrega</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('orders.store') }}"
                        method="POST"
                        id="checkout-form">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="cep"
                                    class="form-label">CEP</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control"
                                        id="cep"
                                        name="cep"
                                        maxlength="8"
                                        required>
                                    <button type="button"
                                        class="btn btn-outline-secondary"
                                        id="check-cep">Buscar</button>
                                </div>
                                <small class="text-muted">Digite apenas números</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="address"
                                    class="form-label">Endereço</label>
                                <input type="text"
                                    class="form-control"
                                    id="address"
                                    name="address"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label for="number"
                                    class="form-label">Número</label>
                                <input type="text"
                                    class="form-control"
                                    id="number"
                                    name="number"
                                    required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="complement"
                                    class="form-label">Complemento</label>
                                <input type="text"
                                    class="form-control"
                                    id="complement"
                                    name="complement">
                            </div>
                            <div class="col-md-6">
                                <label for="neighborhood"
                                    class="form-label">Bairro</label>
                                <input type="text"
                                    class="form-control"
                                    id="neighborhood"
                                    name="neighborhood"
                                    required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="city"
                                    class="form-label">Cidade</label>
                                <input type="text"
                                    class="form-control"
                                    id="city"
                                    name="city"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label for="state"
                                    class="form-label">Estado</label>
                                <input type="text"
                                    class="form-control"
                                    id="state"
                                    name="state"
                                    maxlength="2"
                                    required>
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-success">Finalizar Compra</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Resumo do Pedido</h5>
                </div>
                <div class="card-body">
                    @foreach ($cartItems as $item)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ $item['name'] }} {{ $item['variation_name'] ? "({$item['variation_name']})" : '' }} x
                                {{ $item['quantity'] }}</span>
                            <span>R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</span>
                        </div>
                    @endforeach

                    <hr>

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
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cepInput = document.getElementById('cep');
            const checkCepButton = document.getElementById('check-cep');

            checkCepButton.addEventListener('click', function() {
                const cep = cepInput.value.replace(/\D/g, '');

                if (cep.length !== 8) {
                    alert('Por favor, digite um CEP válido com 8 dígitos.');
                    return;
                }

                fetch(`{{ route('cart.check-cep') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            cep
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('CEP não encontrado');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.erro) {
                            alert('CEP não encontrado.');
                            return;
                        }

                        document.getElementById('address').value = data.logradouro;
                        document.getElementById('neighborhood').value = data.bairro;
                        document.getElementById('city').value = data.localidade;
                        document.getElementById('state').value = data.uf;

                        document.getElementById('number').focus();
                    })
                    .catch(error => {
                        alert('Erro ao consultar o CEP. Verifique se o CEP está correto.');
                        console.error('Erro:', error);
                    });
            });

            cepInput.addEventListener('input', function() {
                const cep = this.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    checkCepButton.click();
                }
            });
        });
    </script>
@endsection
