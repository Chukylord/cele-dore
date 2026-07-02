@csrf

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">

    <div>
        <label class="text-sm font-semibold text-slate-700">Proveedor *</label>

        <select id="proveedor_id"
                name="proveedor_id"
                required
                class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

            <option value="">Seleccionar</option>

            @foreach($proveedores as $p)
                <option value="{{ $p->id }}"
                    {{ (string) old('proveedor_id', $compra->proveedor_id ?? '') === (string) $p->id ? 'selected' : '' }}>
                    {{ $p->nombre }}
                </option>
            @endforeach
        </select>

        @error('proveedor_id')
            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Producto *</label>

        <select id="producto_id"
                name="producto_id"
                required
                class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

            <option value="">Seleccionar</option>

            @foreach($productos as $pr)
                <option value="{{ $pr->id }}"
                        data-proveedor="{{ $pr->proveedor_id }}"
                    {{ (string) old('producto_id', $compra->producto_id ?? '') === (string) $pr->id ? 'selected' : '' }}>
                    {{ $pr->marca }} - {{ $pr->tipo }} {{ $pr->contenido }}
                </option>
            @endforeach
        </select>

        @error('producto_id')
            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Fecha *</label>

        <input type="date"
               name="fecha"
               value="{{ old('fecha', isset($compra) ? \Carbon\Carbon::parse($compra->fecha)->format('Y-m-d') : now()->format('Y-m-d')) }}"
               required
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

        @error('fecha')
            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Cantidad *</label>

        <input type="number"
               name="cantidad"
               min="1"
               value="{{ old('cantidad', $compra->cantidad ?? 1) }}"
               required
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

        @error('cantidad')
            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Costo unitario *</label>

        <input type="number"
               name="precio_unitario"
               step="0.01"
               min="0"
               value="{{ old('precio_unitario', $compra->precio_unitario ?? 0) }}"
               required
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

        <div class="mt-1 text-xs text-slate-500">
            Ingresá el costo sin aplicar el descuento.
        </div>

        @error('precio_unitario')
            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Descuento %</label>

        <input type="number"
               name="descuento_pct"
               step="0.01"
               min="0"
               max="100"
               value="{{ old('descuento_pct', $compra->descuento_pct ?? 0) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

        <div class="mt-1 text-xs text-slate-500">
            Afecta el total de la compra, no el costo usado para calcular ventas.
        </div>

        @error('descuento_pct')
            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
        @enderror
    </div>

</div>

<div class="mt-6 flex gap-2">
    <button type="submit"
            class="rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
        Guardar
    </button>

    <a href="{{ route('compras.index') }}"
       class="rounded-xl border px-4 py-2 hover:bg-slate-50">
        Cancelar
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const proveedorSelect = document.getElementById('proveedor_id');
    const productoSelect = document.getElementById('producto_id');

    if(!proveedorSelect || !productoSelect){
        return;
    }

    const opcionInicial = productoSelect
        .querySelector('option[value=""]')
        ?.cloneNode(true);

    const opcionesOriginales = Array.from(
        productoSelect.querySelectorAll('option[data-proveedor]')
    ).map(opcion => opcion.cloneNode(true));

    function filtrarProductos(){
        const proveedorId = proveedorSelect.value;
        const productoSeleccionado = productoSelect.value;

        productoSelect.innerHTML = '';

        if(opcionInicial){
            productoSelect.appendChild(opcionInicial.cloneNode(true));
        }

        const opcionesFiltradas = proveedorId
            ? opcionesOriginales.filter(
                opcion => opcion.dataset.proveedor === proveedorId
            )
            : opcionesOriginales;

        opcionesFiltradas.forEach(opcion => {
            productoSelect.appendChild(opcion.cloneNode(true));
        });

        const sigueDisponible = Array.from(productoSelect.options)
            .some(opcion => opcion.value === productoSeleccionado);

        productoSelect.value = sigueDisponible
            ? productoSeleccionado
            : '';
    }

    proveedorSelect.addEventListener('change', filtrarProductos);
    filtrarProductos();
});
</script>
