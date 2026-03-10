<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/liquidaciones', [\App\Http\Controllers\LiquidacionController::class, 'index'])->name('liquidaciones.index');
    Route::get('/liquidaciones/create', [\App\Http\Controllers\LiquidacionController::class, 'create'])->name('liquidaciones.create');
    Route::post('/liquidaciones', [\App\Http\Controllers\LiquidacionController::class, 'store'])->name('liquidaciones.store');
    Route::get('/liquidaciones/{liquidacion}', [\App\Http\Controllers\LiquidacionController::class, 'show'])->name('liquidaciones.show');

    Route::get('/informes', [\App\Http\Controllers\InformeController::class, 'index'])->name('informes.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('clientes', \App\Http\Controllers\ClienteController::class);
    Route::resource('colaboradoras', \App\Http\Controllers\ColaboradoraController::class);
    Route::resource('servicios', \App\Http\Controllers\ServicioController::class);
    Route::resource('proveedores', \App\Http\Controllers\ProveedorController::class);

    Route::resource('productos', \App\Http\Controllers\ProductoController::class);
    Route::patch('/productos/{producto}/consumo', [\App\Http\Controllers\ProductoController::class, 'consumo'])
        ->name('productos.consumo');
    Route::patch('/productos/{producto}/usar-peluqueria', [\App\Http\Controllers\ProductoController::class, 'usarPeluqueria'])
        ->name('productos.usarPeluqueria');

    // Compras por lote: primero las rutas específicas
    Route::get('/compras/lotes/{lote}', [\App\Http\Controllers\CompraController::class, 'showLote'])
        ->name('compras.lotes.show');
    Route::get('/compras/lotes/{lote}/edit', [\App\Http\Controllers\CompraController::class, 'editLote'])
        ->name('compras.lotes.edit');
    Route::put('/compras/lotes/{lote}', [\App\Http\Controllers\CompraController::class, 'updateLote'])
        ->name('compras.lotes.update');
    Route::delete('/compras/lotes/{lote}', [\App\Http\Controllers\CompraController::class, 'destroyLote'])
        ->name('compras.lotes.destroy');
    Route::post('/compras/lotes/{lote}/pagos', [\App\Http\Controllers\CompraController::class, 'storePago'])
        ->name('compras.lotes.pagos.store');

    // Después el resource
    Route::resource('compras', \App\Http\Controllers\CompraController::class);

    Route::resource('ventas', \App\Http\Controllers\VentaController::class)->except(['edit', 'update']);
    Route::patch('/ventas/{venta}/marcar-pagado', [\App\Http\Controllers\VentaController::class, 'marcarPagado'])
        ->name('ventas.marcarPagado');

    Route::get('/turnos', [\App\Http\Controllers\TurnoController::class, 'index'])->name('turnos.index');
    Route::get('/turnos/eventos', [\App\Http\Controllers\TurnoController::class, 'eventos'])->name('turnos.eventos');
    Route::post('/turnos', [\App\Http\Controllers\TurnoController::class, 'store'])->name('turnos.store');
    Route::delete('/turnos/{turno}', [\App\Http\Controllers\TurnoController::class, 'destroy'])->name('turnos.destroy');
    Route::put('/turnos/{turno}', [\App\Http\Controllers\TurnoController::class, 'update'])->name('turnos.update');
    Route::get('/turnos/{turno}', [\App\Http\Controllers\TurnoController::class, 'show'])->name('turnos.show');

    Route::resource('fichadas', \App\Http\Controllers\FichadaController::class);

    Route::resource('gastos', \App\Http\Controllers\GastoController::class);

});

require __DIR__.'/auth.php';