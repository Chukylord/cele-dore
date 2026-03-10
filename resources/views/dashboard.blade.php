@extends('layouts.admin')

@section('title', 'Dashboard - Peluquería TOP')
@section('h1', 'Dashboard')
@section('sub', 'Resumen del día y accesos rápidos.')

@section('content')

    {{-- Acciones rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
        <a href="{{ route('compras.create') }}"
           class="rounded-2xl border bg-white p-4 hover:bg-slate-50">
            <div class="text-sm text-slate-600">Atajo</div>
            <div class="text-lg font-bold mt-1">➕ Ingresar compra</div>
            <div class="text-xs text-slate-500 mt-1">Carga múltiple por lote</div>
        </a>

        <a href="{{ route('ventas.create') }}"
           class="rounded-2xl border bg-white p-4 hover:bg-slate-50">
            <div class="text-sm text-slate-600">Atajo</div>
            <div class="text-lg font-bold mt-1">💵 Ingresar venta</div>
            <div class="text-xs text-slate-500 mt-1">Servicios + productos</div>
        </a>

        <a href="{{ route('turnos.index') }}"
           class="rounded-2xl border bg-white p-4 hover:bg-slate-50">
            <div class="text-sm text-slate-600">Atajo</div>
            <div class="text-lg font-bold mt-1">📅 Turnos</div>
            <div class="text-xs text-slate-500 mt-1">Calendario</div>
        </a>

        <a href="{{ route('fichadas.create') }}"
           class="rounded-2xl border bg-white p-4 hover:bg-slate-50">
            <div class="text-sm text-slate-600">Atajo</div>
            <div class="text-lg font-bold mt-1">⏱️ Nueva fichada</div>
            <div class="text-xs text-slate-500 mt-1">Horas normales / extras</div>
        </a>
    </div>

    {{-- Métricas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div class="rounded-2xl border bg-white p-5">
            <div class="text-sm text-slate-600">Ventas hoy</div>
            <div class="text-3xl font-extrabold mt-1">{{ $ventasHoy }}</div>
            <div class="text-sm text-slate-600 mt-2">
                Cobrado hoy: <span class="font-bold">${{ number_format((float)$totalHoy, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="rounded-2xl border bg-yellow-100 p-5">
            <div class="text-sm text-yellow-900">Pendiente de cobrar</div>
            <div class="text-3xl font-extrabold mt-1 text-yellow-900">
                ${{ number_format((float)$pendienteCobrar, 2, ',', '.') }}
            </div>
            <div class="text-xs text-yellow-900 mt-2">Ventas en estado pendiente</div>
            <a class="inline-block mt-3 rounded-xl border border-yellow-300 bg-white px-3 py-2 hover:bg-yellow-50"
               href="{{ route('ventas.index', ['estado' => 'pendiente']) }}">
                Ver pendientes
            </a>
        </div>

        <div class="rounded-2xl border bg-white p-5">
            <div class="text-sm text-slate-600">Turnos hoy</div>
            <div class="text-3xl font-extrabold mt-1">{{ $turnosHoy }}</div>
            <a class="inline-block mt-3 rounded-xl bg-slate-900 text-white px-3 py-2 hover:bg-slate-800"
               href="{{ route('turnos.index') }}">
                Abrir calendario
            </a>
        </div>

        <div class="rounded-2xl border bg-white p-5">
            <div class="text-sm text-slate-600">Stock bajo</div>
            <div class="text-3xl font-extrabold mt-1">{{ $stockBajo }}</div>
            <a class="inline-block mt-3 rounded-xl border px-3 py-2 hover:bg-slate-50"
               href="{{ route('productos.index') }}">
                Ver productos
            </a>
        </div>

        <div class="rounded-2xl border bg-white p-5">
            <div class="text-sm text-slate-600">Horas fichadas hoy</div>
            <div class="text-3xl font-extrabold mt-1">{{ number_format((float)$horasFichadasHoy, 2, ',', '.') }}</div>
            <a class="inline-block mt-3 rounded-xl border px-3 py-2 hover:bg-slate-50"
               href="{{ route('fichadas.index') }}">
                Ver fichadas
            </a>
        </div>

        <div class="rounded-2xl border bg-slate-900 text-white p-5">
            <div class="text-sm text-slate-200">Atajos administrativos</div>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <a class="rounded-xl border border-slate-600 px-3 py-2 hover:bg-slate-800 text-center"
                   href="{{ route('clientes.index') }}">Clientes</a>
                <a class="rounded-xl border border-slate-600 px-3 py-2 hover:bg-slate-800 text-center"
                   href="{{ route('colaboradoras.index') }}">Colaboradoras</a>
                <a class="rounded-xl border border-slate-600 px-3 py-2 hover:bg-slate-800 text-center"
                   href="{{ route('productos.index') }}">Productos</a>
                <a class="rounded-xl border border-slate-600 px-3 py-2 hover:bg-slate-800 text-center"
                   href="{{ route('informes.index') }}">Informes</a>
            </div>
        </div>

    </div>

@endsection