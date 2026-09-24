<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cele Dore Estilista')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="fn-admin bg-[#f7fffc] text-slate-900">

<div class="min-h-screen flex">

    {{-- SIDEBAR --}}
    <aside class="w-64 bg-gradient-to-b from-[#078263] via-[#77458e] to-[#05634d] text-white p-4 flex flex-col shadow-[8px_0_30px_rgba(92,50,114,0.12)]">

        {{-- LOGO --}}
        <div class="mb-7">
            <div class="rounded-[26px] bg-white/10 p-3 border border-white/10 shadow-lg">
                <img
                    src="{{ asset('images/cele-dore-logo.png') }}"
                    alt="Cele Dore Estilista"
                    class="w-full rounded-[20px] object-contain"
                >
            </div>

            <div class="mt-4 px-2">
                <div class="text-sm font-semibold tracking-wide text-white">
                    Sistema de gestión
                </div>

                <div class="mt-1 text-xs text-white/60">
                    Administración del salón
                </div>
            </div>
        </div>

        {{-- NAVEGACIÓN --}}
        <nav class="space-y-1.5 flex-1">

            @auth

                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('dashboard')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>⌂</span>
                    <span>Dashboard</span>
                </a>


                {{-- OPERACIÓN DIARIA --}}
                <div class="mt-6 mb-2 text-[10px] uppercase tracking-[0.22em] text-white/45 px-3 font-bold">
                    Operación diaria
                </div>


                <a
                    href="{{ route('turnos.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('turnos.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>📅</span>
                    <span>Turnos</span>
                </a>


                <a
                    href="{{ route('ventas.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('ventas.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>💵</span>
                    <span>Ventas</span>
                </a>


                <a
                    href="{{ route('compras.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('compras.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>🛍</span>
                    <span>Compras</span>
                </a>


                <a
                    href="{{ route('fichadas.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('fichadas.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>⏱</span>
                    <span>Fichadas</span>
                </a>


                <a
                    href="{{ route('caja-diaria.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('caja-diaria.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>💰</span>
                    <span>Caja diaria</span>
                </a>


                {{-- GESTIÓN --}}
                <div class="mt-6 mb-2 text-[10px] uppercase tracking-[0.22em] text-white/45 px-3 font-bold">
                    Gestión
                </div>


                <a
                    href="{{ route('clientes.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('clientes.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>👤</span>
                    <span>Clientes</span>
                </a>


                <a
                    href="{{ route('colaboradoras.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('colaboradoras.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>👥</span>
                    <span>Colaboradoras</span>
                </a>


                <a
                    href="{{ route('productos.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('productos.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>🧴</span>
                    <span>Productos</span>
                </a>


                <a
                    href="{{ route('servicios.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('servicios.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>✂</span>
                    <span>Servicios</span>
                </a>


                <a
                    href="{{ route('lista-precios.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('lista-precios.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>🏷</span>
                    <span>Lista de precios</span>
                </a>


                <a
                    href="{{ route('proveedores.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ request()->routeIs('proveedores.*')
                        ? 'bg-white text-[#078263] shadow-md font-bold'
                        : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                >
                    <span>🚚</span>
                    <span>Proveedores</span>
                </a>


                {{-- FINANZAS --}}
                @if(auth()->user()->esAdmin())

                    <div class="mt-6 mb-2 text-[10px] uppercase tracking-[0.22em] text-white/45 px-3 font-bold">
                        Finanzas
                    </div>


                    <a
                        href="{{ route('gastos.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                        {{ request()->routeIs('gastos.*')
                            ? 'bg-white text-[#078263] shadow-md font-bold'
                            : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                    >
                        <span>↘</span>
                        <span>Gastos</span>
                    </a>


                    <a
                        href="{{ route('liquidaciones.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                        {{ request()->routeIs('liquidaciones.*')
                            ? 'bg-white text-[#078263] shadow-md font-bold'
                            : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                    >
                        <span>🧾</span>
                        <span>Liquidaciones</span>
                    </a>


                    <a
                        href="{{ route('informes.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                        {{ request()->routeIs('informes.*')
                            ? 'bg-white text-[#078263] shadow-md font-bold'
                            : 'text-white/90 hover:bg-white/10 hover:text-white' }}"
                    >
                        <span>📊</span>
                        <span>Informes</span>
                    </a>

                @endif

            @endauth

        </nav>


        {{-- USUARIO / LOGOUT --}}
        <div class="pt-4 mt-6 border-t border-white/15">

            <div class="rounded-2xl bg-white/10 p-3 mb-3">

                <div class="text-[10px] uppercase tracking-[0.18em] text-white/50">
                    Sesión iniciada
                </div>

                <div class="mt-1 text-sm font-bold text-white">
                    {{ Auth::user()->name }}
                </div>

            </div>


            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    class="w-full text-left px-3 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 transition text-sm font-semibold"
                >
                    Cerrar sesión
                </button>

            </form>

        </div>

    </aside>



    {{-- CONTENIDO --}}
    <div class="flex-1 flex flex-col min-w-0">


        {{-- BARRA SUPERIOR --}}
        <div class="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border-b border-[#bdeedf] shadow-sm">

            <div class="px-6 py-3.5 flex items-center justify-between gap-4">

                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#dff7ef] text-[#078263] font-black">
                        FN
                    </div>

                    <div>
                        <div class="text-sm font-bold text-[#241a2c]">
                            Cele Dore Estilista
                        </div>

                        <div class="text-xs text-slate-400">
                            Sistema de gestión
                        </div>
                    </div>

                </div>


                {{-- ACCESOS RÁPIDOS --}}
                <div class="flex flex-wrap gap-2">

                    <a
                        href="{{ route('clientes.index') }}"
                        class="rounded-xl border border-[#bdeedf] bg-white px-3 py-2 hover:border-[#13c79a] hover:bg-[#f8f4fa] text-sm transition"
                    >
                        👤 Clientes
                    </a>


                    <a
                        href="{{ route('compras.create') }}"
                        class="rounded-xl border border-[#bdeedf] bg-white px-3 py-2 hover:border-[#13c79a] hover:bg-[#f8f4fa] text-sm transition"
                    >
                        ➕ Compra
                    </a>


                    <a
                        href="{{ route('ventas.create') }}"
                        class="rounded-xl bg-[#13c79a] px-3 py-2 text-white hover:bg-[#078263] text-sm font-semibold shadow-sm transition"
                    >
                        💵 Venta
                    </a>


                    <a
                        href="{{ route('turnos.index') }}"
                        class="rounded-xl border border-[#bdeedf] bg-white px-3 py-2 hover:border-[#13c79a] hover:bg-[#f8f4fa] text-sm transition"
                    >
                        📅 Turnos
                    </a>


                    <a
                        href="{{ route('fichadas.create') }}"
                        class="rounded-xl border border-[#bdeedf] bg-white px-3 py-2 hover:border-[#13c79a] hover:bg-[#f8f4fa] text-sm transition"
                    >
                        ⏱ Fichada
                    </a>

                </div>

            </div>

        </div>



        {{-- DASHBOARD --}}
        @if(request()->routeIs('dashboard'))

            <main class="flex-1 p-0 bg-[#f7fffc]">
                @yield('content')
            </main>

        @else

            {{-- RESTO DEL SISTEMA --}}
            <main class="flex-1 p-6 bg-[#f7fffc]">

                <div class="max-w-6xl mx-auto">

                    <div class="mb-6">

                        <h1 class="text-3xl font-black tracking-tight text-[#241a2c]">
                            @yield('h1', 'Dashboard')
                        </h1>

                        @hasSection('sub')
                            <p class="text-slate-500 mt-1">
                                @yield('sub')
                            </p>
                        @endif

                    </div>


                    <div class="bg-white rounded-[26px] border border-[#bdeedf] shadow-[0_12px_35px_rgba(111,62,134,0.07)] p-6">
                        @yield('content')
                    </div>

                </div>

            </main>

        @endif

    </div>

</div>

</body>
</html>