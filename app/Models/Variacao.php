<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variacao extends Model
{
    use HasFactory;
    protected $table = 'variacoes';
    protected $fillable = ['produto_id', 'nome', 'preco_adicional'];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function estoque()
    {
        return $this->hasOne(Estoque::class);
    }
}
