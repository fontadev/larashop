@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('LaraShop') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    Bem-vindo ao LaraShop, o seu aplicativo de e-commerce desenvolvido com Laravel!<br>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
