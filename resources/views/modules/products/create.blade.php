@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1>Criar Novo Produto</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('products.store') }}"
                        method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name"
                                class="form-label">Nome do Produto</label>
                            <input type="text"
                                class="form-control"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="price"
                                class="form-label">Preço (R$)</label>
                            <input type="number"
                                class="form-control"
                                id="price"
                                name="price"
                                value="{{ old('price') }}"
                                step="0.01"
                                min="0"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="description"
                                class="form-label">Descrição (opcional)</label>
                            <textarea class="form-control"
                                id="description"
                                name="description"
                                rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="stock_quantity"
                                class="form-label">Quantidade em Estoque</label>
                            <input type="number"
                                class="form-control"
                                id="stock_quantity"
                                name="stock_quantity"
                                value="{{ old('stock_quantity') }}"
                                min="0"
                                required>
                        </div>

                        <div class="mb-4">
                            <h4>Variações</h4>
                            <p class="text-muted">Deixe em branco se o produto não possui variações.</p>

                            <div id="variations-container">
                                <div class="variation-item mb-3 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Nome da Variação</label>
                                            <input type="text"
                                                class="form-control"
                                                name="variations[0][name]">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Estoque</label>
                                            <input type="number"
                                                class="form-control"
                                                name="variations[0][stock]"
                                                min="0"
                                                value="0">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button"
                                                class="btn btn-danger remove-variation">X</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button"
                                class="btn btn-secondary"
                                id="add-variation">Adicionar Variação</button>
                        </div>

                        <button type="submit"
                            class="btn btn-primary">Salvar Produto</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let variationIndex = 0;

            document.getElementById('add-variation').addEventListener('click', function() {
                variationIndex++;

                const variationHtml = `
                    <div class="variation-item mb-3 p-3 border rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Nome da Variação</label>
                                <input type="text" class="form-control" name="variations[${variationIndex}][name]" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Estoque</label>
                                <input type="number" class="form-control" name="variations[${variationIndex}][stock]" min="0" value="0" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-variation">X</button>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('variations-container').insertAdjacentHTML('beforeend',
                    variationHtml);

                updateRemoveButtons();
            });

            function updateRemoveButtons() {
                document.querySelectorAll('.remove-variation').forEach(button => {
                    button.addEventListener('click', function() {
                        this.closest('.variation-item').remove();
                    });
                });
            }

            updateRemoveButtons();
        });
    </script>
@endsection
