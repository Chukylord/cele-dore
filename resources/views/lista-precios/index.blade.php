@extends('layouts.admin')

@section('title', 'Lista de precios - FN Peluquería')
@section('h1', 'Lista de precios')
@section('sub', 'Consultar, escanear y actualizar únicamente los precios que cambien.')

@section('content')

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('ok') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Hay errores:</div>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $scannerMap = [];

    foreach ($productosScanner as $p) {
        if (empty($p->codigo_barra)) {
            continue;
        }

        $nombre = trim(($p->marca ?? '') . ' - ' . ($p->tipo ?? '') . ' ' . ($p->contenido ?? ''));

        $scannerMap[(string) $p->codigo_barra] = [
            'id' => $p->id,
            'nombre' => $nombre,
            'proveedor' => optional($p->proveedor)->nombre,
            'stock' => (int) $p->stock_venta,
            'costo' => (float) ($p->ultimo_costo ?? 0),
            'efectivo' => (float) $p->precio_efectivo_calculado,
            'tarjeta' => (float) $p->precio_tarjeta_calculado,
            'colaboradora' => (float) $p->costo_colaboradora_calculado,
            'origen' => $p->precio_origen,
            'codigo_barra' => $p->codigo_barra,
        ];
    }

    $cantidadManual = $productos->where('precio_origen', 'Manual')->count();
    $cantidadAutomatico = $productos->where('precio_origen', 'Automático')->count();
    $cantidadSinPrecio = $productos->filter(fn ($p) => (float) $p->precio_efectivo_calculado <= 0)->count();
@endphp

<div class="fn-toolbar mb-5">
    <form method="GET"
          action="{{ route('lista-precios.index') }}"
          class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">

        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-700">Buscar producto</label>
            <input type="text"
                   name="buscar"
                   value="{{ $buscar }}"
                   placeholder="Marca, tipo, contenido o código..."
                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-700">Proveedor</label>
            <select name="proveedor_id"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="">Todos</option>
                @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}"
                        {{ (string) $proveedor_id === (string) $proveedor->id ? 'selected' : '' }}>
                        {{ $proveedor->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button class="fn-primary-action">
                Filtrar
            </button>

            <a href="{{ route('lista-precios.index') }}"
               class="fn-secondary-action">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
    <div class="fn-stat-card">
        <div class="text-sm text-slate-500">Productos mostrados</div>
        <div class="text-2xl font-bold text-slate-900">{{ $productos->count() }}</div>
    </div>

    <div class="fn-stat-card bg-green-50">
        <div class="text-sm text-green-700">Precio automático</div>
        <div class="text-2xl font-bold text-green-800">{{ $cantidadAutomatico }}</div>
    </div>

    <div class="fn-stat-card bg-amber-50">
        <div class="text-sm text-amber-700">Precio manual</div>
        <div class="text-2xl font-bold text-amber-800">{{ $cantidadManual }}</div>
    </div>

    <div class="rounded-2xl border {{ $cantidadSinPrecio > 0 ? 'bg-red-50' : 'bg-slate-50' }} p-4">
        <div class="text-sm {{ $cantidadSinPrecio > 0 ? 'text-red-700' : 'text-slate-600' }}">Sin precio</div>
        <div class="text-2xl font-bold {{ $cantidadSinPrecio > 0 ? 'text-red-800' : 'text-slate-800' }}">
            {{ $cantidadSinPrecio }}
        </div>
    </div>
</div>

<div class="fn-feature-panel rounded-2xl p-5 mb-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
        <div class="lg:col-span-4">
            <label class="text-sm font-semibold text-slate-200">
                Consultar precio con scanner
            </label>

            <input id="scanner_precio"
                   type="text"
                   autocomplete="off"
                   placeholder="Hacé click acá y escaneá..."
                   class="mt-2 w-full rounded-2xl border-slate-700 bg-slate-900 text-white text-lg px-4 py-4 focus:border-white focus:ring-white">

            <div class="text-xs text-slate-400 mt-2">
                El scanner busca entre todos los productos, aunque la tabla esté filtrada.
            </div>

            <button type="button"
                    id="activarScanner"
                    class="fn-secondary-action mt-4 w-full">
                Activar scanner
            </button>
        </div>

        <div class="lg:col-span-8">
            <div id="resultadoScanner"
                 class="min-h-[230px] h-full rounded-2xl border border-slate-700 bg-slate-900 p-6 flex items-center justify-center">
                <div class="text-center">
                    <div class="text-5xl mb-3">🔎</div>
                    <div class="text-2xl font-bold text-white">Esperando producto</div>
                    <div class="text-slate-400 mt-2">Escaneá un código para consultar sus valores.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="POST"
      action="{{ route('lista-precios.actualizar') }}"
      id="formListaPrecios">
    @csrf

    <input type="hidden" name="accion" id="accionFormulario" value="guardar">
    <input type="hidden" name="restablecer_id" id="restablecerId" value="">
    <input type="hidden" name="buscar_actual" value="{{ $buscar }}">
    <input type="hidden" name="proveedor_actual" value="{{ $proveedor_id }}">

    <div class="fn-section-card mb-5">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-end">
            <div class="xl:col-span-4">
                <div class="text-lg font-bold text-slate-800">Aumento masivo</div>
                <div class="text-sm text-slate-600">
                    Seleccioná productos y aplicá un porcentaje. Solo esos productos pasan a precio manual.
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="text-sm font-semibold text-slate-700">% aumento</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="porcentaje_aumento"
                       id="porcentajeAumento"
                       placeholder="Ej: 10"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div class="xl:col-span-3">
                <div id="resumenCambios"
                     class="rounded-xl border bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    No hay cambios pendientes.
                </div>
            </div>

            <div class="xl:col-span-3 flex flex-wrap gap-2 xl:justify-end">
                <button type="button"
                        id="btnAumentar"
                        class="fn-secondary-action">
                    Aumentar seleccionados
                </button>

                <button type="button"
                        id="btnGuardar"
                        class="fn-primary-action">
                    Guardar cambios
                </button>
            </div>
        </div>
    </div>

    <div class="mb-4 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-blue-900">
        <div class="font-bold">Cómo funciona</div>
        <div class="text-sm mt-1">
            El sistema guarda únicamente las filas que realmente modificaste. La tarjeta se calcula automáticamente con 20% y el valor para colaboradora se estima quitando el 40% al precio efectivo vigente cuando corresponde.
        </div>
    </div>

    <div class="fn-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-slate-50 text-slate-700">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" id="checkTodos" class="rounded border-slate-300">
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Producto</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Stock</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Último costo</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Efectivo / Transferencia</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Tarjeta (+20%)</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Colaboradora</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Origen</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold">Acción</th>
                </tr>
                </thead>

                <tbody>
                @forelse($productos as $producto)
                    @php
                        $nombre = trim(($producto->marca ?? '') . ' - ' . ($producto->tipo ?? '') . ' ' . ($producto->contenido ?? ''));
                        $origen = $producto->precio_origen ?? 'Sin precio';
                        $precioEfectivo = (float) $producto->precio_efectivo_calculado;
                        $precioTarjeta = (float) $producto->precio_tarjeta_calculado;
                        $precioColaboradora = (float) $producto->costo_colaboradora_calculado;
                    @endphp

                    <tr class="border-t hover:bg-slate-50 fila-producto"
                        data-producto-id="{{ $producto->id }}"
                        data-original-origen="{{ $origen }}">

                        <td class="px-4 py-3 align-top">
                            <input type="checkbox"
                                   name="seleccionados[]"
                                   value="{{ $producto->id }}"
                                   class="checkProducto rounded border-slate-300">

                            <input type="hidden"
                                   name="modificados[]"
                                   value="{{ $producto->id }}"
                                   class="producto-modificado-hidden"
                                   disabled>
                        </td>

                        <td class="px-4 py-3 align-top min-w-[230px]">
                            <div class="font-semibold text-slate-800">{{ $nombre }}</div>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ optional($producto->proveedor)->nombre ?? 'Sin proveedor' }}
                                @if($producto->codigo_barra)
                                    · Código: {{ $producto->codigo_barra }}
                                @endif
                            </div>
                            <div class="estado-cambio hidden mt-2 text-xs font-semibold text-indigo-700">
                                ● Modificado sin guardar
                            </div>
                        </td>

                        <td class="px-4 py-3 align-top text-sm">
                            {{ (int) $producto->stock_venta }}
                        </td>

                        <td class="px-4 py-3 align-top text-sm whitespace-nowrap">
                            @if((float) ($producto->ultimo_costo ?? 0) > 0)
                                ${{ number_format((float) $producto->ultimo_costo, 2, ',', '.') }}
                            @else
                                <span class="text-slate-400">Sin compra</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 align-top min-w-[180px]">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="productos[{{ $producto->id }}][precio_efectivo_manual]"
                                   value="{{ number_format($precioEfectivo, 2, '.', '') }}"
                                   data-original="{{ number_format($precioEfectivo, 2, '.', '') }}"
                                   data-producto-id="{{ $producto->id }}"
                                   class="precio-efectivo-input w-36 rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500 {{ $precioEfectivo <= 0 ? 'border-red-300 bg-red-50' : '' }}">

                            @if($precioEfectivo <= 0)
                                <div class="text-xs text-red-600 mt-1">Completá este precio.</div>
                            @endif
                        </td>

                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            <div class="precio-tarjeta-view font-semibold text-purple-700"
                                 data-producto-id="{{ $producto->id }}">
                                ${{ number_format($precioTarjeta, 2, ',', '.') }}
                            </div>
                        </td>

                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            <div class="precio-colaboradora-view font-semibold text-blue-700"
                                 data-producto-id="{{ $producto->id }}">
                                @if($precioColaboradora > 0)
                                    ${{ number_format($precioColaboradora, 2, ',', '.') }}
                                @else
                                    <span class="text-red-600">Sin precio</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Costo usado en ventas</div>
                        </td>

                        <td class="px-4 py-3 align-top">
                            <span class="origen-precio rounded-full px-3 py-1 text-xs font-semibold
                                @if($origen === 'Manual') bg-amber-100 text-amber-700
                                @elseif($origen === 'Automático') bg-green-100 text-green-700
                                @elseif($origen === 'Inicial') bg-blue-100 text-blue-700
                                @else bg-red-100 text-red-700
                                @endif">
                                {{ $origen }}
                            </span>
                        </td>

                        <td class="px-4 py-3 align-top text-right">
                            @if($producto->precio_efectivo_manual !== null)
                                <button type="button"
                                        class="btn-restablecer fn-mini-action"
                                        data-producto-id="{{ $producto->id }}"
                                        data-producto-nombre="{{ $nombre }}">
                                    Volver a automático
                                </button>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                            No hay productos para mostrar.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</form>

<script>
const PRODUCTOS_SCANNER = @json($scannerMap);

function round2(n){
    return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
}

function money(n){
    n = Number(n || 0);

    return '$' + n.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function escapeHtml(text){
    return String(text ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('formListaPrecios');
    const accion = document.getElementById('accionFormulario');
    const restablecerId = document.getElementById('restablecerId');
    const resumenCambios = document.getElementById('resumenCambios');
    const scanner = document.getElementById('scanner_precio');
    const resultado = document.getElementById('resultadoScanner');
    const checkTodos = document.getElementById('checkTodos');

    function consultarCodigo(codigo){
        const codigoLimpio = String(codigo || '').trim();

        if(!codigoLimpio){
            return;
        }

        const producto = PRODUCTOS_SCANNER[codigoLimpio];

        if(!producto){
            resultado.innerHTML = `
                <div class="w-full text-center">
                    <div class="text-6xl mb-4">⚠️</div>
                    <div class="text-3xl font-extrabold text-red-300">Producto no encontrado</div>
                    <div class="mt-3 text-lg text-slate-300">Código escaneado:</div>
                    <div class="mt-1 text-2xl font-bold text-white">${escapeHtml(codigoLimpio)}</div>
                </div>
            `;

            return;
        }

        const costoCompra = Number(producto.costo || 0) > 0
            ? money(producto.costo)
            : 'Sin compra registrada';

        resultado.innerHTML = `
            <div class="w-full">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4 mb-6">
                    <div>
                        <div class="text-sm uppercase tracking-[0.28em] text-slate-400 font-semibold">
                            Producto consultado
                        </div>

                        <div class="mt-2 text-3xl lg:text-4xl font-extrabold text-white leading-tight">
                            ${escapeHtml(producto.nombre)}
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2 text-sm">
                            <span class="rounded-full bg-slate-800 border border-slate-700 px-3 py-1 text-slate-300">
                                Proveedor: ${escapeHtml(producto.proveedor || '-')}
                            </span>
                            <span class="rounded-full bg-slate-800 border border-slate-700 px-3 py-1 text-slate-300">
                                Stock: ${escapeHtml(producto.stock)}
                            </span>
                            <span class="rounded-full bg-slate-800 border border-slate-700 px-3 py-1 text-slate-300">
                                Origen: ${escapeHtml(producto.origen)}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-green-400/30 bg-green-400/10 p-5">
                        <div class="text-sm uppercase tracking-[0.15em] text-green-200 font-semibold">
                            Efectivo / Transferencia
                        </div>
                        <div class="mt-3 text-4xl font-black text-green-300">
                            ${money(producto.efectivo)}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-purple-400/30 bg-purple-400/10 p-5">
                        <div class="text-sm uppercase tracking-[0.15em] text-purple-200 font-semibold">
                            Tarjeta
                        </div>
                        <div class="mt-3 text-4xl font-black text-purple-300">
                            ${money(producto.tarjeta)}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-blue-400/30 bg-blue-400/10 p-5">
                        <div class="text-sm uppercase tracking-[0.15em] text-blue-200 font-semibold">
                            Colaboradora
                        </div>
                        <div class="mt-3 text-4xl font-black text-blue-300">
                            ${money(producto.colaboradora)}
                        </div>
                        <div class="text-xs text-blue-200 mt-2">Último costo: ${escapeHtml(costoCompra)}</div>
                    </div>
                </div>
            </div>
        `;
    }

    function actualizarResumenCambios(){
        const cantidad = document.querySelectorAll('.producto-modificado-hidden:not([disabled])').length;

        resumenCambios.classList.remove(
            'border-indigo-200',
            'bg-indigo-50',
            'text-indigo-800',
            'border-slate-200',
            'bg-slate-50',
            'text-slate-600'
        );

        if(cantidad > 0){
            resumenCambios.textContent = cantidad === 1
                ? 'Hay 1 precio modificado sin guardar.'
                : `Hay ${cantidad} precios modificados sin guardar.`;

            resumenCambios.classList.add('border-indigo-200', 'bg-indigo-50', 'text-indigo-800');
        } else {
            resumenCambios.textContent = 'No hay cambios pendientes.';
            resumenCambios.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-600');
        }
    }

    function marcarFilaModificada(input){
        const fila = input.closest('.fila-producto');

        if(!fila){
            return;
        }

        const hidden = fila.querySelector('.producto-modificado-hidden');
        const estado = fila.querySelector('.estado-cambio');
        const origen = fila.querySelector('.origen-precio');
        const original = round2(input.dataset.original || 0);
        const actual = round2(input.value || 0);
        const modificado = Math.abs(actual - original) > 0.009;

        hidden.disabled = !modificado;
        estado.classList.toggle('hidden', !modificado);
        fila.classList.toggle('bg-indigo-50/50', modificado);

        if(origen){
            if(modificado){
                origen.textContent = 'Manual al guardar';
                origen.className = 'origen-precio rounded-full px-3 py-1 text-xs font-semibold bg-indigo-100 text-indigo-700';
            } else {
                const originalOrigen = fila.dataset.originalOrigen || 'Sin precio';
                origen.textContent = originalOrigen;

                if(originalOrigen === 'Manual'){
                    origen.className = 'origen-precio rounded-full px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-700';
                } else if(originalOrigen === 'Automático'){
                    origen.className = 'origen-precio rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-700';
                } else if(originalOrigen === 'Inicial'){
                    origen.className = 'origen-precio rounded-full px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-700';
                } else {
                    origen.className = 'origen-precio rounded-full px-3 py-1 text-xs font-semibold bg-red-100 text-red-700';
                }
            }
        }

        input.classList.toggle('border-red-300', actual <= 0);
        input.classList.toggle('bg-red-50', actual <= 0);

        actualizarResumenCambios();
    }

    document.querySelectorAll('.precio-efectivo-input').forEach(input => {
        input.addEventListener('input', function(){
            const productoId = this.dataset.productoId;
            const efectivo = Number(this.value || 0);
            const tarjeta = round2(efectivo * 1.20);
            const colaboradora = round2(efectivo / 1.40);

            const tarjetaView = document.querySelector(`.precio-tarjeta-view[data-producto-id="${productoId}"]`);
            const colaboradoraView = document.querySelector(`.precio-colaboradora-view[data-producto-id="${productoId}"]`);

            if(tarjetaView){
                tarjetaView.textContent = money(tarjeta);
            }

            if(colaboradoraView){
                colaboradoraView.textContent = efectivo > 0 ? money(colaboradora) : 'Sin precio';
                colaboradoraView.classList.toggle('text-red-600', efectivo <= 0);
                colaboradoraView.classList.toggle('text-blue-700', efectivo > 0);
            }

            marcarFilaModificada(this);
        });
    });

    document.getElementById('btnGuardar').addEventListener('click', function(){
        const cantidad = document.querySelectorAll('.producto-modificado-hidden:not([disabled])').length;

        if(cantidad === 0){
            alert('No hay precios modificados para guardar.');
            return;
        }

        accion.value = 'guardar';
        restablecerId.value = '';
        form.submit();
    });

    document.getElementById('btnAumentar').addEventListener('click', function(){
        const seleccionados = document.querySelectorAll('.checkProducto:checked').length;
        const porcentaje = Number(document.getElementById('porcentajeAumento').value || 0);

        if(seleccionados === 0){
            alert('Seleccioná al menos un producto.');
            return;
        }

        if(porcentaje <= 0){
            alert('Ingresá un porcentaje mayor a 0.');
            return;
        }

        if(!confirm(`¿Aplicar un aumento del ${porcentaje}% a ${seleccionados} producto(s)?`)){
            return;
        }

        accion.value = 'aumentar';
        restablecerId.value = '';
        form.submit();
    });

    document.querySelectorAll('.btn-restablecer').forEach(button => {
        button.addEventListener('click', function(){
            const id = this.dataset.productoId;
            const nombre = this.dataset.productoNombre || 'este producto';

            if(!confirm(`¿Volver ${nombre} al precio automático calculado por la última compra?`)){
                return;
            }

            accion.value = 'restablecer';
            restablecerId.value = id;
            form.submit();
        });
    });

    if(checkTodos){
        checkTodos.addEventListener('change', function(){
            document.querySelectorAll('.checkProducto').forEach(chk => {
                chk.checked = this.checked;
            });
        });
    }

    document.getElementById('activarScanner').addEventListener('click', function(){
        scanner.focus();
    });

    if(scanner){
        scanner.addEventListener('keydown', function(e){
            if(e.key === 'Enter'){
                e.preventDefault();
                consultarCodigo(this.value);
                this.value = '';
            }
        });

        scanner.addEventListener('change', function(){
            consultarCodigo(this.value);
            this.value = '';
        });
    }

    actualizarResumenCambios();
});
</script>

@endsection
