@extends('layouts.admin')

@section('title', 'Nueva venta - FN Peluquería')
@section('h1', 'Nueva Venta')
@section('sub', 'Ingresar venta de servicios y/o productos.')

@section('content')

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Hay errores:</div>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $clienteSeleccionadoId = old('cliente_id', $turno?->cliente_id);
    $clienteSeleccionado = $turno ? $clientes->firstWhere('id', $clienteSeleccionadoId) : null;
    $clienteSeleccionadoTexto = $clienteSeleccionado
        ? $clienteSeleccionado->apellido . ' ' . $clienteSeleccionado->nombre . ' - ' . $clienteSeleccionado->telefono
        : old('cliente_buscar');
@endphp

@if($turno)
    <div class="mb-4 rounded-xl border border-purple-200 bg-purple-50 px-4 py-3 text-[#6f3e86]">
        <div class="font-semibold">Venta generada desde turno</div>
        <div class="text-sm">Clienta: {{ $turno->cliente->nombre }} {{ $turno->cliente->apellido }}</div>
        <div class="text-sm">Turno: {{ $turno->inicio->format('d/m/Y H:i') }}</div>
    </div>
@endif

<form method="POST" action="{{ route('ventas.store') }}" id="formVenta">
    @csrf
    @if($turno)
        <input type="hidden" name="turno_id" value="{{ $turno->id }}">
    @endif

    <div class="fn-feature-panel rounded-2xl p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-3">
                <label class="text-sm font-semibold text-slate-200">Fecha *</label>
                <input type="datetime-local"
                       name="fecha"
                       value="{{ old('fecha', now()->format('Y-m-d\TH:i')) }}"
                       class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white">
            </div>

            <div class="md:col-span-3">
                <label class="text-sm font-semibold text-slate-200">Vendedora (opcional)</label>
                <select id="vendedora_id"
                        name="vendedora_id"
                        class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white">
                    <option value="" data-pct="0">-</option>
                    @foreach($vendedoras as $c)
                        <option value="{{ $c->id }}"
                                data-pct="{{ (float)$c->comision_pct }}"
                            {{ (string)old('vendedora_id', $turno?->colaboradora_id) === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->nombre }} {{ $c->apellido }}
                            ({{ number_format((float)$c->comision_pct, 2, ',', '.') }}%)
                        </option>
                    @endforeach
                </select>
                <div class="text-xs text-slate-300 mt-1">La comisión se calcula sobre productos.</div>
            </div>

            <div class="md:col-span-6">
                <label class="text-sm font-semibold text-slate-200">Tipo de cliente *</label>
                <div class="mt-2 flex flex-wrap gap-4">
                    <label class="flex items-center gap-2">
                        <input type="radio"
                               name="tipo_cliente"
                               value="cliente"
                            {{ old('tipo_cliente', 'cliente') === 'cliente' ? 'checked' : '' }}>
                        <span>Cliente</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="radio"
                               name="tipo_cliente"
                               value="colaboradora"
                            {{ old('tipo_cliente') === 'colaboradora' ? 'checked' : '' }}>
                        <span>Colaboradora (productos a costo)</span>
                    </label>
                </div>
            </div>

            <div id="box_cliente" class="md:col-span-3">
                <label class="text-sm font-semibold text-slate-200">Cliente *</label>

                <input id="cliente_buscar"
                       list="datalist_clientes"
                       placeholder="Escribí para buscar..."
                       class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white"
                       value="{{ $clienteSeleccionadoTexto }}">

                <datalist id="datalist_clientes">
                    @foreach($clientes as $cl)
                        <option value="{{ $cl->apellido }} {{ $cl->nombre }} - {{ $cl->telefono }}"></option>
                    @endforeach
                </datalist>

                <input type="hidden" name="cliente_id" id="cliente_id" value="{{ $clienteSeleccionadoId }}">
                <div class="text-xs text-slate-300 mt-1">Si no existe, crearlo en “Clientes”.</div>
            </div>

            <div id="box_colab" class="md:col-span-3 hidden">
                <label class="text-sm font-semibold text-slate-200">Colaboradora *</label>

                <input id="colab_buscar"
                       list="datalist_colabs"
                       placeholder="Escribí para buscar..."
                       class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white"
                       value="{{ old('colab_buscar') }}">

                <datalist id="datalist_colabs">
                    @foreach($colaboradoras as $c)
                        <option value="{{ $c->apellido }} {{ $c->nombre }}"></option>
                    @endforeach
                </datalist>

                <input type="hidden"
                       name="cliente_colaboradora_id"
                       id="cliente_colaboradora_id"
                       value="{{ old('cliente_colaboradora_id') }}">

                <div class="text-xs text-slate-300 mt-1">
                    Los productos se cobran a costo.
                </div>
            </div>

            <div class="md:col-span-6">
                <label class="text-sm font-semibold text-slate-200">Notas</label>
                <textarea name="notas"
                          rows="2"
                          class="mt-1 w-full rounded-xl border-slate-700 bg-slate-800 text-white focus:border-white focus:ring-white"
                          placeholder="Ej: color usado, observaciones, etc.">{{ old('notas') }}</textarea>
            </div>
        </div>
    </div>

    <div class="mt-6 flex gap-2">
        <button type="button"
                id="tabServicios"
                class="fn-primary-action">
            Servicios
        </button>

        <button type="button"
                id="tabProductos"
                class="fn-secondary-action">
            Productos
        </button>
    </div>

    <div id="panelServicios" class="fn-table-shell mt-4">
        <div class="p-4 border-b flex items-center justify-between">
            <div>
                <div class="text-lg font-bold text-slate-900">Servicios</div>
                <div class="text-sm text-slate-600">
                    Los precios cargados acá son precios base de efectivo/transferencia.
                </div>
            </div>

            <button type="button"
                    id="addServicio"
                    class="fn-primary-action">
                + Agregar servicio
            </button>
        </div>

        <div class="p-4 overflow-x-auto">
            <table class="min-w-full bg-white" id="tablaServicios">
                <thead class="bg-slate-100 text-slate-700">
                <tr>
                    <th class="text-left px-3 py-2 text-sm font-semibold">Servicio</th>
                    <th class="text-left px-3 py-2 text-sm font-semibold">Precio base</th>
                    <th class="text-left px-3 py-2 text-sm font-semibold">Detalle</th>
                    <th class="text-right px-3 py-2 text-sm font-semibold">Quitar</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-slate-600">Subtotal servicios</div>
                <div class="text-2xl font-bold" id="subtotalServicios">$0,00</div>
            </div>
        </div>
    </div>

    <div id="panelProductos" class="fn-table-shell mt-4 hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <div>
                <div class="text-lg font-bold text-slate-900">Productos</div>
                <div class="text-sm text-slate-600">
                    Muestra stock y calcula el precio base de efectivo/transferencia.
                </div>
            </div>

            <button type="button"
                    id="addProducto"
                    class="fn-primary-action">
                + Agregar producto
            </button>
        </div>

        <div class="p-4">
            <div class="fn-soft-panel mb-4 p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-slate-700">Escanear producto</label>
                        <input id="scan_producto"
                               type="text"
                               autocomplete="off"
                               placeholder="Hacé click acá y escaneá el código..."
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

                        <div class="text-xs text-slate-500 mt-1">
                            Si el producto ya está agregado, suma 1 a la cantidad.
                        </div>
                    </div>

                    <div class="text-sm text-slate-500">
                        El lector funciona como teclado.
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white" id="tablaProductos">
                    <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="text-left px-3 py-2 text-sm font-semibold">Producto</th>
                        <th class="text-left px-3 py-2 text-sm font-semibold">Stock</th>
                        <th class="text-left px-3 py-2 text-sm font-semibold">Cant.</th>
                        <th class="text-left px-3 py-2 text-sm font-semibold">Unit. base</th>
                        <th class="text-left px-3 py-2 text-sm font-semibold">Subtotal</th>
                        <th class="text-right px-3 py-2 text-sm font-semibold">Quitar</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="rounded-xl border bg-slate-50 p-3 flex items-center justify-between">
                    <div class="text-sm text-slate-600">Subtotal productos</div>
                    <div class="text-xl font-bold" id="subtotalProductos">$0,00</div>
                </div>

                <div class="rounded-xl border bg-slate-50 p-3 flex items-center justify-between">
                    <div class="text-sm text-slate-600">Comisión vendedora</div>
                    <div class="text-xl font-bold" id="montoComision">$0,00</div>
                </div>
            </div>
        </div>
    </div>

    <section class="fn-checkout mt-8 overflow-hidden rounded-3xl border">
        <div class="flex flex-col gap-4 border-b border-slate-800 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-blue-400/20 bg-blue-500/10 text-blue-300 shadow-lg shadow-blue-950/40">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </span>

                <div>
                    <div class="text-xs font-bold uppercase tracking-widest text-blue-400">
                        Finalizar venta
                    </div>
                    <h2 class="mt-1 text-xl font-extrabold tracking-tight text-white sm:text-2xl">
                        Resumen y forma de pago
                    </h2>
                </div>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-2 text-xs font-semibold text-emerald-300 shadow-inner shadow-emerald-950/20">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                </span>
                Totales actualizados automáticamente
            </div>
        </div>

        <div class="p-5 sm:p-7">
            <div class="grid max-w-full grid-cols-1 gap-5 overflow-hidden xl:grid-cols-3">
                <div class="min-w-0">
                    <div class="relative h-full overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-900 via-slate-950 to-black p-5 text-white shadow-2xl shadow-black/30 sm:p-6">
                        <div class="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-blue-500/25 blur-2xl"></div>
                        <div class="pointer-events-none absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-violet-500/10 blur-3xl"></div>
                        <div class="relative text-xs font-semibold uppercase tracking-wider text-blue-300">
                            Total a cobrar
                        </div>

                        <div class="relative mt-2 break-words text-4xl font-extrabold tracking-tight text-white sm:text-5xl"
                             id="totalFinal">
                            $0,00
                        </div>

                        <div class="relative mt-6 overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04] backdrop-blur-sm">
                            <div class="flex items-center justify-between gap-4 border-b border-slate-800 px-4 py-3.5">
                                <span class="text-sm text-slate-400">Total base</span>
                                <strong id="totalBaseResumen" class="text-base font-bold text-white">$0,00</strong>
                            </div>

                            <div class="flex items-center justify-between gap-4 px-4 py-3.5">
                                <span class="flex items-center gap-2 text-sm text-slate-400">
                                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                                    Recargo tarjeta
                                </span>
                                <strong id="recargoTarjetaResumen" class="text-base font-bold text-white">$0,00</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contents">
                    <div class="contents">
                        <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-700/80 bg-slate-900/80 p-5 shadow-lg shadow-black/10 transition hover:border-slate-600 hover:bg-slate-900">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-blue-400/20 bg-blue-500/10 text-blue-300">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-5 w-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M12 6v12m3-9.75C15 7.007 13.657 6 12 6S9 7.007 9 8.25s1.343 2.25 3 2.25 3 1.007 3 2.25S13.657 15 12 15s-3-1.007-3-2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </span>

                                <div>
                                    <label for="tipo_pago" class="block text-sm font-bold text-white">
                                        Forma de pago <span class="text-blue-400">*</span>
                                    </label>
                                    <p class="mt-0.5 text-xs text-slate-400">
                                        Elegí cómo abona la clienta.
                                    </p>
                                </div>
                            </div>

                            <div class="relative mt-4">
                                <select id="tipo_pago"
                                        name="tipo_pago"
                                        class="w-full appearance-none rounded-xl border-slate-700 bg-slate-950/80 py-3 pl-4 pr-10 text-sm font-semibold text-white shadow-inner shadow-black/20 transition hover:border-slate-500 focus:border-blue-500 focus:bg-slate-950 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50">
                                    <option value="efectivo" {{ old('tipo_pago', 'efectivo') === 'efectivo' ? 'selected' : '' }}>
                                        Efectivo
                                    </option>
                                    <option value="transferencia" {{ old('tipo_pago') === 'transferencia' ? 'selected' : '' }}>
                                        Transferencia
                                    </option>
                                    <option value="tarjeta" {{ old('tipo_pago') === 'tarjeta' ? 'selected' : '' }}>
                                        Tarjeta
                                    </option>
                                    <option value="combinado" {{ old('tipo_pago') === 'combinado' ? 'selected' : '' }}>
                                        Pago combinado
                                    </option>
                                </select>

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 9-7.5 7.5L4.5 9"/>
                                </svg>
                            </div>

                            <div class="mt-3 flex items-start gap-2 text-xs leading-5 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="mt-0.5 h-4 w-4 shrink-0 text-blue-400"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M11.25 11.25 12 10.5m0 0 .75-.75M12 10.5v4.125m9-2.625a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                Solo el importe abonado con tarjeta lleva un 20% de recargo.
                            </div>
                        </div>

                        <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-700/80 bg-slate-900/80 p-5 shadow-lg shadow-black/10 transition hover:border-slate-600 hover:bg-slate-900">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-violet-400/20 bg-violet-500/10 text-violet-300">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-5 w-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </span>

                                <div>
                                    <div class="text-sm font-bold text-white">
                                        Condición de pago <span class="text-blue-400">*</span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-slate-400">
                                        Indicá si el cobro queda pendiente.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 min-w-0">
                                <label for="condicion_pago_ui" class="block text-sm font-semibold text-slate-200">Condición de pago</label>
                                <div class="relative mt-1">
                                    <select id="condicion_pago_ui" class="w-full min-w-0 appearance-none rounded-xl border-slate-700 bg-slate-950/80 py-3 pl-4 pr-12 text-sm font-semibold text-white shadow-inner shadow-black/20 focus:border-violet-400 focus:ring-violet-400">
                                        <option value="completo">Paga el total ahora</option>
                                        <option value="parcial">Paga una parte</option>
                                        <option value="pendiente">Todo queda pendiente</option>
                                    </select>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 9-7.5 7.5L4.5 9"/>
                                    </svg>
                                </div>
                                <input type="hidden" name="condicion_pago" id="condicion_pago" value="completo">
                                <input type="checkbox" name="pendiente_pago" id="pendiente_pago" value="1" class="hidden" aria-hidden="true" {{ old('pendiente_pago') ? 'checked' : '' }}>
                            </div>

                            <div id="monto_pago_parcial_box" class="hidden mt-4 min-w-0">
                                <label for="monto_pago" class="block text-sm font-semibold text-slate-200">Importe base que abona ahora <span class="text-blue-400">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="monto_pago" id="monto_pago" class="mt-1 w-full min-w-0 rounded-xl border-slate-700 bg-slate-950/80 text-white focus:border-violet-400 focus:ring-violet-400" placeholder="Ej: 30000">
                                <p class="mt-2 text-xs leading-5 text-slate-400">En pago combinado, distribuí el importe en los campos de abajo.</p>
                            </div>

                            <div id="resumen_pago_parcial" class="hidden mt-4 grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="min-w-0 rounded-xl border border-blue-400/30 bg-blue-400/10 p-3">
                                    <span class="block text-xs text-slate-400">Base abonada hoy</span>
                                    <strong id="base_abonada_hoy" class="mt-1 block break-words text-base text-white">$0,00</strong>
                                </div>
                                <div class="min-w-0 rounded-xl border border-violet-400/30 bg-violet-400/10 p-3">
                                    <span class="block text-xs text-slate-400">Saldo base pendiente</span>
                                    <strong id="saldo_base_pendiente" class="mt-1 block break-words text-base text-white">$0,00</strong>
                                </div>
                            </div>                            <div class="mt-3 flex items-start gap-2 text-xs leading-5 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="mt-0.5 h-4 w-4 shrink-0 text-violet-400"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                Si queda pendiente, no se guarda ninguna forma de pago hasta que la clienta abone.
                            </div>
                        </div>
                    </div>

                    <div id="boxPagoCombinado"
                         class="col-span-full hidden min-w-0 max-w-full overflow-hidden rounded-2xl border border-blue-400/20 bg-blue-500/[0.06] p-5 shadow-inner shadow-blue-950/20">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2 font-bold text-white">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500 text-xs text-white shadow-lg shadow-blue-950/40">
                                        %
                                    </span>
                                    Distribución del total base
                                </div>
                                <div class="mt-1.5 text-xs leading-5 text-slate-400">
                                    Los importes deben sumar el total base. El 20% se agrega únicamente a la parte de tarjeta.
                                </div>
                            </div>

                            <span class="inline-flex w-fit rounded-full border border-blue-400/20 bg-blue-400/10 px-3 py-1 text-xs font-semibold text-blue-300">
                                Pago combinado
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="rounded-xl border border-slate-700 bg-slate-900/80 p-3 shadow-lg shadow-black/10">
                                <label for="pago_efectivo" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Parte en efectivo
                                </label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="pagos[efectivo]"
                                       id="pago_efectivo"
                                       value="{{ old('pagos.efectivo', 0) }}"
                                       class="pago-combinado mt-2 w-full rounded-xl border-slate-700 bg-slate-950/80 text-white focus:border-blue-500 focus:bg-slate-950 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50">
                            </div>

                            <div class="rounded-xl border border-slate-700 bg-slate-900/80 p-3 shadow-lg shadow-black/10">
                                <label for="pago_transferencia" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Parte en transferencia
                                </label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="pagos[transferencia]"
                                       id="pago_transferencia"
                                       value="{{ old('pagos.transferencia', 0) }}"
                                       class="pago-combinado mt-2 w-full rounded-xl border-slate-700 bg-slate-950/80 text-white focus:border-blue-500 focus:bg-slate-950 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50">
                            </div>

                            <div class="rounded-xl border border-slate-700 bg-slate-900/80 p-3 shadow-lg shadow-black/10">
                                <label for="pago_tarjeta" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Parte en tarjeta
                                </label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="pagos[tarjeta]"
                                       id="pago_tarjeta"
                                       value="{{ old('pagos.tarjeta', 0) }}"
                                       class="pago-combinado mt-2 w-full rounded-xl border-slate-700 bg-slate-950/80 text-white focus:border-blue-500 focus:bg-slate-950 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50">

                                <div class="mt-2 text-xs text-blue-300">
                                    Total con recargo: <strong id="tarjetaFinalPreview">$0,00</strong>
                                </div>
                            </div>
                        </div>

                        <div id="estadoDistribucionPago"
                             class="mt-4 flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-sm font-medium text-slate-300">
                            Falta asignar: $0,00
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-800 bg-slate-950/80 px-5 py-5 sm:px-7">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button type="button"
                            id="btnDescuento"
                            class="fn-secondary-action w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M9 14.25 15 8.25m4.5 3.75a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0ZM9.75 9h.008v.008H9.75V9Zm4.5 6h.008v.008h-.008V15Z"/>
                        </svg>
                        Aplicar descuento
                    </button>

                    <a href="{{ route('ventas.index') }}"
                       class="fn-secondary-action w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                        Cancelar
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <a href="https://www.afip.gob.ar/"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="fn-secondary-action w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M6.75 3h10.5A1.75 1.75 0 0 1 19 4.75v14.5A1.75 1.75 0 0 1 17.25 21H6.75A1.75 1.75 0 0 1 5 19.25V4.75A1.75 1.75 0 0 1 6.75 3ZM8 7h8M8 11h2m2 0h2m2 0h0M8 15h2m2 0h2m2 0h0"/>
                        </svg>
                        Facturar
                    </a>

                    <button type="submit"
                            class="fn-primary-action w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2.25">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                        Registrar venta
                    </button>
                </div>
            </div>

            <div class="mt-4 flex items-start gap-2 text-xs leading-5 text-slate-500 xl:justify-end">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="mt-0.5 h-4 w-4 shrink-0"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 0h10.5A1.75 1.75 0 0 1 19 12.25v7A1.75 1.75 0 0 1 17.25 21H6.75A1.75 1.75 0 0 1 5 19.25v-7a1.75 1.75 0 0 1 1.75-1.75Z"/>
                </svg>
                Revisá el total y la forma de pago antes de registrar la venta.
            </div>
        </div>
    </section>

    <div id="modalDescuento" class="fixed inset-0 hidden items-center justify-center bg-black/40 p-4 z-50">
        <div class="fn-modal-card w-full max-w-3xl p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-lg font-bold">Aplicar descuento</div>
                    <div class="text-sm text-slate-600">Seleccioná ítems y porcentaje.</div>
                </div>
            <button type="button" id="closeDescuento" class="fn-icon-action text-slate-500">✖</button>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">% descuento</label>
                    <input id="descuentoPct"
                           type="number"
                           min="0"
                           max="100"
                           step="0.01"
                           value="0"
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div class="flex items-center gap-2">
                    <input id="selTodo"
                           type="checkbox"
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                    <label for="selTodo" class="text-sm font-semibold text-slate-700">Seleccionar todo</label>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border p-3">
                    <div class="font-semibold mb-2">Servicios</div>
                    <div id="listaDescServicios" class="space-y-2 text-sm text-slate-700"></div>
                </div>

                <div class="rounded-2xl border p-3">
                    <div class="font-semibold mb-2">Productos</div>
                    <div id="listaDescProductos" class="space-y-2 text-sm text-slate-700"></div>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button"
                        id="aplicarDescuento"
                        class="fn-primary-action">
                    Aplicar
                </button>
            </div>
        </div>
    </div>
</form>

<div id="toastProductoAgregado"
     class="fixed top-5 right-5 z-[9999] hidden rounded-2xl bg-green-600 text-white px-4 py-3 shadow-xl">
    Producto agregado
</div>

@php
    $clientesMap = [];
    foreach ($clientes as $cl) {
        $clientesMap[$cl->apellido.' '.$cl->nombre.' - '.$cl->telefono] = $cl->id;
    }

    $colabsMap = [];
    foreach ($colaboradoras as $c) {
        $colabsMap[$c->apellido.' '.$c->nombre] = $c->id;
    }

    $serviciosMap = [];
    $serviciosPrecio = [];
    foreach ($servicios as $s) {
        $serviciosMap[$s->nombre] = $s->id;
        $serviciosPrecio[$s->id] = (float)$s->precio;
    }

    $productosMap = [];
    $productosData = [];
    $productosBarcodeMap = [];

    foreach ($productos as $p) {
        $label = trim(($p->marca.' - '.$p->tipo.' '.$p->contenido));
        $productosMap[$label] = $p->id;

        $productosData[$p->id] = [
            'label' => $label,
            'precio_manual' => (float)$p->precio_venta,
            'precio_efectivo_manual' => $p->precio_efectivo_manual !== null ? (float)$p->precio_efectivo_manual : null,
            'ultimo_costo' => $p->ultimo_costo !== null ? (float)$p->ultimo_costo : null,
            'ultimo_costo_at' => $p->ultimo_costo_at ?? null,
            'precio_manual_updated_at' => $p->precio_manual_updated_at ?? null,
            'stock_venta' => (int)$p->stock_venta,
            'codigo_barra' => $p->codigo_barra,
        ];

        if (!empty($p->codigo_barra)) {
            $productosBarcodeMap[(string)$p->codigo_barra] = $p->id;
        }
    }
@endphp

<script>
const CLIENTES_MAP = @json($clientesMap);
const COLABS_MAP = @json($colabsMap);
const SERVICIOS_MAP = @json($serviciosMap);
const SERVICIOS_PRECIO = @json($serviciosPrecio);
const PRODUCTOS_MAP = @json($productosMap);
const PRODUCTOS_DATA = @json($productosData);
const PRODUCTOS_BARCODE_MAP = @json($productosBarcodeMap);

let TOTAL_BASE_ACTUAL = 0;

function round2(n){
    return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
}

function money(n){
    n = round2(n);
    return '$' + n.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function mostrarToastProductoAgregado(texto = 'Producto agregado'){
    const toast = document.getElementById('toastProductoAgregado');
    if(!toast) return;

    toast.textContent = texto;
    toast.classList.remove('hidden');

    clearTimeout(window._toastProductoTimeout);
    window._toastProductoTimeout = setTimeout(() => {
        toast.classList.add('hidden');
    }, 1600);
}

function esPagoPendiente(){
    return document.getElementById('pendiente_pago')?.checked === true;
}

function getTipoPago(){
    return document.getElementById('tipo_pago')?.value || 'efectivo';
}

function esClienteColab(){
    return document.querySelector('input[name="tipo_cliente"]:checked')?.value === 'colaboradora';
}

function vendedoraPct(){
    const sel = document.getElementById('vendedora_id');
    const opt = sel?.options[sel.selectedIndex];
    return Number(opt?.dataset?.pct || 0);
}

function bindDatalist(inputEl, mapObj, hiddenEl, preservarIdInicial = false){
    const textoInicial = inputEl.value;
    const idInicial = hiddenEl.value;
    const setId = () => {
        // Preservar el ID precargado aunque dos clientas compartan el mismo texto.
        if(preservarIdInicial && idInicial && inputEl.value === textoInicial){
            hiddenEl.value = idInicial;
            return;
        }
        const v = (inputEl.value || '').trim();
        hiddenEl.value = mapObj[v] ? String(mapObj[v]) : '';
    };

    inputEl.addEventListener('change', setId);
    inputEl.addEventListener('blur', setId);
}

function manualEsMasNuevoQueCompra(p){
    if(p.precio_efectivo_manual === null || !p.precio_manual_updated_at){
        return false;
    }

    if(!p.ultimo_costo_at){
        return true;
    }

    return new Date(p.precio_manual_updated_at).getTime()
        >= new Date(p.ultimo_costo_at).getTime();
}

function precioUnitarioEfectivoVenta(pid){
    const p = PRODUCTOS_DATA[pid];

    if(!p) return 0;

    if(manualEsMasNuevoQueCompra(p)){
        return round2(p.precio_efectivo_manual);
    }

    if(p.ultimo_costo !== null){
        return round2(p.ultimo_costo * 1.40);
    }

    if(p.precio_efectivo_manual !== null){
        return round2(p.precio_efectivo_manual);
    }

    return round2(p.precio_manual);
}

function precioUnitarioCosto(pid){
    const p = PRODUCTOS_DATA[pid];

    if(!p) return 0;

    /*
        Si el precio manual es más nuevo que la última compra,
        usamos el precio efectivo vigente y quitamos el 40%.
    */
    if(manualEsMasNuevoQueCompra(p)){
        return round2(Number(p.precio_efectivo_manual || 0) / 1.40);
    }

    /* Si la compra es más reciente, usamos el costo real. */
    if(p.ultimo_costo !== null){
        return round2(p.ultimo_costo);
    }

    /* Producto sin compras: costo estimado desde el precio vigente. */
    if(p.precio_efectivo_manual !== null){
        return round2(Number(p.precio_efectivo_manual) / 1.40);
    }

    return round2(Number(p.precio_manual || 0) / 1.40);
}

function getDescPct(tr){
    return Number(tr.querySelector('input.desc-pct')?.value || 0);
}

function applyDesc(valor, pct){
    return round2(valor * (1 - (pct / 100)));
}

function recalcularPago(totalBase){
    TOTAL_BASE_ACTUAL = round2(totalBase);

    const tipoPago = document.getElementById('tipo_pago');
    const pendiente = document.getElementById('pendiente_pago');
    const boxCombinado = document.getElementById('boxPagoCombinado');
    const totalBaseResumen = document.getElementById('totalBaseResumen');
    const recargoResumen = document.getElementById('recargoTarjetaResumen');
    const totalFinal = document.getElementById('totalFinal');
    const estado = document.getElementById('estadoDistribucionPago');
    const tarjetaPreview = document.getElementById('tarjetaFinalPreview');
    const inputsCombinados = document.querySelectorAll('.pago-combinado');

    if(totalBaseResumen){
        totalBaseResumen.textContent = money(TOTAL_BASE_ACTUAL);
    }

    if(esPagoPendiente()){
        tipoPago.disabled = true;
        boxCombinado.classList.add('hidden');
        inputsCombinados.forEach(i => i.disabled = true);

        recargoResumen.textContent = money(0);
        totalFinal.textContent = money(TOTAL_BASE_ACTUAL);
        return;
    }

    tipoPago.disabled = false;

    if(getTipoPago() !== 'combinado'){
        boxCombinado.classList.add('hidden');
        inputsCombinados.forEach(i => i.disabled = true);

        const recargo = getTipoPago() === 'tarjeta'
            ? round2(TOTAL_BASE_ACTUAL * 0.20)
            : 0;

        recargoResumen.textContent = money(recargo);
        totalFinal.textContent = money(TOTAL_BASE_ACTUAL + recargo);
        return;
    }

    boxCombinado.classList.remove('hidden');
    inputsCombinados.forEach(i => i.disabled = false);

    const efectivo = Number(document.getElementById('pago_efectivo')?.value || 0);
    const transferencia = Number(document.getElementById('pago_transferencia')?.value || 0);
    const tarjeta = Number(document.getElementById('pago_tarjeta')?.value || 0);

    const asignado = round2(efectivo + transferencia + tarjeta);
    const diferencia = round2(TOTAL_BASE_ACTUAL - asignado);
    const recargo = round2(tarjeta * 0.20);
    const tarjetaFinal = round2(tarjeta + recargo);
    const totalConRecargo = round2(TOTAL_BASE_ACTUAL + recargo);

    recargoResumen.textContent = money(recargo);
    totalFinal.textContent = money(totalConRecargo);
    tarjetaPreview.textContent = money(tarjetaFinal);

    estado.classList.remove(
        'border-slate-700',
        'text-slate-300',
        'bg-slate-950/70',
        'border-emerald-400/40',
        'text-emerald-300',
        'bg-emerald-400/10',
        'border-rose-400/40',
        'text-rose-300',
        'bg-rose-400/10'
    );

    if(Math.abs(diferencia) < 0.01){
        estado.textContent = 'Distribución correcta.';
        estado.classList.add('border-emerald-400/40', 'text-emerald-300', 'bg-emerald-400/10');
    } else if(diferencia > 0){
        estado.textContent = 'Falta asignar: ' + money(diferencia);
        estado.classList.add('border-slate-700', 'text-slate-300', 'bg-slate-950/70');
    } else {
        estado.textContent = 'Se excede por: ' + money(Math.abs(diferencia));
        estado.classList.add('border-rose-400/40', 'text-rose-300', 'bg-rose-400/10');
    }
}

function recalcular(){
    let subServ = 0;

    document.querySelectorAll('#tablaServicios tbody tr').forEach(tr => {
        const precioBase = Number(tr.querySelector('input.precio-serv')?.value || 0);
        const precioFinalBase = applyDesc(precioBase, getDescPct(tr));

        const precioShow = tr.querySelector('.precio-show');
        if(precioShow){
            precioShow.textContent = money(precioFinalBase);
        }

        subServ += precioFinalBase;
    });

    subServ = round2(subServ);
    document.getElementById('subtotalServicios').textContent = money(subServ);

    let subProd = 0;

    document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
        const pid = Number(tr.querySelector('input.prod-id')?.value || 0);
        const qty = Number(tr.querySelector('input.cant')?.value || 0);
        const stockCell = tr.querySelector('.stock');
        const unitCell = tr.querySelector('.unit-main');
        const unitFinalCell = tr.querySelector('.unit-final');
        const subCell = tr.querySelector('.sub');

        if(!pid || qty <= 0){
            if(stockCell) stockCell.textContent = '-';
            if(unitCell) unitCell.textContent = '-';
            if(unitFinalCell) unitFinalCell.textContent = '';
            if(subCell) subCell.textContent = '-';
            return;
        }

        const p = PRODUCTOS_DATA[pid];
        const stock = Number(p.stock_venta || 0);
        const unitOriginal = esClienteColab()
            ? precioUnitarioCosto(pid)
            : precioUnitarioEfectivoVenta(pid);

        const unitFinal = applyDesc(unitOriginal, getDescPct(tr));
        const sub = round2(unitFinal * qty);

        stockCell.textContent = stock;
        unitCell.textContent = money(unitOriginal);
        unitFinalCell.textContent = getDescPct(tr) > 0 ? 'Final: ' + money(unitFinal) : '';
        subCell.textContent = money(sub);

        stockCell.classList.toggle('text-red-700', qty > stock);
        stockCell.classList.toggle('font-bold', qty > stock);

        subProd += sub;
    });

    subProd = round2(subProd);
    document.getElementById('subtotalProductos').textContent = money(subProd);

    const pct = vendedoraPct();
    let baseComision = 0;

    if(!esClienteColab() && pct > 0){
        document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
            const pid = Number(tr.querySelector('input.prod-id')?.value || 0);
            const qty = Number(tr.querySelector('input.cant')?.value || 0);

            if(!pid || qty <= 0) return;

            const unitFinal = applyDesc(
                precioUnitarioEfectivoVenta(pid),
                getDescPct(tr)
            );

            baseComision += round2(unitFinal * qty);
        });
    }

    const comision = !esClienteColab() && pct > 0
        ? round2(baseComision * (pct / 100))
        : 0;

    document.getElementById('montoComision').textContent = money(comision);

    recalcularPago(round2(subServ + subProd));
}

function addServicioRow(){
    const tbody = document.querySelector('#tablaServicios tbody');
    const idx = tbody.children.length;
    const tr = document.createElement('tr');

    tr.className = 'border-t';
    tr.innerHTML = `
        <td class="px-3 py-2">
            <input list="dl_servicios_${idx}"
                   class="serv-text w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                   placeholder="Escribí para buscar...">

            <datalist id="dl_servicios_${idx}">
                ${Object.keys(SERVICIOS_MAP).map(n => `<option value="${n}"></option>`).join('')}
            </datalist>

            <input type="hidden" name="servicios[${idx}][servicio_id]" class="serv-id" value="">
            <input type="hidden" name="servicios[${idx}][descuento_pct]" class="desc-pct" value="0">
        </td>

        <td class="px-3 py-2">
            <input name="servicios[${idx}][precio]"
                   type="number"
                   step="0.01"
                   min="0"
                   value="0"
                   class="precio-serv w-32 rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

            <div class="text-xs text-slate-500 mt-1">
                Final base: <span class="precio-show">$0,00</span>
            </div>
        </td>

        <td class="px-3 py-2">
            <textarea name="servicios[${idx}][detalle]"
                      rows="2"
                      class="w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                      placeholder="Ej: color, observación..."></textarea>
        </td>

        <td class="px-3 py-2 text-right">
            <button type="button"
                    class="btn-remove-servicio fn-icon-action fn-icon-action-danger">
                🗑️
            </button>
        </td>
    `;

    const inputText = tr.querySelector('.serv-text');
    const inputId = tr.querySelector('.serv-id');
    const precioInput = tr.querySelector('.precio-serv');

    const setServicio = () => {
        const valor = (inputText.value || '').trim();
        const sid = SERVICIOS_MAP[valor] ? Number(SERVICIOS_MAP[valor]) : 0;

        inputId.value = sid ? String(sid) : '';

        if(sid && SERVICIOS_PRECIO[sid] !== undefined){
            precioInput.value = String(SERVICIOS_PRECIO[sid]);
        }

        recalcular();
    };

    inputText.addEventListener('change', setServicio);
    inputText.addEventListener('blur', setServicio);
    precioInput.addEventListener('input', recalcular);

    tr.querySelector('.btn-remove-servicio').addEventListener('click', () => {
        tr.remove();
        recalcular();
    });

    tbody.appendChild(tr);
    recalcular();
}

function addProductoRow(prefillPid = null, prefillQty = 1){
    const tbody = document.querySelector('#tablaProductos tbody');
    const idx = tbody.children.length;
    const tr = document.createElement('tr');

    tr.className = 'border-t';
    tr.innerHTML = `
        <td class="px-3 py-2">
            <input list="dl_productos_${idx}"
                   class="prod-text w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                   placeholder="Escribí para buscar...">

            <datalist id="dl_productos_${idx}">
                ${Object.keys(PRODUCTOS_MAP).map(n => `<option value="${n}"></option>`).join('')}
            </datalist>

            <input type="hidden" name="productos[${idx}][producto_id]" class="prod-id" value="">
            <input type="hidden" name="productos[${idx}][descuento_pct]" class="desc-pct" value="0">
        </td>

        <td class="px-3 py-2 stock">-</td>

        <td class="px-3 py-2">
            <input name="productos[${idx}][cantidad]"
                   type="number"
                   min="1"
                   value="${prefillQty}"
                   class="cant w-24 rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        </td>

        <td class="px-3 py-2">
            <div class="unit-main">-</div>
            <div class="unit-final text-xs text-slate-500 mt-1"></div>
        </td>

        <td class="px-3 py-2 sub">-</td>

        <td class="px-3 py-2 text-right">
            <button type="button"
                    class="btn-remove-producto fn-icon-action fn-icon-action-danger">
                🗑️
            </button>
        </td>
    `;

    const inputText = tr.querySelector('.prod-text');
    const inputId = tr.querySelector('.prod-id');

    const setProducto = () => {
        const valor = (inputText.value || '').trim();
        const pid = PRODUCTOS_MAP[valor] ? Number(PRODUCTOS_MAP[valor]) : 0;

        inputId.value = pid ? String(pid) : '';
        recalcular();
    };

    inputText.addEventListener('change', setProducto);
    inputText.addEventListener('blur', setProducto);
    tr.querySelector('.cant').addEventListener('input', recalcular);

    tr.querySelector('.btn-remove-producto').addEventListener('click', () => {
        tr.remove();
        reindexarFilasProductos();

        if(!tbody.children.length){
            addProductoRow();
            return;
        }

        recalcular();
    });

    tbody.appendChild(tr);

    if(prefillPid && PRODUCTOS_DATA[prefillPid]){
        inputText.value = PRODUCTOS_DATA[prefillPid].label;
        inputId.value = String(prefillPid);
    }

    recalcular();
}

function filaProductoRealmenteVacia(tr){
    const productoId = (tr.querySelector('.prod-id')?.value || '').trim();
    const productoTexto = (tr.querySelector('.prod-text')?.value || '').trim();
    const descuento = Number(tr.querySelector('.desc-pct')?.value || 0);

    return productoId === '' && productoTexto === '' && descuento === 0;
}

function cargarProductoEnFila(tr, productoId, cantidad = 1){
    const producto = PRODUCTOS_DATA[productoId];

    if(!producto) return;

    tr.querySelector('.prod-text').value = producto.label;
    tr.querySelector('.prod-id').value = String(productoId);
    tr.querySelector('.cant').value = String(cantidad);
}

function reindexarFilasProductos(){
    document.querySelectorAll('#tablaProductos tbody tr').forEach((tr, idx) => {
        const texto = tr.querySelector('.prod-text');
        const datalist = tr.querySelector('datalist');

        texto.setAttribute('list', `dl_productos_${idx}`);
        datalist.id = `dl_productos_${idx}`;
        tr.querySelector('.prod-id').name = `productos[${idx}][producto_id]`;
        tr.querySelector('.desc-pct').name = `productos[${idx}][descuento_pct]`;
        tr.querySelector('.cant').name = `productos[${idx}][cantidad]`;
    });
}

function limpiarFilasProductoVacias(){
    document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
        if(filaProductoRealmenteVacia(tr)){
            tr.remove();
        }
    });

    reindexarFilasProductos();
}

function agregarProductoPorCodigo(codigo){
    const limpio = String(codigo || '').trim();

    if(!limpio) return;

    const pid = PRODUCTOS_BARCODE_MAP[limpio]
        ? Number(PRODUCTOS_BARCODE_MAP[limpio])
        : 0;

    if(!pid){
        alert('No existe un producto con ese código de barras.');
        return;
    }

    const filaExistente = Array.from(
        document.querySelectorAll('#tablaProductos tbody tr')
    ).find(tr => Number(tr.querySelector('.prod-id')?.value || 0) === pid);

    const nombre = PRODUCTOS_DATA[pid]?.label || 'Producto';
    const stock = Number(PRODUCTOS_DATA[pid]?.stock_venta || 0);

    if(stock <= 0){
        alert('No hay stock disponible para agregar este producto.');
        return;
    }

    if(filaExistente){
        const qty = filaExistente.querySelector('.cant');
        const cantidadActual = Number(qty.value || 0);
        if(cantidadActual >= stock){
            alert('No hay más stock disponible para agregar este producto.');
            return;
        }

        qty.value = String(cantidadActual + 1);
        recalcular();
        mostrarToastProductoAgregado(nombre + ' agregado');
        return;
    }

    const filaVacia = Array.from(
        document.querySelectorAll('#tablaProductos tbody tr')
    ).find(filaProductoRealmenteVacia);

    if(filaVacia){
        cargarProductoEnFila(filaVacia, pid, 1);
    } else {
        addProductoRow(pid, 1);
    }

    limpiarFilasProductoVacias();
    recalcular();
    mostrarToastProductoAgregado(nombre + ' agregado');
}

function toggleClienteBoxes(){
    const colab = esClienteColab();

    document.getElementById('box_cliente').classList.toggle('hidden', colab);
    document.getElementById('box_colab').classList.toggle('hidden', !colab);

    recalcular();
}

function activarTab(tab){
    const servicios = tab === 'servicios';

    document.getElementById('tabServicios').className = servicios
        ? 'fn-primary-action'
        : 'fn-secondary-action';

    document.getElementById('tabProductos').className = servicios
        ? 'fn-secondary-action'
        : 'fn-primary-action';

    document.getElementById('panelServicios').classList.toggle('hidden', !servicios);
    document.getElementById('panelProductos').classList.toggle('hidden', servicios);
}

function abrirDescuento(){
    const contS = document.getElementById('listaDescServicios');
    const contP = document.getElementById('listaDescProductos');

    contS.innerHTML = '';
    contP.innerHTML = '';

    document.querySelectorAll('#tablaServicios tbody tr').forEach((tr, i) => {
        const txt = tr.querySelector('.serv-text')?.value || '(servicio)';

        contS.insertAdjacentHTML('beforeend', `
            <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-desc" data-target="serv" data-idx="${i}">
                <span>${txt}</span>
            </label>
        `);
    });

    document.querySelectorAll('#tablaProductos tbody tr').forEach((tr, i) => {
        const txt = tr.querySelector('.prod-text')?.value || '(producto)';

        contP.insertAdjacentHTML('beforeend', `
            <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-desc" data-target="prod" data-idx="${i}">
                <span>${txt}</span>
            </label>
        `);
    });

    const modal = document.getElementById('modalDescuento');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarDescuento(){
    const modal = document.getElementById('modalDescuento');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function aplicarDescuento(){
    const pct = Number(document.getElementById('descuentoPct')?.value || 0);

    document.querySelectorAll('.chk-desc:checked').forEach(chk => {
        const filas = chk.dataset.target === 'serv'
            ? document.querySelectorAll('#tablaServicios tbody tr')
            : document.querySelectorAll('#tablaProductos tbody tr');

        const tr = filas[Number(chk.dataset.idx)];

        if(tr){
            tr.querySelector('.desc-pct').value = String(pct);
        }
    });

    recalcular();
    cerrarDescuento();
}

document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('tabServicios').addEventListener('click', () => activarTab('servicios'));
    document.getElementById('tabProductos').addEventListener('click', () => activarTab('productos'));
    document.getElementById('addServicio').addEventListener('click', addServicioRow);
    document.getElementById('addProducto').addEventListener('click', () => addProductoRow());
    document.getElementById('vendedora_id').addEventListener('change', recalcular);
    document.getElementById('tipo_pago').addEventListener('change', recalcular);
    document.getElementById('pendiente_pago').addEventListener('change', recalcular);

    document.querySelectorAll('.pago-combinado').forEach(input => {
        input.addEventListener('input', recalcular);
    });

    document.querySelectorAll('input[name="tipo_cliente"]').forEach(radio => {
        radio.addEventListener('change', toggleClienteBoxes);
    });

    bindDatalist(
        document.getElementById('cliente_buscar'),
        CLIENTES_MAP,
        document.getElementById('cliente_id'),
        @json((bool) $turno)
    );

    bindDatalist(
        document.getElementById('colab_buscar'),
        COLABS_MAP,
        document.getElementById('cliente_colaboradora_id')
    );

    document.getElementById('btnDescuento').addEventListener('click', abrirDescuento);
    document.getElementById('closeDescuento').addEventListener('click', cerrarDescuento);
    document.getElementById('aplicarDescuento').addEventListener('click', aplicarDescuento);

    document.getElementById('selTodo').addEventListener('change', function(){
        document.querySelectorAll('.chk-desc').forEach(c => c.checked = this.checked);
    });

    document.getElementById('modalDescuento').addEventListener('click', function(e){
        if(e.target === this){
            cerrarDescuento();
        }
    });

    const scanProducto = document.getElementById('scan_producto');

    scanProducto.addEventListener('keydown', function(e){
        if(e.key === 'Enter'){
            e.preventDefault();

            if(this.value.trim() !== ''){
                agregarProductoPorCodigo(this.value);
                this.value = '';
                activarTab('productos');
            }
        }
    });

    scanProducto.addEventListener('change', function(){
        if(this.value.trim() !== ''){
            agregarProductoPorCodigo(this.value);
            this.value = '';
            activarTab('productos');
        }
    });

    document.getElementById('formVenta').addEventListener('submit', function(e){
        if(esPagoPendiente() || getTipoPago() !== 'combinado'){
            return;
        }

        const efectivo = Number(document.getElementById('pago_efectivo')?.value || 0);
        const transferencia = Number(document.getElementById('pago_transferencia')?.value || 0);
        const tarjeta = Number(document.getElementById('pago_tarjeta')?.value || 0);
        const suma = round2(efectivo + transferencia + tarjeta);
        const cantidadMetodos = [efectivo, transferencia, tarjeta].filter(v => v > 0).length;

        if(Math.abs(suma - TOTAL_BASE_ACTUAL) > 0.01){
            e.preventDefault();
            alert('La distribución del pago combinado debe coincidir con el total base.');
            return;
        }

        if(cantidadMetodos < 2){
            e.preventDefault();
            alert('Para pago combinado tenés que usar al menos dos formas de pago.');
        }
    });

    activarTab('servicios');
    toggleClienteBoxes();
    addServicioRow();
    addProductoRow();
    recalcular();
});
</script>

@endsection
