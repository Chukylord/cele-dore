@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <div class="md:col-span-2">
        <label class="text-sm font-semibold text-slate-700">Colaboradora *</label>
        <select name="colaboradora_id"
                class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
            <option value="">Seleccionar</option>
            @foreach($colaboradoras as $c)
                <option value="{{ $c->id }}"
                    {{ (string)old('colaboradora_id', $fichada->colaboradora_id ?? '') === (string)$c->id ? 'selected' : '' }}>
                    {{ $c->apellido }} {{ $c->nombre }}
                </option>
            @endforeach
        </select>
        @error('colaboradora_id') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Fecha *</label>
        <input type="date" name="fecha"
               value="{{ old('fecha', isset($fichada) ? $fichada->fecha?->format('Y-m-d') : now()->format('Y-m-d')) }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('fecha') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="flex items-end">
        <label class="flex items-center gap-2 pb-2">
            <input type="checkbox" name="es_extra"
                   class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                   {{ old('es_extra', $fichada->es_extra ?? false) ? 'checked' : '' }}>
            <span class="text-sm font-semibold text-slate-700">Horas extras</span>
        </label>
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Hora inicio *</label>
        <input type="time" name="hora_inicio" step="900"
               value="{{ old('hora_inicio', isset($fichada) ? substr($fichada->hora_inicio,0,5) : '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        <div class="text-xs text-slate-500 mt-1">Solo cuartos: 00, 15, 30, 45</div>
        @error('hora_inicio') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Hora fin *</label>
        <input type="time" name="hora_fin" step="900"
               value="{{ old('hora_fin', isset($fichada) ? substr($fichada->hora_fin,0,5) : '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        <div class="text-xs text-slate-500 mt-1">Solo cuartos: 00, 15, 30, 45</div>
        @error('hora_fin') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

</div>

<div class="mt-6 flex gap-2">
    <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
        Guardar
    </button>

    <a href="{{ route('fichadas.index') }}" class="rounded-xl border px-4 py-2 hover:bg-slate-50">
        Cancelar
    </a>
</div>