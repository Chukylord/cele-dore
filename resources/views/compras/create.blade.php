@extends('layouts.admin')

@section('title', 'Nueva Compra - Cele Dore Estilista')
@section('h1', 'Nueva Compra')
@section('sub', 'Cargar varias líneas en una sola compra (lote).')

@section('content')

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

    $prodsByProv = [];
    $productosData = [];
    $productosBarcodeMap = [];

    foreach ($productos as $p) {
        $label = trim(
            ($p->marca ?? '') .
            ' - ' .
            ($p->tipo ?? '') .
            ' ' .
            ($p->contenido ?? '')
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
            'ultimo_costo' => $p->ultimo_costo !== null
                ? (float) $p->ultimo_costo
                : 0,
            'codigo_barra' => $p->codigo_barra,
        ];

        if (!empty($p->codigo_barra)) {
            $productosBarcodeMap[(string) $p->codigo_barra] = $p->id;
        }
    }
@endphp

<form method="POST"
      action="{{ route('compras.store') }}"
      id="formCompra">

    @csrf

    <div class="mb-6 rounded-2xl border bg-white p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <div>
                <label class="text-sm font-semibold text-slate-700">
                    Fecha *
                </label>

                <input type="date"
                       name="fecha"
                       value="{{ old('fecha', now()->format('Y-m-d')) }}"
                       required
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div class="md:col-span-3">
                <label class="text-sm font-semibold text-slate-700">
                    Nota
                </label>

                <input name="nota"
                       value="{{ old('nota') }}"
                       placeholder="Ej: compra mayorista"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

        </div>
    </div>

    <div class="rounded-2xl border bg-white p-4">

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-lg font-bold">
                    Productos de la compra
                </div>

                <div class="text-sm text-slate-600">
                    El descuento afecta el total de la compra, pero el precio unitario queda sin descuento para calcular ventas.
                </div>
            </div>

            <button type="button"
                    id="addRow"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                + Agregar línea
            </button>
        </div>

        <div class="mb-4 rounded-2xl border bg-slate-50 p-4">
            <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-3">

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">
                        Escanear producto
                    </label>

                    <input id="scan_producto_compra"
                           type="text"
                           autocomplete="off"
                           placeholder="Hacé click acá y escaneá el código..."
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

                    <div class="mt-1 text-xs text-slate-500">
                        Si hay una línea vacía, el producto se carga ahí. Si ya está agregado, suma 1 a la cantidad.
                    </div>
                </div>

                <div class="text-sm text-slate-500">
                    El lector funciona como teclado y completa este campo automáticamente.
                </div>

            </div>
        </div>

        <div id="avisoLineaIncompleta"
             class="mb-4 hidden rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900">

            <div class="font-semibold">
                Hay una línea incompleta.
            </div>

            <div class="mt-1 text-sm" id="textoAvisoLineaIncompleta">
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
                        <th class="px-4 py-3 text-left text-sm font-semibold">Precio unitario</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">% Desc.</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Subtotal</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold">Quitar</th>
                    </tr>
                </thead>

                <tbody></tbody>

                <tfoot class="bg-slate-50">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-right font-semibold">
                            Total
                        </td>

                        <td class="px-4 py-3 font-bold" id="totalCompra">
                            $0,00
                        </td>

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
                Guardar compra
            </button>
        </div>

    </div>
</form>

<div id="toastProductoAgregado"
     class="fixed right-5 top-5 z-[9999] hidden rounded-2xl bg-green-600 px-4 py-3 text-white shadow-xl">
    Producto agregado
</div>

<script>
const PROV_MAP = @json($provMap);
const PRODS_BY_PROV = @json($prodsByProv);
const PRODUCTOS_DATA = @json($productosData);
const PRODUCTOS_BARCODE_MAP = @json($productosBarcodeMap);
const OLD_ITEMS = @json(old('items', []));

const PROV_BY_ID = Object.fromEntries(
    Object.entries(PROV_MAP).map(([nombre, id]) => [String(id), nombre])
);

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

function mostrarToastProductoAgregado(texto = 'Producto agregado'){
    const toast = document.getElementById('toastProductoAgregado');

    if(!toast){
        return;
    }

    toast.textContent = texto;
    toast.classList.remove('hidden');

    clearTimeout(window._toastProductoTimeout);

    window._toastProductoTimeout = setTimeout(() => {
        toast.classList.add('hidden');
    }, 1600);
}

function obtenerFilas(){
    return Array.from(
        document.querySelectorAll('#tablaItems tbody tr')
    );
}

function ocultarAvisoLineaIncompleta(){
    const aviso = document.getElementById('avisoLineaIncompleta');

    if(aviso){
        aviso.classList.add('hidden');
    }
}

function mostrarAvisoLineaIncompleta(mensaje){
    const aviso = document.getElementById('avisoLineaIncompleta');
    const texto = document.getElementById('textoAvisoLineaIncompleta');

    if(texto){
        texto.textContent = mensaje;
    }

    if(aviso){
        aviso.classList.remove('hidden');
        aviso.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
}

function limpiarMarcaDeErrores(){
    obtenerFilas().forEach(tr => {
        tr.classList.remove(
            'bg-amber-50',
            'outline',
            'outline-2',
            'outline-amber-300'
        );
    });
}

function marcarFilaIncompleta(tr, mensaje){
    limpiarMarcaDeErrores();

    tr.classList.add(
        'bg-amber-50',
        'outline',
        'outline-2',
        'outline-amber-300'
    );

    mostrarAvisoLineaIncompleta(mensaje);
}

function addRow(opciones = {}){
    const tbody = document.querySelector('#tablaItems tbody');
    const idx = NEXT_ITEM_INDEX++;

    const productoIdInicial = Number(opciones.producto_id || 0);
    const productoInicial = PRODUCTOS_DATA[productoIdInicial] || null;

    const ultimaFila = tbody.lastElementChild;

    let proveedorIdInicial = opciones.proveedor_id
        ? String(opciones.proveedor_id)
        : '';

    let proveedorTextoInicial = opciones.proveedor_texto || '';

    if(productoInicial){
        proveedorIdInicial = productoInicial.proveedor_id
            ? String(productoInicial.proveedor_id)
            : '';

        proveedorTextoInicial = productoInicial.proveedor_nombre || '';
    }else if(!proveedorIdInicial && !proveedorTextoInicial && ultimaFila){
        proveedorIdInicial =
            ultimaFila.querySelector('.prov-id')?.value || '';

        proveedorTextoInicial =
            ultimaFila.querySelector('.prov-text')?.value || '';
    }

    if(proveedorIdInicial && !proveedorTextoInicial){
        proveedorTextoInicial = PROV_BY_ID[proveedorIdInicial] || '';
    }

    const cantidadInicial = Number(opciones.cantidad || 1);
    const precioInicial = opciones.precio_unitario !== undefined
        ? Number(opciones.precio_unitario || 0)
        : 0;

    const descuentoInicial = Number(opciones.descuento_pct || 0);

    const tr = document.createElement('tr');
    tr.className = 'border-t transition';

    tr.innerHTML = `
        <td class="px-4 py-3 align-top">
            <input
                list="dl_proveedores_${idx}"
                name="items[${idx}][proveedor_texto]"
                class="prov-text w-full min-w-[180px] rounded-xl border-slate-300"
                placeholder="Buscar proveedor..."
                value="${escaparHtml(proveedorTextoInicial)}"
            >

            <datalist id="dl_proveedores_${idx}">
                ${Object.keys(PROV_MAP)
                    .map(nombre => `<option value="${escaparHtml(nombre)}"></option>`)
                    .join('')}
            </datalist>

            <input
                type="hidden"
                name="items[${idx}][proveedor_id]"
                class="prov-id"
                value="${escaparHtml(proveedorIdInicial)}"
            >
        </td>

        <td class="px-4 py-3 align-top">
            <input
                list="dl_productos_${idx}"
                name="items[${idx}][producto_texto]"
                class="prod-text w-full min-w-[220px] rounded-xl border-slate-300"
                placeholder="Buscar producto..."
                value="${escaparHtml(opciones.producto_texto || '')}"
            >

            <datalist id="dl_productos_${idx}"></datalist>

            <input
                type="hidden"
                name="items[${idx}][producto_id]"
                class="prod-id"
                value="${productoIdInicial || ''}"
            >
        </td>

        <td class="px-4 py-3 align-top">
            <input
                type="number"
                min="1"
                value="${cantidadInicial}"
                name="items[${idx}][cantidad]"
                class="cant w-24 rounded-xl border-slate-300"
            >
        </td>

        <td class="px-4 py-3 align-top">
            <input
                type="number"
                step="0.01"
                min="0"
                value="${precioInicial}"
                name="items[${idx}][precio_unitario]"
                class="precio w-32 rounded-xl border-slate-300"
            >

            <div class="mt-1 text-xs text-slate-500">
                Sin descuento
            </div>
        </td>

        <td class="px-4 py-3 align-top">
            <input
                type="number"
                step="0.01"
                min="0"
                max="100"
                value="${descuentoInicial}"
                name="items[${idx}][descuento_pct]"
                class="desc w-24 rounded-xl border-slate-300"
            >
        </td>

        <td class="sub whitespace-nowrap px-4 py-3 align-top font-semibold">
            $0,00
        </td>

        <td class="px-4 py-3 text-right align-top">
            <button type="button"
                    class="btn-quitar rounded-lg border px-3 py-1 hover:bg-slate-50"
                    title="Quitar línea">
                🗑️
            </button>
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
        const proveedorEscrito = (provText.value || '').trim();
        const proveedorSeleccionado = PROV_MAP[proveedorEscrito]
            ? Number(PROV_MAP[proveedorEscrito])
            : 0;

        provId.value = proveedorSeleccionado
            ? String(proveedorSeleccionado)
            : '';

        dlProd.innerHTML = '';

        if(limpiarProducto){
            prodText.value = '';
            prodId.value = '';
        }

        if(
            proveedorSeleccionado &&
            PRODS_BY_PROV[proveedorSeleccionado]
        ){
            const productosProveedor =
                PRODS_BY_PROV[proveedorSeleccionado];

            dlProd.innerHTML = productosProveedor
                .map(producto => `
                    <option value="${escaparHtml(producto.label)}"></option>
                `)
                .join('');

            tr.dataset.prodMap = JSON.stringify(
                Object.fromEntries(
                    productosProveedor.map(producto => [
                        producto.label,
                        producto.id
                    ])
                )
            );
        }else{
            tr.dataset.prodMap = JSON.stringify({});
        }
    }

    function sincronizarProducto(completarPrecio = false){
        const map = JSON.parse(tr.dataset.prodMap || '{}');
        const productoEscrito = (prodText.value || '').trim();
        const id = map[productoEscrito]
            ? Number(map[productoEscrito])
            : 0;

        prodId.value = id ? String(id) : '';

        if(
            completarPrecio &&
            id &&
            Number(precioInput.value || 0) === 0 &&
            PRODUCTOS_DATA[id]
        ){
            precioInput.value =
                Number(PRODUCTOS_DATA[id].ultimo_costo || 0);
        }

        recalcular();
    }

    function sincronizarIds(){
        cargarProductosDelProveedor(false);
        sincronizarProducto(false);
    }

    function completarConProducto(productoId, cantidad = 1){
        const producto = PRODUCTOS_DATA[productoId];

        if(!producto){
            return false;
        }

        provText.value = producto.proveedor_nombre || '';
        provId.value = producto.proveedor_id
            ? String(producto.proveedor_id)
            : '';

        cargarProductosDelProveedor(false);

        prodText.value = producto.label || '';
        prodId.value = String(producto.id);
        cantInput.value = String(Number(cantidad || 1));
        precioInput.value = Number(producto.ultimo_costo || 0);
        descInput.value = 0;

        ocultarAvisoLineaIncompleta();
        limpiarMarcaDeErrores();
        recalcular();

        return true;
    }

    function estaVaciaParaScanner(){
        const productoId = (prodId.value || '').trim();
        const productoTexto = (prodText.value || '').trim();
        const precio = Number(precioInput.value || 0);
        const descuento = Number(descInput.value || 0);

        return productoId === ''
            && productoTexto === ''
            && precio === 0
            && descuento === 0;
    }

    function estaCompletamenteVacia(){
        return estaVaciaParaScanner();
    }

    tr.completarConProducto = completarConProducto;
    tr.estaVaciaParaScanner = estaVaciaParaScanner;
    tr.estaCompletamenteVacia = estaCompletamenteVacia;
    tr.sincronizarIds = sincronizarIds;

    provText.addEventListener('change', function(){
        cargarProductosDelProveedor(true);
        ocultarAvisoLineaIncompleta();
        limpiarMarcaDeErrores();
        recalcular();
    });

    provText.addEventListener('blur', function(){
        cargarProductosDelProveedor(false);
    });

    prodText.addEventListener('change', function(){
        sincronizarProducto(true);
        ocultarAvisoLineaIncompleta();
        limpiarMarcaDeErrores();
    });

    prodText.addEventListener('blur', function(){
        sincronizarProducto(false);
    });

    cantInput.addEventListener('input', recalcular);
    precioInput.addEventListener('input', recalcular);
    descInput.addEventListener('input', recalcular);

    tr.querySelector('.btn-quitar').addEventListener('click', function(){
        tr.remove();
        ocultarAvisoLineaIncompleta();
        limpiarMarcaDeErrores();
        recalcular();

        if(obtenerFilas().length === 0){
            addRow();
        }
    });

    tbody.appendChild(tr);
    cargarProductosDelProveedor(false);

    if(productoInicial){
        completarConProducto(
            productoInicial.id,
            cantidadInicial
        );

        if(opciones.precio_unitario !== undefined){
            precioInput.value = Number(opciones.precio_unitario || 0);
        }

        descInput.value = descuentoInicial;
    }else{
        if(opciones.producto_texto){
            prodText.value = opciones.producto_texto;
        }

        if(productoIdInicial){
            prodId.value = String(productoIdInicial);
        }
    }

    recalcular();

    return tr;
}

function agregarProductoPorCodigo(codigo){
    const codigoLimpio = String(codigo || '').trim();

    if(!codigoLimpio){
        return;
    }

    const productoId = PRODUCTOS_BARCODE_MAP[codigoLimpio]
        ? Number(PRODUCTOS_BARCODE_MAP[codigoLimpio])
        : 0;

    if(!productoId || !PRODUCTOS_DATA[productoId]){
        alert('No existe un producto con ese código de barras.');
        return;
    }

    const producto = PRODUCTOS_DATA[productoId];
    const filas = obtenerFilas();

    const filaExistente = filas.find(tr => {
        const idActual = Number(
            tr.querySelector('.prod-id')?.value || 0
        );

        return idActual === productoId;
    });

    if(filaExistente){
        const qtyInput = filaExistente.querySelector('.cant');
        const cantidadActual = Number(qtyInput.value || 0);

        qtyInput.value = String(cantidadActual + 1);

        recalcular();
        mostrarToastProductoAgregado(producto.label + ' agregado');

        return;
    }

    const filaVacia = filas.find(tr =>
        typeof tr.estaVaciaParaScanner === 'function' &&
        tr.estaVaciaParaScanner()
    );

    if(
        filaVacia &&
        typeof filaVacia.completarConProducto === 'function'
    ){
        filaVacia.completarConProducto(productoId, 1);
    }else{
        addRow({
            producto_id: productoId,
            cantidad: 1
        });
    }

    mostrarToastProductoAgregado(producto.label + ' agregado');
}

function revisarFila(tr){
    if(typeof tr.sincronizarIds === 'function'){
        tr.sincronizarIds();
    }

    const proveedorId =
        (tr.querySelector('.prov-id')?.value || '').trim();

    const productoId =
        (tr.querySelector('.prod-id')?.value || '').trim();

    const productoTexto =
        (tr.querySelector('.prod-text')?.value || '').trim();

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
            mensaje: 'Completá un precio unitario válido.'
        };
    }

    return null;
}

function recalcular(){
    let total = 0;

    obtenerFilas().forEach(tr => {
        const cantidad = Number(
            tr.querySelector('.cant')?.value || 0
        );

        const precio = Number(
            tr.querySelector('.precio')?.value || 0
        );

        let descuento = Number(
            tr.querySelector('.desc')?.value || 0
        );

        if(descuento < 0){
            descuento = 0;
        }

        if(descuento > 100){
            descuento = 100;
        }

        const precioConDescuento =
            precio * (1 - (descuento / 100));

        const subtotal =
            cantidad * precioConDescuento;

        const subtotalCelda = tr.querySelector('.sub');

        if(subtotalCelda){
            subtotalCelda.textContent = money(subtotal);
        }

        total += subtotal;
    });

    document.getElementById('totalCompra').textContent =
        money(total);
}

document.addEventListener('DOMContentLoaded', function(){
    const addRowBtn = document.getElementById('addRow');
    const scanProducto = document.getElementById('scan_producto_compra');
    const formCompra = document.getElementById('formCompra');

    if(addRowBtn){
        addRowBtn.addEventListener('click', function(){
            ocultarAvisoLineaIncompleta();
            limpiarMarcaDeErrores();
            addRow();
        });
    }

    if(scanProducto){
        scanProducto.addEventListener('keydown', function(e){
            if(e.key !== 'Enter'){
                return;
            }

            e.preventDefault();

            const codigo = this.value;

            if(codigo.trim() !== ''){
                agregarProductoPorCodigo(codigo);
                this.value = '';
            }

            this.focus();
        });

        scanProducto.addEventListener('change', function(){
            const codigo = this.value;

            if(codigo.trim() !== ''){
                agregarProductoPorCodigo(codigo);
                this.value = '';
            }

            this.focus();
        });
    }

    if(formCompra){
        formCompra.addEventListener('submit', function(e){
            ocultarAvisoLineaIncompleta();
            limpiarMarcaDeErrores();

            obtenerFilas().forEach(tr => {
                if(
                    typeof tr.estaCompletamenteVacia === 'function' &&
                    tr.estaCompletamenteVacia()
                ){
                    tr.remove();
                }
            });

            const filasRestantes = obtenerFilas();

            if(filasRestantes.length === 0){
                e.preventDefault();

                alert('Agregá al menos un producto antes de guardar la compra.');
                addRow();

                return;
            }

            for(const tr of filasRestantes){
                const problema = revisarFila(tr);

                if(problema){
                    e.preventDefault();

                    marcarFilaIncompleta(tr, problema.mensaje);
                    alert(problema.mensaje);
                    problema.campo?.focus();

                    return;
                }
            }
        });
    }

    const itemsAnteriores = Array.isArray(OLD_ITEMS)
        ? OLD_ITEMS
        : Object.values(OLD_ITEMS || {});

    if(itemsAnteriores.length > 0){
        itemsAnteriores.forEach(item => {
            addRow({
                proveedor_id: item.proveedor_id || '',
                proveedor_texto: item.proveedor_texto || '',
                producto_id: item.producto_id || '',
                producto_texto: item.producto_texto || '',
                cantidad: item.cantidad || 1,
                precio_unitario: item.precio_unitario ?? 0,
                descuento_pct: item.descuento_pct ?? 0
            });
        });
    }else{
        addRow();
    }

    recalcular();
});
</script>

@endsection
