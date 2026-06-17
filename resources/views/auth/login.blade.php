<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión - VIR TISONE STUDIO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#0f0f10] text-slate-900">

    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.10),transparent_30%),linear-gradient(135deg,#101010_0%,#1c1c1f_45%,#080808_100%)] px-4 py-6 sm:px-6 lg:px-10 flex items-center justify-center">

        <div class="w-full max-w-7xl overflow-hidden rounded-[36px] border border-white/10 bg-white shadow-[0_40px_120px_rgba(0,0,0,0.50)]">

            <div class="grid grid-cols-1 lg:grid-cols-[1.05fr_0.95fr]">

                {{-- PANEL IZQUIERDO --}}
                <section class="relative hidden lg:flex min-h-[760px] bg-[#202020] overflow-hidden">

                    <div class="absolute inset-0 bg-gradient-to-br from-[#161616] via-[#232323] to-[#101010]"></div>

                    <div class="absolute left-10 top-10 z-20">
                        <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-black/30 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.35em] text-white/80 backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-white"></span>
                            VIR TISONE STUDIO
                        </div>
                    </div>

                    <div class="relative z-10 flex h-full w-full items-center justify-center p-12 xl:p-16">
                        <img src="{{ asset('images/vir-tisone-logo-blanco1.png') }}"
                             alt="Vir Tisone Studio"
                             class="w-full max-w-[680px] object-contain drop-shadow-[0_20px_60px_rgba(255,255,255,0.08)]">
                    </div>

                    <div class="absolute bottom-8 left-10 right-10 z-20">
                        <div class="h-px bg-white/10"></div>
                        <div class="mt-5 flex items-center justify-between text-xs uppercase tracking-[0.30em] text-white/45">
                            <span>Beauty Management</span>
                            <span>Studio System</span>
                        </div>
                    </div>
                </section>

                {{-- PANEL DERECHO --}}
                <section class="relative flex min-h-[760px] items-center justify-center bg-[#f8fafc] px-6 py-10 sm:px-10 lg:px-16">

                    <div class="w-full max-w-md">

                        {{-- Logo mobile/tablet --}}
                        <div class="lg:hidden mb-8 rounded-[30px] bg-[#202020] px-6 py-8 shadow-2xl">
                            <img src="{{ asset('images/vir-tisone-logo-blanco1.png') }}"
                                 alt="Vir Tisone Studio"
                                 class="mx-auto w-full max-w-[320px] object-contain">
                        </div>

                        <div class="mb-9">
                            <div class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-[11px] font-bold uppercase tracking-[0.35em] text-slate-500 shadow-sm">
                                Acceso privado
                            </div>

                            <h1 class="mt-6 text-5xl font-black tracking-tight text-slate-950">
                                Bienvenida
                            </h1>

                            <p class="mt-4 text-lg leading-8 text-slate-500">
                                Ingresá al panel de gestión de
                                <span class="font-bold text-slate-900">Vir Tisone Studio</span>.
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

                        <form method="POST" action="{{ route('login') }}" class="space-y-5">
                            @csrf

                            <div>
                                <label for="email" class="mb-2.5 block text-sm font-bold text-slate-700">
                                    Correo electrónico
                                </label>

                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400">
                                        <span class="text-lg">✉</span>
                                    </div>

                                    <input id="email"
                                           name="email"
                                           type="email"
                                           value="{{ old('email') }}"
                                           required
                                           autofocus
                                           autocomplete="username"
                                           placeholder="ejemplo@correo.com"
                                           class="w-full rounded-[22px] border border-slate-200 bg-white py-4 pl-14 pr-5 text-base text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-slate-900 focus:ring-4 focus:ring-slate-200">
                                </div>
                            </div>

                            <div>
                                <label for="password" class="mb-2.5 block text-sm font-bold text-slate-700">
                                    Contraseña
                                </label>

                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400">
                                        <span class="text-lg">●</span>
                                    </div>

                                    <input id="password"
                                           name="password"
                                           type="password"
                                           required
                                           autocomplete="current-password"
                                           placeholder="********"
                                           class="w-full rounded-[22px] border border-slate-200 bg-white py-4 pl-14 pr-5 text-base text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-slate-900 focus:ring-4 focus:ring-slate-200">
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <label for="remember_me" class="flex items-center gap-3">
                                    <input id="remember_me"
                                           type="checkbox"
                                           name="remember"
                                           class="rounded border-slate-300 text-slate-950 focus:ring-slate-500">

                                    <span class="text-sm font-medium text-slate-600">
                                        Recordarme
                                    </span>
                                </label>
                            </div>

                            <button type="submit"
                                    class="group relative w-full overflow-hidden rounded-[22px] bg-[#050816] px-5 py-4 text-lg font-bold text-white shadow-[0_18px_45px_rgba(15,23,42,0.28)] transition hover:-translate-y-0.5 hover:bg-black">
                                <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition duration-700 group-hover:translate-x-full"></span>
                                <span class="relative">Ingresar al sistema</span>
                            </button>
                        </form>

                        <div class="mt-9 rounded-[26px] border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#050816] text-white">
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