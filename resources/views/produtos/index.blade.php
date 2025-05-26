@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2>Produtos</h2>
    <a href="{{ route('produtos.create') }}" class="btn btn-primary">Novo Produto</a>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Preço</th>
            <th>Estoque</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($produtos as $produto)
        <tr>
            <td>{{ $produto->nome }}</td>
            <td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
            <td>
                @if($produto->variacoes->count())
                @foreach($produto->variacoes as $var)
                {{ $var->nome }}:
                {{ $produto->estoques->where('variacao_id', $var->id)->first()?->quantidade ?? 0 }}<br>
                @endforeach
                @else
                {{ $produto->estoques->first()?->quantidade ?? 0 }}
                @endif
            </td>
            <td>
                <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-sm btn-warning">Editar</a>
                <form action="{{ route('produtos.comprar', $produto->id) }}" method="POST" class="d-inline">
                    @csrf
                    @if($produto->variacoes->count())
                    <select name="variacao_id" class="form-select form-select-sm d-inline w-auto mb-1">
                        <option value="">Selecione a variação</option>
                        @foreach($produto->variacoes as $var)
                        <option value="{{ $var->id }}">{{ $var->nome }} (R$ {{ number_format($produto->preco + $var->preco_adicional, 2, ',', '.') }})</option>
                        @endforeach
                    </select>
                    @endif
                    <input type="hidden" name="quantidade" value="1">
                    <button class="btn btn-sm btn-success">Comprar</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection