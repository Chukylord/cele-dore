@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="text-sm font-semibold text-slate-700">Nombre *</label>
        <input name="nombre" value="{{ old('nombre', $colaboradora->nombre ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
        @error('nombre') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Apellido</label>
        <input name="apellido" value="{{ old('apellido', $colaboradora->apellido ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
        @error('apellido') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Teléfono</label>
        <input name="telefono" value="{{ old('telefono', $colaboradora->telefono ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
        @error('telefono') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">% Comisión</label>
        <input name="comision_pct" type="number" step="0.01" min="0" max="100"
               value="{{ old('comision_pct', $colaboradora->comision_pct ?? 10) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
        @error('comision_pct') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="md:col-span-2 flex items-center gap-2">
        <input id="activa" name="activa" type="checkbox"
               class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
               {{ old('activa', $colaboradora->activa ?? true) ? 'checked' : '' }}>
        <label for="activa" class="text-sm font-semibold text-slate-700">Activa</label>
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="fn-primary-action">
        Guardar
    </button>

    <a href="{{ route('colaboradoras.index') }}" class="fn-secondary-action">
        Cancelar
    </a>
</div>
