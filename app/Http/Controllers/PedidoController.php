<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Variacao;
use App\Models\Estoque;
use App\Models\Cupom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\ItemPedido;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{

    public function carrinho()
    {
        $carrinho = Session::get('carrinho', []);
        $itens = [];
        $subtotal = 0;

        foreach ($carrinho as $item) {
            // dd($item);
            $produto = Produto::find($item['produto_id']);
            $variacao = $item['variacao_id'] ? Variacao::find($item['variacao_id']) : null;

            $preco_base = $produto->preco;
            $preco_variacao = $variacao ? $variacao->preco_adicional : 0;
            $preco = $preco_base + $preco_variacao;

            $total = $preco * $item['quantidade'];
            $subtotal += $total;

            $itens[] = [
                'produto' => $produto,
                'variacao' => $variacao,
                'quantidade' => $item['quantidade'],
                'preco_base' => $preco_base,
                'preco_variacao' => $preco_variacao,
                'preco' => $preco,
                'total' => $total
            ];
        }

        $frete = 20;
        if ($subtotal >= 52 && $subtotal <= 166.59) {
            $frete = 15;
        } elseif ($subtotal > 200) {
            $frete = 0;
        }

        $cupons = Cupom::all();

        return view('pedido.carrinho', compact('itens', 'subtotal', 'frete', 'cupons'));
    }

    public function webhook(Request $request)
    {
        $pedido = Pedido::find($request->id);
        if (!$pedido) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        if ($request->status === 'cancelado') {
            foreach ($pedido->itens as $item) {
                $estoque = Estoque::where('produto_id', $item->produto_id)
                    ->where('variacao_id', $item->variacao_id)
                    ->first();

                if ($estoque) {
                    $estoque->increment('quantidade', $item->quantidade);
                }
            }
            try {
                $this->notificarEmail(
                    $pedido,
                    "Cancelamento do Pedido #{$pedido->id}",
                    "Seu pedido foi cancelado. Se tiver dúvidas, entre em contato."
                );
                Log::info("E-mail de cancelamento enviado para {$pedido->cliente_email}.");
            } catch (\Exception $e) {
                Log::error("Erro ao enviar e-mail de cancelamento do pedido: " . $e->getMessage());
            }
        } else {
            try {
                $this->notificarEmail(
                    $pedido,
                    "Atualização do Pedido #{$pedido->id}",
                    "Seu pedido foi atualizado para o status: {$request->status}."
                );
                Log::info("E-mail de atualização enviado para {$pedido->cliente_email}.");
            } catch (\Exception $e) {
                Log::error("Erro ao enviar e-mail de atualização do pedido: " . $e->getMessage());
            }
            $pedido->update(['status' => $request->status]);
        }

        return response()->json(['message' => 'Webhook processado']);
    }


    public function finalizar(Request $request)
    {
        $request->validate([
            'cliente_nome' => 'required|string|max:255',
            'cliente_email' => 'required|email',
            'cep' => 'required|string',
            'endereco' => 'required|string',
            'cupom' => 'nullable|string',
        ]);

        $carrinho = session('carrinho', []);
        if (empty($carrinho)) {
            return back()->withErrors('Carrinho vazio.');
        }

        DB::beginTransaction();

        try {
            $subtotal = 0;
            foreach ($carrinho as $item) {
                $subtotal += $item['preco'] * $item['quantidade'];
            }

            $frete = 20.00;
            if ($subtotal >= 52 && $subtotal <= 166.59) {
                $frete = 15.00;
            } elseif ($subtotal > 200) {
                $frete = 0.00;
            }

            $cupom = null;
            $desconto = 0;
            if ($request->cupom) {
                $cupom = Cupom::where('codigo', $request->cupom)
                    ->whereDate('validade', '>=', now())
                    ->first();

                if (!$cupom) {
                    DB::rollBack();
                    return back()->withErrors('Cupom inválido ou expirado.');
                }
                if ($subtotal < $cupom->valor_minimo) {
                    DB::rollBack();
                    return back()->withErrors('O valor mínimo para este cupom é R$ ' . number_format($cupom->valor_minimo, 2, ',', '.'));
                }
                $desconto = ($subtotal * $cupom->desconto_percentual) / 100;
            }

            $total = $subtotal + $frete - $desconto;

            $pedido = Pedido::create([
                'cliente_nome' => $request->cliente_nome,
                'cliente_email' => $request->cliente_email,
                'cep' => $request->cep,
                'endereco' => $request->endereco,
                'subtotal' => $subtotal,
                'frete' => $frete,
                'total' => $total,
                'status' => 'aguardando',
            ]);

            Log::info("Pedido #{$pedido->id} criado com sucesso.");
            foreach ($carrinho as $item) {
                Log::info("Processando item do carrinho: " . json_encode($item));
                $produto = Produto::find($item['produto_id']);
                $variacao_id = $item['variacao_id'] ?? null;
                Log::info("Processando item: Produto ID {$produto->id}, Variação ID {$variacao_id}, Quantidade {$item['quantidade']}");
                $estoque = Estoque::where('produto_id', $produto->id)
                    ->where('variacao_id', $variacao_id)
                    ->first();
                Log::info("Estoque encontrado: " . ($estoque ? "ID {$estoque->id}, Quantidade {$estoque->quantidade}" : "Nenhum estoque encontrado"));
                if (!$estoque || $estoque->quantidade < $item['quantidade']) {
                    DB::rollBack();
                    return back()->withErrors("Estoque insuficiente para o produto {$produto->nome}" . ($variacao_id ? " (variação)" : ""));
                }

                $estoque->decrement('quantidade', $item['quantidade']);

                try {
                    ItemPedido::create([
                        'pedido_id' => $pedido->id,
                        'produto_id' => $produto->id,
                        'variacao_id' => $variacao_id ?? null,
                        'quantidade' => $item['quantidade'],
                        'preco_unitario' => (float)$item['preco'],
                        'total' => (float)$item['preco'] * $item['quantidade'],
                    ]);
                    Log::info("Item adicionado ao pedido: Produto ID {$produto->id}, Variação ID {$variacao_id}, Quantidade {$item['quantidade']}");
                } catch (\Exception $e) {
                    Log::error("Erro ao adicionar item ao pedido: " . $e->getMessage());
                    DB::rollBack();
                    return back()->withErrors('Erro ao adicionar item ao pedido: ' . $e->getMessage());
                }
            }

            DB::commit();
            Log::info("Pedido #{$pedido->id} finalizado com sucesso.");
            session()->forget('carrinho');
            Log::info('Carrinho limpo após finalização do pedido.');

            try {
                $this->notificarEmail(
                    $pedido,
                    "Confirmação do Pedido #{$pedido->id}",
                    "Obrigado por seu pedido! Detalhes: \n" .
                        "Subtotal: R$ " . number_format($pedido->subtotal, 2, ',', '.') . "\n" .
                        "Frete: R$ " . number_format($pedido->frete, 2, ',', '.') . "\n" .
                        "Total: R$ " . number_format($pedido->total, 2, ',', '.') . "\n" .
                        "Status: {$pedido->status}"
                );
                Log::info("E-mail de confirmação enviado para {$pedido->cliente_email}.");
            } catch (\Exception $e) {
                Log::error("Erro ao enviar e-mail: " . $e->getMessage());
            }

            return redirect()->route('produtos.index')->with('success', 'Pedido finalizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pedido: ' . $e->getMessage());
            return back()->withErrors('Erro ao processar pedido: ' . $e->getMessage());
        }
    }
    public function limparCarrinho()
    {
        Session::forget('carrinho');
        return redirect()->route('produtos.index')->with('success', 'Carrinho limpo com sucesso.');
    }

    protected function notificarEmail(Pedido $pedido, string $assunto, string $mensagem)
    {
        try {
            Mail::raw(
                $mensagem,
                function ($message) use ($pedido, $assunto) {
                    $message->to($pedido->cliente_email)
                        ->subject($assunto);
                }
            );
            Log::info("E-mail enviado para {$pedido->cliente_email} com assunto: {$assunto}");
        } catch (\Exception $e) {
            Log::error("Erro ao enviar e-mail: " . $e->getMessage());
        }
    }
}
