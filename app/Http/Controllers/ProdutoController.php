<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Variacao;
use App\Models\Estoque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProdutoController extends Controller
{
    public function index()
    {
        $produtos = Produto::with('variacoes', 'estoques')->get();
        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        return view('produtos.create');
    }

    public function store(Request $request)
    {
        $produto = Produto::create($request->only(['nome', 'preco']));

        if ($request->has('variacoes') && is_array($request->variacoes)) {
            foreach ($request->variacoes as $var) {
                $variacao = Variacao::create([
                    'produto_id' => $produto->id,
                    'nome' => $var['nome'],
                    'preco_adicional' => $var['preco_adicional'] ?? 0
                ]);

                Estoque::create([
                    'produto_id' => $produto->id,
                    'variacao_id' => $variacao->id,
                    'quantidade' => $var['quantidade'] ?? 0
                ]);
            }
        }

        // Se não houver variações, cria estoque padrão
        if (!$request->has('variacoes') || empty($request->variacoes)) {
            Estoque::create([
                'produto_id' => $produto->id,
                'quantidade' => $request->estoque ?? 0
            ]);
        }

        return redirect()->route('produtos.index');
    }

    public function edit($id)
    {
        $produto = Produto::with('variacoes', 'estoques')->findOrFail($id);
        return view('produtos.edit', compact('produto'));
    }

    public function update(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $produto->update($request->only(['nome', 'preco']));

        if ($request->variacoes) {
            foreach ($request->variacoes as $var_id => $var) {
                // Se for uma variação nova (id começa com 'new_')
                if (str_starts_with($var_id, 'new_')) {
                    $novaVariacao = Variacao::create([
                        'produto_id' => $produto->id,
                        'nome' => $var['nome'],
                        'preco_adicional' => $var['preco_adicional'] ?? 0
                    ]);
                    Estoque::create([
                        'produto_id' => $produto->id,
                        'variacao_id' => $novaVariacao->id,
                        'quantidade' => $var['quantidade'] ?? 0
                    ]);
                } else {
                    // Atualiza variação existente
                    $variacao = Variacao::find($var_id);
                    if ($variacao) {
                        $variacao->update([
                            'nome' => $var['nome'],
                            'preco_adicional' => $var['preco_adicional'] ?? 0
                        ]);
                        $estoque = Estoque::where('produto_id', $produto->id)
                            ->where('variacao_id', $var_id)
                            ->first();
                        if ($estoque) {
                            $estoque->update(['quantidade' => $var['quantidade'] ?? 0]);
                        } else {
                            // Cria estoque se não existir
                            Estoque::create([
                                'produto_id' => $produto->id,
                                'variacao_id' => $var_id,
                                'quantidade' => $var['quantidade'] ?? 0
                            ]);
                        }
                    }
                }
            }
        }

        if ($request->estoque_padrao !== null) {
            $estoque = Estoque::where('produto_id', $produto->id)
                ->whereNull('variacao_id')
                ->first();

            if ($estoque) {
                $estoque->update(['quantidade' => $request->estoque_padrao]);
            } else {
                Estoque::create([
                    'produto_id' => $produto->id,
                    'quantidade' => $request->estoque_padrao
                ]);
            }
        }

        return redirect()->route('produtos.index');
    }

    public function comprar(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $quantidade = $request->quantidade ?? 1;
        $variacao_id = $request->variacao_id ?? null;
        $variacao = null;
        $variacao_nome = null;
        $preco = $produto->preco;

        if ($variacao_id) {
            $variacao = $produto->variacoes()->where('id', $variacao_id)->first();
            if ($variacao) {
                $preco += $variacao->preco_adicional;
                $variacao_nome = $variacao->nome;
            }
        }

        $carrinho = Session::get('carrinho', []);
        $item_id = $id . '-' . ($variacao_id ?? '0');

        if (isset($carrinho[$item_id])) {
            $carrinho[$item_id]['quantidade'] += $quantidade;
        } else {
            $carrinho[$item_id] = [
                'produto_id' => $id,
                'produto_nome' => $produto->nome,
                'variacao_id' => $variacao_id,
                'variacao_nome' => $variacao_nome,
                'preco' => $preco,
                'quantidade' => $quantidade,
            ];
        }

        Session::put('carrinho', $carrinho);

        return redirect()->route('pedido.carrinho');
    }
}
