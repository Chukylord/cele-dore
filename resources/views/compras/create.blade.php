@extends('layouts.admin')

@section('title', 'Nueva compra - FN Peluquería')
@section('h1', 'Nueva Compra')
@section('sub', 'Cargar varias líneas en una sola compra (lote).')

@section('content')

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
    $provMap = $proveedores->pluck('id','nombre');

    $prodsByProv = [];
    $productosData = [];
    $productosBarcodeMap = [];

    foreach($productos as $p){
        $label = trim(($p->marca.' - '.$p->tipo.' '.$p->contenido));

        $prodsByProv[$p->proveedor_id][] = [
            'id' => $p->id,
            'label' => $label,
        ];

        $productosData[$p->id] = [
            'id' => $p->id,
            'label' => $label,
            'proveedor_id' => $p->proveedor_id,
            'proveedor_nombre' => optional($p->proveedor)->nombre,
            'ultimo_costo' => $p->ultimo_costo !== null ? (float)$p->ultimo_costo : 0,
            'codigo_barra' => $p->codigo_barra,
        ];

        if (!empty($p->codigo_barra)) {
            $productosBarcodeMap[(string)$p->codigo_barra] = $p->id;
        }
    }
@endphp

<form method="POST" action="{{ route('compras.store') }}" id="formCompra">
    @csrf

    <div class="fn-section-card mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="text-sm font-semibold text-slate-700">Fecha *</label>
                <input type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Entrega inicial</label>
                <input type="number" step="0.01" min="0" name="entrega_inicial" value="{{ old('entrega_inicial', 0) }}"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Nota</label>
                <input name="nota" value="{{ old('nota') }}" placeholder="Ej: compra mayorista"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>
        </div>
    </div>

    <div class="fn-section-card">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-lg font-bold">Productos de la compra</div>
                <div class="text-sm text-slate-600">
                    El descuento afecta el total de la compra, pero el precio unitario queda sin descuento para calcular ventas.
                </div>
            </div>
            <button type="button" id="addRow"
                    class="fn-primary-action">
                + Agregar línea
            </button>
        </div>

        <div class="fn-soft-panel mb-4 p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Escanear producto</label>
                    <input id="scan_producto_compra"
                           type="text"
                           autocomplete="off"
                           placeholder="Hacé click acá y escaneá el código..."
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    <div class="text-xs text-slate-500 mt-1">
                        Si el producto ya está cargado en la compra, suma 1 a la cantidad.
                    </div>
                </div>

                <div class="text-sm text-slate-500">
                    El lector funciona como teclado y completa este campo automáticamente.
                </div>
            </div>
        </div>

        <div class="fn-table-shell overflow-x-auto">
            <table class="min-w-full bg-white" id="tablaItems">
                <thead class="bg-slate-50 text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Proveedor</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Producto</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Cantidad</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Precio unitario</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">% Desc.</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Subtotal</th>
                    <th class="text-right px-4 py-3 text-sm font-semibold">Quitar</th>
                </tr>
                </thead>
                <tbody></tbody>
                <tfoot class="bg-slate-50">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-right font-semibold">Total</td>
                    <td class="px-4 py-3 font-bold" id="totalCompra">$0,00</td>
                    <td></td>
                </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-6 flex gap-2 justify-end">
            <a href="{{ route('compras.index') }}" class="fn-secondary-action">Cancelar</a>
            <button class="fn-primary-action">
                Guardar compra
            </button>
        </div>
    </div>
</form>

<div id="toastProductoAgregado"
     class="fixed top-5 right-5 z-[9999] hidden rounded-2xl bg-green-600 text-white px-4 py-3 shadow-xl">
    Producto agregado
</div>

<script>
const PROV_MAP = @json($provMap);
const PRODS_BY_PROV = @json($prodsByProv);
const PRODUCTOS_DATA = @json($productosData);
const PRODUCTOS_BARCODE_MAP = @json($productosBarcodeMap);

function money(n){
    n = Number(n || 0);
    return '$' + n.toLocaleString('es-AR', {minimumFractionDigits:2, maximumFractionDigits:2});
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

function limpiarTexto(valor){
    return String(valor || '').replaceAll('"','&quot;');
}

function addRow(prefillProductoId = null, prefillCantidad = 1){
    const tbody = document.querySelector('#tablaItems tbody');
    const idx = tbody.children.length;

    let productoPrefill = null;

    if (prefillProductoId && PRODUCTOS_DATA[prefillProductoId]) {
        productoPrefill = PRODUCTOS_DATA[prefillProductoId];
    }

    let prevProvText = '';
    let prevProvId = '';

    const lastRow = tbody.lastElementChild;

    if (!productoPrefill && lastRow) {
        prevProvText = lastRow.querySelector('.prov-text')?.value || '';
        prevProvId = lastRow.querySelector('.prov-id')?.value || '';
    }

    if (productoPrefill) {
        prevProvText = productoPrefill.proveedor_nombre || '';
        prevProvId = productoPrefill.proveedor_id || '';
    }

    const tr = document.createElement('tr');
    tr.className = 'border-t';

    tr.innerHTML = `
        <td class="px-4 py-3">
            <input list="dl_proveedores_${idx}" class="prov-text w-full rounded-xl border-slate-300"
                   placeholder="Buscar proveedor..." value="${limpiarTexto(prevProvText)}">
            <datalist id="dl_proveedores_${idx}">
                ${Object.keys(PROV_MAP).map(n => `<option value="${n}"></option>`).join('')}
            </datalist>
            <input type="hidden" name="items[${idx}][proveedor_id]" class="prov-id" value="${prevProvId}">
        </td>

        <td class="px-4 py-3">
            <input list="dl_productos_${idx}" class="prod-text w-full rounded-xl border-slate-300"
                   placeholder="Buscar producto...">
            <datalist id="dl_productos_${idx}"></datalist>
            <input type="hidden" name="items[${idx}][producto_id]" class="prod-id" value="">
        </td>

        <td class="px-4 py-3">
            <input type="number" min="1" value="${prefillCantidad}" name="items[${idx}][cantidad]"
                   class="cant w-24 rounded-xl border-slate-300">
        </td>

        <td class="px-4 py-3">
            <input type="number" step="0.01" min="0" value="0" name="items[${idx}][precio_unitario]"
                   class="precio w-32 rounded-xl border-slate-300">
            <div class="text-xs text-slate-500 mt-1">Sin descuento</div>
        </td>

        <td class="px-4 py-3">
            <input type="number" step="0.01" min="0" max="100" value="0" name="items[${idx}][descuento_pct]"
                   class="desc w-24 rounded-xl border-slate-300">
        </td>

        <td class="px-4 py-3 font-semibold sub">$0,00</td>

        <td class="px-4 py-3 text-right">
            <button type="button" class="fn-icon-action fn-icon-action-danger">🗑️</button>
        </td>
    `;

    const provText = tr.querySelector('.prov-text');
    const provId = tr.querySelector('.prov-id');

    const prodText = tr.querySelector('.prod-text');
    const prodId = tr.querySelector('.prod-id');
    const dlProd = tr.querySelector(`#dl_productos_${idx}`);

    const precioInput = tr.querySelector('.precio');
    const cantInput = tr.querySelector('.cant');
    const descInput = tr.querySelector('.desc');

    function cargarProductosDelProveedor(limpiarProducto = true){
        const ptxt = (provText.value || '').trim();
        const pid = PROV_MAP[ptxt] ? Number(PROV_MAP[ptxt]) : 0;
        provId.value = pid ? String(pid) : '';

        dlProd.innerHTML = '';

        if (limpiarProducto) {
            prodText.value = '';
            prodId.value = '';
        }

        if(pid && PRODS_BY_PROV[pid]){
            dlProd.innerHTML = PRODS_BY_PROV[pid].map(x => `<option value="${x.label}"></option>`).join('');
            tr.dataset.prodMap = JSON.stringify(Object.fromEntries(PRODS_BY_PROV[pid].map(x => [x.label, x.id])));
        } else {
            tr.dataset.prodMap = JSON.stringify({});
        }

        recalcular();
    }

    function setProductoId(){
        const map = JSON.parse(tr.dataset.prodMap || '{}');
        const v = (prodText.value || '').trim();
        prodId.value = map[v] ? String(map[v]) : '';
        recalcular();
    }

    provText.addEventListener('change', () => cargarProductosDelProveedor(true));
    provText.addEventListener('blur', () => cargarProductosDelProveedor(true));

    prodText.addEventListener('change', setProductoId);
    prodText.addEventListener('blur', setProductoId);

    cantInput.addEventListener('input', recalcular);
    precioInput.addEventListener('input', recalcular);
    descInput.addEventListener('input', recalcular);

    tr.querySelector('button').addEventListener('click', () => {
        tr.remove();
        reindexarFilasCompra();

        if(!tbody.children.length){
            addRow();
            return;
        }

        recalcular();
    });

    tbody.appendChild(tr);

    cargarProductosDelProveedor(false);

    if (productoPrefill) {
        prodText.value = productoPrefill.label;
        prodId.value = String(productoPrefill.id);
        precioInput.value = productoPrefill.ultimo_costo || 0;
    }

    recalcular();
}

function filaCompraRealmenteVacia(tr){
    const proveedorId = (tr.querySelector('.prov-id')?.value || '').trim();
    const proveedorTexto = (tr.querySelector('.prov-text')?.value || '').trim();
    const productoId = (tr.querySelector('.prod-id')?.value || '').trim();
    const productoTexto = (tr.querySelector('.prod-text')?.value || '').trim();
    const precio = Number(tr.querySelector('.precio')?.value || 0);
    const descuento = Number(tr.querySelector('.desc')?.value || 0);

    return proveedorId === ''
        && proveedorTexto === ''
        && productoId === ''
        && productoTexto === ''
        && precio === 0
        && descuento === 0;
}

function cargarProductoCompraEnFila(tr, producto, cantidad = 1){
    const proveedorId = Number(producto.proveedor_id || 0);
    const productosProveedor = PRODS_BY_PROV[proveedorId] || [];

    tr.querySelector('.prov-text').value = producto.proveedor_nombre || '';
    tr.querySelector('.prov-id').value = proveedorId ? String(proveedorId) : '';
    tr.querySelector('.prod-text').value = producto.label;
    tr.querySelector('.prod-id').value = String(producto.id);
    tr.querySelector('.cant').value = String(cantidad);
    tr.querySelector('.precio').value = String(producto.ultimo_costo || 0);
    tr.querySelector('datalist[id^="dl_productos_"]').innerHTML = productosProveedor
        .map(item => `<option value="${item.label}"></option>`)
        .join('');
    tr.dataset.prodMap = JSON.stringify(
        Object.fromEntries(productosProveedor.map(item => [item.label, item.id]))
    );
}

function reindexarFilasCompra(){
    document.querySelectorAll('#tablaItems tbody tr').forEach((tr, idx) => {
        const proveedorTexto = tr.querySelector('.prov-text');
        const productoTexto = tr.querySelector('.prod-text');
        const datalists = tr.querySelectorAll('datalist');

        proveedorTexto.setAttribute('list', `dl_proveedores_${idx}`);
        productoTexto.setAttribute('list', `dl_productos_${idx}`);
        datalists[0].id = `dl_proveedores_${idx}`;
        datalists[1].id = `dl_productos_${idx}`;
        tr.querySelector('.prov-id').name = `items[${idx}][proveedor_id]`;
        tr.querySelector('.prod-id').name = `items[${idx}][producto_id]`;
        tr.querySelector('.cant').name = `items[${idx}][cantidad]`;
        tr.querySelector('.precio').name = `items[${idx}][precio_unitario]`;
        tr.querySelector('.desc').name = `items[${idx}][descuento_pct]`;
    });
}

function limpiarFilasCompraVacias(){
    document.querySelectorAll('#tablaItems tbody tr').forEach(tr => {
        if(filaCompraRealmenteVacia(tr)){
            tr.remove();
        }
    });

    reindexarFilasCompra();
}

function agregarProductoPorCodigo(codigo){
    const codigoLimpio = String(codigo || '').trim();

    if (!codigoLimpio) return;

    const productoId = PRODUCTOS_BARCODE_MAP[codigoLimpio] ? Number(PRODUCTOS_BARCODE_MAP[codigoLimpio]) : 0;

    if (!productoId || !PRODUCTOS_DATA[productoId]) {
        alert('No existe un producto con ese código de barras.');
        return;
    }

    const producto = PRODUCTOS_DATA[productoId];

    const filas = Array.from(document.querySelectorAll('#tablaItems tbody tr'));

    const filaExistente = filas.find(tr => {
        const hid = tr.querySelector('.prod-id');
        return hid && Number(hid.value || 0) === productoId;
    });

    if (filaExistente) {
        const qtyInput = filaExistente.querySelector('.cant');
        const actual = Number(qtyInput.value || 0);
        qtyInput.value = String(actual + 1);

        recalcular();
        mostrarToastProductoAgregado(producto.label + ' agregado');
        return;
    }

    const filaVacia = filas.find(filaCompraRealmenteVacia);

    if(filaVacia){
        cargarProductoCompraEnFila(filaVacia, producto, 1);
    } else {
        addRow(productoId, 1);
    }

    limpiarFilasCompraVacias();
    recalcular();
    mostrarToastProductoAgregado(producto.label + ' agregado');
}

function recalcular(){
    let total = 0;

    document.querySelectorAll('#tablaItems tbody tr').forEach(tr => {
        const cant = Number(tr.querySelector('.cant')?.value || 0);
        const precio = Number(tr.querySelector('.precio')?.value || 0);
        let desc = Number(tr.querySelector('.desc')?.value || 0);

        if (desc < 0) {
            desc = 0;
        }

        if (desc > 100) {
            desc = 100;
        }

        const precioConDescuento = precio * (1 - (desc / 100));
        const sub = cant * precioConDescuento;

        tr.querySelector('.sub').textContent = money(sub);
        total += sub;
    });

    document.getElementById('totalCompra').textContent = money(total);
}

document.addEventListener('DOMContentLoaded', function(){
    const addRowBtn = document.getElementById('addRow');
    const scanProducto = document.getElementById('scan_producto_compra');

    if (addRowBtn) {
        addRowBtn.addEventListener('click', () => addRow());
    }

    if (scanProducto) {
        scanProducto.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                const codigo = this.value;

                if (codigo.trim() !== '') {
                    agregarProductoPorCodigo(codigo);
                    this.value = '';
                }
            }
        });

        scanProducto.addEventListener('change', function() {
            const codigo = this.value;

            if (codigo.trim() !== '') {
                agregarProductoPorCodigo(codigo);
                this.value = '';
            }
        });
    }

    addRow();
});
</script>

@endsection
