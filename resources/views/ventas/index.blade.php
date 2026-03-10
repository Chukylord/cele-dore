@extends('layouts.admin')

@section('title', 'Ventas - Peluquería TOP')
@section('h1', 'Ventas')
@section('sub', 'Ventas de servicios y productos.')

@section('content')

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <div class="flex flex-col gap-3 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-6 gap-3 w-full" method="GET" action="{{ route('ventas.index') }}">
            <div>
                <label class="text-sm font-semibold text-slate-700">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}"
                       class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Método</label>
                <select name="metodo_pago" class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    <option value="" {{ ($metodo ?? '') === '' ? 'selected' : '' }}>Todos</option>
                    <option value="efectivo" {{ ($metodo ?? '') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                    <option value="tarjeta" {{ ($metodo ?? '') === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Estado</label>
                <select name="estado" class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    <option value="" {{ ($estado ?? '') === '' ? 'selected' : '' }}>Todos</option>
                    <option value="pagado" {{ ($estado ?? '') === 'pagado' ? 'selected' : '' }}>Pagado</option>
                    <option value="pendiente" {{ ($estado ?? '') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Colaboradora</label>
                <select name="colaboradora_id" class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                    <option value="" {{ ($colaboradora_id ?? '') === '' ? 'selected' : '' }}>Todas</option>
                    @foreach($colaboradoras as $c)
                        <option value="{{ $c->id }}" {{ (string)($colaboradora_id ?? '') === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->apellido }} {{ $c->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button class="mt-6 w-full rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Filtrar
                </button>

                <a href="{{ route('ventas.index') }}"
                   class="mt-6 w-full text-center rounded-xl border px-4 py-2 hover:bg-slate-50">
                    Limpiar
                </a>
            </div>
        </form>

        <div class="flex justify-end">
            <a href="{{ route('ventas.create') }}"
               class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                + Nueva Venta
            </a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50 text-slate-700">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold">Fecha</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Cliente</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Vendedora</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Método</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Estado</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Fecha pago</th>
                <th class="text-left px-4 py-3 text-sm font-semibold">Total</th>
                <th class="text-right px-4 py-3 text-sm font-semibold">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($ventas as $v)
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-3">{{ $v->fecha?->format('d/m/Y H:i') }}</td>

                    <td class="px-4 py-3">
                        @if($v->cliente)
                            {{ $v->cliente->nombre }} {{ $v->cliente->apellido }}
                        @elseif($v->clienteColaboradora)
                            (Colab) {{ $v->clienteColaboradora->nombre }} {{ $v->clienteColaboradora->apellido }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        {{ $v->vendedora ? ($v->vendedora->nombre.' '.$v->vendedora->apellido) : '-' }}
                    </td>

                    <td class="px-4 py-3">{{ ucfirst($v->metodo_pago) }}</td>

                    <td class="px-4 py-3">
                        @if($v->pendiente_pago)
                            <span class="px-2 py-1 rounded-lg bg-yellow-50 text-yellow-800 border border-yellow-200 text-sm">
                                Pendiente
                            </span>
                        @else
                            <span class="px-2 py-1 rounded-lg bg-green-50 text-green-700 border border-green-200 text-sm">
                                Pagado
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        {{ $v->fecha_pago ? $v->fecha_pago->format('d/m/Y H:i') : '-' }}
                    </td>

                    <td class="px-4 py-3 font-semibold">
                        ${{ number_format((float)$v->total, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2 flex-wrap">
                            <a href="{{ route('ventas.show', $v) }}"
                               class="rounded-lg border px-3 py-1 hover:bg-white"
                               title="Ver">
                                🔎
                            </a>

                            @if($v->pendiente_pago)
                                <form method="POST" action="{{ route('ventas.marcarPagado', $v) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-lg border px-3 py-1 hover:bg-green-50"
                                            onclick="return confirm('¿Marcar esta venta como pagada?')"
                                            title="Marcar como pagado">
                                        ✅
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('ventas.destroy', $v) }}"
                                  onsubmit="return confirm('¿Eliminar venta?');">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border px-3 py-1 hover:bg-white" title="Eliminar">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                        No hay ventas.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ventas->links() }}
    </div>

@endsection