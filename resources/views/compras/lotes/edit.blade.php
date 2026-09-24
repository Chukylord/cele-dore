@extends('layouts.admin')

@section('title', 'Editar Lote - Cele Dore Estilista')
@section('h1', 'Editar Lote')
@section('sub', 'Modificar la compra y corregir el stock automáticamente.')

@section('content')

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-900">
        {{ session('ok') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="mb-1 font-semibold">Hay errores:</div>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $provMap = $proveedores->pluck('id', 'nombre');
    $provNameById = $proveedores->pluck('nombre', 'id');

    $prodsByProv = [];
    $productosData = [];

    foreach ($productos as $p) {
        $label = trim(
            ($p->marca ?? '')
            . ' - '
            . ($p->tipo ?? '')
            . ' '
            . ($p->contenido ?? '')
        );

        $prodsByProv[$p->proveedor_id][] = [
            'id' => $p->id,
            'label' => $label,
        ];

        $productosData[$p->id] = [
            'id' => $p->id,
            'label' => $label,
            'proveedor_id' => $p->proveedor_id,
            'proveedor_nombre' => optional($p->proveedor)->nombre,
        ];
    }

    $itemsIniciales = [];

    foreach ($lote->compras as $c) {
        $itemsIniciales[] = [
            'proveedor_id' => $c->proveedor_id,
            'proveedor_texto' => $c->proveedor?->nombre ?? '',
            'producto_id' => $c->producto_id,
            'producto_texto' => trim(
                ($c->producto?->marca ?? '')
                . ' - '
                . ($c->producto?->tipo ?? '')
                . ' '
                . ($c->producto?->contenido ?? '')
            ),
            'cantidad' => $c->cantidad,
            'precio_unitario' => $c->precio_unitario,
            'descuento_pct' => $c->descuento_pct ?? 0,
        ];
    }

    $itemsVista = old('items');

    if (!is_array($itemsVista) || count($itemsVista) === 0) {
        $itemsVista = $itemsIniciales;
    }
@endphp

<form method="POST"
      action="{{ route('compras.lotes.update', $lote) }}"
      id="formCompra">

    @csrf
    @method('PUT')

    <div class="mb-6 rounded-2xl border bg-white p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <div>
                <label class="text-sm font-semibold text-slate-700">Fecha *</label>

                <input type="date"
                       name="fecha"
                       value="{{ old('fecha', \Carbon\Carbon::parse($lote->fecha)->format('Y-m-d')) }}"
                       required
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div class="md:col-span-3">
                <label class="text-sm font-semibold text-slate-700">Nota</label>

                <input name="nota"
                       value="{{ old('nota', $lote->nota) }}"
                       placeholder="Ej: compra mayorista"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

        </div>
    </div>

    <div class="rounded-2xl border bg-white p-4">

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-lg font-bold">Productos del lote #{{ $lote->id }}</div>
                <div class="text-sm text-slate-600">
                    Podés cambiar proveedores, productos, cantidades, costos y descuentos.
                </div>
            </div>

            <button type="button"
                    id="addRow"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                + Agregar línea
            </button>
        </div>

        <div id="avisoLineaIncompleta"
             class="mb-4 hidden rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900">
            <div class="font-semibold">Hay una línea incompleta.</div>
            <div id="textoAvisoLineaIncompleta" class="mt-1 text-sm">
                Completá los datos de esa línea o eliminála antes de guardar.
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border">
            <table class="min-w-full bg-white" id="tablaItems">
                <thead class="bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Proveedor</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Producto</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Cantidad</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Costo unitario</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">% Desc.</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Subtotal</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold">Quitar</th>
                    </tr>
                </thead>

                <tbody></tbody>

                <tfoot class="bg-slate-50">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-right font-semibold">Total</td>
                        <td id="totalCompra" class="px-4 py-3 font-bold">$0,00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <a href="{{ route('compras.index') }}"
               class="rounded-xl border px-4 py-2 hover:bg-slate-50">
                Cancelar
            </a>

            <button type="submit"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                Guardar cambios
            </button>
        </div>

    </div>
</form>

<script>
const PROV_MAP = @json($provMap);
const PROV_NAME_BY_ID = @json($provNameById);
const PRODS_BY_PROV = @json($prodsByProv);
const PRODUCTOS_DATA = @json($productosData);
const ITEMS_VISTA = @json(array_values($itemsVista));

let NEXT_ITEM_INDEX = 0;

function money(n){
    n = Number(n || 0);

    return '$' + n.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function escaparHtml(valor){
    return String(valor ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function obtenerFilas(){
    return Array.from(document.querySelectorAll('#tablaItems tbody tr'));
}

function ocultarAviso(){
    document.getElementById('avisoLineaIncompleta')?.classList.add('hidden');
}

function limpiarMarcas(){
    obtenerFilas().forEach(tr => {
        tr.classList.remove('bg-amber-50', 'outline', 'outline-2', 'outline-amber-300');
    });
}

function marcarProblema(tr, mensaje){
    limpiarMarcas();

    tr.classList.add('bg-amber-50', 'outline', 'outline-2', 'outline-amber-300');

    const aviso = document.getElementById('avisoLineaIncompleta');
    const texto = document.getElementById('textoAvisoLineaIncompleta');

    if(texto){
        texto.textContent = mensaje;
    }

    if(aviso){
        aviso.classList.remove('hidden');
        aviso.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function recalcular(){
    let total = 0;

    obtenerFilas().forEach(tr => {
        const cantidad = Number(tr.querySelector('.cant')?.value || 0);
        const precio = Number(tr.querySelector('.precio')?.value || 0);
        let descuento = Number(tr.querySelector('.desc')?.value || 0);

        descuento = Math.min(100, Math.max(0, descuento));

        const precioConDescuento = precio * (1 - (descuento / 100));
        const subtotal = cantidad * precioConDescuento;

        const celdaSubtotal = tr.querySelector('.sub');
        if(celdaSubtotal){
            celdaSubtotal.textContent = money(subtotal);
        }

        total += subtotal;
    });

    document.getElementById('totalCompra').textContent = money(total);
}

function addRow(prefill = {}){
    const tbody = document.querySelector('#tablaItems tbody');
    const idx = NEXT_ITEM_INDEX++;
    const ultimaFila = tbody.lastElementChild;

    let proveedorId = String(prefill.proveedor_id || '');
    let proveedorTexto = prefill.proveedor_texto || '';

    if(!proveedorId && !proveedorTexto && ultimaFila){
        proveedorId = ultimaFila.querySelector('.prov-id')?.value || '';
        proveedorTexto = ultimaFila.querySelector('.prov-text')?.value || '';
    }

    if(proveedorId && !proveedorTexto){
        proveedorTexto = PROV_NAME_BY_ID[proveedorId] || '';
    }

    const productoId = Number(prefill.producto_id || 0);
    const productoTexto = prefill.producto_texto
        || PRODUCTOS_DATA[productoId]?.label
        || '';

    const cantidad = Number(prefill.cantidad || 1);
    const precio = prefill.precio_unitario !== undefined
        ? Number(prefill.precio_unitario || 0)
        : 0;
    const descuento = Number(prefill.descuento_pct || 0);

    const tr = document.createElement('tr');
    tr.className = 'border-t transition';

    tr.innerHTML = `
        <td class="px-4 py-3 align-top">
            <input list="dl_proveedores_${idx}"
                   name="items[${idx}][proveedor_texto]"
                   class="prov-text w-full min-w-[180px] rounded-xl border-slate-300"
                   placeholder="Buscar proveedor..."
                   value="${escaparHtml(proveedorTexto)}">

            <datalist id="dl_proveedores_${idx}">
                ${Object.keys(PROV_MAP)
                    .map(nombre => `<option value="${escaparHtml(nombre)}"></option>`)
                    .join('')}
            </datalist>

            <input type="hidden"
                   name="items[${idx}][proveedor_id]"
                   class="prov-id"
                   value="${escaparHtml(proveedorId)}">
        </td>

        <td class="px-4 py-3 align-top">
            <input list="dl_productos_${idx}"
                   name="items[${idx}][producto_texto]"
                   class="prod-text w-full min-w-[220px] rounded-xl border-slate-300"
                   placeholder="Buscar producto..."
                   value="${escaparHtml(productoTexto)}">

            <datalist id="dl_productos_${idx}"></datalist>

            <input type="hidden"
                   name="items[${idx}][producto_id]"
                   class="prod-id"
                   value="${productoId || ''}">
        </td>

        <td class="px-4 py-3 align-top">
            <input type="number"
                   min="1"
                   value="${cantidad}"
                   name="items[${idx}][cantidad]"
                   class="cant w-24 rounded-xl border-slate-300">
        </td>

        <td class="px-4 py-3 align-top">
            <input type="number"
                   step="0.01"
                   min="0"
                   value="${precio}"
                   name="items[${idx}][precio_unitario]"
                   class="precio w-32 rounded-xl border-slate-300">
            <div class="mt-1 text-xs text-slate-500">Sin descuento</div>
        </td>

        <td class="px-4 py-3 align-top">
            <input type="number"
                   step="0.01"
                   min="0"
                   max="100"
                   value="${descuento}"
                   name="items[${idx}][descuento_pct]"
                   class="desc w-24 rounded-xl border-slate-300">
        </td>

        <td class="sub whitespace-nowrap px-4 py-3 align-top font-semibold">$0,00</td>

        <td class="px-4 py-3 text-right align-top">
            <button type="button"
                    class="btn-quitar rounded-lg border px-3 py-1 hover:bg-slate-50"
                    title="Quitar línea">🗑️</button>
        </td>
    `;

    const provText = tr.querySelector('.prov-text');
    const provIdInput = tr.querySelector('.prov-id');
    const prodText = tr.querySelector('.prod-text');
    const prodIdInput = tr.querySelector('.prod-id');
    const dlProd = tr.querySelector(`#dl_productos_${idx}`);

    function cargarProductos(limpiarProducto = false){
        const nombreProveedor = (provText.value || '').trim();
        const idProveedor = PROV_MAP[nombreProveedor]
            ? Number(PROV_MAP[nombreProveedor])
            : 0;

        provIdInput.value = idProveedor ? String(idProveedor) : '';

        if(limpiarProducto){
            prodText.value = '';
            prodIdInput.value = '';
        }

        const productosProveedor = idProveedor && PRODS_BY_PROV[idProveedor]
            ? PRODS_BY_PROV[idProveedor]
            : [];

        dlProd.innerHTML = productosProveedor
            .map(producto => `<option value="${escaparHtml(producto.label)}"></option>`)
            .join('');

        tr.dataset.prodMap = JSON.stringify(
            Object.fromEntries(productosProveedor.map(producto => [producto.label, producto.id]))
        );
    }

    function sincronizarProducto(){
        const mapa = JSON.parse(tr.dataset.prodMap || '{}');
        const textoProducto = (prodText.value || '').trim();

        prodIdInput.value = mapa[textoProducto]
            ? String(mapa[textoProducto])
            : '';
    }

    function sincronizarIds(){
        cargarProductos(false);
        sincronizarProducto();
    }

    function estaCompletamenteVacia(){
        const idProducto = (prodIdInput.value || '').trim();
        const textoProducto = (prodText.value || '').trim();
        const valorPrecio = Number(tr.querySelector('.precio')?.value || 0);
        const valorDescuento = Number(tr.querySelector('.desc')?.value || 0);

        return idProducto === ''
            && textoProducto === ''
            && valorPrecio === 0
            && valorDescuento === 0;
    }

    tr.sincronizarIds = sincronizarIds;
    tr.estaCompletamenteVacia = estaCompletamenteVacia;

    provText.addEventListener('change', function(){
        cargarProductos(true);
        ocultarAviso();
        limpiarMarcas();
        recalcular();
    });

    provText.addEventListener('blur', function(){
        cargarProductos(false);
    });

    prodText.addEventListener('change', function(){
        sincronizarProducto();
        ocultarAviso();
        limpiarMarcas();
    });

    prodText.addEventListener('blur', sincronizarProducto);
    tr.querySelector('.cant').addEventListener('input', recalcular);
    tr.querySelector('.precio').addEventListener('input', recalcular);
    tr.querySelector('.desc').addEventListener('input', recalcular);

    tr.querySelector('.btn-quitar').addEventListener('click', function(){
        tr.remove();
        ocultarAviso();
        limpiarMarcas();
        recalcular();

        if(obtenerFilas().length === 0){
            addRow();
        }
    });

    tbody.appendChild(tr);
    cargarProductos(false);

    if(productoId){
        prodIdInput.value = String(productoId);
    }

    recalcular();
    return tr;
}

function revisarFila(tr){
    if(typeof tr.sincronizarIds === 'function'){
        tr.sincronizarIds();
    }

    const proveedorId = (tr.querySelector('.prov-id')?.value || '').trim();
    const productoId = (tr.querySelector('.prod-id')?.value || '').trim();
    const productoTexto = (tr.querySelector('.prod-text')?.value || '').trim();
    const cantidadInput = tr.querySelector('.cant');
    const precioInput = tr.querySelector('.precio');
    const cantidad = Number(cantidadInput?.value || 0);
    const precioTexto = precioInput?.value ?? '';
    const precio = Number(precioTexto || 0);

    if(!proveedorId){
        return {
            campo: tr.querySelector('.prov-text'),
            mensaje: 'Seleccioná un proveedor válido en la línea marcada.'
        };
    }

    if(!productoId){
        return {
            campo: tr.querySelector('.prod-text'),
            mensaje: productoTexto !== ''
                ? 'El producto escrito no coincide con un producto del proveedor. Seleccionalo de la lista.'
                : 'Seleccioná un producto en la línea marcada.'
        };
    }

    if(cantidad < 1){
        return {
            campo: cantidadInput,
            mensaje: 'La cantidad debe ser de al menos 1.'
        };
    }

    if(precioTexto === '' || precio < 0){
        return {
            campo: precioInput,
            mensaje: 'Completá un costo unitario válido.'
        };
    }

    return null;
}

document.addEventListener('DOMContentLoaded', function(){
    const addRowButton = document.getElementById('addRow');
    const form = document.getElementById('formCompra');

    addRowButton.addEventListener('click', function(){
        ocultarAviso();
        limpiarMarcas();
        addRow();
    });

    form.addEventListener('submit', function(event){
        ocultarAviso();
        limpiarMarcas();

        obtenerFilas().forEach(tr => {
            if(
                typeof tr.estaCompletamenteVacia === 'function'
                && tr.estaCompletamenteVacia()
            ){
                tr.remove();
            }
        });

        const filas = obtenerFilas();

        if(filas.length === 0){
            event.preventDefault();
            alert('Agregá al menos un producto antes de guardar el lote.');
            addRow();
            return;
        }

        for(const tr of filas){
            const problema = revisarFila(tr);

            if(problema){
                event.preventDefault();
                marcarProblema(tr, problema.mensaje);
                alert(problema.mensaje);
                problema.campo?.focus();
                return;
            }
        }
    });

    if(Array.isArray(ITEMS_VISTA) && ITEMS_VISTA.length > 0){
        ITEMS_VISTA.forEach(item => addRow(item));
    }else{
        addRow();
    }

    recalcular();
});
</script>

@endsection
