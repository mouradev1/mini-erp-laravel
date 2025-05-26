<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemPedido extends Model
{
    use HasFactory;
    protected $table = 'itens_pedidos';
    protected $fillable = [
        'pedido_id',
        'produto_id',
        'variacao_id',
        'quantidade',
        'preco_unitario',
        'total'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function variacao()
    {
        return $this->belongsTo(Variacao::class);
    }
}
