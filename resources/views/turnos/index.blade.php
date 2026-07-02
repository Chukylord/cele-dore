@extends('layouts.admin')

@section('title', 'Turnos - Cele Dore Estilista')
@section('h1', 'Turnos')
@section('sub', 'Calendario, estados y gestión rápida de turnos.')

@section('content')

@if(session('ok'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('ok') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Revisá estos datos:</div>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Resumen de hoy --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
    <div class="rounded-2xl border bg-slate-900 p-4 text-white">
        <div class="text-xs uppercase tracking-wider text-slate-300">Turnos de hoy</div>
        <div class="mt-1 text-3xl font-black">{{ $estadisticas['total'] }}</div>
    </div>

    <div class="rounded-2xl border bg-yellow-50 p-4">
        <div class="text-xs uppercase tracking-wider text-yellow-700">Pendientes</div>
        <div class="mt-1 text-3xl font-black text-yellow-800">{{ $estadisticas['pendiente'] }}</div>
    </div>

    <div class="rounded-2xl border bg-green-50 p-4">
        <div class="text-xs uppercase tracking-wider text-green-700">Confirmados</div>
        <div class="mt-1 text-3xl font-black text-green-800">{{ $estadisticas['confirmado'] }}</div>
    </div>

    <div class="rounded-2xl border bg-blue-50 p-4">
        <div class="text-xs uppercase tracking-wider text-blue-700">Atendidos</div>
        <div class="mt-1 text-3xl font-black text-blue-800">{{ $estadisticas['atendido'] }}</div>
    </div>

    <div class="rounded-2xl border bg-red-50 p-4">
        <div class="text-xs uppercase tracking-wider text-red-700">Cancelados</div>
        <div class="mt-1 text-3xl font-black text-red-800">{{ $estadisticas['cancelado'] }}</div>
    </div>
</div>

{{-- Filtros y acciones --}}
<div class="rounded-2xl border bg-white p-4 mb-5">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
        <div class="lg:col-span-3">
            <label class="text-sm font-semibold text-slate-700">Filtrar por estado</label>
            <select id="filtro_estado"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="confirmado">Confirmado</option>
                <option value="atendido">Atendido</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>

        <div class="lg:col-span-4">
            <label class="text-sm font-semibold text-slate-700">Filtrar por colaboradora</label>
            <select id="filtro_colaboradora"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                <option value="">Todas las colaboradoras</option>
                @foreach($colaboradoras as $colaboradora)
                    <option value="{{ $colaboradora->id }}">
                        {{ $colaboradora->apellido }} {{ $colaboradora->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="lg:col-span-2">
            <button type="button"
                    id="btnLimpiarFiltros"
                    class="w-full rounded-xl border px-4 py-2 hover:bg-slate-50">
                Limpiar filtros
            </button>
        </div>

        <div class="lg:col-span-3 lg:text-right">
            <button type="button"
                    id="btnNuevoTurno"
                    class="w-full lg:w-auto rounded-xl bg-slate-900 text-white px-5 py-2.5 font-semibold hover:bg-slate-800">
                + Nuevo turno
            </button>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
        <span class="rounded-full bg-yellow-100 px-3 py-1 text-yellow-800">Pendiente</span>
        <span class="rounded-full bg-green-100 px-3 py-1 text-green-800">Confirmado</span>
        <span class="rounded-full bg-blue-100 px-3 py-1 text-blue-800">Atendido</span>
        <span class="rounded-full bg-red-100 px-3 py-1 text-red-800">Cancelado</span>
        <span class="text-slate-500">Tocá un día u horario para crear. Tocá un turno para editar.</span>
    </div>
</div>

{{-- Calendario --}}
<div class="rounded-2xl border bg-white p-3 sm:p-5 shadow-sm">
    <div id="calendar"></div>
</div>

{{-- Modal turno --}}
<div id="modalTurno"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-3 sm:p-5">

    <div class="max-h-[94vh] w-full max-w-3xl overflow-y-auto rounded-[28px] bg-white shadow-2xl">
        <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b bg-white px-5 py-4 sm:px-6">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 id="tituloModalTurno" class="text-xl font-black text-slate-900">Nuevo turno</h2>
                    <span id="badgeEstadoTurno"
                          class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-bold text-yellow-800">
                        Pendiente
                    </span>
                </div>
                <div id="subtituloModalTurno" class="mt-1 text-sm text-slate-500">
                    Completá los datos del turno.
                </div>
            </div>

            <button type="button"
                    id="cerrarModalTurno"
                    class="rounded-xl border px-3 py-2 text-slate-500 hover:bg-slate-50 hover:text-slate-900"
                    aria-label="Cerrar">
                ✕
            </button>
        </div>

        <form method="POST"
              action="{{ route('turnos.store') }}"
              id="formTurno"
              class="p-5 sm:p-6">
            @csrf

            <input type="hidden" name="turno_id" id="turno_id" value="{{ old('turno_id') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Clienta *</label>

                    <input id="turno_cliente_buscar"
                           list="datalist_clientes_turnos"
                           placeholder="Buscá por nombre, apellido o teléfono..."
                           autocomplete="off"
                           value=""
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">

                    <datalist id="datalist_clientes_turnos">
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->apellido }} {{ $cliente->nombre }} - {{ $cliente->telefono }}"></option>
                        @endforeach
                    </datalist>

                    <input type="hidden"
                           name="cliente_id"
                           id="turno_cliente_id"
                           value="{{ old('cliente_id') }}">

                    <div class="mt-1 text-xs text-slate-500">
                        Elegí una clienta de la lista para que el turno quede correctamente asociado.
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Servicio / título</label>
                    <input name="titulo"
                           id="turno_titulo"
                           value="{{ old('titulo') }}"
                           placeholder="Ej: Corte, Color, Peinado..."
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Colaboradora</label>
                    <select name="colaboradora_id"
                            id="turno_colaboradora_id"
                            class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                        <option value="">Sin asignar</option>
                        @foreach($colaboradoras as $colaboradora)
                            <option value="{{ $colaboradora->id }}">
                                {{ $colaboradora->apellido }} {{ $colaboradora->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Fecha y hora *</label>
                    <input type="datetime-local"
                           name="inicio"
                           id="turno_inicio"
                           value="{{ old('inicio') }}"
                           class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                           required>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Estado *</label>
                    <select name="estado"
                            id="turno_estado"
                            class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                            required>
                        <option value="pendiente">Pendiente</option>
                        <option value="confirmado">Confirmado</option>
                        <option value="atendido">Atendido</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Detalle / observaciones</label>
                    <textarea name="detalle"
                              id="turno_detalle"
                              rows="4"
                              placeholder="Ej: fórmula de color, servicio solicitado, aclaraciones..."
                              class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">{{ old('detalle') }}</textarea>
                </div>
            </div>

            {{-- Acciones rápidas solo al editar --}}
            <div id="accionesRapidasTurno" class="mt-5 hidden rounded-2xl border bg-slate-50 p-4">
                <div class="mb-3 text-sm font-semibold text-slate-700">Cambiar estado rápidamente</div>

                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            data-estado-rapido="confirmado"
                            class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                        Confirmar
                    </button>

                    <button type="button"
                            data-estado-rapido="atendido"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Marcar atendido
                    </button>

                    <button type="button"
                            data-estado-rapido="cancelado"
                            class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Cancelar turno
                    </button>

                    <button type="button"
                            data-estado-rapido="pendiente"
                            class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                        Volver a pendiente
                    </button>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="button"
                        id="btnEliminarTurno"
                        class="hidden rounded-xl border border-red-300 px-4 py-2 font-semibold text-red-700 hover:bg-red-50">
                    Eliminar turno
                </button>

                <div class="flex flex-col-reverse gap-2 sm:ml-auto sm:flex-row">
                    <button type="button"
                            id="cancelarModalTurno"
                            class="rounded-xl border px-4 py-2 hover:bg-slate-50">
                        Volver
                    </button>

                    <button type="submit"
                            id="btnGuardarTurno"
                            class="rounded-xl bg-slate-900 px-5 py-2 font-semibold text-white hover:bg-slate-800">
                        Guardar turno
                    </button>
                </div>
            </div>
        </form>

        <form method="POST" id="formEliminarTurno" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

@php
    $clientesMap = [];
    $clientesTextoPorId = [];

    foreach ($clientes as $cliente) {
        $texto = $cliente->apellido . ' ' . $cliente->nombre . ' - ' . $cliente->telefono;
        $clientesMap[$texto] = $cliente->id;
        $clientesTextoPorId[$cliente->id] = $texto;
    }

    $oldTurno = [
        'tiene_errores' => $errors->any(),
        'turno_id' => old('turno_id'),
        'cliente_id' => old('cliente_id'),
        'colaboradora_id' => old('colaboradora_id'),
        'titulo' => old('titulo'),
        'inicio' => old('inicio'),
        'estado' => old('estado', 'pendiente'),
        'detalle' => old('detalle'),
    ];
@endphp

<style>
    #calendar .fc-toolbar {
        gap: .75rem;
    }

    #calendar .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
    }

    #calendar .fc-button {
        border-radius: .75rem !important;
        border: 1px solid #cbd5e1 !important;
        background: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        text-transform: capitalize !important;
    }

    #calendar .fc-button:hover,
    #calendar .fc-button-active {
        background: #0f172a !important;
        color: #ffffff !important;
        border-color: #0f172a !important;
    }

    #calendar .fc-event {
        cursor: pointer;
        border-radius: .55rem;
        padding: 2px 4px;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .08);
    }

    #calendar .fc-daygrid-day-number,
    #calendar .fc-col-header-cell-cushion {
        color: #334155;
        font-weight: 700;
    }

    #calendar .fc-day-today {
        background: #f8fafc !important;
    }

    @media (max-width: 767px) {
        #calendar .fc-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        #calendar .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: .35rem;
        }

        #calendar .fc-toolbar-title {
            font-size: 1.05rem;
            text-align: center;
        }

        #calendar .fc-button {
            padding: .4rem .6rem !important;
            font-size: .78rem !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const CLIENTES_MAP = @json($clientesMap);
    const CLIENTES_TXT_BY_ID = @json($clientesTextoPorId);
    const OLD_TURNO = @json($oldTurno);

    const URL_STORE = @json(route('turnos.store'));
    const URL_EVENTOS = @json(route('turnos.eventos'));
    const URL_UPDATE_TEMPLATE = @json(route('turnos.update', ['turno' => '__ID__']));
    const URL_DELETE_TEMPLATE = @json(route('turnos.destroy', ['turno' => '__ID__']));

    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('modalTurno');
    const form = document.getElementById('formTurno');
    const formEliminar = document.getElementById('formEliminarTurno');

    const tituloModal = document.getElementById('tituloModalTurno');
    const subtituloModal = document.getElementById('subtituloModalTurno');
    const badgeEstado = document.getElementById('badgeEstadoTurno');
    const accionesRapidas = document.getElementById('accionesRapidasTurno');

    const turnoId = document.getElementById('turno_id');
    const clienteBuscar = document.getElementById('turno_cliente_buscar');
    const clienteId = document.getElementById('turno_cliente_id');
    const colaboradoraId = document.getElementById('turno_colaboradora_id');
    const estado = document.getElementById('turno_estado');
    const titulo = document.getElementById('turno_titulo');
    const inicio = document.getElementById('turno_inicio');
    const detalle = document.getElementById('turno_detalle');

    const btnEliminar = document.getElementById('btnEliminarTurno');
    const btnGuardar = document.getElementById('btnGuardarTurno');
    const filtroEstado = document.getElementById('filtro_estado');
    const filtroColaboradora = document.getElementById('filtro_colaboradora');

    function urlConId(template, id) {
        return template.replace('__ID__', encodeURIComponent(String(id)));
    }

    function toDateTimeLocal(date) {
        if (!date) return '';

        const d = date instanceof Date ? new Date(date.getTime()) : new Date(date);
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 16);
    }

    function proximaMediaHora() {
        const fecha = new Date();
        fecha.setSeconds(0, 0);

        const minutos = fecha.getMinutes();
        const agregar = minutos === 0 || minutos === 30 ? 0 : (minutos < 30 ? 30 - minutos : 60 - minutos);
        fecha.setMinutes(minutos + agregar);

        return fecha;
    }

    function setClienteHidden() {
        const valor = (clienteBuscar.value || '').trim();
        clienteId.value = CLIENTES_MAP[valor] ? String(CLIENTES_MAP[valor]) : '';
    }

    function actualizarBadgeEstado(valor) {
        const config = {
            pendiente: ['Pendiente', 'bg-yellow-100', 'text-yellow-800'],
            confirmado: ['Confirmado', 'bg-green-100', 'text-green-800'],
            atendido: ['Atendido', 'bg-blue-100', 'text-blue-800'],
            cancelado: ['Cancelado', 'bg-red-100', 'text-red-800'],
        };

        const actual = config[valor] || config.pendiente;

        badgeEstado.textContent = actual[0];
        badgeEstado.className = `rounded-full px-3 py-1 text-xs font-bold ${actual[1]} ${actual[2]}`;
    }

    function quitarMetodoSpoof() {
        form.querySelectorAll('input[name="_method"]').forEach(input => input.remove());
    }

    function agregarMetodoPut() {
        quitarMetodoSpoof();

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_method';
        input.value = 'PUT';
        form.appendChild(input);
    }

    function prepararNuevoTurno(fechaInicio = null) {
        form.reset();
        quitarMetodoSpoof();

        form.action = URL_STORE;
        turnoId.value = '';
        clienteBuscar.value = '';
        clienteId.value = '';
        estado.value = 'pendiente';

        const inicioTurno = fechaInicio || proximaMediaHora();

        inicio.value = toDateTimeLocal(inicioTurno);
        inicio.min = toDateTimeLocal(new Date());

        tituloModal.textContent = 'Nuevo turno';
        subtituloModal.textContent = 'Completá los datos y guardá el turno.';
        btnGuardar.textContent = 'Guardar turno';
        btnEliminar.classList.add('hidden');
        accionesRapidas.classList.add('hidden');
        actualizarBadgeEstado('pendiente');
    }

    function prepararEdicion(evento) {
        const id = evento.id;

        form.reset();
        agregarMetodoPut();

        form.action = urlConId(URL_UPDATE_TEMPLATE, id);
        formEliminar.action = urlConId(URL_DELETE_TEMPLATE, id);
        turnoId.value = id;

        const cliId = evento.extendedProps.cliente_id || '';
        clienteId.value = cliId;
        clienteBuscar.value = cliId && CLIENTES_TXT_BY_ID[cliId]
            ? CLIENTES_TXT_BY_ID[cliId]
            : '';

        colaboradoraId.value = evento.extendedProps.colaboradora_id || '';
        estado.value = evento.extendedProps.estado || 'pendiente';
        titulo.value = evento.extendedProps.titulo || '';
        inicio.value = toDateTimeLocal(evento.start);
        detalle.value = evento.extendedProps.detalle || '';

        inicio.removeAttribute('min');

        tituloModal.textContent = 'Editar turno';
        subtituloModal.textContent = `${evento.extendedProps.cliente || 'Clienta'} · ${evento.extendedProps.telefono || '-'}`;
        btnGuardar.textContent = 'Guardar cambios';
        btnEliminar.classList.remove('hidden');
        accionesRapidas.classList.remove('hidden');
        actualizarBadgeEstado(estado.value);
    }

    function abrirModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        setTimeout(() => clienteBuscar.focus(), 80);
    }

    function cerrarModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    function fechaElegidaDesdeCalendario(info) {
        let elegida = new Date(info.date);

        if (info.allDay) {
            elegida.setHours(9, 0, 0, 0);

            const ahora = new Date();
            const mismoDia = elegida.toDateString() === ahora.toDateString();

            if (mismoDia && elegida < ahora) {
                elegida = proximaMediaHora();
            }
        }

        return elegida;
    }

    clienteBuscar.addEventListener('input', setClienteHidden);
    clienteBuscar.addEventListener('change', setClienteHidden);
    clienteBuscar.addEventListener('blur', setClienteHidden);

    estado.addEventListener('change', function () {
        actualizarBadgeEstado(this.value);
    });

    document.getElementById('btnNuevoTurno').addEventListener('click', function () {
        prepararNuevoTurno();
        abrirModal();
    });

    document.getElementById('cerrarModalTurno').addEventListener('click', cerrarModal);
    document.getElementById('cancelarModalTurno').addEventListener('click', cerrarModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            cerrarModal();
        }
    });

    btnEliminar.addEventListener('click', function () {
        const nombreClienta = clienteBuscar.value || 'esta clienta';

        if (confirm(`¿Eliminar definitivamente el turno de ${nombreClienta}?`)) {
            formEliminar.submit();
        }
    });

    document.querySelectorAll('[data-estado-rapido]').forEach(button => {
        button.addEventListener('click', function () {
            const nuevoEstado = this.dataset.estadoRapido;
            estado.value = nuevoEstado;
            actualizarBadgeEstado(nuevoEstado);

            const etiquetas = {
                confirmado: 'confirmado',
                atendido: 'atendido',
                cancelado: 'cancelado',
                pendiente: 'pendiente',
            };

            if (confirm(`¿Guardar el turno como ${etiquetas[nuevoEstado]}?`)) {
                form.submit();
            }
        });
    });

    form.addEventListener('submit', function (event) {
        setClienteHidden();

        if (!clienteId.value) {
            event.preventDefault();
            alert('Seleccioná una clienta válida de la lista.');
            clienteBuscar.focus();
            return;
        }

        btnGuardar.disabled = true;
        btnGuardar.textContent = 'Guardando...';
    });

    if (!window.FullCalendar || !window.FullCalendar.Calendar) {
        calendarEl.innerHTML = `
            <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-800">
                No se pudo cargar el calendario. Ejecutá npm run build y recargá la pantalla.
            </div>
        `;
        return;
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: [
            FullCalendar.dayGridPlugin,
            FullCalendar.timeGridPlugin,
            FullCalendar.interactionPlugin
        ],
        initialView: window.innerWidth < 768 ? 'timeGridDay' : 'dayGridMonth',
        locale: 'es',
        firstDay: 1,
        nowIndicator: true,
        navLinks: true,
        dayMaxEvents: true,
        height: 'auto',
        slotMinTime: '07:00:00',
        slotMaxTime: '22:00:00',
        slotDuration: '00:30:00',
        slotLabelInterval: '01:00:00',
        allDaySlot: false,
        editable: false,
        selectable: false,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'
        },
        events: function (fetchInfo, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: fetchInfo.startStr,
                end: fetchInfo.endStr,
            });

            if (filtroEstado.value) {
                params.set('estado', filtroEstado.value);
            }

            if (filtroColaboradora.value) {
                params.set('colaboradora_id', filtroColaboradora.value);
            }

            fetch(`${URL_EVENTOS}?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('No se pudieron cargar los turnos.');
                    }

                    return response.json();
                })
                .then(successCallback)
                .catch(error => {
                    console.error(error);
                    failureCallback(error);
                });
        },
        dateClick: function (info) {
            const fechaInicio = fechaElegidaDesdeCalendario(info);

            if (fechaInicio.getTime() < new Date().getTime() - 60000) {
                alert('No se pueden crear turnos en fechas u horarios pasados.');
                return;
            }

            prepararNuevoTurno(fechaInicio);
            abrirModal();
        },
        eventClick: function (info) {
            prepararEdicion(info.event);
            abrirModal();
        },
        eventDidMount: function (info) {
            const props = info.event.extendedProps;
            info.el.title = [
                info.event.title,
                props.colaboradora ? `Colaboradora: ${props.colaboradora}` : '',
                props.telefono ? `Teléfono: ${props.telefono}` : '',
                props.estado ? `Estado: ${props.estado}` : '',
            ].filter(Boolean).join('\n');
        }
    });

    filtroEstado.addEventListener('change', () => calendar.refetchEvents());
    filtroColaboradora.addEventListener('change', () => calendar.refetchEvents());

    document.getElementById('btnLimpiarFiltros').addEventListener('click', function () {
        filtroEstado.value = '';
        filtroColaboradora.value = '';
        calendar.refetchEvents();
    });

    calendar.render();

    if (OLD_TURNO.tiene_errores) {
        const cliId = OLD_TURNO.cliente_id || '';

        if (OLD_TURNO.turno_id) {
            prepararNuevoTurno();
            agregarMetodoPut();
            turnoId.value = OLD_TURNO.turno_id;
            form.action = urlConId(URL_UPDATE_TEMPLATE, OLD_TURNO.turno_id);
            formEliminar.action = urlConId(URL_DELETE_TEMPLATE, OLD_TURNO.turno_id);
            tituloModal.textContent = 'Editar turno';
            subtituloModal.textContent = 'Corregí los datos marcados y volvé a guardar.';
            btnGuardar.textContent = 'Guardar cambios';
            btnEliminar.classList.remove('hidden');
            accionesRapidas.classList.remove('hidden');
            inicio.removeAttribute('min');
        } else {
            prepararNuevoTurno();
            subtituloModal.textContent = 'Corregí los datos marcados y volvé a guardar.';
        }

        clienteId.value = cliId;
        clienteBuscar.value = cliId && CLIENTES_TXT_BY_ID[cliId]
            ? CLIENTES_TXT_BY_ID[cliId]
            : '';
        colaboradoraId.value = OLD_TURNO.colaboradora_id || '';
        titulo.value = OLD_TURNO.titulo || '';
        inicio.value = OLD_TURNO.inicio ? String(OLD_TURNO.inicio).slice(0, 16) : '';
        estado.value = OLD_TURNO.estado || 'pendiente';
        detalle.value = OLD_TURNO.detalle || '';
        actualizarBadgeEstado(estado.value);
        abrirModal();
    }
});
</script>

@endsection
