@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1>Produtos</h1>
            @if (Auth::check() && Auth::user()->is_admin)
                <a href="{{ route('products.create') }}"
                    class="btn btn-primary">Novo Produto</a>
            @endif
        </div>
    </div>

    <div class="row">
        @forelse($products as $product)
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text">
                            <strong>Preço:</strong> R$ {{ number_format($product->price, 2, ',', '.') }}
                        </p>
                        <p class="card-text">
                            <strong>Estoque:</strong>
                            @if ($product->mainStock)
                                {{ $product->mainStock->quantity }}
                            @else
                                0
                            @endif
                        </p>
                        <a href="{{ route('products.show', $product) }}"
                            class="btn btn-info">Ver Detalhes</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-md-12">
                <div class="alert alert-info">
                    Nenhum produto disponível.
                </div>
            </div>
        @endforelse
    </div>
@endsection
