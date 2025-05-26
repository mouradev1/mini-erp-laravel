<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CupomController;

Route::get('/', [ProdutoController::class, 'index'])->name('produtos.index');
Route::get('/produtos/create', [ProdutoController::class, 'create'])->name('produtos.create');
Route::post('/produtos', [ProdutoController::class, 'store'])->name('produtos.store');
Route::get('/produtos/{id}/edit', [ProdutoController::class, 'edit'])->name('produtos.edit');
Route::put('/produtos/{id}', [ProdutoController::class, 'update'])->name('produtos.update');
Route::post('/produtos/{id}/comprar', [ProdutoController::class, 'comprar'])->name('produtos.comprar');
Route::post('/pedido/finalizar', [PedidoController::class, 'finalizar'])->name('pedido.finalizar');
Route::get('/pedido/carrinho/limpar', [PedidoController::class, 'limparCarrinho'])->name('pedido.carrinho.limpar');

Route::get('/pedido/carrinho', [PedidoController::class, 'carrinho'])->name('pedido.carrinho');

Route::post('/pedido/finalizar', [PedidoController::class, 'finalizar'])->name('pedido.finalizar');

Route::get('/cupons', [CupomController::class, 'index'])->name('cupons.index');
Route::get('/cupons/create', [CupomController::class, 'create'])->name('cupons.create');
Route::post('/cupons', [CupomController::class, 'store'])->name('cupons.store');
