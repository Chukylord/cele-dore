@extends('layouts.admin')

@section('title', 'Turnos - Peluquería TOP')
@section('h1', 'Turnos')
@section('sub', 'Calendario de turnos.')

@section('content')

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <div class="flex flex-wrap gap-2 text-sm">
            <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-800">Pendiente</span>
            <span class="px-3 py-1 rounded-full bg-green-100 text-green-800">Confirmado</span>
            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800">Atendido</span>
            <span class="px-3 py-1 rounded-full bg-red-100 text-red-800">Cancelado</span>
        </div>

        <button type="button" id="btnNuevoTurno"
                class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
            + Nuevo turno
        </button>
    </div>

    <div class="rounded-2xl border bg-white p-4">
        <div id="calendar"></div>
    </div>

    {{-- Modal turno --}}
    <div id="modalTurno" class="fixed inset-0 hidden items-center justify-center bg-black/40 p-4 z-50">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow p-5">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <div class="text-lg font-bold" id="tituloModalTurno">Nuevo turno</div>
                    <div class="text-sm text-slate-600">Completá los datos del turno.</div>
                </div>
                <button type="button" id="cerrarModalTurno" class="text-slate-500 hover:text-slate-900">✖</button>
            </div>

            <form method="POST" action="{{ route('turnos.store') }}" id="formTurno">
                @csrf
                <input type="hidden" id="form_method_turno" value="POST">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-slate-700">Cliente *</label>

                        <input id="turno_cliente_buscar"
                               list="datalist_clientes_turnos"
                               placeholder="Escribí para buscar cliente..."
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />

                        <datalist id="datalist_clientes_turnos">
                            @foreach($clientes as $c)
                                <option value="{{ $c->apellido }} {{ $c->nombre }} - {{ $c->telefono }}"></option>
                            @endforeach
                        </datalist>

                        <input type="hidden" name="cliente_id" id="turno_cliente_id" required>

                        <div class="text-xs text-slate-500 mt-1">
                            Buscá por apellido, nombre o teléfono visualmente.
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Colaboradora</label>
                        <select name="colaboradora_id" id="turno_colaboradora_id"
                                class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
                            <option value="">-</option>
                            @foreach($colaboradoras as $c)
                                <option value="{{ $c->id }}">{{ $c->apellido }} {{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Estado *</label>
                        <select name="estado" id="turno_estado"
                                class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="atendido">Atendido</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Título</label>
                        <input name="titulo" id="turno_titulo" placeholder="Ej: Corte / Color"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" />
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Fecha y Hora *</label>
                        <input type="datetime-local" name="inicio" id="turno_inicio"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500" required />
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-slate-700">Detalle</label>
                        <textarea name="detalle" id="turno_detalle" rows="3"
                                  class="mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                                  placeholder="Ej: color, observaciones, etc."></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-between gap-2">
                    <div>
                        <button type="button" id="btnEliminarTurno"
                                class="hidden rounded-xl border border-red-300 text-red-700 px-4 py-2 hover:bg-red-50">
                            Eliminar
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" id="cancelarModalTurno"
                                class="rounded-xl border px-4 py-2 hover:bg-slate-50">
                            Cancelar
                        </button>

                        <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
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

        foreach($clientes as $c){
            $txt = $c->apellido . ' ' . $c->nombre . ' - ' . $c->telefono;
            $clientesMap[$txt] = $c->id;
            $clientesTextoPorId[$c->id] = $txt;
        }
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const CLIENTES_MAP = @json($clientesMap);
            const CLIENTES_TXT_BY_ID = @json($clientesTextoPorId);

            const calendarEl = document.getElementById('calendar');
            const modal = document.getElementById('modalTurno');

            const form = document.getElementById('formTurno');
            const formEliminar = document.getElementById('formEliminarTurno');

            const tituloModal = document.getElementById('tituloModalTurno');
            const btnEliminar = document.getElementById('btnEliminarTurno');

            const clienteBuscar = document.getElementById('turno_cliente_buscar');
            const clienteId = document.getElementById('turno_cliente_id');

            const colaboradoraId = document.getElementById('turno_colaboradora_id');
            const estado = document.getElementById('turno_estado');
            const titulo = document.getElementById('turno_titulo');
            const inicio = document.getElementById('turno_inicio');
            const detalle = document.getElementById('turno_detalle');

            function setClienteHidden() {
                const v = (clienteBuscar.value || '').trim();
                clienteId.value = CLIENTES_MAP[v] ? String(CLIENTES_MAP[v]) : '';
            }

            clienteBuscar.addEventListener('change', setClienteHidden);
            clienteBuscar.addEventListener('blur', setClienteHidden);

            function limpiarFormulario() {
                form.action = "{{ route('turnos.store') }}";
                tituloModal.textContent = 'Nuevo turno';
                btnEliminar.classList.add('hidden');

                const oldMethod = form.querySelector('input[name="_method"]');
                if (oldMethod) oldMethod.remove();

                form.reset();
                clienteBuscar.value = '';
                clienteId.value = '';
            }

            function abrirModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function cerrarModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function toDateTimeLocal(dateStr) {
                if (!dateStr) return '';
                return dateStr.slice(0, 16);
            }

            document.getElementById('btnNuevoTurno').addEventListener('click', function () {
                limpiarFormulario();
                abrirModal();
            });

            document.getElementById('cerrarModalTurno').addEventListener('click', cerrarModal);
            document.getElementById('cancelarModalTurno').addEventListener('click', cerrarModal);

            modal.addEventListener('click', function (e) {
                if (e.target === modal) cerrarModal();
            });

            btnEliminar.addEventListener('click', function () {
                if (confirm('¿Eliminar este turno?')) {
                    formEliminar.submit();
                }
            });

            const calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: [
                    FullCalendar.dayGridPlugin,
                    FullCalendar.timeGridPlugin,
                    FullCalendar.interactionPlugin
                ],
                initialView: 'dayGridMonth',
                locale: 'es',
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
                selectable: true,
                editable: false,
                events: '{{ route('turnos.eventos') }}',

                select: function(info) {
                    limpiarFormulario();
                    inicio.value = info.startStr.slice(0,16);
                    abrirModal();
                },

                eventClick: function(info) {
                    const e = info.event;

                    limpiarFormulario();

                    tituloModal.textContent = 'Editar turno';
                    form.action = '/turnos/' + e.id;

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    form.appendChild(methodInput);

                    const cliId = e.extendedProps.cliente_id || '';
                    clienteId.value = cliId;
                    clienteBuscar.value = cliId && CLIENTES_TXT_BY_ID[cliId] ? CLIENTES_TXT_BY_ID[cliId] : '';

                    colaboradoraId.value = e.extendedProps.colaboradora_id || '';
                    estado.value = e.extendedProps.estado || 'pendiente';
                    titulo.value = e.extendedProps.titulo || '';
                    inicio.value = toDateTimeLocal(e.startStr);
                    detalle.value = e.extendedProps.detalle || '';

                    formEliminar.action = '/turnos/' + e.id;
                    btnEliminar.classList.remove('hidden');

                    abrirModal();
                }
            });

            calendar.render();
        });
    </script>

@endsection