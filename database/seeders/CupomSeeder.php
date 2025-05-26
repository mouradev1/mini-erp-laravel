<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cupom;

class CupomSeeder extends Seeder
{
    public function run(): void
    {
        Cupom::create([
            'codigo' => 'DESCONTO10',
            'desconto_percentual' => 10,
            'valor_minimo' => 50,
            'validade' => now()->addDays(30),
        ]);

        Cupom::create([
            'codigo' => 'FRETEGRATIS',
            'desconto_percentual' => 100,
            'valor_minimo' => 200,
            'validade' => now()->addDays(15),
        ]);

        Cupom::create([
            'codigo' => 'PROMO5',
            'desconto_percentual' => 5,
            'valor_minimo' => 20,
            'validade' => now()->addDays(60),
        ]);
    }
}