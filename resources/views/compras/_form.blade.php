@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <div>
        <label class="text-sm font-semibold text-slate-700">Proveedor *</label>
        <select id="proveedor_id" name="proveedor_id" class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            <option value="">Seleccionar</option>
            @foreach($proveedores as $p)
                <option value="{{ $p->id }}"
                    {{ (string)old('proveedor_id', $compra->proveedor_id ?? '') === (string)$p->id ? 'selected' : '' }}>
                    {{ $p->nombre }}
                </option>
            @endforeach
        </select>
        @error('proveedor_id') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Producto *</label>
        <select id="producto_id" name="producto_id" class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            <option value="">Seleccionar</option>
            @foreach($productos as $pr)
                <option value="{{ $pr->id }}"
                        data-proveedor="{{ $pr->proveedor_id }}"
                    {{ (string)old('producto_id', $compra->producto_id ?? '') === (string)$pr->id ? 'selected' : '' }}>
                    {{ $pr->marca }} - {{ $pr->tipo }} {{ $pr->contenido }}
                </option>
            @endforeach
        </select>
        @error('producto_id') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Fecha *</label>
        <input type="date" name="fecha"
               value="{{ old('fecha', isset($compra) ? $compra->fecha : date('Y-m-d')) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
        @error('fecha') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Cantidad *</label>
        <input type="number" name="cantidad" min="1"
               value="{{ old('cantidad', $compra->cantidad ?? 1) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
        @error('cantidad') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-semibold text-slate-700">Costo unitario *</label>
        <input type="number" name="precio_unitario" step="0.01" min="0"
               value="{{ old('precio_unitario', $compra->precio_unitario ?? 0) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
        @error('precio_unitario') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

</div>

<div class="mt-6 flex gap-2">
    <button class="fn-primary-action">
        Guardar
    </button>

    <a href="{{ route('compras.index') }}" class="fn-secondary-action">
        Cancelar
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selProv = document.getElementById('proveedor_id');
    const selProd = document.getElementById('producto_id');

    if (!selProv || !selProd) return;

    // Guardamos todas las opciones originales (menos la primera "Seleccionar")
    const firstOption = selProd.querySelector('option[value=""]');
    const allOptions = Array.from(selProd.querySelectorAll('option')).filter(o => o.value !== '');

    function filtrarProductos() {
        const provId = selProv.value;
        const selectedValue = selProd.value;

        // Limpiar select
        selProd.innerHTML = '';
        if (firstOption) selProd.appendChild(firstOption.cloneNode(true));

        // Si no eligió proveedor, mostramos todos
        let opciones = allOptions;
        if (provId) {
            opciones = allOptions.filter(o => (o.dataset.proveedor || '') === provId);
        }

        // Agregar opciones filtradas
        opciones.forEach(o => selProd.appendChild(o.cloneNode(true)));

        // Mantener seleccionado si todavía existe
        const stillExists = Array.from(selProd.options).some(o => o.value === selectedValue);
        selProd.value = stillExists ? selectedValue : '';
    }

    selProv.addEventListener('change', filtrarProductos);

    // Ejecutar una vez al cargar (sirve para create y edit)
    filtrarProductos();
});
</script>
