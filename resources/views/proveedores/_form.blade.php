@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="text-sm font-semibold text-slate-700">Nombre *</label>
        <input name="nombre" value="{{ old('nombre', $proveedor->nombre ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
        @error('nombre') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="fn-primary-action">
        Guardar
    </button>

    <a href="{{ route('proveedores.index') }}" class="fn-secondary-action">
        Cancelar
    </a>
</div>
