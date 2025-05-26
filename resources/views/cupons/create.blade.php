@extends('layouts.app')

@section('content')
<h2>Novo Cupom</h2>

<form action="{{ route('cupons.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label>Código</label>
        <input type="text" name="codigo" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Desconto (%)</label>
        <input type="number" step="0.01" name="desconto_percentual" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Valor Mínimo</label>
        <input type="number" step="0.01" name="valor_minimo" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Validade</label>
        <input type="date" name="validade" class="form-control" required>
    </div>

    <button class="btn btn-success">Salvar</button>
</form>
@endsection
