@extends('layouts.app')

@section('content')
<h2>Carrinho</h2>

@if (count($itens))
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Produto</th>
            <th>Variação</th>
            <th>Quantidade</th>
            <th>Preço</th>
            <th>Total</th>
        </tr>
    </thead>
<tbody>
    @foreach ($itens as $item)
    <tr>
        <td>{{ is_object($item['produto']) ? $item['produto']->nome : ($item['produto_nome'] ?? '-') }}</td>
        <td>
            @if(isset($item['variacao']) && is_object($item['variacao']))
                {{ $item['variacao']->nome }}
            @elseif(isset($item['variacao_nome']))
                {{ $item['variacao_nome'] }}
            @else
                -
            @endif
        </td>
        <td>{{ $item['quantidade'] }}</td>
        <td>R$ {{ number_format($item['preco'], 2, ',', '.') }}</td>
        <td>R$ {{ number_format($item['total'], 2, ',', '.') }}</td>
    </tr>
    @endforeach
</tbody>
</table>

<p>Subtotal: <strong>R$ {{ number_format($subtotal, 2, ',', '.') }}</strong></p>
<p>Frete: <strong>R$ {{ number_format($frete, 2, ',', '.') }}</strong></p>

<form action="{{ route('pedido.finalizar') }}" method="POST">
    @csrf
    <div class="mb-2">
        <label>Nome</label>
        <input type="text" name="cliente_nome" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>Email</label>
        <input type="email" name="cliente_email" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>CEP</label>
        <input type="text" name="cep" id="cep" class="form-control" required>
    </div>
    <div class="mb-2">
        <label>Endereço</label>
        <input type="text" name="endereco" class="form-control" required>
    </div>

    <div class="mb-2">
        <label>Aplicar Cupom</label>
        <select name="cupom" class="form-select">
            <option value="">Nenhum</option>
            @foreach($cupons as $cupom)
            <option value="{{ $cupom->codigo }}">{{ $cupom->codigo }} - {{ $cupom->desconto_percentual }}% (mín. R$ {{ number_format($cupom->valor_minimo, 2, ',', '.') }})</option>
            @endforeach
        </select>
    </div>

    <button class="btn btn-success">Finalizar Pedido</button>
    <a href="{{ route('pedido.carrinho.limpar') }}" class="btn btn-danger">Limpar Carrinho</a>
</form>
@else
<p>Carrinho vazio.</p>
@endif
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('cep').addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.querySelector('input[name="endereco"]').value =
                                `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
                        } else {
                            alert('CEP não encontrado.');
                        }
                    })
                    .catch(() => alert('Erro ao buscar CEP.'));
            }
        });
    });
</script>