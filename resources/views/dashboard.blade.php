@extends('layouts.admin')

@section('title', 'Dashboard - Cele Dore Estilista')
@section('h1', 'Dashboard')
@section('sub', 'Resumen del día y accesos rápidos.')

@section('content')

<div class="min-h-full bg-[#f7fffc]">

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-[#078263] via-[#13c79a] to-[#05634d] px-6 py-8 lg:px-10 lg:py-10">

        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-32 right-48 h-72 w-72 rounded-full bg-white/5"></div>

        <div class="relative mx-auto max-w-7xl">

            <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">

                <div class="max-w-2xl">

                    <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.20em] text-white/80 backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                        Panel de gestión
                    </div>

                    <h1 class="mt-5 text-4xl font-black tracking-tight text-white sm:text-5xl">
                        Bienvenida a Cele Dore Estilista
                    </h1>

                    <p class="mt-3 text-base leading-7 text-white/75 sm:text-lg">
                        Acá tenés el resumen del salón y los accesos principales para trabajar durante el día.
                    </p>

                </div>

                <div class="hidden lg:block">
                    <div class="rounded-[30px] border border-white/15 bg-white/10 p-3 shadow-2xl backdrop-blur">
                        <img
                            src="{{ asset('images/cele-dore-logo.png') }}"
                            alt="Cele Dore Estilista"
                            class="h-44 w-44 rounded-[24px] object-cover"
                        >
                    </div>
                </div>

            </div>

        </div>

    </section>


    {{-- CONTENIDO --}}
    <section class="px-6 py-8 lg:px-10">

        <div class="mx-auto max-w-7xl">

            {{-- ENCABEZADO --}}
            <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">

                <div>
                    <div class="text-sm font-bold uppercase tracking-[0.15em] text-[#13c79a]">
                        Resumen
                    </div>

                    <h2 class="mt-1 text-3xl font-black tracking-tight text-[#241a2c]">
                        Hoy en el salón
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Información actualizada con los movimientos registrados.
                    </p>
                </div>

                <div class="rounded-2xl border border-[#bdeedf] bg-white px-4 py-3 text-sm shadow-sm">
                    <span class="text-slate-400">Fecha:</span>
                    <span class="ml-1 font-bold text-[#078263]">
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>

            </div>


            {{-- MÉTRICAS PRINCIPALES --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                {{-- VENTAS --}}
                <div class="group rounded-[26px] border border-[#bdeedf] bg-white p-5 shadow-[0_12px_30px_rgba(111,62,134,0.06)] transition hover:-translate-y-1 hover:shadow-[0_18px_40px_rgba(111,62,134,0.12)]">

                    <div class="flex items-start justify-between">

                        <div>
                            <div class="text-sm font-semibold text-slate-500">
                                Ventas hoy
                            </div>

                            <div class="mt-2 text-4xl font-black text-[#241a2c]">
                                {{ $ventasHoy }}
                            </div>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#dff7ef] text-2xl">
                            💵
                        </div>

                    </div>

                    <div class="mt-5 border-t border-[#eee9f1] pt-4">
                        <div class="text-xs text-slate-400">
                            Cobrado hoy
                        </div>

                        <div class="mt-1 text-lg font-black text-[#078263]">
                            ${{ number_format((float)$totalHoy, 2, ',', '.') }}
                        </div>
                    </div>

                </div>


                {{-- PENDIENTE --}}
                <div class="group rounded-[26px] border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">

                    <div class="flex items-start justify-between">

                        <div>
                            <div class="text-sm font-semibold text-amber-700">
                                Pendiente de cobrar
                            </div>

                            <div class="mt-2 text-3xl font-black text-amber-900">
                                ${{ number_format((float)$pendienteCobrar, 2, ',', '.') }}
                            </div>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-2xl">
                            ⏳
                        </div>

                    </div>

                    <a
                        href="{{ route('ventas.index', ['estado' => 'pendiente']) }}"
                        class="mt-5 inline-flex items-center rounded-xl border border-amber-200 bg-white px-3 py-2 text-sm font-bold text-amber-800 transition hover:bg-amber-100"
                    >
                        Ver pendientes →
                    </a>

                </div>


                {{-- TURNOS --}}
                <div class="group rounded-[26px] border border-[#bdeedf] bg-white p-5 shadow-[0_12px_30px_rgba(111,62,134,0.06)] transition hover:-translate-y-1 hover:shadow-[0_18px_40px_rgba(111,62,134,0.12)]">

                    <div class="flex items-start justify-between">

                        <div>
                            <div class="text-sm font-semibold text-slate-500">
                                Turnos hoy
                            </div>

                            <div class="mt-2 text-4xl font-black text-[#241a2c]">
                                {{ $turnosHoy }}
                            </div>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#dff7ef] text-2xl">
                            📅
                        </div>

                    </div>

                    <a
                        href="{{ route('turnos.index') }}"
                        class="mt-5 inline-flex items-center rounded-xl bg-[#13c79a] px-3 py-2 text-sm font-bold text-white transition hover:bg-[#078263]"
                    >
                        Abrir calendario →
                    </a>

                </div>


                {{-- STOCK --}}
                <div class="group rounded-[26px] border border-[#bdeedf] bg-white p-5 shadow-[0_12px_30px_rgba(111,62,134,0.06)] transition hover:-translate-y-1 hover:shadow-[0_18px_40px_rgba(111,62,134,0.12)]">

                    <div class="flex items-start justify-between">

                        <div>
                            <div class="text-sm font-semibold text-slate-500">
                                Stock bajo
                            </div>

                            <div class="mt-2 text-4xl font-black text-[#241a2c]">
                                {{ $stockBajo }}
                            </div>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#dff7ef] text-2xl">
                            🧴
                        </div>

                    </div>

                    <a
                        href="{{ route('productos.index') }}"
                        class="mt-5 inline-flex items-center rounded-xl border border-[#bdeedf] px-3 py-2 text-sm font-bold text-[#078263] transition hover:bg-[#f8f4fa]"
                    >
                        Ver productos →
                    </a>

                </div>

            </div>


            {{-- SEGUNDA FILA --}}
            <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[1fr_1.6fr]">

                {{-- HORAS --}}
                <div class="rounded-[28px] border border-[#bdeedf] bg-white p-6 shadow-[0_12px_30px_rgba(111,62,134,0.06)]">

                    <div class="flex items-center justify-between">

                        <div>
                            <div class="text-sm font-semibold text-slate-500">
                                Horas fichadas hoy
                            </div>

                            <div class="mt-2 text-4xl font-black text-[#241a2c]">
                                {{ number_format((float)$horasFichadasHoy, 2, ',', '.') }}
                                <span class="text-lg font-bold text-slate-400">hs</span>
                            </div>
                        </div>

                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#dff7ef] text-3xl">
                            ⏱
                        </div>

                    </div>

                    <p class="mt-4 text-sm leading-6 text-slate-500">
                        Total acumulado de horas registradas por las colaboradoras durante el día.
                    </p>

                    <a
                        href="{{ route('fichadas.index') }}"
                        class="mt-5 inline-flex rounded-xl border border-[#bdeedf] px-4 py-2.5 text-sm font-bold text-[#078263] transition hover:bg-[#f8f4fa]"
                    >
                        Ver fichadas
                    </a>

                </div>


                {{-- ACCIONES RÁPIDAS --}}
                <div class="rounded-[28px] border border-[#bdeedf] bg-white p-6 shadow-[0_12px_30px_rgba(111,62,134,0.06)]">

                    <div class="mb-5">

                        <div class="text-sm font-bold uppercase tracking-[0.15em] text-[#13c79a]">
                            Accesos rápidos
                        </div>

                        <h3 class="mt-1 text-2xl font-black text-[#241a2c]">
                            ¿Qué querés hacer?
                        </h3>

                    </div>


                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                        <a
                            href="{{ route('ventas.create') }}"
                            class="group flex items-center gap-4 rounded-2xl bg-gradient-to-r from-[#13c79a] to-[#078263] p-4 text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 text-xl">
                                💵
                            </div>

                            <div>
                                <div class="font-black">
                                    Nueva venta
                                </div>

                                <div class="mt-0.5 text-xs text-white/70">
                                    Servicios y productos
                                </div>
                            </div>
                        </a>


                        <a
                            href="{{ route('turnos.index') }}"
                            class="group flex items-center gap-4 rounded-2xl border border-[#bdeedf] bg-[#fcfbfe] p-4 transition hover:border-[#13c79a] hover:bg-[#f8f4fa]"
                        >
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#dff7ef] text-xl">
                                📅
                            </div>

                            <div>
                                <div class="font-black text-[#241a2c]">
                                    Gestionar turnos
                                </div>

                                <div class="mt-0.5 text-xs text-slate-500">
                                    Ver calendario
                                </div>
                            </div>
                        </a>


                        <a
                            href="{{ route('compras.create') }}"
                            class="group flex items-center gap-4 rounded-2xl border border-[#bdeedf] bg-[#fcfbfe] p-4 transition hover:border-[#13c79a] hover:bg-[#f8f4fa]"
                        >
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#dff7ef] text-xl">
                                🛍
                            </div>

                            <div>
                                <div class="font-black text-[#241a2c]">
                                    Ingresar compra
                                </div>

                                <div class="mt-0.5 text-xs text-slate-500">
                                    Productos y stock
                                </div>
                            </div>
                        </a>


                        <a
                            href="{{ route('fichadas.create') }}"
                            class="group flex items-center gap-4 rounded-2xl border border-[#bdeedf] bg-[#fcfbfe] p-4 transition hover:border-[#13c79a] hover:bg-[#f8f4fa]"
                        >
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#dff7ef] text-xl">
                                ⏱
                            </div>

                            <div>
                                <div class="font-black text-[#241a2c]">
                                    Nueva fichada
                                </div>

                                <div class="mt-0.5 text-xs text-slate-500">
                                    Horas normales y extras
                                </div>
                            </div>
                        </a>

                    </div>

                </div>

            </div>


            {{-- GESTIÓN --}}
            <div class="mt-6 rounded-[28px] bg-gradient-to-r from-[#05634d] to-[#7b468f] p-6 text-white shadow-[0_18px_45px_rgba(92,50,114,0.20)]">

                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <div class="text-xs font-bold uppercase tracking-[0.20em] text-white/60">
                            Administración
                        </div>

                        <h3 class="mt-1 text-2xl font-black">
                            Gestión del salón
                        </h3>

                        <p class="mt-1 text-sm text-white/70">
                            Accedé rápidamente a las principales áreas administrativas.
                        </p>
                    </div>


                    <div class="grid grid-cols-2 gap-2 sm:flex">

                        <a
                            href="{{ route('clientes.index') }}"
                            class="rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-center text-sm font-bold transition hover:bg-white/20"
                        >
                            Clientes
                        </a>

                        <a
                            href="{{ route('colaboradoras.index') }}"
                            class="rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-center text-sm font-bold transition hover:bg-white/20"
                        >
                            Colaboradoras
                        </a>

                        <a
                            href="{{ route('productos.index') }}"
                            class="rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-center text-sm font-bold transition hover:bg-white/20"
                        >
                            Productos
                        </a>

                        @if(auth()->user()->esAdmin())
                            <a
                                href="{{ route('informes.index') }}"
                                class="rounded-xl bg-white px-4 py-2.5 text-center text-sm font-black text-[#078263] transition hover:bg-[#f5eef8]"
                            >
                                Informes
                            </a>
                        @endif

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>

@endsection