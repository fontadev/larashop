@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1>Gestão de Cupons</h1>
            <a href="{{ route('coupons.create') }}"
                class="btn btn-primary">Novo Cupom</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if ($coupons->isEmpty())
                <div class="alert alert-info">
                    Nenhum cupom cadastrado ainda.
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                        <th>Valor Mínimo</th>
                                        <th>Expira em</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($coupons as $coupon)
                                        <tr>
                                            <td>{{ $coupon->code }}</td>
                                            <td>{{ $coupon->type === 'percentage' ? 'Percentual' : 'Valor Fixo' }}</td>
                                            <td>
                                                @if ($coupon->type === 'percentage')
                                                    {{ $coupon->value }}%
                                                @else
                                                    R$ {{ number_format($coupon->value, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td>R$ {{ number_format($coupon->min_value, 2, ',', '.') }}</td>
                                            <td>
                                                @if ($coupon->expires_at)
                                                    {{ $coupon->expires_at->format('d/m/Y') }}
                                                @else
                                                    Sem expiração
                                                @endif
                                            </td>
                                            <td>
                                                @if ($coupon->isValid())
                                                    <span class="badge bg-success">Válido</span>
                                                @else
                                                    <span class="badge bg-danger">Expirado</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('coupons.edit', $coupon) }}"
                                                    class="btn btn-sm btn-warning">Editar</a>
                                                <form action="{{ route('coupons.destroy', $coupon) }}"
                                                    method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Tem certeza que deseja excluir este cupom?')">Excluir</button>
                                                </form>
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
@endsection
