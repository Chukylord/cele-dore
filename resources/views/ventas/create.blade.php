@extends('layouts.admin')

@section('title', 'Nueva Venta - Vir Tisone Studio')
@section('h1', 'Nueva Venta')
@section('sub', 'Ingresar venta de servicios y/o productos.')

@section('content')

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Hay errores:</div>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('ventas.store') }}" id="formVenta">
    @csrf

    <div class="rounded-2xl border bg-slate-900 text-white p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-3">
                <label class="text-sm font-semibold text-slate-200">Fecha *</label>
                <input type="datetime-local"
                       name="fecha"
                       value="{{ old('fecha', now()->format('Y-m-d\TH:i')) }}"
                       class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white">
            </div>

            <div class="md:col-span-3">
                <label class="text-sm font-semibold text-slate-200">Vendedora (opcional)</label>
                <select id="vendedora_id"
                        name="vendedora_id"
                        class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white">
                    <option value="" data-pct="0">-</option>
                    @foreach($colaboradoras as $c)
                        <option value="{{ $c->id }}"
                                data-pct="{{ (float)$c->comision_pct }}"
                            {{ (string)old('vendedora_id') === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->nombre }} {{ $c->apellido }}
                            ({{ number_format((float)$c->comision_pct, 2, ',', '.') }}%)
                        </option>
                    @endforeach
                </select>
                <div class="text-xs text-slate-300 mt-1">La comisión se calcula sobre productos.</div>
            </div>

            <div class="md:col-span-6">
                <label class="text-sm font-semibold text-slate-200">Tipo de cliente *</label>
                <div class="mt-2 flex flex-wrap gap-4">
                    <label class="flex items-center gap-2">
                        <input type="radio"
                               name="tipo_cliente"
                               value="cliente"
                            {{ old('tipo_cliente', 'cliente') === 'cliente' ? 'checked' : '' }}>
                        <span>Cliente</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="radio"
                               name="tipo_cliente"
                               value="colaboradora"
                            {{ old('tipo_cliente') === 'colaboradora' ? 'checked' : '' }}>
                        <span>Colaboradora (productos a costo)</span>
                    </label>
                </div>
            </div>

            <div id="box_cliente" class="md:col-span-3">
                <label class="text-sm font-semibold text-slate-200">Cliente *</label>

                <input id="cliente_buscar"
                       list="datalist_clientes"
                       placeholder="Escribí para buscar..."
                       class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white"
                       value="{{ old('cliente_buscar') }}">

                <datalist id="datalist_clientes">
                    @foreach($clientes as $cl)
                        <option value="{{ $cl->apellido }} {{ $cl->nombre }} - {{ $cl->telefono }}"></option>
                    @endforeach
                </datalist>

                <input type="hidden" name="cliente_id" id="cliente_id" value="{{ old('cliente_id') }}">
                <div class="text-xs text-slate-300 mt-1">Si no existe, crearlo en “Clientes”.</div>
            </div>

            <div id="box_colab" class="md:col-span-3 hidden">
                <label class="text-sm font-semibold text-slate-200">Colaboradora *</label>

                <input id="colab_buscar"
                       list="datalist_colabs"
                       placeholder="Escribí para buscar..."
                       class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white"
                       value="{{ old('colab_buscar') }}">

                <datalist id="datalist_colabs">
                    @foreach($colaboradoras as $c)
                        <option value="{{ $c->apellido }} {{ $c->nombre }}"></option>
                    @endforeach
                </datalist>

                <input type="hidden"
                       name="cliente_colaboradora_id"
                       id="cliente_colaboradora_id"
                       value="{{ old('cliente_colaboradora_id') }}">

                <div class="text-xs text-slate-300 mt-1">
                    Los productos se cobran a costo.
                </div>
            </div>

            <div class="md:col-span-6">
                <label class="text-sm font-semibold text-slate-200">Notas</label>
                <textarea name="notas"
                          rows="2"
                          class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white"
                          placeholder="Ej: color usado, observaciones, etc.">{{ old('notas') }}</textarea>
            </div>
        </div>
    </div>

    <div class="mt-6 flex gap-2">
        <button type="button"
                id="tabServicios"
                class="px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800">
            Servicios
        </button>

        <button type="button"
                id="tabProductos"
                class="px-4 py-2 rounded-xl border bg-white hover:bg-slate-50">
            Productos
        </button>
    </div>

    <div id="panelServicios" class="mt-4 rounded-2xl border bg-white">
        <div class="p-4 border-b flex items-center justify-between">
            <div>
                <div class="text-lg font-bold text-slate-900">Servicios</div>
                <div class="text-sm text-slate-600">
                    Los precios cargados acá son precios base de efectivo/transferencia.
                </div>
            </div>

            <button type="button"
                    id="addServicio"
                    class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                + Agregar servicio
            </button>
        </div>

        <div class="p-4 overflow-x-auto">
            <table class="min-w-full bg-white" id="tablaServicios">
                <thead class="bg-slate-100 text-slate-700">
                <tr>
                    <th class="text-left px-3 py-2 text-sm font-semibold">Servicio</th>
                    <th class="text-left px-3 py-2 text-sm font-semibold">Precio base</th>
                    <th class="text-left px-3 py-2 text-sm font-semibold">Detalle</th>
                    <th class="text-right px-3 py-2 text-sm font-semibold">Quitar</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-slate-600">Subtotal servicios</div>
                <div class="text-2xl font-bold" id="subtotalServicios">$0,00</div>
            </div>
        </div>
    </div>

    <div id="panelProductos" class="mt-4 rounded-2xl border bg-white hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <div>
                <div class="text-lg font-bold text-slate-900">Productos</div>
                <div class="text-sm text-slate-600">
                    Muestra stock y calcula el precio base de efectivo/transferencia.
                </div>
            </div>

            <button type="button"
                    id="addProducto"
                    class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                + Agregar producto
            </button>
        </div>

        <div class="p-4">
            <div class="mb-4 rounded-2xl border bg-slate-50 p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-slate-700">Escanear producto</label>
                        <input id="scan_producto"
                               type="text"
                               autocomplete="off"
                               placeholder="Hacé click acá y escaneá el código..."
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

                        <div class="text-xs text-slate-500 mt-1">
                            Si el producto ya está agregado, suma 1 a la cantidad.
                        </div>
                    </div>

                    <div class="text-sm text-slate-500">
                        El lector funciona como teclado.
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white" id="tablaProductos">
                    <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="text-left px-3 py-2 text-sm font-semibold">Producto</th>
                        <th class="text-left px-3 py-2 text-sm font-semibold">Stock</th>
                        <th class="text-left px-3 py-2 text-sm font-semibold">Cant.</th>
                        <th class="text-left px-3 py-2 text-sm font-semibold">Unit. base</th>
                        <th class="text-left px-3 py-2 text-sm font-semibold">Subtotal</th>
                        <th class="text-right px-3 py-2 text-sm font-semibold">Quitar</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="rounded-xl border bg-slate-50 p-3 flex items-center justify-between">
                    <div class="text-sm text-slate-600">Subtotal productos</div>
                    <div class="text-xl font-bold" id="subtotalProductos">$0,00</div>
                </div>

                <div class="rounded-xl border bg-slate-50 p-3 flex items-center justify-between">
                    <div class="text-sm text-slate-600">Comisión vendedora</div>
                    <div class="text-xl font-bold" id="montoComision">$0,00</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border bg-slate-900 text-white p-5">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
            <div class="xl:col-span-3">
                <div class="text-sm text-slate-300">TOTAL A COBRAR</div>
                <div class="text-4xl font-extrabold mt-1" id="totalFinal">$0,00</div>

                <div class="mt-3 space-y-1 text-sm">
                    <div class="flex justify-between gap-3 text-slate-300">
                        <span>Total base:</span>
                        <strong id="totalBaseResumen" class="text-white">$0,00</strong>
                    </div>

                    <div class="flex justify-between gap-3 text-slate-300">
                        <span>Recargo tarjeta:</span>
                        <strong id="recargoTarjetaResumen" class="text-white">$0,00</strong>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-3">
                <label class="text-sm font-semibold text-slate-200">Forma de pago *</label>

                <select id="tipo_pago"
                        name="tipo_pago"
                        class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white">
                    <option value="efectivo" {{ old('tipo_pago', 'efectivo') === 'efectivo' ? 'selected' : '' }}>
                        Efectivo
                    </option>
                    <option value="transferencia" {{ old('tipo_pago') === 'transferencia' ? 'selected' : '' }}>
                        Transferencia
                    </option>
                    <option value="tarjeta" {{ old('tipo_pago') === 'tarjeta' ? 'selected' : '' }}>
                        Tarjeta
                    </option>
                    <option value="combinado" {{ old('tipo_pago') === 'combinado' ? 'selected' : '' }}>
                        Pago combinado
                    </option>
                </select>

                <div class="text-xs text-slate-400 mt-2">
                    Solo la parte pagada con tarjeta lleva 20% de recargo.
                </div>
            </div>

            <div class="xl:col-span-3">
                <label class="flex items-start gap-3 rounded-xl border border-slate-700 bg-slate-800 p-3">
                    <input type="checkbox"
                           name="pendiente_pago"
                           id="pendiente_pago"
                           value="1"
                           class="mt-1 rounded border-slate-500 text-slate-900 focus:ring-slate-500"
                        {{ old('pendiente_pago') ? 'checked' : '' }}>

                    <span>
                        <span class="block text-sm font-semibold">Pendiente de pago</span>
                        <span class="block text-xs text-slate-400 mt-1">
                            No se guarda ninguna forma de pago hasta que la clienta abone.
                        </span>
                    </span>
                </label>
            </div>

            <div class="xl:col-span-3 flex flex-wrap gap-2 xl:justify-end">
                <button type="button"
                        id="btnDescuento"
                        class="rounded-xl border border-slate-500 px-4 py-2 hover:bg-slate-800">
                    Aplicar descuento
                </button>

                <a href="{{ route('ventas.index') }}"
                   class="rounded-xl border border-slate-500 px-4 py-2 hover:bg-slate-800">
                    Cancelar
                </a>

                <a href="https://www.afip.gob.ar/"
                   target="_blank"
                   class="rounded-xl border border-slate-500 px-4 py-2 hover:bg-slate-800">
                    Facturar
                </a>

                <button class="rounded-xl bg-white text-slate-900 px-4 py-2 hover:bg-slate-100 font-semibold">
                    Registrar venta
                </button>
            </div>
        </div>

        <div id="boxPagoCombinado"
             class="hidden mt-5 rounded-2xl border border-slate-700 bg-slate-800 p-4">
            <div class="font-bold">Distribución del total base</div>
            <div class="text-xs text-slate-400 mt-1">
                Estos importes deben sumar el total base. El sistema agrega 20% únicamente a la parte de tarjeta.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="text-sm font-semibold text-slate-200">Parte en efectivo</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="pagos[efectivo]"
                           id="pago_efectivo"
                           value="{{ old('pagos.efectivo', 0) }}"
                           class="pago-combinado mt-1 w-full rounded-xl border-slate-600 bg-slate-900 text-white">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-200">Parte en transferencia</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="pagos[transferencia]"
                           id="pago_transferencia"
                           value="{{ old('pagos.transferencia', 0) }}"
                           class="pago-combinado mt-1 w-full rounded-xl border-slate-600 bg-slate-900 text-white">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-200">Parte en tarjeta</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="pagos[tarjeta]"
                           id="pago_tarjeta"
                           value="{{ old('pagos.tarjeta', 0) }}"
                           class="pago-combinado mt-1 w-full rounded-xl border-slate-600 bg-slate-900 text-white">

                    <div class="text-xs text-blue-300 mt-1">
                        La tarjeta cobrará: <strong id="tarjetaFinalPreview">$0,00</strong>
                    </div>
                </div>
            </div>

            <div id="estadoDistribucionPago"
                 class="mt-4 rounded-xl border border-slate-600 px-4 py-3 text-sm text-slate-300">
                Falta asignar: $0,00
            </div>
        </div>
    </div>

    <div id="modalDescuento" class="fixed inset-0 hidden items-center justify-center bg-black/40 p-4 z-50">
        <div class="w-full max-w-3xl bg-white rounded-2xl shadow p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-lg font-bold">Aplicar descuento</div>
                    <div class="text-sm text-slate-600">Seleccioná ítems y porcentaje.</div>
                </div>
                <button type="button" id="closeDescuento" class="text-slate-500 hover:text-slate-900">✖</button>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">% descuento</label>
                    <input id="descuentoPct"
                           type="number"
                           min="0"
                           max="100"
                           step="0.01"
                           value="0"
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div class="flex items-center gap-2">
                    <input id="selTodo"
                           type="checkbox"
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                    <label for="selTodo" class="text-sm font-semibold text-slate-700">Seleccionar todo</label>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border p-3">
                    <div class="font-semibold mb-2">Servicios</div>
                    <div id="listaDescServicios" class="space-y-2 text-sm text-slate-700"></div>
                </div>

                <div class="rounded-2xl border p-3">
                    <div class="font-semibold mb-2">Productos</div>
                    <div id="listaDescProductos" class="space-y-2 text-sm text-slate-700"></div>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button"
                        id="aplicarDescuento"
                        class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Aplicar
                </button>
            </div>
        </div>
    </div>
</form>

<div id="toastProductoAgregado"
     class="fixed top-5 right-5 z-[9999] hidden rounded-2xl bg-green-600 text-white px-4 py-3 shadow-xl">
    Producto agregado
</div>

@php
    $clientesMap = [];
    foreach ($clientes as $cl) {
        $clientesMap[$cl->apellido.' '.$cl->nombre.' - '.$cl->telefono] = $cl->id;
    }

    $colabsMap = [];
    foreach ($colaboradoras as $c) {
        $colabsMap[$c->apellido.' '.$c->nombre] = $c->id;
    }

    $serviciosMap = [];
    $serviciosPrecio = [];
    foreach ($servicios as $s) {
        $serviciosMap[$s->nombre] = $s->id;
        $serviciosPrecio[$s->id] = (float)$s->precio;
    }

    $productosMap = [];
    $productosData = [];
    $productosBarcodeMap = [];

    foreach ($productos as $p) {
        $label = trim(($p->marca.' - '.$p->tipo.' '.$p->contenido));
        $productosMap[$label] = $p->id;

        $productosData[$p->id] = [
            'label' => $label,
            'precio_manual' => (float)$p->precio_venta,
            'precio_efectivo_manual' => $p->precio_efectivo_manual !== null ? (float)$p->precio_efectivo_manual : null,
            'ultimo_costo' => $p->ultimo_costo !== null ? (float)$p->ultimo_costo : null,
            'ultimo_costo_at' => $p->ultimo_costo_at ?? null,
            'precio_manual_updated_at' => $p->precio_manual_updated_at ?? null,
            'stock_venta' => (int)$p->stock_venta,
            'codigo_barra' => $p->codigo_barra,
        ];

        if (!empty($p->codigo_barra)) {
            $productosBarcodeMap[(string)$p->codigo_barra] = $p->id;
        }
    }
@endphp

<script>
const CLIENTES_MAP = @json($clientesMap);
const COLABS_MAP = @json($colabsMap);
const SERVICIOS_MAP = @json($serviciosMap);
const SERVICIOS_PRECIO = @json($serviciosPrecio);
const PRODUCTOS_MAP = @json($productosMap);
const PRODUCTOS_DATA = @json($productosData);
const PRODUCTOS_BARCODE_MAP = @json($productosBarcodeMap);

let TOTAL_BASE_ACTUAL = 0;

function round2(n){
    return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
}

function money(n){
    n = round2(n);
    return '$' + n.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function mostrarToastProductoAgregado(texto = 'Producto agregado'){
    const toast = document.getElementById('toastProductoAgregado');
    if(!toast) return;

    toast.textContent = texto;
    toast.classList.remove('hidden');

    clearTimeout(window._toastProductoTimeout);
    window._toastProductoTimeout = setTimeout(() => {
        toast.classList.add('hidden');
    }, 1600);
}

function esPagoPendiente(){
    return document.getElementById('pendiente_pago')?.checked === true;
}

function getTipoPago(){
    return document.getElementById('tipo_pago')?.value || 'efectivo';
}

function esClienteColab(){
    return document.querySelector('input[name="tipo_cliente"]:checked')?.value === 'colaboradora';
}

function vendedoraPct(){
    const sel = document.getElementById('vendedora_id');
    const opt = sel?.options[sel.selectedIndex];
    return Number(opt?.dataset?.pct || 0);
}

function bindDatalist(inputEl, mapObj, hiddenEl){
    const setId = () => {
        const v = (inputEl.value || '').trim();
        hiddenEl.value = mapObj[v] ? String(mapObj[v]) : '';
    };

    inputEl.addEventListener('change', setId);
    inputEl.addEventListener('blur', setId);
}

function manualEsMasNuevoQueCompra(p){
    if(p.precio_efectivo_manual === null || !p.precio_manual_updated_at){
        return false;
    }

    if(!p.ultimo_costo_at){
        return true;
    }

    return new Date(p.precio_manual_updated_at).getTime()
        >= new Date(p.ultimo_costo_at).getTime();
}

function precioUnitarioEfectivoVenta(pid){
    const p = PRODUCTOS_DATA[pid];

    if(!p) return 0;

    if(manualEsMasNuevoQueCompra(p)){
        return round2(p.precio_efectivo_manual);
    }

    if(p.ultimo_costo !== null){
        return round2(p.ultimo_costo * 1.40);
    }

    if(p.precio_efectivo_manual !== null){
        return round2(p.precio_efectivo_manual);
    }

    return round2(p.precio_manual);
}

function precioUnitarioCosto(pid){
    const p = PRODUCTOS_DATA[pid];

    if(!p) return 0;

    /*
        Si el precio manual es más nuevo que la última compra,
        usamos el precio efectivo vigente y quitamos el 40%.
    */
    if(manualEsMasNuevoQueCompra(p)){
        return round2(Number(p.precio_efectivo_manual || 0) / 1.40);
    }

    /* Si la compra es más reciente, usamos el costo real. */
    if(p.ultimo_costo !== null){
        return round2(p.ultimo_costo);
    }

    /* Producto sin compras: costo estimado desde el precio vigente. */
    if(p.precio_efectivo_manual !== null){
        return round2(Number(p.precio_efectivo_manual) / 1.40);
    }

    return round2(Number(p.precio_manual || 0) / 1.40);
}

function getDescPct(tr){
    return Number(tr.querySelector('input.desc-pct')?.value || 0);
}

function applyDesc(valor, pct){
    return round2(valor * (1 - (pct / 100)));
}

function recalcularPago(totalBase){
    TOTAL_BASE_ACTUAL = round2(totalBase);

    const tipoPago = document.getElementById('tipo_pago');
    const pendiente = document.getElementById('pendiente_pago');
    const boxCombinado = document.getElementById('boxPagoCombinado');
    const totalBaseResumen = document.getElementById('totalBaseResumen');
    const recargoResumen = document.getElementById('recargoTarjetaResumen');
    const totalFinal = document.getElementById('totalFinal');
    const estado = document.getElementById('estadoDistribucionPago');
    const tarjetaPreview = document.getElementById('tarjetaFinalPreview');
    const inputsCombinados = document.querySelectorAll('.pago-combinado');

    if(totalBaseResumen){
        totalBaseResumen.textContent = money(TOTAL_BASE_ACTUAL);
    }

    if(esPagoPendiente()){
        tipoPago.disabled = true;
        boxCombinado.classList.add('hidden');
        inputsCombinados.forEach(i => i.disabled = true);

        recargoResumen.textContent = money(0);
        totalFinal.textContent = money(TOTAL_BASE_ACTUAL);
        return;
    }

    tipoPago.disabled = false;

    if(getTipoPago() !== 'combinado'){
        boxCombinado.classList.add('hidden');
        inputsCombinados.forEach(i => i.disabled = true);

        const recargo = getTipoPago() === 'tarjeta'
            ? round2(TOTAL_BASE_ACTUAL * 0.20)
            : 0;

        recargoResumen.textContent = money(recargo);
        totalFinal.textContent = money(TOTAL_BASE_ACTUAL + recargo);
        return;
    }

    boxCombinado.classList.remove('hidden');
    inputsCombinados.forEach(i => i.disabled = false);

    const efectivo = Number(document.getElementById('pago_efectivo')?.value || 0);
    const transferencia = Number(document.getElementById('pago_transferencia')?.value || 0);
    const tarjeta = Number(document.getElementById('pago_tarjeta')?.value || 0);

    const asignado = round2(efectivo + transferencia + tarjeta);
    const diferencia = round2(TOTAL_BASE_ACTUAL - asignado);
    const recargo = round2(tarjeta * 0.20);
    const tarjetaFinal = round2(tarjeta + recargo);
    const totalConRecargo = round2(TOTAL_BASE_ACTUAL + recargo);

    recargoResumen.textContent = money(recargo);
    totalFinal.textContent = money(totalConRecargo);
    tarjetaPreview.textContent = money(tarjetaFinal);

    estado.classList.remove(
        'border-slate-600',
        'text-slate-300',
        'border-green-500',
        'text-green-300',
        'border-red-500',
        'text-red-300'
    );

    if(Math.abs(diferencia) < 0.01){
        estado.textContent = 'Distribución correcta.';
        estado.classList.add('border-green-500', 'text-green-300');
    } else if(diferencia > 0){
        estado.textContent = 'Falta asignar: ' + money(diferencia);
        estado.classList.add('border-slate-600', 'text-slate-300');
    } else {
        estado.textContent = 'Se excede por: ' + money(Math.abs(diferencia));
        estado.classList.add('border-red-500', 'text-red-300');
    }
}

function recalcular(){
    let subServ = 0;

    document.querySelectorAll('#tablaServicios tbody tr').forEach(tr => {
        const precioBase = Number(tr.querySelector('input.precio-serv')?.value || 0);
        const precioFinalBase = applyDesc(precioBase, getDescPct(tr));

        const precioShow = tr.querySelector('.precio-show');
        if(precioShow){
            precioShow.textContent = money(precioFinalBase);
        }

        subServ += precioFinalBase;
    });

    subServ = round2(subServ);
    document.getElementById('subtotalServicios').textContent = money(subServ);

    let subProd = 0;

    document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
        const pid = Number(tr.querySelector('input.prod-id')?.value || 0);
        const qty = Number(tr.querySelector('input.cant')?.value || 0);
        const stockCell = tr.querySelector('.stock');
        const unitCell = tr.querySelector('.unit-main');
        const unitFinalCell = tr.querySelector('.unit-final');
        const subCell = tr.querySelector('.sub');

        if(!pid || qty <= 0){
            if(stockCell) stockCell.textContent = '-';
            if(unitCell) unitCell.textContent = '-';
            if(unitFinalCell) unitFinalCell.textContent = '';
            if(subCell) subCell.textContent = '-';
            return;
        }

        const p = PRODUCTOS_DATA[pid];
        const stock = Number(p.stock_venta || 0);
        const unitOriginal = esClienteColab()
            ? precioUnitarioCosto(pid)
            : precioUnitarioEfectivoVenta(pid);

        const unitFinal = applyDesc(unitOriginal, getDescPct(tr));
        const sub = round2(unitFinal * qty);

        stockCell.textContent = stock;
        unitCell.textContent = money(unitOriginal);
        unitFinalCell.textContent = getDescPct(tr) > 0 ? 'Final: ' + money(unitFinal) : '';
        subCell.textContent = money(sub);

        stockCell.classList.toggle('text-red-700', qty > stock);
        stockCell.classList.toggle('font-bold', qty > stock);

        subProd += sub;
    });

    subProd = round2(subProd);
    document.getElementById('subtotalProductos').textContent = money(subProd);

    const pct = vendedoraPct();
    let baseComision = 0;

    if(!esClienteColab() && pct > 0){
        document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
            const pid = Number(tr.querySelector('input.prod-id')?.value || 0);
            const qty = Number(tr.querySelector('input.cant')?.value || 0);

            if(!pid || qty <= 0) return;

            const unitFinal = applyDesc(
                precioUnitarioEfectivoVenta(pid),
                getDescPct(tr)
            );

            baseComision += round2(unitFinal * qty);
        });
    }

    const comision = !esClienteColab() && pct > 0
        ? round2(baseComision * (pct / 100))
        : 0;

    document.getElementById('montoComision').textContent = money(comision);

    recalcularPago(round2(subServ + subProd));
}

function addServicioRow(){
    const tbody = document.querySelector('#tablaServicios tbody');
    const idx = tbody.children.length;
    const tr = document.createElement('tr');

    tr.className = 'border-t';
    tr.innerHTML = `
        <td class="px-3 py-2">
            <input list="dl_servicios_${idx}"
                   class="serv-text w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                   placeholder="Escribí para buscar...">

            <datalist id="dl_servicios_${idx}">
                ${Object.keys(SERVICIOS_MAP).map(n => `<option value="${n}"></option>`).join('')}
            </datalist>

            <input type="hidden" name="servicios[${idx}][servicio_id]" class="serv-id" value="">
            <input type="hidden" name="servicios[${idx}][descuento_pct]" class="desc-pct" value="0">
        </td>

        <td class="px-3 py-2">
            <input name="servicios[${idx}][precio]"
                   type="number"
                   step="0.01"
                   min="0"
                   value="0"
                   class="precio-serv w-32 rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

            <div class="text-xs text-slate-500 mt-1">
                Final base: <span class="precio-show">$0,00</span>
            </div>
        </td>

        <td class="px-3 py-2">
            <textarea name="servicios[${idx}][detalle]"
                      rows="2"
                      class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                      placeholder="Ej: color, observación..."></textarea>
        </td>

        <td class="px-3 py-2 text-right">
            <button type="button"
                    class="btn-remove-servicio rounded-xl border px-3 py-1 hover:bg-slate-50">
                🗑️
            </button>
        </td>
    `;

    const inputText = tr.querySelector('.serv-text');
    const inputId = tr.querySelector('.serv-id');
    const precioInput = tr.querySelector('.precio-serv');

    const setServicio = () => {
        const valor = (inputText.value || '').trim();
        const sid = SERVICIOS_MAP[valor] ? Number(SERVICIOS_MAP[valor]) : 0;

        inputId.value = sid ? String(sid) : '';

        if(sid && SERVICIOS_PRECIO[sid] !== undefined){
            precioInput.value = String(SERVICIOS_PRECIO[sid]);
        }

        recalcular();
    };

    inputText.addEventListener('change', setServicio);
    inputText.addEventListener('blur', setServicio);
    precioInput.addEventListener('input', recalcular);

    tr.querySelector('.btn-remove-servicio').addEventListener('click', () => {
        tr.remove();
        recalcular();
    });

    tbody.appendChild(tr);
    recalcular();
}

function addProductoRow(prefillPid = null, prefillQty = 1){
    const tbody = document.querySelector('#tablaProductos tbody');
    const idx = tbody.children.length;
    const tr = document.createElement('tr');

    tr.className = 'border-t';
    tr.innerHTML = `
        <td class="px-3 py-2">
            <input list="dl_productos_${idx}"
                   class="prod-text w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                   placeholder="Escribí para buscar...">

            <datalist id="dl_productos_${idx}">
                ${Object.keys(PRODUCTOS_MAP).map(n => `<option value="${n}"></option>`).join('')}
            </datalist>

            <input type="hidden" name="productos[${idx}][producto_id]" class="prod-id" value="">
            <input type="hidden" name="productos[${idx}][descuento_pct]" class="desc-pct" value="0">
        </td>

        <td class="px-3 py-2 stock">-</td>

        <td class="px-3 py-2">
            <input name="productos[${idx}][cantidad]"
                   type="number"
                   min="1"
                   value="${prefillQty}"
                   class="cant w-24 rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </td>

        <td class="px-3 py-2">
            <div class="unit-main">-</div>
            <div class="unit-final text-xs text-slate-500 mt-1"></div>
        </td>

        <td class="px-3 py-2 sub">-</td>

        <td class="px-3 py-2 text-right">
            <button type="button"
                    class="btn-remove-producto rounded-xl border px-3 py-1 hover:bg-slate-50">
                🗑️
            </button>
        </td>
    `;

    const inputText = tr.querySelector('.prod-text');
    const inputId = tr.querySelector('.prod-id');

    const setProducto = () => {
        const valor = (inputText.value || '').trim();
        const pid = PRODUCTOS_MAP[valor] ? Number(PRODUCTOS_MAP[valor]) : 0;

        inputId.value = pid ? String(pid) : '';
        recalcular();
    };

    inputText.addEventListener('change', setProducto);
    inputText.addEventListener('blur', setProducto);
    tr.querySelector('.cant').addEventListener('input', recalcular);

    tr.querySelector('.btn-remove-producto').addEventListener('click', () => {
        tr.remove();
        recalcular();
    });

    tbody.appendChild(tr);

    if(prefillPid && PRODUCTOS_DATA[prefillPid]){
        inputText.value = PRODUCTOS_DATA[prefillPid].label;
        inputId.value = String(prefillPid);
    }

    recalcular();
}

function agregarProductoPorCodigo(codigo){
    const limpio = String(codigo || '').trim();

    if(!limpio) return;

    const pid = PRODUCTOS_BARCODE_MAP[limpio]
        ? Number(PRODUCTOS_BARCODE_MAP[limpio])
        : 0;

    if(!pid){
        alert('No existe un producto con ese código de barras.');
        return;
    }

    const filaExistente = Array.from(
        document.querySelectorAll('#tablaProductos tbody tr')
    ).find(tr => Number(tr.querySelector('.prod-id')?.value || 0) === pid);

    const nombre = PRODUCTOS_DATA[pid]?.label || 'Producto';

    if(filaExistente){
        const qty = filaExistente.querySelector('.cant');
        qty.value = String(Number(qty.value || 0) + 1);
        recalcular();
        mostrarToastProductoAgregado(nombre + ' agregado');
        return;
    }

    addProductoRow(pid, 1);
    mostrarToastProductoAgregado(nombre + ' agregado');
}

function toggleClienteBoxes(){
    const colab = esClienteColab();

    document.getElementById('box_cliente').classList.toggle('hidden', colab);
    document.getElementById('box_colab').classList.toggle('hidden', !colab);

    recalcular();
}

function activarTab(tab){
    const servicios = tab === 'servicios';

    document.getElementById('tabServicios').className = servicios
        ? 'px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800'
        : 'px-4 py-2 rounded-xl border bg-white hover:bg-slate-50';

    document.getElementById('tabProductos').className = servicios
        ? 'px-4 py-2 rounded-xl border bg-white hover:bg-slate-50'
        : 'px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800';

    document.getElementById('panelServicios').classList.toggle('hidden', !servicios);
    document.getElementById('panelProductos').classList.toggle('hidden', servicios);
}

function abrirDescuento(){
    const contS = document.getElementById('listaDescServicios');
    const contP = document.getElementById('listaDescProductos');

    contS.innerHTML = '';
    contP.innerHTML = '';

    document.querySelectorAll('#tablaServicios tbody tr').forEach((tr, i) => {
        const txt = tr.querySelector('.serv-text')?.value || '(servicio)';

        contS.insertAdjacentHTML('beforeend', `
            <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-desc" data-target="serv" data-idx="${i}">
                <span>${txt}</span>
            </label>
        `);
    });

    document.querySelectorAll('#tablaProductos tbody tr').forEach((tr, i) => {
        const txt = tr.querySelector('.prod-text')?.value || '(producto)';

        contP.insertAdjacentHTML('beforeend', `
            <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-desc" data-target="prod" data-idx="${i}">
                <span>${txt}</span>
            </label>
        `);
    });

    const modal = document.getElementById('modalDescuento');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarDescuento(){
    const modal = document.getElementById('modalDescuento');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function aplicarDescuento(){
    const pct = Number(document.getElementById('descuentoPct')?.value || 0);

    document.querySelectorAll('.chk-desc:checked').forEach(chk => {
        const filas = chk.dataset.target === 'serv'
            ? document.querySelectorAll('#tablaServicios tbody tr')
            : document.querySelectorAll('#tablaProductos tbody tr');

        const tr = filas[Number(chk.dataset.idx)];

        if(tr){
            tr.querySelector('.desc-pct').value = String(pct);
        }
    });

    recalcular();
    cerrarDescuento();
}

document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('tabServicios').addEventListener('click', () => activarTab('servicios'));
    document.getElementById('tabProductos').addEventListener('click', () => activarTab('productos'));
    document.getElementById('addServicio').addEventListener('click', addServicioRow);
    document.getElementById('addProducto').addEventListener('click', () => addProductoRow());
    document.getElementById('vendedora_id').addEventListener('change', recalcular);
    document.getElementById('tipo_pago').addEventListener('change', recalcular);
    document.getElementById('pendiente_pago').addEventListener('change', recalcular);

    document.querySelectorAll('.pago-combinado').forEach(input => {
        input.addEventListener('input', recalcular);
    });

    document.querySelectorAll('input[name="tipo_cliente"]').forEach(radio => {
        radio.addEventListener('change', toggleClienteBoxes);
    });

    bindDatalist(
        document.getElementById('cliente_buscar'),
        CLIENTES_MAP,
        document.getElementById('cliente_id')
    );

    bindDatalist(
        document.getElementById('colab_buscar'),
        COLABS_MAP,
        document.getElementById('cliente_colaboradora_id')
    );

    document.getElementById('btnDescuento').addEventListener('click', abrirDescuento);
    document.getElementById('closeDescuento').addEventListener('click', cerrarDescuento);
    document.getElementById('aplicarDescuento').addEventListener('click', aplicarDescuento);

    document.getElementById('selTodo').addEventListener('change', function(){
        document.querySelectorAll('.chk-desc').forEach(c => c.checked = this.checked);
    });

    document.getElementById('modalDescuento').addEventListener('click', function(e){
        if(e.target === this){
            cerrarDescuento();
        }
    });

    const scanProducto = document.getElementById('scan_producto');

    scanProducto.addEventListener('keydown', function(e){
        if(e.key === 'Enter'){
            e.preventDefault();

            if(this.value.trim() !== ''){
                agregarProductoPorCodigo(this.value);
                this.value = '';
                activarTab('productos');
            }
        }
    });

    scanProducto.addEventListener('change', function(){
        if(this.value.trim() !== ''){
            agregarProductoPorCodigo(this.value);
            this.value = '';
            activarTab('productos');
        }
    });

    document.getElementById('formVenta').addEventListener('submit', function(e){
        if(esPagoPendiente() || getTipoPago() !== 'combinado'){
            return;
        }

        const efectivo = Number(document.getElementById('pago_efectivo')?.value || 0);
        const transferencia = Number(document.getElementById('pago_transferencia')?.value || 0);
        const tarjeta = Number(document.getElementById('pago_tarjeta')?.value || 0);
        const suma = round2(efectivo + transferencia + tarjeta);
        const cantidadMetodos = [efectivo, transferencia, tarjeta].filter(v => v > 0).length;

        if(Math.abs(suma - TOTAL_BASE_ACTUAL) > 0.01){
            e.preventDefault();
            alert('La distribución del pago combinado debe coincidir con el total base.');
            return;
        }

        if(cantidadMetodos < 2){
            e.preventDefault();
            alert('Para pago combinado tenés que usar al menos dos formas de pago.');
        }
    });

    activarTab('servicios');
    toggleClienteBoxes();
    addServicioRow();
    addProductoRow();
    recalcular();
});
</script>

@endsection
