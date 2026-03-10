@extends('layouts.admin')

@section('title', 'Nueva Compra - Peluquería TOP')
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
    // Productos por proveedor para datalist dinámico
    $prodsByProv = [];
    foreach($productos as $p){
        $label = trim(($p->marca.' - '.$p->tipo.' '.$p->contenido));
        $prodsByProv[$p->proveedor_id][] = [
            'id' => $p->id,
            'label' => $label,
        ];
    }
@endphp

<form method="POST" action="{{ route('compras.store') }}" id="formCompra">
    @csrf

    <div class="rounded-2xl border bg-white p-4 mb-6">
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

    <div class="rounded-2xl border bg-white p-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-lg font-bold">Productos de la compra</div>
                <div class="text-sm text-slate-600">Podés mezclar proveedores en un mismo lote.</div>
            </div>
            <button type="button" id="addRow"
                    class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                + Agregar línea
            </button>
        </div>

        <div class="overflow-x-auto rounded-2xl border">
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
            <a href="{{ route('compras.index') }}" class="rounded-xl border px-4 py-2 hover:bg-slate-50">Cancelar</a>
            <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                Guardar compra
            </button>
        </div>
    </div>
</form>

<script>
const PROV_MAP = @json($provMap);
const PRODS_BY_PROV = @json($prodsByProv);

function money(n){
    n = Number(n || 0);
    return '$' + n.toLocaleString('es-AR', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function addRow(){
    const tbody = document.querySelector('#tablaItems tbody');
    const idx = tbody.children.length;

    // 👇 Tomar proveedor de la fila anterior (si existe)
    let prevProvText = '';
    let prevProvId = '';
    const lastRow = tbody.lastElementChild;
    if (lastRow) {
        prevProvText = lastRow.querySelector('.prov-text')?.value || '';
        prevProvId = lastRow.querySelector('.prov-id')?.value || '';
    }

    const tr = document.createElement('tr');
    tr.className = 'border-t';

    tr.innerHTML = `
        <td class="px-4 py-3">
            <input list="dl_proveedores_${idx}" class="prov-text w-full rounded-xl border-slate-300"
                   placeholder="Buscar proveedor..." value="${prevProvText.replaceAll('"','&quot;')}">
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
            <input type="number" min="1" value="1" name="items[${idx}][cantidad]"
                   class="cant w-24 rounded-xl border-slate-300">
        </td>

        <td class="px-4 py-3">
            <input type="number" step="0.01" min="0" value="0" name="items[${idx}][precio_unitario]"
                   class="precio w-32 rounded-xl border-slate-300">
        </td>

        <td class="px-4 py-3 font-semibold sub">$0,00</td>

        <td class="px-4 py-3 text-right">
            <button type="button" class="rounded-lg border px-3 py-1 hover:bg-white">🗑️</button>
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
        prodText.value = '';
        prodId.value = '';

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

    provText.addEventListener('change', cargarProductosDelProveedor);
    provText.addEventListener('blur', cargarProductosDelProveedor);

    prodText.addEventListener('change', setProductoId);
    prodText.addEventListener('blur', setProductoId);

    tr.querySelector('.cant').addEventListener('input', recalcular);
    tr.querySelector('.precio').addEventListener('input', recalcular);

    tr.querySelector('button').addEventListener('click', () => { tr.remove(); recalcular(); });

    tbody.appendChild(tr);

    // 👇 Si ya venía proveedor cargado, cargar productos automáticamente
    cargarProductosDelProveedor();
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

document.getElementById('addRow').addEventListener('click', addRow);

// inicial
addRow();
</script>

@endsection