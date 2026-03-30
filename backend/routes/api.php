<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\RevisaoController;
use App\Http\Controllers\RelatorioController;

//  Autenticação
Route::prefix('auth')->group(function () {
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

//  Rotas protegidas
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // listagens gerais
    Route::get('/veiculos', [VeiculoController::class, 'todos']);
    Route::get('/revisoes', [RevisaoController::class, 'todas']);

    Route::get('/clientes/verificar-cpf',     [ClienteController::class, 'verificarCpf']);
    Route::get('/clientes/verificar-email',   [ClienteController::class, 'verificarEmail']);
    Route::get('/veiculos/verificar-placa',   [VeiculoController::class, 'verificarPlaca']);

    // CRUD de clientes
    Route::apiResource('clientes', ClienteController::class);

    // Veículos de um cliente
    Route::apiResource('clientes.veiculos', VeiculoController::class);

    // Revisões de um veículo
    Route::apiResource('clientes.veiculos.revisoes', RevisaoController::class);


    Route::prefix('relatorios')->group(function () {
        Route::get('/veiculos',              [RelatorioController::class, 'todosVeiculos']);
        Route::get('/veiculos-por-pessoa',   [RelatorioController::class, 'veiculosPorPessoa']);
        Route::get('/veiculos-por-genero',   [RelatorioController::class, 'topQuantVeiculos']);
        Route::get('/veiculos-por-marca',    [RelatorioController::class, 'veiculosPorMarca']);
        Route::get('/marcas-homens',         [RelatorioController::class, 'marcasHomens']);
        Route::get('/marcas-mulheres',       [RelatorioController::class, 'marcasMulheres']);
        Route::get('/pessoas',               [RelatorioController::class, 'todasPessoas']);
        Route::get('/pessoas-por-genero',    [RelatorioController::class, 'pessoasPorGenero']);
        Route::get('/revisoes-por-periodo',  [RelatorioController::class, 'revisoesPorPeriodo']);
        Route::get('/marcas-revisoes',       [RelatorioController::class, 'marcasComMaisRevisoes']);
        Route::get('/pessoas-revisoes',      [RelatorioController::class, 'pessoasComMaisRevisoes']);
        Route::get('/media-tempo',           [RelatorioController::class, 'mediaTempoRevisoes']);
        Route::get('/proximas-revisoes',     [RelatorioController::class, 'proximasRevisoes']);
    });
});
