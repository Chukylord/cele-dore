<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Peluquería TOP')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 text-slate-900">
<div class="min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white p-4 flex flex-col">
        <div class="mb-6">
            <div class="text-2xl font-bold">VIR TISONE STUDIO</div>
            <div class="text-sm text-slate-300">Sistema de gestión</div>
        </div>

        <nav class="space-y-2 flex-1">
            @auth

                <a href="{{ route('dashboard') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-slate-800' : '' }}">
                    Dashboard
                </a>

                <div class="mt-4 text-xs uppercase tracking-wider text-slate-400 px-3">
                    Operación diaria
                </div>

                <a href="{{ route('turnos.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('turnos.*') ? 'bg-slate-800' : '' }}">
                    TURNOS
                </a>

                <a href="{{ route('ventas.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('ventas.*') ? 'bg-slate-800' : '' }}">
                    VENTAS
                </a>

                <a href="{{ route('compras.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('compras.*') ? 'bg-slate-800' : '' }}">
                    COMPRAS
                </a>

                <a href="{{ route('fichadas.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('fichadas.*') ? 'bg-slate-800' : '' }}">
                    FICHADAS
                </a>

                <div class="mt-4 text-xs uppercase tracking-wider text-slate-400 px-3">
                    GESTIÓN
                </div>

                <a href="{{ route('clientes.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('clientes.*') ? 'bg-slate-800' : '' }}">
                    CLIENTES
                </a>

                <a href="{{ route('colaboradoras.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('colaboradoras.*') ? 'bg-slate-800' : '' }}">
                    COLABORADORAS
                </a>

                <a href="{{ route('productos.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('productos.*') ? 'bg-slate-800' : '' }}">
                    PRODUCTOS
                </a>

                <a href="{{ route('servicios.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('servicios.*') ? 'bg-slate-800' : '' }}">
                    SERVICIOS
                </a>

                <a href="{{ route('lista-precios.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('lista-precios.*') ? 'bg-slate-800' : '' }}">
                    LISTA DE PRECIOS
                </a>

                <a href="{{ route('proveedores.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('proveedores.*') ? 'bg-slate-800' : '' }}">
                    PROVEEDORES
                </a>

                <div class="mt-4 text-xs uppercase tracking-wider text-slate-400 px-3">
                    FINANZAS
                </div>

                <a href="{{ route('gastos.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('gastos.*') ? 'bg-slate-800' : '' }}">
                    GASTOS
                </a>

                @if(auth()->user()->esAdmin())
                    <a href="{{ route('liquidaciones.index') }}"
                       class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('liquidaciones.*') ? 'bg-slate-800' : '' }}">
                        LIQUIDACIONES
                    </a>

                    <a href="{{ route('informes.index') }}"
                       class="block px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('informes.*') ? 'bg-slate-800' : '' }}">
                        INFORMES
                    </a>
                @endif
            @endauth
        </nav>

        <!-- Usuario + logout -->
        <div class="pt-4 border-t border-slate-700">
            <div class="text-sm text-slate-300 mb-2">
                {{ Auth::user()->name }}
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full text-left px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <!-- Content -->
    <div class="flex-1 flex flex-col">

        {{-- Topbar --}}
        <div class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b">
            <div class="px-6 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-600 hidden md:inline">
                        VIR TISONE STUDIO - Sistema de gestión
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="font-bold text-slate-900">Accesos rápidos</span>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('clientes.index') }}"
                       class="rounded-xl border px-3 py-2 hover:bg-slate-50 text-sm">
                        👤 CLIENTES
                    </a>

                    <a href="{{ route('compras.create') }}"
                       class="rounded-xl border px-3 py-2 hover:bg-slate-50 text-sm">
                        ➕ COMPRA
                    </a>

                    <a href="{{ route('ventas.create') }}"
                       class="rounded-xl border px-3 py-2 hover:bg-slate-50 text-sm">
                        💵 VENTA
                    </a>

                    <a href="{{ route('turnos.index') }}"
                       class="rounded-xl border px-3 py-2 hover:bg-slate-50 text-sm">
                        📅 TURNOS
                    </a>

                    <a href="{{ route('fichadas.create') }}"
                       class="rounded-xl border px-3 py-2 hover:bg-slate-50 text-sm">
                        ⏱️ FICHADA
                    </a>
                </div>
            </div>
        </div>

        @if(request()->routeIs('dashboard'))
            <main class="flex-1 p-0">
                @yield('content')
            </main>
        @else
            <main class="flex-1 p-6">
                <div class="max-w-6xl mx-auto">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold">@yield('h1', 'Dashboard')</h1>
                        @hasSection('sub')
                            <p class="text-slate-600 mt-1">@yield('sub')</p>
                        @endif
                    </div>

                    <div class="bg-white rounded-2xl shadow p-6">
                        @yield('content')
                    </div>
                </div>
            </main>
        @endif
    </div>

</div>
</body>
</html>