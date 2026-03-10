@csrf

@php
    // mapa proveedor -> id para datalist
    $proveedoresMap = $proveedores->pluck('id','nombre');
    $proveedorTexto = '';
    if (old('proveedor_buscar')) {
        $proveedorTexto = old('proveedor_buscar');
    } elseif (isset($producto) && $producto->proveedor) {
        $proveedorTexto = $producto->proveedor->nombre;
    }
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <div class="md:col-span-2">
        <label class="text-sm font-semibold text-slate-700">Proveedor *</label>

        <input id="proveedor_buscar"
               list="datalist_proveedores"
               placeholder="Escribí para buscar..."
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
               value="{{ $proveedorTexto }}">

        <datalist id="datalist_proveedores">
            @foreach($proveedores as $pr)
                <option value="{{ $pr->nombre }}"></option>
            @endforeach
        </datalist>

        <input type="hidden" name="proveedor_id" id="proveedor_id"
               value="{{ old('proveedor_id', $producto->proveedor_id ?? '') }}">

        @error('proveedor_id') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Marca *</label>
        <input name="marca"
               value="{{ old('marca', $producto->marca ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('marca') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Tipo *</label>
        <input name="tipo"
               value="{{ old('tipo', $producto->tipo ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('tipo') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Contenido *</label>
        <input name="contenido"
               value="{{ old('contenido', $producto->contenido ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
               placeholder="Ej: 1L, 250ml">
        @error('contenido') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Código de barras</label>
        <input name="codigo_barra"
               value="{{ old('codigo_barra', $producto->codigo_barra ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('codigo_barra') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Precio efectivo (manual) *</label>
        <input type="number" step="0.01" min="0" name="precio_venta"
               value="{{ old('precio_venta', $producto->precio_venta ?? 0) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('precio_venta') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Stock ventas * (Cantidad de productos en stock)</label>
        <input type="number" min="0" name="stock_venta"
               value="{{ old('stock_venta', $producto->stock_venta ?? 0) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('stock_venta') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Stock mínimo *</label>
        <input type="number" min="0" name="stock_minimo"
               value="{{ old('stock_minimo', $producto->stock_minimo ?? 0) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('stock_minimo') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

</div>

<div class="mt-6 flex gap-2">
    <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
        Guardar
    </button>

    <a href="{{ route('productos.index') }}" class="rounded-xl border px-4 py-2 hover:bg-slate-50">
        Cancelar
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const map = @json($proveedoresMap);
    const inp = document.getElementById('proveedor_buscar');
    const hid = document.getElementById('proveedor_id');

    function setId(){
        const v = (inp.value || '').trim();
        hid.value = map[v] ? String(map[v]) : '';
    }

    inp.addEventListener('change', setId);
    inp.addEventListener('blur', setId);
    setId();
});
</script>