@csrf

@if(session('cliente_duplicado'))
    @php
        $duplicado = session('cliente_duplicado');
        $esEdicion = isset($cliente);
    @endphp

    <div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 p-5">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xl">
                ⚠️
            </div>

            <div class="flex-1">
                <div class="text-lg font-bold text-amber-900">
                    Ya existe una clienta con este teléfono
                </div>

                <div class="mt-2 text-sm text-amber-800">
                    El número <strong>{{ old('telefono') }}</strong> ya está registrado a nombre de:
                </div>

                <div class="mt-3 rounded-xl border border-amber-200 bg-white p-4">
                    <div class="font-bold text-slate-900">
                        {{ $duplicado['apellido'] }} {{ $duplicado['nombre'] }}
                    </div>

                    <div class="mt-1 text-sm text-slate-600">
                        Teléfono: {{ $duplicado['telefono'] }}
                    </div>
                </div>

                <div class="mt-3 text-sm text-amber-800">
                    Puede ser una clienta duplicada o dos personas que comparten el mismo teléfono.
                    Revisá los datos antes de continuar.
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('clientes.show', $duplicado['id']) }}"
                       target="_blank"
                       rel="noopener"
                       class="fn-secondary-action">
                        👁️ Ver clienta existente
                    </a>

                    <button type="submit"
                            name="confirmar_telefono_duplicado"
                            value="1"
                            class="fn-primary-action">
                        {{ $esEdicion ? 'Guardar igualmente' : 'Crear igualmente' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

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
               maxlength="11"
               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
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

        <div class="mt-1 text-xs text-slate-500">
            Si el teléfono ya está registrado, el sistema te avisará antes de guardar.
        </div>
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

        <div class="fn-soft-panel mt-2 px-3 py-2 text-xs text-slate-600">
            Usá <strong>Enter</strong> para separar cada fórmula o nota en un renglón distinto.
        </div>

        @error('observacion')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-6 flex flex-wrap gap-2">
    @if(!session('cliente_duplicado'))
        <button type="submit" class="fn-primary-action">
            Guardar
        </button>
    @endif

    <a href="{{ route('clientes.index') }}"
       class="fn-secondary-action">
        Cancelar
    </a>
</div>
