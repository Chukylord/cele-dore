@extends('layouts.admin')

@section('title', 'Dashboard - Cele Dore Estilista')
@section('h1', 'Dashboard')
@section('sub', 'Resumen del día y accesos rápidos.')

@section('content')

    {{-- CABECERA PRINCIPAL --}}
    <section class="px-6 pt-6">
        <div class="max-w-6xl mx-auto">
            <div class="relative overflow-hidden rounded-[32px] bg-gradient-to-r from-[#09b98b] via-[#18c99c] to-[#7edfc4] px-8 py-10 shadow-[0_25px_60px_rgba(18,151,115,0.22)]">

                {{-- Decoración --}}
                <div class="absolute -top-16 -left-16 h-56 w-56 rounded-full border border-white/20"></div>
                <div class="absolute -bottom-20 right-[-40px] h-72 w-72 rounded-full border border-white/20"></div>
                <div class="absolute top-10 right-20 h-20 w-20 rounded-full bg-white/10 blur-md"></div>
                <div class="absolute bottom-8 left-1/3 h-16 w-16 rounded-full bg-white/10 blur-md"></div>

                <div class="relative z-10 flex flex-col items-center text-center lg:flex-row lg:items-center lg:justify-between lg:text-left gap-8">

                    <div class="flex items-center gap-6">
                        <div class="flex h-28 w-28 items-center justify-center rounded-full bg-white/15 p-2 shadow-lg backdrop-blur-sm sm:h-32 sm:w-32">
                            <img src="{{ asset('images/cele-dore-logo.png') }}"
                                 alt="Estilista Cele Dore"
                                 class="h-full w-full object-contain">
                        </div>

                        <div class="text-white">
                            <div class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/15 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.30em] backdrop-blur-sm">
                                <span class="h-2 w-2 rounded-full bg-white"></span>
                                Sistema de gestión
                            </div>

                            <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">
                                Celeste Doré
                            </h1>

                            <p class="mt-2 text-lg font-medium text-white/90">
                                Estética integral
                            </p>

                            <p class="mt-3 max-w-xl text-sm leading-6 text-white/85">
                                Bienvenida al panel de administración. Desde aquí podés gestionar ventas, turnos, compras, stock y más.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-white">
                        <div class="rounded-2xl bg-white/15 px-5 py-4 backdrop-blur-sm">
                            <div class="text-xs uppercase tracking-[0.25em] text-white/80">Ventas hoy</div>
                            <div class="mt-2 text-2xl font-extrabold">{{ $ventasHoy }}</div>
                        </div>

                        <div class="rounded-2xl bg-white/15 px-5 py-4 backdrop-blur-sm">
                            <div class="text-xs uppercase tracking-[0.25em] text-white/80">Turnos hoy</div>
                            <div class="mt-2 text-2xl font-extrabold">{{ $turnosHoy }}</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- DASHBOARD --}}
    <section class="px-6 py-8 bg-slate-100">
        <div class="max-w-6xl mx-auto">

            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
                <p class="text-slate-600 mt-1">Resumen del día y accesos rápidos.</p>
            </div>

            {{-- Acciones rápidas --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                <a href="{{ route('compras.create') }}"
                   class="rounded-2xl border bg-white p-4 hover:bg-slate-50 transition">
                    <div class="text-sm text-slate-600">Atajo</div>
                    <div class="text-lg font-bold mt-1">➕ Ingresar compra</div>
                    <div class="text-xs text-slate-500 mt-1">Carga múltiple por lote</div>
                </a>

                <a href="{{ route('ventas.create') }}"
                   class="rounded-2xl border bg-white p-4 hover:bg-slate-50 transition">
                    <div class="text-sm text-slate-600">Atajo</div>
                    <div class="text-lg font-bold mt-1">💵 Ingresar venta</div>
                    <div class="text-xs text-slate-500 mt-1">Servicios + productos</div>
                </a>

                <a href="{{ route('turnos.index') }}"
                   class="rounded-2xl border bg-white p-4 hover:bg-slate-50 transition">
                    <div class="text-sm text-slate-600">Atajo</div>
                    <div class="text-lg font-bold mt-1">📅 Turnos</div>
                    <div class="text-xs text-slate-500 mt-1">Calendario</div>
                </a>

                <a href="{{ route('fichadas.create') }}"
                   class="rounded-2xl border bg-white p-4 hover:bg-slate-50 transition">
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
                        Cobrado hoy:
                        <span class="font-bold">
                            ${{ number_format((float)$totalHoy, 2, ',', '.') }}
                        </span>
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
                    <div class="text-3xl font-extrabold mt-1">
                        {{ number_format((float)$horasFichadasHoy, 2, ',', '.') }}
                    </div>
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
        </div>
    </section>

@endsection