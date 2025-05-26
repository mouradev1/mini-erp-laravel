@extends('layouts.app')

@section('content')
<h2>{{ isset($produto) ? 'Editar Produto' : 'Novo Produto' }}</h2>

<form action="{{ isset($produto) ? route('produtos.update', $produto->id) : route('produtos.store') }}" method="POST">
    @csrf
    @if(isset($produto)) @method('PUT') @endif

    <div class="mb-3">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" value="{{ $produto->nome ?? '' }}" required>
    </div>

    <div class="mb-3">
        <label>Preço</label>
        <input type="number" step="0.01" name="preco" class="form-control" value="{{ $produto->preco ?? '' }}" required>
    </div>

    <div class="mb-3">
        <label>Estoque Padrão (se não houver variações)</label>
        <input type="number" name="estoque{{ isset($produto) ? '_padrao' : '' }}" class="form-control"
            value="{{ isset($produto) ? ($produto->estoques->where('variacao_id', null)->first()?->quantidade ?? 0) : '' }}">
    </div>

    <div class="mb-3">
        <label>Variações</label>
        <div id="variacoes">
            @if(isset($produto) && $produto->variacoes->count())
                @foreach($produto->variacoes as $variacao)
                    <div class="border p-2 mb-2 variacao-item">
                        <input type="text" name="variacoes[{{ $variacao->id }}][nome]" placeholder="Nome" 
                            class="form-control mb-2" value="{{ $variacao->nome }}">
                        <input type="number" step="0.01" name="variacoes[{{ $variacao->id }}][preco_adicional]" 
                            placeholder="Preço adicional" class="form-control mb-2" 
                            value="{{ $variacao->preco_adicional }}">
                        <input type="number" name="variacoes[{{ $variacao->id }}][quantidade]" 
                            placeholder="Estoque" class="form-control mb-2"
                            value="{{ $produto->estoques->where('variacao_id', $variacao->id)->first()?->quantidade ?? 0 }}">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removerVariacao(this)">Remover</button>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" class="btn btn-sm btn-secondary" onclick="addVariacao()">Adicionar Variação</button>
    </div>

    <button class="btn btn-success">Salvar</button>
</form>

<script>
function addVariacao() {
    const div = document.createElement('div')
    div.classList.add('border', 'p-2', 'mb-2', 'variacao-item')
    const id = 'new_' + Date.now()
    div.innerHTML = `
        <input type="text" name="variacoes[${id}][nome]" placeholder="Nome" class="form-control mb-2">
        <input type="number" step="0.01" name="variacoes[${id}][preco_adicional]" placeholder="Preço adicional" class="form-control mb-2">
        <input type="number" name="variacoes[${id}][quantidade]" placeholder="Estoque" class="form-control mb-2">
        <button type="button" class="btn btn-danger btn-sm" onclick="removerVariacao(this)">Remover</button>
    `
    document.getElementById('variacoes').appendChild(div)
}
function removerVariacao(btn) {
    btn.closest('.variacao-item').remove()
}
</script>
@endsection