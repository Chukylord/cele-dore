<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListaPrecioController;
use App\Http\Controllers\CajaDiariaController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/liquidaciones', [\App\Http\Controllers\LiquidacionController::class, 'index'])->name('liquidaciones.index');
    Route::get('/liquidaciones/create', [\App\Http\Controllers\LiquidacionController::class, 'create'])->name('liquidaciones.create');
    Route::post('/liquidaciones', [\App\Http\Controllers\LiquidacionController::class, 'store'])->name('liquidaciones.store');
    Route::get('/liquidaciones/{liquidacion}', [\App\Http\Controllers\LiquidacionController::class, 'show'])->name('liquidaciones.show');

    Route::get('/informes', [\App\Http\Controllers\InformeController::class, 'index'])->name('informes.index');

    Route::resource('gastos', \App\Http\Controllers\GastoController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('clientes', \App\Http\Controllers\ClienteController::class);
    Route::resource('colaboradoras', \App\Http\Controllers\ColaboradoraController::class);
    Route::resource('servicios', \App\Http\Controllers\ServicioController::class);

    Route::get('/proveedores/{proveedor}/cuenta', [\App\Http\Controllers\ProveedorController::class, 'cuenta'])
        ->name('proveedores.cuenta');
    Route::post('/proveedores/{proveedor}/pagos', [\App\Http\Controllers\ProveedorController::class, 'storePago'])
        ->name('proveedores.pagos.store');
    Route::put('/proveedores/{proveedor}/pagos/{pago}', [\App\Http\Controllers\ProveedorController::class, 'updatePago'])
        ->name('proveedores.pagos.update');
    Route::delete('/proveedores/{proveedor}/pagos/{pago}', [\App\Http\Controllers\ProveedorController::class, 'destroyPago'])
        ->name('proveedores.pagos.destroy');
    Route::resource('proveedores', \App\Http\Controllers\ProveedorController::class);

    Route::resource('productos', \App\Http\Controllers\ProductoController::class);
    Route::patch('/productos/{producto}/consumo', [\App\Http\Controllers\ProductoController::class, 'consumo'])
        ->name('productos.consumo');
    Route::patch('/productos/{producto}/usar-peluqueria', [\App\Http\Controllers\ProductoController::class, 'usarPeluqueria'])
        ->name('productos.usarPeluqueria');

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
    Route::resource('compras', \App\Http\Controllers\CompraController::class);

    Route::get('/ventas/{venta}/saldo', [\App\Http\Controllers\VentaController::class, 'saldo'])
        ->name('ventas.saldo');
    Route::patch('/ventas/{venta}/marcar-pagado', [\App\Http\Controllers\VentaController::class, 'marcarPagado'])
        ->name('ventas.marcarPagado');
    Route::resource('ventas', \App\Http\Controllers\VentaController::class)->except(['edit', 'update']);

    Route::get('/turnos', [\App\Http\Controllers\TurnoController::class, 'index'])->name('turnos.index');
    Route::get('/turnos/eventos', [\App\Http\Controllers\TurnoController::class, 'eventos'])->name('turnos.eventos');
    Route::post('/turnos', [\App\Http\Controllers\TurnoController::class, 'store'])->name('turnos.store');
    Route::delete('/turnos/{turno}', [\App\Http\Controllers\TurnoController::class, 'destroy'])->name('turnos.destroy');
    Route::put('/turnos/{turno}', [\App\Http\Controllers\TurnoController::class, 'update'])->name('turnos.update');
    Route::get('/turnos/{turno}', [\App\Http\Controllers\TurnoController::class, 'show'])->name('turnos.show');

    Route::resource('fichadas', \App\Http\Controllers\FichadaController::class);

    Route::get('/caja-diaria', [CajaDiariaController::class, 'index'])
        ->name('caja-diaria.index');
    Route::post('/caja-diaria/abrir', [CajaDiariaController::class, 'abrir'])
        ->name('caja-diaria.abrir');
    Route::post('/caja-diaria/gastos', [CajaDiariaController::class, 'registrarGasto'])
        ->name('caja-diaria.gastos.store');
    Route::patch('/caja-diaria/{caja}/cerrar', [CajaDiariaController::class, 'cerrar'])
        ->name('caja-diaria.cerrar');

    Route::get('/lista-precios', [ListaPrecioController::class, 'index'])->name('lista-precios.index');
    Route::post('/lista-precios/actualizar', [ListaPrecioController::class, 'actualizar'])->name('lista-precios.actualizar');
});

require __DIR__.'/auth.php';
