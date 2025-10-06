<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui é onde pode registar as rotas da API para a sua aplicação.
| Estas rotas são carregadas pelo RouteServiceProvider e todas elas
| receberão o prefixo /api. Divirta-se a construir a sua API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/discounts/calculate', [DiscountController::class, 'calculate']);

Route::post('/products', [ProductController::class, 'calculate']);
