@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2>Cupons</h2>
    <a href="{{ route('cupons.create') }}" class="btn btn-primary">Novo Cupom</a>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Código</th>
            <th>Desconto (%)</th>
            <th>Valor Mínimo</th>
            <th>Validade</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cupons as $cupom)
            <tr>
                <td>{{ $cupom->codigo }}</td>
                <td>{{ $cupom->desconto_percentual }}%</td>
                <td>R$ {{ number_format($cupom->valor_minimo, 2, ',', '.') }}</td>
                <td>{{ \Carbon\Carbon::parse($cupom->validade)->format('d/m/Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
