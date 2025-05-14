@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1>Criar Novo Cupom</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('coupons.store') }}"
                        method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="code"
                                class="form-label">Código do Cupom</label>
                            <input type="text"
                                class="form-control"
                                id="code"
                                name="code"
                                value="{{ old('code') }}"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="type"
                                class="form-label">Tipo de Desconto</label>
                            <select class="form-select"
                                id="type"
                                name="type"
                                required>
                                <option value="percentage"
                                    {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentual (%)</option>
                                <option value="fixed"
                                    {{ old('type') === 'fixed' ? 'selected' : '' }}>Valor Fixo (R$)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="value"
                                class="form-label">Valor do Desconto</label>
                            <input type="number"
                                class="form-control"
                                id="value"
                                name="value"
                                value="{{ old('value') }}"
                                step="0.01"
                                min="0"
                                required>
                            <small class="text-muted"
                                id="value-hint">Para percentual, use valores como 10, 15, 20, etc.</small>
                        </div>

                        <div class="mb-3">
                            <label for="min_value"
                                class="form-label">Valor Mínimo de Compra</label>
                            <input type="number"
                                class="form-control"
                                id="min_value"
                                name="min_value"
                                value="{{ old('min_value', 0) }}"
                                step="0.01"
                                min="0"
                                required>
                            <small class="text-muted">O valor mínimo do subtotal para o cupom ser válido</small>
                        </div>

                        <div class="mb-3">
                            <label for="expires_at"
                                class="form-label">Data de Expiração (opcional)</label>
                            <input type="date"
                                class="form-control"
                                id="expires_at"
                                name="expires_at"
                                value="{{ old('expires_at') }}">
                            <small class="text-muted">Deixe em branco para um cupom sem data de expiração</small>
                        </div>

                        <button type="submit"
                            class="btn btn-primary">Salvar Cupom</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const valueHint = document.getElementById('value-hint');

            typeSelect.addEventListener('change', function() {
                updateValueHint();
            });

            function updateValueHint() {
                if (typeSelect.value === 'percentage') {
                    valueHint.textContent = 'Para percentual, use valores como 10, 15, 20, etc.';
                } else {
                    valueHint.textContent = 'Para valor fixo, use o valor em reais (ex: 10.00, 15.50, etc.)';
                }
            }

            updateValueHint();
        });
    </script>
@endsection
