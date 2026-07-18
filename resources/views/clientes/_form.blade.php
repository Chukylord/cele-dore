@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="text-sm font-semibold text-slate-700">Nombre *</label>
        <input name="nombre"
               value="{{ old('nombre', $cliente->nombre ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('nombre')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Apellido *</label>
        <input name="apellido"
               value="{{ old('apellido', $cliente->apellido ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        @error('apellido')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">DNI</label>
        <input name="dni"
               inputmode="numeric"
               value="{{ old('dni', $cliente->dni ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
               placeholder="Ej: 34025037">
        <div class="text-xs text-slate-500 mt-1">
            Se usa para identificar a la clienta y copiarlo al facturador.
        </div>
        @error('dni')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="text-sm font-semibold text-slate-700">Teléfono *</label>
        <input name="telefono"
               value="{{ old('telefono', $cliente->telefono ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
               placeholder="Ej: 3402123456 o +54 9 3402 123456">
        @error('telefono')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="text-sm font-semibold text-slate-700">Observación</label>

        <textarea name="observacion"
                  rows="7"
                  class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500 font-mono text-sm leading-7"
                  placeholder="Ejemplo:
COLOR CRECIMIENTOS 5(20) 6.33(20) 6.12(20) 10VOL
COLOR LARGOS 6.33(50) 6.12(50) 10VOL
S.O.S COLOR 5(2) 6(2) 6.33(2) 6.12(2) 10VOL">{{ old('observacion', $cliente->observacion ?? '') }}</textarea>

        <div class="mt-2 rounded-xl border bg-slate-50 px-3 py-2 text-xs text-slate-600">
            Usá <strong>Enter</strong> para separar cada fórmula o nota en un renglón distinto.
        </div>

        @error('observacion')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
        Guardar
    </button>

    <a href="{{ route('clientes.index') }}"
       class="rounded-xl border px-4 py-2 hover:bg-slate-50">
        Cancelar
    </a>
</div>
