@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <div>
        <label class="text-sm font-semibold text-slate-700">Fecha *</label>
        <input type="date" name="fecha"
               value="{{ old('fecha', isset($gasto) ? $gasto->fecha?->format('Y-m-d') : now()->format('Y-m-d')) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('fecha') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Categoría *</label>
        <input name="categoria"
               value="{{ old('categoria', $gasto->categoria ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
               placeholder="Ej: Luz, Alquiler, Internet">
        @error('categoria') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-semibold text-slate-700">Descripción</label>
        <input name="descripcion"
               value="{{ old('descripcion', $gasto->descripcion ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
               placeholder="Ej: Factura de luz de mayo">
        @error('descripcion') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Monto *</label>
        <input type="number" step="0.01" min="0" name="monto"
               value="{{ old('monto', $gasto->monto ?? 0) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('monto') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

</div>

<div class="mt-6 flex gap-2">
    <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
        Guardar
    </button>

    <a href="{{ route('gastos.index') }}" class="rounded-xl border px-4 py-2 hover:bg-slate-50">
        Cancelar
    </a>
</div>