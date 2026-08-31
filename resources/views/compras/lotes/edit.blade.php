@extends('layouts.admin')

@section('title', 'Editar lote - FN Peluquería')
@section('h1', 'Editar Lote')
@section('sub', 'Editar compra múltiple y actualizar stock.')

@section('content')

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-900">
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
    $provMap = $proveedores->pluck('id','nombre');
    $provNameById = $proveedores->pluck('nombre','id');

    // Productos por proveedor para datalist dinámico
    $prodsByProv = [];
    foreach($productos as $p){
        $label = trim(($p->marca.' - '.$p->tipo.' '.$p->contenido));
        $prodsByProv[$p->proveedor_id][] = [
            'id' => $p->id,
            'label' => $label,
        ];
    }

    $itemsIniciales = [];
    foreach($lote->compras as $c){
        $itemsIniciales[] = [
            'proveedor_id' => $c->proveedor_id,
            'proveedor_nombre' => $c->proveedor?->nombre ?? '',
            'producto_id' => $c->producto_id,
            'producto_label' => trim(($c->producto?->marca.' - '.$c->producto?->tipo.' '.$c->producto?->contenido)),
            'cantidad' => $c->cantidad,
            'precio_unitario' => $c->precio_unitario,
        ];
    }
@endphp

<form method="POST" action="{{ route('compras.lotes.update', $lote) }}" id="formCompra">
    @csrf
    @method('PUT')

    <div class="fn-section-card mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="text-sm font-semibold text-slate-700">Fecha *</label>
                <input type="date" name="fecha"
                    value="{{ old('fecha', \Carbon\Carbon::parse($lote->fecha)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Entrega inicial</label>
                <input type="number"
                    step="0.01"
                    min="0"
                    name="entrega_inicial"
                    value="{{ old('entrega_inicial', $entregaInicial ?? 0) }}"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <p class="mt-1 text-sm text-slate-500">
                    Corresponde al pago inicial del lote.
                </p>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Nota</label>
                <input name="nota"
                    value="{{ old('nota', $lote->nota) }}"
                    placeholder="Ej: compra mayorista"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>
        </div>
    </div>

    <div class="fn-section-card">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-lg font-bold">Items del lote #{{ $lote->id }}</div>
                <div class="text-sm text-slate-600">Podés cambiar cantidades, precios, productos y proveedores.</div>
            </div>
            <button type="button" id="addRow"
                    class="fn-primary-action">
                + Agregar línea
            </button>
        </div>

        <div class="fn-table-shell overflow-x-auto">
            <table class="min-w-full bg-white" id="tablaItems">
                <thead class="bg-slate-50 text-slate-700">
                <tr>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Proveedor</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Producto</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Cantidad</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Precio unitario</th>
                    <th class="text-left px-4 py-3 text-sm font-semibold">Subtotal</th>
                    <th class="text-right px-4 py-3 text-sm font-semibold">Quitar</th>
                </tr>
                </thead>
                <tbody></tbody>
                <tfoot class="bg-slate-50">
                <tr>
                    <td colspan="4" class="px-4 py-3 text-right font-semibold">Total</td>
                    <td class="px-4 py-3 font-bold" id="totalCompra">$0,00</td>
                    <td></td>
                </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-6 flex gap-2 justify-end">
            <a href="{{ route('compras.index') }}" class="fn-secondary-action">Cancelar</a>
            <button class="fn-primary-action">
                Guardar cambios
            </button>
        </div>
    </div>
</form>

<script>
const PROV_MAP = @json($provMap);
const PRODS_BY_PROV = @json($prodsByProv);
const ITEMS_INICIALES = @json($itemsIniciales);

function money(n){
    n = Number(n || 0);
    return '$' + n.toLocaleString('es-AR', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function recalcular(){
    let total = 0;
    document.querySelectorAll('#tablaItems tbody tr').forEach(tr => {
        const cant = Number(tr.querySelector('.cant')?.value || 0);
        const precio = Number(tr.querySelector('.precio')?.value || 0);
        const sub = cant * precio;
        tr.querySelector('.sub').textContent = money(sub);
        total += sub;
    });
    document.getElementById('totalCompra').textContent = money(total);
}

function addRow(prefill = null){
    const tbody = document.querySelector('#tablaItems tbody');
    const idx = tbody.children.length;

    // heredar proveedor de fila anterior si no hay prefill
    let prevProvText = '';
    let prevProvId = '';
    const lastRow = tbody.lastElementChild;
    if (lastRow && !prefill) {
        prevProvText = lastRow.querySelector('.prov-text')?.value || '';
        prevProvId = lastRow.querySelector('.prov-id')?.value || '';
    }

    const provTextValue = prefill ? (prefill.proveedor_nombre || '') : prevProvText;
    const provIdValue   = prefill ? (prefill.proveedor_id || '') : prevProvId;

    const prodTextValue = prefill ? (prefill.producto_label || '') : '';
    const prodIdValue   = prefill ? (prefill.producto_id || '') : '';

    const cantValue     = prefill ? (prefill.cantidad || 1) : 1;
    const precioValue   = prefill ? (prefill.precio_unitario || 0) : 0;

    const tr = document.createElement('tr');
    tr.className = 'border-t';

    tr.innerHTML = `
        <td class="px-4 py-3">
            <input list="dl_proveedores_${idx}" class="prov-text w-full rounded-xl border-slate-300"
                   placeholder="Buscar proveedor..." value="${String(provTextValue).replaceAll('"','&quot;')}">
            <datalist id="dl_proveedores_${idx}">
                ${Object.keys(PROV_MAP).map(n => `<option value="${n}"></option>`).join('')}
            </datalist>
            <input type="hidden" name="items[${idx}][proveedor_id]" class="prov-id" value="${provIdValue}">
        </td>

        <td class="px-4 py-3">
            <input list="dl_productos_${idx}" class="prod-text w-full rounded-xl border-slate-300"
                   placeholder="Buscar producto..." value="${String(prodTextValue).replaceAll('"','&quot;')}">
            <datalist id="dl_productos_${idx}"></datalist>
            <input type="hidden" name="items[${idx}][producto_id]" class="prod-id" value="${prodIdValue}">
        </td>

        <td class="px-4 py-3">
            <input type="number" min="1" value="${cantValue}" name="items[${idx}][cantidad]"
                   class="cant w-24 rounded-xl border-slate-300">
        </td>

        <td class="px-4 py-3">
            <input type="number" step="0.01" min="0" value="${precioValue}" name="items[${idx}][precio_unitario]"
                   class="precio w-32 rounded-xl border-slate-300">
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

    function cargarProductosDelProveedor(){
        const ptxt = (provText.value || '').trim();
        const pid = PROV_MAP[ptxt] ? Number(PROV_MAP[ptxt]) : 0;
        provId.value = pid ? String(pid) : '';

        dlProd.innerHTML = '';
        // si cambio proveedor, reseteo producto
        if(!prefill){
            prodText.value = '';
            prodId.value = '';
        }

        if(pid && PRODS_BY_PROV[pid]){
            dlProd.innerHTML = PRODS_BY_PROV[pid].map(x => `<option value="${x.label}"></option>`).join('');
            tr.dataset.prodMap = JSON.stringify(Object.fromEntries(PRODS_BY_PROV[pid].map(x => [x.label, x.id])));
        } else {
            tr.dataset.prodMap = JSON.stringify({});
        }

        // si hay prefill producto, setear id
        if(prefill){
            prodId.value = String(prefill.producto_id || '');
        }

        recalcular();
    }

    function setProductoId(){
        const map = JSON.parse(tr.dataset.prodMap || '{}');
        const v = (prodText.value || '').trim();
        prodId.value = map[v] ? String(map[v]) : '';
        recalcular();
    }

    provText.addEventListener('change', () => { prefill = null; cargarProductosDelProveedor(); });
    provText.addEventListener('blur', () => { prefill = null; cargarProductosDelProveedor(); });

    prodText.addEventListener('change', setProductoId);
    prodText.addEventListener('blur', setProductoId);

    tr.querySelector('.cant').addEventListener('input', recalcular);
    tr.querySelector('.precio').addEventListener('input', recalcular);

    tr.querySelector('button').addEventListener('click', () => { tr.remove(); recalcular(); });

    tbody.appendChild(tr);
    cargarProductosDelProveedor();
    recalcular();
}

document.getElementById('addRow').addEventListener('click', () => addRow());

// cargar inicial
if(ITEMS_INICIALES.length){
    ITEMS_INICIALES.forEach(it => addRow(it));
} else {
    addRow();
}
</script>

@endsection
