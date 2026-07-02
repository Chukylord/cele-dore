<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Iniciar sesión - Estilista Cele Dore</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#eafaf5] text-slate-900">

    <div class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top_left,rgba(20,199,154,0.22),transparent_32%),radial-gradient(circle_at_bottom_right,rgba(20,199,154,0.16),transparent_35%),linear-gradient(135deg,#f7fffc_0%,#eafaf5_50%,#dff7ef_100%)] px-4 py-6 sm:px-6 lg:px-10">

        <div class="w-full max-w-7xl overflow-hidden rounded-[36px] border border-white/80 bg-white shadow-[0_35px_100px_rgba(18,122,96,0.20)]">

            <div class="grid grid-cols-1 lg:grid-cols-[1.05fr_0.95fr]">

                {{-- PANEL IZQUIERDO --}}
                <section class="relative hidden min-h-[760px] overflow-hidden bg-[#13c79a] lg:flex">

                    <div class="absolute inset-0 bg-gradient-to-br from-[#09b98b] via-[#18c99c] to-[#82dfc6]"></div>

                    {{-- Decoración --}}
                    <div class="absolute -left-32 -top-32 h-[420px] w-[420px] rounded-full border border-white/20"></div>
                    <div class="absolute -bottom-48 -right-28 h-[520px] w-[520px] rounded-full border border-white/20"></div>
                    <div class="absolute right-20 top-32 h-24 w-24 rounded-full bg-white/10 blur-sm"></div>
                    <div class="absolute bottom-36 left-24 h-16 w-16 rounded-full bg-white/10 blur-sm"></div>

                    {{-- Etiqueta superior --}}
                    <div class="absolute left-10 top-10 z-20">
                        <div class="inline-flex items-center gap-3 rounded-full border border-white/30 bg-white/15 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.30em] text-white backdrop-blur-md">
                            <span class="h-2 w-2 rounded-full bg-white"></span>
                            Estilista Cele Dore
                        </div>
                    </div>

                    {{-- Contenido central --}}
                    <div class="relative z-10 flex h-full w-full flex-col items-center justify-center px-10 pb-28 pt-24">

                        {{-- Logo centrado --}}
                        <div class="flex h-[400px] w-[400px] flex-none items-center justify-center xl:h-[455px] xl:w-[455px]">
                            <img
                                src="{{ asset('images/cele-dore-logo.png') }}"
                                alt="Logo de Estilista Cele Dore"
                                class="block h-full w-full object-contain drop-shadow-[0_24px_45px_rgba(0,90,65,0.24)]"
                            >
                        </div>

                        {{-- Nombre --}}
                        <div class="mt-7 text-center text-white">
                            <h2 class="text-4xl font-black tracking-tight xl:text-5xl">
                                Celeste Doré
                            </h2>

                            <p class="mt-3 text-lg font-semibold tracking-wide text-white/90">
                                Estética integral
                            </p>
                        </div>

                    </div>

                    {{-- Pie del panel --}}
                    <div class="absolute bottom-8 left-10 right-10 z-20">
                        <div class="h-px bg-white/25"></div>

                        <div class="mt-5 flex items-center justify-between text-xs font-semibold uppercase tracking-[0.25em] text-white/75">
                            <span>Belleza integral</span>
                            <span>Sistema de gestión</span>
                        </div>
                    </div>

                </section>

                {{-- PANEL DERECHO --}}
                <section class="relative flex min-h-[760px] items-center justify-center bg-[#fbfefc] px-6 py-10 sm:px-10 lg:px-16">

                    <div class="w-full max-w-md">

                        {{-- Logo para celulares y tablets --}}
                        <div class="mb-8 lg:hidden">
                            <div class="rounded-[30px] bg-gradient-to-br from-[#09b98b] via-[#18c99c] to-[#7edfc4] px-6 py-8 shadow-[0_25px_60px_rgba(18,151,115,0.25)]">

                                <div class="mx-auto flex h-52 w-52 items-center justify-center">
                                    <img
                                        src="{{ asset('images/cele-dore-logo.png') }}"
                                        alt="Logo de Estilista Cele Dore"
                                        class="block h-full w-full object-contain drop-shadow-[0_18px_35px_rgba(0,90,65,0.22)]"
                                    >
                                </div>

                                <div class="mt-5 text-center text-white">
                                    <div class="text-2xl font-black">
                                        Estilista Cele Dore
                                    </div>

                                    <div class="mt-1 text-sm font-medium text-white/90">
                                        Celeste Doré Estética integral
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Encabezado --}}
                        <div class="mb-9">

                            <div class="inline-flex items-center gap-2 rounded-full border border-[#bdeedf] bg-[#edfbf6] px-4 py-2 text-[11px] font-bold uppercase tracking-[0.30em] text-[#078263] shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-[#13c79a]"></span>
                                Acceso privado
                            </div>

                            <h1 class="mt-6 text-5xl font-black tracking-tight text-slate-950">
                                Bienvenida
                            </h1>

                            <p class="mt-4 text-lg leading-8 text-slate-500">
                                Ingresá al panel de gestión de
                                <span class="font-bold text-[#078263]">
                                    Estilista Cele Dore
                                </span>.
                            </p>

                        </div>

                        {{-- Mensajes --}}
                        @if (session('status'))
                            <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        {{-- Formulario --}}
                        <form method="POST" action="{{ route('login') }}" class="space-y-5">

                            @csrf

                            <div>
                                <label for="email" class="mb-2.5 block text-sm font-bold text-slate-700">
                                    Correo electrónico
                                </label>

                                <div class="relative">

                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5 text-[#13a981]">
                                        <span class="text-lg">✉</span>
                                    </div>

                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="ejemplo@correo.com"
                                        class="w-full rounded-[22px] border border-slate-200 bg-white py-4 pl-14 pr-5 text-base text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#13c79a] focus:ring-4 focus:ring-[#13c79a]/15"
                                    >

                                </div>
                            </div>

                            <div>
                                <label for="password" class="mb-2.5 block text-sm font-bold text-slate-700">
                                    Contraseña
                                </label>

                                <div class="relative">

                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5 text-[#13a981]">
                                        <span class="text-lg">●</span>
                                    </div>

                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="********"
                                        class="w-full rounded-[22px] border border-slate-200 bg-white py-4 pl-14 pr-5 text-base text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#13c79a] focus:ring-4 focus:ring-[#13c79a]/15"
                                    >

                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">

                                <label for="remember_me" class="flex cursor-pointer items-center gap-3">
                                    <input
                                        id="remember_me"
                                        type="checkbox"
                                        name="remember"
                                        class="rounded border-slate-300 text-[#13c79a] focus:ring-[#13c79a]"
                                    >

                                    <span class="text-sm font-medium text-slate-600">
                                        Recordarme
                                    </span>
                                </label>

                            </div>

                            <button
                                type="submit"
                                class="group relative w-full overflow-hidden rounded-[22px] bg-gradient-to-r from-[#08ae82] to-[#18c99c] px-5 py-4 text-lg font-bold text-white shadow-[0_18px_45px_rgba(18,169,129,0.30)] transition hover:-translate-y-0.5 hover:shadow-[0_22px_55px_rgba(18,169,129,0.38)]"
                            >
                                <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/25 to-transparent transition duration-700 group-hover:translate-x-full"></span>

                                <span class="relative">
                                    Ingresar al sistema
                                </span>
                            </button>

                        </form>

                        {{-- Información inferior --}}
                        <div class="mt-9 rounded-[26px] border border-[#d9f3eb] bg-white p-4 shadow-sm">

                            <div class="flex items-center gap-3">

                                <div class="flex h-11 w-11 flex-none items-center justify-center rounded-2xl bg-[#e7faf4] text-xl text-[#07906d]">
                                    ✦
                                </div>

                                <div>
                                    <div class="text-sm font-bold text-slate-900">
                                        Gestión profesional
                                    </div>

                                    <div class="text-xs leading-5 text-slate-500">
                                        Ventas, turnos, compras, stock y administración del salón.
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                </section>

            </div>

        </div>

    </div>

</body>
</html>
