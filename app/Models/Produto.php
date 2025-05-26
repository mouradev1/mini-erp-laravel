<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'preco'];

    public function variacoes()
    {
        return $this->hasMany(Variacao::class);
    }

    public function estoques()
    {
        return $this->hasMany(Estoque::class);
    }
}
