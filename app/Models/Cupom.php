<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cupom extends Model
{
    use HasFactory;
    protected $table = 'cupons';
    protected $fillable = ['codigo', 'desconto_percentual', 'valor_minimo', 'validade'];
}
