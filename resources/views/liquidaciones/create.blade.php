@extends('layouts.admin')

@section('title', 'Nueva Liquidación - Vir Tisone Studio')
@section('h1', 'Nueva Liquidación')
@section('sub', 'Calcular y registrar pago de colaboradora.')

@section('content')

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            <div class="font-semibold mb-1">Hay errores:</div>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Selección de colaboradora para ver resumen --}}
    <form method="GET" action="{{ route('liquidaciones.create') }}" class="rounded-2xl border bg-white p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">Colaboradora</label>
                <select name="colaboradora_id"
                        class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    <option value="">Seleccionar</option>
                    @foreach($colaboradoras as $c)
                        <option value="{{ $c->id }}" {{ (string)$colaboradoraId === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->apellido }} {{ $c->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button class="w-full rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Ver resumen
                </button>
            </div>
        </div>
    </form>

    @if($colaboradoraId > 0 && $resumen)
        <form method="POST" action="{{ route('liquidaciones.store') }}">
            @csrf
            <input type="hidden" name="colaboradora_id" value="{{ $colaboradoraId }}">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                <div class="rounded-2xl border bg-white p-4">
                    <div class="text-sm text-slate-600">Horas normales</div>
                    <div class="text-2xl font-bold mt-1">{{ number_format($resumen['horas_normales'], 2, ',', '.') }} hs</div>
                </div>

                <div class="rounded-2xl border bg-white p-4">
                    <div class="text-sm text-slate-600">Horas extras</div>
                    <div class="text-2xl font-bold mt-1">{{ number_format($resumen['horas_extras'], 2, ',', '.') }} hs</div>
                </div>

                <div class="rounded-2xl border bg-white p-4">
                    <div class="text-sm text-slate-600">Comisión</div>
                    <div class="text-2xl font-bold mt-1">${{ number_format($resumen['monto_comision'], 2, ',', '.') }}</div>
                </div>

                <div class="rounded-2xl border bg-white p-4">
                    <div class="text-sm text-slate-600">Productos a costo</div>
                    <div class="text-2xl font-bold mt-1">${{ number_format($resumen['monto_productos_costo'], 2, ',', '.') }}</div>
                </div>
            </div>

            <div class="rounded-2xl border bg-white p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Fecha pago *</label>
                        <input type="date" name="fecha_pago"
                               value="{{ old('fecha_pago', now()->format('Y-m-d')) }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Valor hora normal *</label>
                        <input type="number" step="0.01" min="0" name="valor_hora" id="valor_hora"
                               value="{{ old('valor_hora', 0) }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                        <div class="text-xs text-slate-500 mt-1">Hora extra = doble.</div>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Observaciones</label>
                        <input type="text" name="observaciones"
                               value="{{ old('observaciones') }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-6">
                    <div class="rounded-2xl border bg-slate-50 p-4">
                        <div class="text-sm text-slate-600">Pago horas normales</div>
                        <div class="text-2xl font-bold mt-1" id="monto_normales">$0,00</div>
                    </div>

                    <div class="rounded-2xl border bg-slate-50 p-4">
                        <div class="text-sm text-slate-600">Pago horas extras</div>
                        <div class="text-2xl font-bold mt-1" id="monto_extras">$0,00</div>
                    </div>

                    <div class="rounded-2xl border bg-slate-50 p-4">
                        <div class="text-sm text-slate-600">+ Comisión / - Productos</div>
                        <div class="text-lg font-bold mt-1">
                            + ${{ number_format($resumen['monto_comision'], 2, ',', '.') }}
                            <br>
                            - ${{ number_format($resumen['monto_productos_costo'], 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="rounded-2xl border bg-slate-900 text-white p-4">
                        <div class="text-sm text-slate-200">Total a pagar</div>
                        <div class="text-3xl font-extrabold mt-1" id="monto_total">$0,00</div>
                    </div>
                </div>

                <div class="mt-6 flex gap-2 flex-wrap">
                    <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                        Registrar liquidación
                    </button>

                    <a href="{{ route('liquidaciones.index') }}"
                       class="rounded-xl border px-4 py-2 hover:bg-slate-50">
                        Cancelar
                    </a>

                    <a href="https://www.arca.gob.ar/" target="_blank"
                       class="rounded-xl bg-slate-700 text-white px-4 py-2 hover:bg-slate-600">
                        Facturar
                    </a>
                </div>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const valorHora = document.getElementById('valor_hora');
                const montoNormales = document.getElementById('monto_normales');
                const montoExtras = document.getElementById('monto_extras');
                const montoTotal = document.getElementById('monto_total');

                const horasNormales = {{ (float)$resumen['horas_normales'] }};
                const horasExtras = {{ (float)$resumen['horas_extras'] }};
                const comision = {{ (float)$resumen['monto_comision'] }};
                const productosCosto = {{ (float)$resumen['monto_productos_costo'] }};

                function round2(n){ return Math.round((Number(n)+Number.EPSILON)*100)/100; }
                function money(n){
                    n = round2(n);
                    return '$' + n.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }

                function recalcular() {
                    const vh = Number(valorHora.value || 0);

                    const norm = round2(horasNormales * vh);
                    const extra = round2(horasExtras * (vh * 2));
                    const total = round2(norm + extra + comision - productosCosto);

                    montoNormales.textContent = money(norm);
                    montoExtras.textContent = money(extra);
                    montoTotal.textContent = money(total);
                }

                valorHora.addEventListener('input', recalcular);
                recalcular();
            });
        </script>
    @endif

@endsection