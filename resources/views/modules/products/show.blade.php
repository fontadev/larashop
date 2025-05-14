@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>{{ $product->name }}</h1>
            @if ($product->description)
                <p>{{ $product->description }}</p>
            @endif
        </div>
        <div class="col-md-4 text-end">
            @if (Auth::check() && Auth::user()->is_admin)
                <a href="{{ route('products.edit', $product) }}"
                    class="btn btn-warning">Editar</a>
                <form action="{{ route('products.destroy', $product) }}"
                    method="POST"
                    class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="btn btn-danger"
                        onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Detalhes do Produto</h5>
                </div>
                <div class="card-body">
                    <p><strong>Preço:</strong> R$ {{ number_format($product->price, 2, ',', '.') }}</p>
                    <p>
                        <strong>Estoque:</strong>
                        @if ($product->mainStock)
                            {{ $product->mainStock->quantity }}
                        @else
                            0
                        @endif
                    </p>

                    @if ($product->variations->count() > 0)
                        <h5 class="mt-3">Variações disponíveis:</h5>
                        <ul class="list-group">
                            @foreach ($product->variations as $variation)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $variation->name }}
                                    <span class="badge bg-primary rounded-pill">
                                        Estoque: {{ $variation->stock ? $variation->stock->quantity : 0 }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Comprar Produto</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('cart.add') }}"
                        method="POST">
                        @csrf
                        <input type="hidden"
                            name="product_id"
                            value="{{ $product->id }}">

                        @if ($product->variations->count() > 0)
                            <div class="mb-3">
                                <label for="variation_id"
                                    class="form-label">Escolha a variação:</label>
                                <select name="variation_id"
                                    id="variation_id"
                                    class="form-select"
                                    required>
                                    <option value="">Selecione uma variação</option>
                                    @foreach ($product->variations as $variation)
                                        @if ($variation->stock && $variation->stock->quantity > 0)
                                            <option value="{{ $variation->id }}">{{ $variation->name }} (Estoque:
                                                {{ $variation->stock->quantity }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="quantity"
                                class="form-label">Quantidade:</label>
                            <input type="number"
                                name="quantity"
                                id="quantity"
                                class="form-control"
                                min="1"
                                value="1"
                                required>
                        </div>

                        <button type="submit"
                            class="btn btn-success">Adicionar ao Carrinho</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
