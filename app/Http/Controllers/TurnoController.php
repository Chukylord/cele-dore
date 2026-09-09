<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Colaboradora;
use App\Models\Turno;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TurnoController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::query()
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $colaboradoras = Colaboradora::query()
            ->where('activa', true)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $hoy = now()->toDateString();

        $estadisticas = [
            'total' => Turno::query()->whereDate('inicio', $hoy)->count(),
            'pendiente' => Turno::query()->whereDate('inicio', $hoy)->where('estado', 'pendiente')->count(),
            'confirmado' => Turno::query()->whereDate('inicio', $hoy)->where('estado', 'confirmado')->count(),
            'atendido' => Turno::query()->whereDate('inicio', $hoy)->where('estado', 'atendido')->count(),
            'cancelado' => Turno::query()->whereDate('inicio', $hoy)->where('estado', 'cancelado')->count(),
        ];

        $turnoAnterior = $request->old('turno_id');
        $ventaAnteriorId = is_scalar($turnoAnterior) && ctype_digit((string) $turnoAnterior)
            ? Turno::find($turnoAnterior)?->venta?->id
            : null;

        $servicios = Servicio::orderBy('nombre')->get();

        return view('turnos.index', compact('clientes', 'colaboradoras', 'estadisticas', 'ventaAnteriorId', 'servicios'));
    }

    public function eventos(Request $request)
    {
        $query = Turno::query()
            ->with(['cliente', 'colaboradora', 'venta:id,turno_id', 'servicios']);

        if ($request->filled('start')) {
            $query->where('inicio', '>=', Carbon::parse((string) $request->get('start')));
        }

        if ($request->filled('end')) {
            $query->where('inicio', '<', Carbon::parse((string) $request->get('end')));
        }

        if ($request->filled('estado')) {
            $query->where('estado', (string) $request->get('estado'));
        }

        if ($request->filled('colaboradora_id')) {
            $query->where('colaboradora_id', (int) $request->get('colaboradora_id'));
        }

        $turnos = $query
            ->orderBy('inicio')
            ->get();

        $eventos = $turnos->map(function (Turno $turno) {
            $cliente = $turno->cliente
                ? trim($turno->cliente->nombre . ' ' . $turno->cliente->apellido)
                : 'Cliente';

            $telefono = $turno->cliente?->telefono ?: '-';
            $servicio = trim((string) $turno->titulo);
            $tituloEvento = $servicio !== ''
                ? $cliente . ' · ' . $servicio
                : $cliente;

            $color = match ($turno->estado) {
                'confirmado' => '#16a34a',
                'cancelado' => '#dc2626',
                'atendido' => '#2563eb',
                default => '#f59e0b',
            };

            return [
                'id' => $turno->id,
                'title' => $tituloEvento,
                'start' => $turno->inicio?->format('Y-m-d\TH:i:s'),
                'end' => $turno->fin?->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'venta_id' => $turno->venta?->id,
                    'detalle' => $turno->detalle,
                    'estado' => $turno->estado,
                    'cliente' => $cliente,
                    'cliente_id' => $turno->cliente_id,
                    'telefono' => $telefono,
                    'colaboradora' => $turno->colaboradora
                        ? trim($turno->colaboradora->nombre . ' ' . $turno->colaboradora->apellido)
                        : '-',
                    'colaboradora_id' => $turno->colaboradora_id,
                    'titulo' => $turno->titulo,
                    'servicios' => $turno->servicios->map(fn (Servicio $servicio) => [
                        'id' => $servicio->id,
                        'nombre' => $servicio->nombre,
                        'precio' => $servicio->precio,
                    ])->values(),
                ],
            ];
        });

        return response()->json($eventos);
    }

    public function show(Turno $turno)
    {
        $turno->load(['cliente', 'colaboradora', 'servicios']);

        return response()->json($turno);
    }

    public function store(Request $request)
    {
        $data = $this->validarTurno($request, true);
        $data = $this->normalizarDatos($data);

        $this->guardarTurno(new Turno(), $data);

        return redirect()
            ->route('turnos.index')
            ->with('ok', 'Turno creado correctamente.');
    }

    public function update(Request $request, Turno $turno)
    {
        $data = $this->validarTurno($request, false);
        $data = $this->normalizarDatos($data);

        $this->guardarTurno($turno, $data);

        return redirect()
            ->route('turnos.index')
            ->with('ok', 'Turno actualizado correctamente.');
    }

    public function destroy(Turno $turno)
    {
        $turno->delete();

        return redirect()
            ->route('turnos.index')
            ->with('ok', 'Turno eliminado correctamente.');
    }

    private function guardarTurno(Turno $turno, array $data): void
    {
        DB::transaction(function () use ($turno, $data) {
            $ids = array_values(array_unique($data['servicios'] ?? []));
            unset($data['servicios']);

            if ($ids) {
                $titulo = Servicio::whereIn('id', $ids)->orderBy('nombre')->pluck('nombre')->implode(' + ');
                // La columna histórica admite 255 caracteres; los vínculos conservan todos los servicios.
                $data['titulo'] = Str::limit($titulo, 255, '');
            }

            $turno->fill($data)->save();
            $turno->servicios()->sync($ids);
        });
    }

    private function validarTurno(Request $request, bool $esNuevo): array
    {
        $reglaInicio = ['required', 'date'];

        if ($esNuevo) {
            $reglaInicio[] = 'after_or_equal:now';
        }

        return $request->validate([
            'turno_id' => ['nullable', 'integer'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'colaboradora_id' => ['nullable', 'exists:colaboradoras,id'],
            'titulo' => ['nullable', 'string', 'max:255'],
            'servicios' => ['nullable', 'array'],
            'servicios.*' => ['required', 'integer', 'exists:servicios,id'],
            'detalle' => ['nullable', 'string', 'max:2000'],
            'inicio' => $reglaInicio,
            'fin' => ['nullable', 'date', 'after:inicio'],
            'estado' => ['required', 'in:pendiente,confirmado,cancelado,atendido'],
        ], [
            'cliente_id.required' => 'Seleccioná una clienta válida de la lista.',
            'cliente_id.exists' => 'La clienta seleccionada no es válida.',
            'inicio.required' => 'La fecha y hora de inicio son obligatorias.',
            'inicio.after_or_equal' => 'No se puede crear un turno en una fecha u hora pasada.',
            'fin.after' => 'La hora de finalización debe ser posterior al inicio.',
            'estado.required' => 'Seleccioná el estado del turno.',
        ]);
    }

    private function normalizarDatos(array $data): array
    {
        $inicio = Carbon::parse($data['inicio']);

        if (empty($data['fin'])) {
            $data['fin'] = $inicio->copy()->addHour();
        }

        $data['titulo'] = isset($data['titulo']) && trim((string) $data['titulo']) !== ''
            ? trim((string) $data['titulo'])
            : null;

        $data['detalle'] = isset($data['detalle']) && trim((string) $data['detalle']) !== ''
            ? trim((string) $data['detalle'])
            : null;

        unset($data['turno_id']);

        return $data;
    }
}
