<?php

namespace App\Http\Controllers;

use App\Models\Cupom;
use Illuminate\Http\Request;

class CupomController extends Controller
{
    public function index()
    {
        $cupons = Cupom::all();
        return view('cupons.index', compact('cupons'));
    }

    public function create()
    {
        return view('cupons.create');
    }

    public function store(Request $request)
    {
        Cupom::create($request->only([
            'codigo',
            'desconto_percentual',
            'valor_minimo',
            'validade'
        ]));

        return redirect()->route('cupons.index');
    }
}
