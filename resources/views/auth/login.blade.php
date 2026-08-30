<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión - fn peluquería</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f8f6fb] text-slate-900">

    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(143,87,166,0.22),transparent_28%),radial-gradient(circle_at_bottom_right,rgba(111,62,134,0.18),transparent_30%),linear-gradient(135deg,#f8f6fb_0%,#f4eff8_45%,#fbf9fd_100%)] px-4 py-6 sm:px-6 lg:px-10 flex items-center justify-center">

        <div class="w-full max-w-7xl overflow-hidden rounded-[36px] border border-[#e7e1ec] bg-white shadow-[0_35px_90px_rgba(111,62,134,0.18)]">

            <div class="grid grid-cols-1 lg:grid-cols-[1.05fr_0.95fr]">

                {{-- PANEL IZQUIERDO --}}
                <section class="relative hidden lg:flex min-h-[760px] overflow-hidden bg-[#6f3e86]">

                    <div class="absolute inset-0 bg-gradient-to-br from-[#6f3e86] via-[#8f57a6] to-[#5c3272]"></div>
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.18),transparent_28%),radial-gradient(circle_at_bottom_right,rgba(255,255,255,0.10),transparent_25%)]"></div>

                    <div class="absolute left-10 top-10 z-20">
                        <div class="inline-flex items-center gap-3 rounded-full border border-white/20 bg-white/10 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.35em] text-white/90 backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-white"></span>
                            FN PELUQUERÍA
                        </div>
                    </div>

                    <div class="relative z-10 flex h-full w-full flex-col items-center justify-center px-12 py-16 text-center">
                        <div class="rounded-[34px] bg-white/10 p-5 shadow-[0_25px_60px_rgba(0,0,0,0.18)] backdrop-blur">
                            <img src="{{ asset('images/fn-peluqueria-logo.jpeg') }}"
                                 alt="FN Peluquería"
                                 class="w-full max-w-[420px] rounded-[26px] object-contain shadow-[0_18px_45px_rgba(0,0,0,0.12)]">
                        </div>

                        <div class="mt-10 max-w-xl">
                            <h2 class="text-4xl font-black tracking-tight text-white">
                                Gestión profesional para tu salón
                            </h2>

                            <p class="mt-4 text-lg leading-8 text-white/85">
                                Turnos, ventas, caja, stock, compras y administración
                                en un solo sistema para <span class="font-bold text-white">FN Peluquería</span>.
                            </p>
                        </div>
                    </div>

                    <div class="absolute bottom-8 left-10 right-10 z-20">
                        <div class="h-px bg-white/20"></div>
                        <div class="mt-5 flex items-center justify-between text-xs uppercase tracking-[0.30em] text-white/60">
                            <span>Beauty Management</span>
                            <span>Salon System</span>
                        </div>
                    </div>
                </section>

                {{-- PANEL DERECHO --}}
                <section class="relative flex min-h-[760px] items-center justify-center bg-[#fcfbfe] px-6 py-10 sm:px-10 lg:px-16">

                    <div class="w-full max-w-md">

                        {{-- Logo mobile/tablet --}}
                        <div class="mb-8 rounded-[30px] bg-gradient-to-br from-[#8f57a6] to-[#6f3e86] px-6 py-8 shadow-[0_25px_60px_rgba(111,62,134,0.22)] lg:hidden">
                            <img src="{{ asset('images/fn-peluqueria-logo.jpeg') }}"
                                 alt="FN Peluquería"
                                 class="mx-auto w-full max-w-[260px] rounded-[22px] object-contain shadow-lg">
                        </div>

                        <div class="mb-9">
                            <div class="inline-flex items-center rounded-full border border-[#e7e1ec] bg-white px-4 py-2 text-[11px] font-bold uppercase tracking-[0.35em] text-[#6f3e86] shadow-sm">
                                Acceso privado
                            </div>

                            <h1 class="mt-6 text-5xl font-black tracking-tight text-[#241a2c]">
                                Bienvenida
                            </h1>

                            <p class="mt-4 text-lg leading-8 text-slate-500">
                                Ingresá al panel de gestión de
                                <span class="font-bold text-[#6f3e86]">FN Peluquería</span>.
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
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5 text-[#8f57a6]">
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
                                           class="w-full rounded-[22px] border border-[#e7e1ec] bg-white py-4 pl-14 pr-5 text-base text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#8f57a6] focus:ring-4 focus:ring-[#efe6f4]">
                                </div>
                            </div>

                            <div>
                                <label for="password" class="mb-2.5 block text-sm font-bold text-slate-700">
                                    Contraseña
                                </label>

                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5 text-[#8f57a6]">
                                        <span class="text-lg">●</span>
                                    </div>

                                    <input id="password"
                                           name="password"
                                           type="password"
                                           required
                                           autocomplete="current-password"
                                           placeholder="********"
                                           class="w-full rounded-[22px] border border-[#e7e1ec] bg-white py-4 pl-14 pr-5 text-base text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#8f57a6] focus:ring-4 focus:ring-[#efe6f4]">
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <label for="remember_me" class="flex items-center gap-3">
                                    <input id="remember_me"
                                           type="checkbox"
                                           name="remember"
                                           class="rounded border-slate-300 text-[#6f3e86] focus:ring-[#8f57a6]">

                                    <span class="text-sm font-medium text-slate-600">
                                        Recordarme
                                    </span>
                                </label>
                            </div>

                            <button type="submit"
                                    class="group relative w-full overflow-hidden rounded-[22px] bg-gradient-to-r from-[#8f57a6] to-[#6f3e86] px-5 py-4 text-lg font-bold text-white shadow-[0_18px_45px_rgba(111,62,134,0.28)] transition hover:-translate-y-0.5 hover:shadow-[0_22px_50px_rgba(111,62,134,0.34)]">
                                <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition duration-700 group-hover:translate-x-full"></span>
                                <span class="relative">Ingresar al sistema</span>
                            </button>
                        </form>

                        <div class="mt-9 rounded-[26px] border border-[#e7e1ec] bg-white p-4 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#8f57a6] text-white shadow-md">
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