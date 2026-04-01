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

    {{-- CABECERA --}}
    <div class="rounded-2xl border bg-slate-900 text-white p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-200">Fecha *</label>
                <input type="datetime-local" name="fecha"
                       value="{{ old('fecha', now()->format('Y-m-d\TH:i')) }}"
                       class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-200">Método de pago *</label>
                <select id="metodo_pago" name="metodo_pago"
                        class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white">
                    <option value="efectivo" {{ old('metodo_pago','efectivo')==='efectivo'?'selected':'' }}>Efectivo / Transferencia</option>
                    <option value="tarjeta" {{ old('metodo_pago')==='tarjeta'?'selected':'' }}>Tarjeta</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-200">Vendedora (opcional)</label>
                <select id="vendedora_id" name="vendedora_id"
                        class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white">
                    <option value="" data-pct="0">-</option>
                    @foreach($colaboradoras as $c)
                        <option value="{{ $c->id }}"
                                data-pct="{{ (float)$c->comision_pct }}"
                            {{ (string)old('vendedora_id')===(string)$c->id ? 'selected':'' }}>
                            {{ $c->nombre }} {{ $c->apellido }} ({{ number_format((float)$c->comision_pct,2,',','.') }}%)
                        </option>
                    @endforeach
                </select>
                <div class="text-xs text-slate-300 mt-1">La comisión se calcula sobre productos.</div>
            </div>

            <div class="md:col-span-6">
                <label class="text-sm font-semibold text-slate-200">Tipo de cliente *</label>
                <div class="mt-2 flex flex-wrap gap-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="tipo_cliente" value="cliente" {{ old('tipo_cliente','cliente')==='cliente'?'checked':'' }}>
                        <span>Cliente</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="tipo_cliente" value="colaboradora" {{ old('tipo_cliente')==='colaboradora'?'checked':'' }}>
                        <span>Colaboradora (productos a costo)</span>
                    </label>
                </div>
            </div>

            {{-- CLIENTE + NOTAS --}}
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

                <input type="hidden" name="cliente_colaboradora_id" id="cliente_colaboradora_id" value="{{ old('cliente_colaboradora_id') }}">
                <div class="text-xs text-slate-300 mt-1">Productos se cobran a costo (última compra).</div>
            </div>

            <div class="md:col-span-6">
                <label class="text-sm font-semibold text-slate-200">Notas</label>
                <textarea name="notas" rows="2"
                          class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white"
                          placeholder="Ej: color usado, observaciones, etc.">{{ old('notas') }}</textarea>
            </div>

        </div>
    </div>

    {{-- SOLAPAS --}}
    <div class="mt-6 flex gap-2">
        <button type="button" id="tabServicios"
                class="px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800">
            Servicios
        </button>

        <button type="button" id="tabProductos"
                class="px-4 py-2 rounded-xl border bg-white hover:bg-slate-50">
            Productos
        </button>
    </div>

    {{-- PANEL SERVICIOS --}}
    <div id="panelServicios" class="mt-4 rounded-2xl border bg-white">
        <div class="p-4 border-b flex items-center justify-between">
            <div>
                <div class="text-lg font-bold text-slate-900">Servicios</div>
                <div class="text-sm text-slate-600">Agregar servicios con detalle y precio.</div>
            </div>
            <button type="button" id="addServicio"
                    class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                + Agregar servicio
            </button>
        </div>

        <div class="p-4 overflow-x-auto">
            <table class="min-w-full bg-white" id="tablaServicios">
                <thead class="bg-slate-100 text-slate-700">
                <tr>
                    <th class="text-left px-3 py-2 text-sm font-semibold">Servicio</th>
                    <th class="text-left px-3 py-2 text-sm font-semibold">Precio</th>
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

    {{-- PANEL PRODUCTOS --}}
    <div id="panelProductos" class="mt-4 rounded-2xl border bg-white hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <div>
                <div class="text-lg font-bold text-slate-900">Productos</div>
                <div class="text-sm text-slate-600">Muestra stock y calcula precios por costo y método.</div>
            </div>
            <button type="button" id="addProducto"
                    class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                + Agregar producto
            </button>
        </div>

        <div class="p-4">
            {{-- ESCANEAR PRODUCTO --}}
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
                        El lector funciona como teclado y completa este campo automáticamente.
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
                        <th class="text-left px-3 py-2 text-sm font-semibold">Unit.</th>
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

    {{-- TOTAL + ACCIONES --}}
    <div class="mt-6 rounded-2xl border bg-slate-900 text-white p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
                <div class="text-sm text-slate-300">TOTAL A COBRAR</div>
                <div class="text-4xl font-extrabold mt-1" id="totalFinal">$0,00</div>
                <div class="text-xs text-slate-300 mt-1">Según método seleccionado.</div>
            </div>

            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="pendiente_pago" id="pendiente_pago"
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                        {{ old('pendiente_pago') ? 'checked' : '' }}>
                    <span class="text-sm font-semibold">Pendiente de pago</span>
                </label>
            </div>

            <div class="flex flex-wrap gap-2 justify-end">
                <button type="button" id="btnDescuento"
                        class="rounded-xl border border-slate-500 px-4 py-2 hover:bg-slate-800">
                    Aplicar descuento
                </button>

                <a href="{{ route('ventas.index') }}"
                   class="rounded-xl border border-slate-500 px-4 py-2 hover:bg-slate-800">
                    Cancelar
                </a>

                <a href="https://www.afip.gob.ar/" target="_blank"
                   class="rounded-xl border border-slate-500 px-4 py-2 hover:bg-slate-800">
                    Facturar
                </a>

                <button class="rounded-xl bg-white text-slate-900 px-4 py-2 hover:bg-slate-100 font-semibold">
                    Registrar venta
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DESCUENTO --}}
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
                    <input id="descuentoPct" type="number" min="0" max="100" step="0.01" value="0"
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div class="flex items-center gap-2">
                    <input id="selTodo" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
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

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" id="aplicarDescuento"
                        class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Aplicar
                </button>
            </div>
        </div>
    </div>

</form>

{{-- TOAST PRODUCTO AGREGADO --}}
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
            'ultimo_costo' => $p->ultimo_costo !== null ? (float)$p->ultimo_costo : null,
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

function round2(n){ return Math.round((Number(n) + Number.EPSILON) * 100) / 100; }
function money(n){
    n = round2(n);
    return '$' + n.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
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

function getMetodo(){ return document.getElementById('metodo_pago').value; }
function esClienteColab(){
    const v = document.querySelector('input[name="tipo_cliente"]:checked')?.value;
    return v === 'colaboradora';
}
function vendedoraPct(){
    const sel = document.getElementById('vendedora_id');
    const opt = sel.options[sel.selectedIndex];
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

function precioUnitarioVenta(pid, metodo){
    const p = PRODUCTOS_DATA[pid];
    if(!p) return 0;

    const costo = p.ultimo_costo;

    if(costo !== null){
        return round2(metodo === 'tarjeta' ? costo * 1.60 : costo * 1.40);
    }

    if(metodo === 'tarjeta') return round2((p.precio_manual / 1.40) * 1.60);
    return round2(p.precio_manual);
}

function precioUnitarioCosto(pid){
    const p = PRODUCTOS_DATA[pid];
    if(!p) return 0;
    const costo = p.ultimo_costo;
    if(costo !== null) return round2(costo);
    return round2(p.precio_manual / 1.40);
}

function getDescPct(tr){
    return Number(tr.querySelector('input.desc-pct')?.value || 0);
}
function applyDesc(valor, pct){
    return round2(valor * (1 - (pct/100)));
}

function recalcular(){
    let subServ = 0;

    document.querySelectorAll('#tablaServicios tbody tr').forEach(tr => {
        const precio = Number(tr.querySelector('input.precio-serv')?.value || 0);
        const desc = getDescPct(tr);
        const precioFinal = applyDesc(precio, desc);

        const precioShow = tr.querySelector('.precio-show');
        if(precioShow){
            precioShow.textContent = money(precioFinal);
        }

        subServ += precioFinal;
    });

    subServ = round2(subServ);
    const subtotalServiciosEl = document.getElementById('subtotalServicios');
    if(subtotalServiciosEl){
        subtotalServiciosEl.textContent = money(subServ);
    }

    let subProdMetodo = 0;

    document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
        const hid = tr.querySelector('input.prod-id');
        const pid = hid && hid.value ? Number(hid.value) : 0;
        const qty = Number(tr.querySelector('input.cant')?.value || 0);
        const desc = getDescPct(tr);

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
        if(stockCell) stockCell.textContent = stock;

        let unitOriginal = 0;
        if(esClienteColab()){
            unitOriginal = precioUnitarioCosto(pid);
        } else {
            unitOriginal = precioUnitarioVenta(pid, getMetodo());
        }

        const unitFinal = applyDesc(unitOriginal, desc);
        const sub = round2(unitFinal * qty);

        if(unitCell) unitCell.textContent = money(unitOriginal);

        if(unitFinalCell){
            if(desc > 0){
                unitFinalCell.textContent = 'Final: ' + money(unitFinal);
            } else {
                unitFinalCell.textContent = '';
            }
        }

        if(subCell) subCell.textContent = money(sub);

        if(stockCell){
            stockCell.classList.toggle('text-red-700', qty > stock);
            stockCell.classList.toggle('font-bold', qty > stock);
        }

        subProdMetodo += sub;
    });

    subProdMetodo = round2(subProdMetodo);
    const subtotalProductosEl = document.getElementById('subtotalProductos');
    if(subtotalProductosEl){
        subtotalProductosEl.textContent = money(subProdMetodo);
    }

    const pct = vendedoraPct();
    let baseComision = 0;

    if(!esClienteColab() && pct > 0){
        document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
            const hid = tr.querySelector('input.prod-id');
            const pid = hid && hid.value ? Number(hid.value) : 0;
            const qty = Number(tr.querySelector('input.cant')?.value || 0);
            const desc = getDescPct(tr);

            if(!pid || qty <= 0) return;

            const unitEfectivo = precioUnitarioVenta(pid, 'efectivo');
            const unitEfectivoFinal = applyDesc(unitEfectivo, desc);

            baseComision += round2(unitEfectivoFinal * qty);
        });
    }

    baseComision = round2(baseComision);

    let comision = 0;
    if(!esClienteColab() && pct > 0){
        comision = round2(baseComision * (pct/100));
    }

    const montoComisionEl = document.getElementById('montoComision');
    if(montoComisionEl){
        montoComisionEl.textContent = money(comision);
    }

    const total = round2(subServ + subProdMetodo);
    const totalFinalEl = document.getElementById('totalFinal');
    if(totalFinalEl){
        totalFinalEl.textContent = money(total);
    }
}

function addServicioRow(){
    const tbody = document.querySelector('#tablaServicios tbody');
    const idx = tbody.children.length;

    const tr = document.createElement('tr');
    tr.className = 'border-t';

    tr.innerHTML = `
        <td class="px-3 py-2">
            <input list="dl_servicios_${idx}" class="serv-text w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                   placeholder="Escribí para buscar...">
            <datalist id="dl_servicios_${idx}">
                ${Object.keys(SERVICIOS_MAP).map(n => `<option value="${n}"></option>`).join('')}
            </datalist>

            <input type="hidden" name="servicios[${idx}][servicio_id]" class="serv-id" value="">
            <input type="hidden" name="servicios[${idx}][descuento_pct]" class="desc-pct" value="0">
        </td>

        <td class="px-3 py-2">
            <input name="servicios[${idx}][precio]" type="number" step="0.01" min="0" value="0"
                   class="precio-serv w-32 rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
            <div class="text-xs text-slate-500 mt-1">Final: <span class="precio-show">$0,00</span></div>
        </td>

        <td class="px-3 py-2">
            <textarea name="servicios[${idx}][detalle]" rows="2"
                      class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                      placeholder="Ej: color, observación..."></textarea>
        </td>

        <td class="px-3 py-2 text-right">
            <button type="button" class="btn-remove-servicio rounded-xl border px-3 py-1 hover:bg-slate-50">🗑️</button>
        </td>
    `;

    const inputText = tr.querySelector('.serv-text');
    const inputId = tr.querySelector('.serv-id');
    const precioInput = tr.querySelector('.precio-serv');
    const removeBtn = tr.querySelector('.btn-remove-servicio');

    const setServicio = () => {
        const v = (inputText.value || '').trim();
        const sid = SERVICIOS_MAP[v] ? Number(SERVICIOS_MAP[v]) : 0;
        inputId.value = sid ? String(sid) : '';

        if(sid && SERVICIOS_PRECIO[sid] !== undefined){
            precioInput.value = String(SERVICIOS_PRECIO[sid]);
        }

        recalcular();
    };

    inputText.addEventListener('change', setServicio);
    inputText.addEventListener('blur', setServicio);
    precioInput.addEventListener('input', recalcular);

    removeBtn.addEventListener('click', () => {
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
            <input list="dl_productos_${idx}" class="prod-text w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                   placeholder="Escribí para buscar...">
            <datalist id="dl_productos_${idx}">
                ${Object.keys(PRODUCTOS_MAP).map(n => `<option value="${n}"></option>`).join('')}
            </datalist>

            <input type="hidden" name="productos[${idx}][producto_id]" class="prod-id" value="">
            <input type="hidden" name="productos[${idx}][descuento_pct]" class="desc-pct" value="0">
        </td>

        <td class="px-3 py-2 stock">-</td>

        <td class="px-3 py-2">
            <input name="productos[${idx}][cantidad]" type="number" min="1" value="${prefillQty}"
                   class="cant w-24 rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
        </td>

        <td class="px-3 py-2 unit">
            <div class="unit-main">-</div>
            <div class="unit-final text-xs text-slate-500 mt-1"></div>
        </td>

        <td class="px-3 py-2 sub">-</td>

        <td class="px-3 py-2 text-right">
            <button type="button" class="btn-remove-producto rounded-xl border px-3 py-1 hover:bg-slate-50">🗑️</button>
        </td>
    `;

    const inputText = tr.querySelector('.prod-text');
    const inputId = tr.querySelector('.prod-id');
    const qtyInput = tr.querySelector('.cant');
    const removeBtn = tr.querySelector('.btn-remove-producto');

    const setProducto = () => {
        const v = (inputText.value || '').trim();
        const pid = PRODUCTOS_MAP[v] ? Number(PRODUCTOS_MAP[v]) : 0;
        inputId.value = pid ? String(pid) : '';
        recalcular();
    };

    inputText.addEventListener('change', setProducto);
    inputText.addEventListener('blur', setProducto);
    qtyInput.addEventListener('input', recalcular);

    removeBtn.addEventListener('click', () => {
        tr.remove();
        recalcular();
    });

    tbody.appendChild(tr);

    if (prefillPid && PRODUCTOS_DATA[prefillPid]) {
        inputText.value = PRODUCTOS_DATA[prefillPid].label;
        inputId.value = String(prefillPid);
    }

    recalcular();
}

function agregarProductoPorCodigo(codigo){
    const codigoLimpio = String(codigo || '').trim();
    if (!codigoLimpio) return;

    const pid = PRODUCTOS_BARCODE_MAP[codigoLimpio] ? Number(PRODUCTOS_BARCODE_MAP[codigoLimpio]) : 0;

    if (!pid) {
        alert('No existe un producto con ese código de barras.');
        return;
    }

    const nombreProducto = PRODUCTOS_DATA[pid]?.label || 'Producto';

    const filas = Array.from(document.querySelectorAll('#tablaProductos tbody tr'));
    const filaExistente = filas.find(tr => {
        const hid = tr.querySelector('.prod-id');
        return hid && Number(hid.value || 0) === pid;
    });

    if (filaExistente) {
        const qtyInput = filaExistente.querySelector('.cant');
        const actual = Number(qtyInput.value || 0);
        qtyInput.value = String(actual + 1);
        recalcular();
        mostrarToastProductoAgregado(nombreProducto + ' agregado');
        return;
    }

    addProductoRow(pid, 1);
    mostrarToastProductoAgregado(nombreProducto + ' agregado');
}

function toggleClienteBoxes(){
    const aColab = esClienteColab();

    const boxCliente = document.getElementById('box_cliente');
    const boxColab = document.getElementById('box_colab');

    if(boxCliente) boxCliente.classList.toggle('hidden', aColab);
    if(boxColab) boxColab.classList.toggle('hidden', !aColab);

    recalcular();
}

function activarTab(tab){
    const btnS = document.getElementById('tabServicios');
    const btnP = document.getElementById('tabProductos');
    const panS = document.getElementById('panelServicios');
    const panP = document.getElementById('panelProductos');

    if(tab === 'servicios'){
        if(btnS) btnS.className = 'px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800';
        if(btnP) btnP.className = 'px-4 py-2 rounded-xl border bg-white hover:bg-slate-50';
        if(panS) panS.classList.remove('hidden');
        if(panP) panP.classList.add('hidden');
    } else {
        if(btnP) btnP.className = 'px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800';
        if(btnS) btnS.className = 'px-4 py-2 rounded-xl border bg-white hover:bg-slate-50';
        if(panP) panP.classList.remove('hidden');
        if(panS) panS.classList.add('hidden');
    }
}

function abrirDescuento(){
    const contS = document.getElementById('listaDescServicios');
    const contP = document.getElementById('listaDescProductos');

    if(contS) contS.innerHTML = '';
    if(contP) contP.innerHTML = '';

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

    const m = document.getElementById('modalDescuento');
    if(m){
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
}

function cerrarDescuento(){
    const m = document.getElementById('modalDescuento');
    if(m){
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
}

function aplicarDescuento(){
    const pct = Number(document.getElementById('descuentoPct')?.value || 0);

    document.querySelectorAll('.chk-desc:checked').forEach(chk => {
        const t = chk.dataset.target;
        const idx = Number(chk.dataset.idx);

        if(t === 'serv'){
            const tr = document.querySelectorAll('#tablaServicios tbody tr')[idx];
            if(tr) tr.querySelector('.desc-pct').value = String(pct);
        } else {
            const tr = document.querySelectorAll('#tablaProductos tbody tr')[idx];
            if(tr) tr.querySelector('.desc-pct').value = String(pct);
        }
    });

    recalcular();
    cerrarDescuento();
}

document.addEventListener('DOMContentLoaded', function () {
    const tabServicios = document.getElementById('tabServicios');
    const tabProductos = document.getElementById('tabProductos');
    const addServicio = document.getElementById('addServicio');
    const addProducto = document.getElementById('addProducto');
    const metodoPago = document.getElementById('metodo_pago');
    const vendedoraId = document.getElementById('vendedora_id');
    const btnDescuento = document.getElementById('btnDescuento');
    const closeDescuento = document.getElementById('closeDescuento');
    const aplicarDescuentoBtn = document.getElementById('aplicarDescuento');
    const selTodo = document.getElementById('selTodo');
    const modalDescuento = document.getElementById('modalDescuento');
    const scanProducto = document.getElementById('scan_producto');

    if(tabServicios) tabServicios.addEventListener('click', () => activarTab('servicios'));
    if(tabProductos) tabProductos.addEventListener('click', () => activarTab('productos'));
    if(addServicio) addServicio.addEventListener('click', addServicioRow);
    if(addProducto) addProducto.addEventListener('click', () => addProductoRow());
    if(metodoPago) metodoPago.addEventListener('change', recalcular);
    if(vendedoraId) vendedoraId.addEventListener('change', recalcular);

    document.querySelectorAll('input[name="tipo_cliente"]').forEach(r => {
        r.addEventListener('change', toggleClienteBoxes);
    });

    const clienteBuscar = document.getElementById('cliente_buscar');
    const clienteId = document.getElementById('cliente_id');
    const colabBuscar = document.getElementById('colab_buscar');
    const clienteColabId = document.getElementById('cliente_colaboradora_id');

    if(clienteBuscar && clienteId){
        bindDatalist(clienteBuscar, CLIENTES_MAP, clienteId);
    }

    if(colabBuscar && clienteColabId){
        bindDatalist(colabBuscar, COLABS_MAP, clienteColabId);
    }

    if(btnDescuento) btnDescuento.addEventListener('click', abrirDescuento);
    if(closeDescuento) closeDescuento.addEventListener('click', cerrarDescuento);
    if(aplicarDescuentoBtn) aplicarDescuentoBtn.addEventListener('click', aplicarDescuento);

    if(selTodo){
        selTodo.addEventListener('change', function(){
            const on = this.checked;
            document.querySelectorAll('.chk-desc').forEach(c => c.checked = on);
        });
    }

    if(modalDescuento){
        modalDescuento.addEventListener('click', function(e){
            if(e.target === this) cerrarDescuento();
        });
    }

    if (scanProducto) {
        scanProducto.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const codigo = this.value;
                if (codigo.trim() !== '') {
                    agregarProductoPorCodigo(codigo);
                    this.value = '';
                    activarTab('productos');
                }
            }
        });

        scanProducto.addEventListener('change', function() {
            const codigo = this.value;
            if (codigo.trim() !== '') {
                agregarProductoPorCodigo(codigo);
                this.value = '';
                activarTab('productos');
            }
        });
    }

    activarTab('servicios');
    toggleClienteBoxes();
    addServicioRow();
    addProductoRow();
    recalcular();
});
</script>

@endsection