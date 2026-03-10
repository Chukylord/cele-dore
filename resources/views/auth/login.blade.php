<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión - VIR TISONE STUDIO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-r from-slate-50 via-slate-200 to-slate-800 text-slate-900">

    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-7xl grid grid-cols-1 lg:grid-cols-2 overflow-hidden rounded-[36px] border border-slate-200 bg-white shadow-2xl">

            {{-- Panel izquierdo --}}
            <div class="hidden lg:flex relative min-h-[760px] overflow-hidden bg-slate-950 text-white">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-700"></div>
                <div class="absolute -top-24 -left-20 h-72 w-72 rounded-full bg-indigo-500/10 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <div class="relative z-10 flex h-full w-full flex-col justify-between p-14">
                    <div>
                        <div class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-5 py-2 text-xs uppercase tracking-[0.35em] text-slate-300 backdrop-blur">
                            VIR TISONE STUDIO
                        </div>

                        <div class="mt-20 max-w-xl">
                            <h1 class="text-6xl font-extrabold leading-[1.02] tracking-tight">
                                Gestión simple,
                                <span class="block text-slate-300">ordenada y profesional.</span>
                            </h1>

                            <p class="mt-8 max-w-lg text-xl leading-9 text-slate-300">
                                Centralizá turnos, ventas, compras, stock y fichadas
                                en una sola plataforma pensada para el día a día del salón.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5 max-w-2xl">
                        <div class="rounded-3xl border border-white/10 bg-white/10 p-6 backdrop-blur">
                            <div class="text-sm text-slate-300">Operación diaria</div>
                            <div class="mt-3 text-2xl font-bold">Turnos & Ventas</div>
                            <div class="mt-2 text-sm text-slate-300">
                                Organización rápida del trabajo diario.
                            </div>
                        </div>

                        <div class="rounded-3xl border border-white/10 bg-white/10 p-6 backdrop-blur">
                            <div class="text-sm text-slate-300">Control interno</div>
                            <div class="mt-3 text-2xl font-bold">Stock & Compras</div>
                            <div class="mt-2 text-sm text-slate-300">
                                Seguimiento claro de productos y movimientos.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel derecho --}}
            <div class="flex items-center justify-center p-6 sm:p-10 lg:p-16">
                <div class="w-full max-w-xl">

                    {{-- Branding mobile --}}
                    <div class="lg:hidden mb-8 text-center">
                        <div class="inline-flex items-center rounded-full border border-slate-200 bg-white px-5 py-2 text-xs font-semibold uppercase tracking-[0.35em] text-slate-500 shadow-sm">
                            VIR TISONE STUDIO
                        </div>
                    </div>

                    <div class="mb-10">
                        <div class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">
                            Acceso al sistema
                        </div>

                        <h2 class="mt-5 text-5xl font-extrabold tracking-tight text-slate-900">
                            Iniciar sesión
                        </h2>

                        <p class="mt-4 max-w-lg text-xl leading-8 text-slate-500">
                            Ingresá con tu correo y contraseña para continuar.
                        </p>
                    </div>

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

                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label for="email" class="mb-3 block text-base font-semibold text-slate-700">
                                Correo electrónico
                            </label>
                            <input id="email"
                                   name="email"
                                   type="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   autocomplete="username"
                                   placeholder="ejemplo@correo.com"
                                   class="w-full rounded-3xl border border-slate-300 bg-white px-5 py-4 text-lg shadow-sm outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200">
                        </div>

                        <div>
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <label for="password" class="block text-base font-semibold text-slate-700">
                                    Contraseña
                                </label>
                            </div>

                            <input id="password"
                                   name="password"
                                   type="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="********"
                                   class="w-full rounded-3xl border border-slate-300 bg-white px-5 py-4 text-lg shadow-sm outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-200">
                        </div>

                        <label for="remember_me" class="flex items-center gap-3 pt-1">
                            <input id="remember_me"
                                   type="checkbox"
                                   name="remember"
                                   class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                            <span class="text-base text-slate-600">Recordarme</span>
                        </label>

                        <button type="submit"
                                class="w-full rounded-3xl bg-slate-950 px-5 py-4 text-xl font-semibold text-white shadow-xl shadow-slate-900/10 transition hover:bg-slate-800">
                            Ingresar
                        </button>
                    </form>

                    <div class="mt-10 text-center text-base text-slate-400">
                        Acceso privado para administración y colaboradoras
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>