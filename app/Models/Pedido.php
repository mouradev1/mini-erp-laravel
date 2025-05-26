<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_nome',
        'cliente_email',
        'cep',
        'endereco',
        'subtotal',
        'frete',
        'total',
        'status'
    ];

    public function itens()
    {
        return $this->hasMany(ItemPedido::class);
    }
    
}
