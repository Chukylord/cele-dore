<?php

namespace App\Http\Controllers;

use App\Models\Colaboradora;
use App\Models\Fichada;
use Illuminate\Http\Request;

class FichadaController extends Controller
{
    public function index(Request $request)
    {
        $colaboradora_id = trim((string) $request->get('colaboradora_id', ''));
        $desde = trim((string) $request->get('desde', ''));
        $hasta = trim((string) $request->get('hasta', ''));

        $baseQuery = Fichada::query()->with('colaboradora');

        if ($colaboradora_id !== '') {
            $baseQuery->where('colaboradora_id', (int) $colaboradora_id);
        }

        if ($desde !== '') {
            $baseQuery->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $baseQuery->whereDate('fecha', '<=', $hasta);
        }

        // Totales del filtro (no dependen de la paginación)
        $totales = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(minutos_normales),0) as normales, COALESCE(SUM(minutos_extras),0) as extras')
            ->first();

        $totalNormalesMin = (int) ($totales->normales ?? 0);
        $totalExtrasMin   = (int) ($totales->extras ?? 0);
        $totalGeneralMin  = $totalNormalesMin + $totalExtrasMin;

        $fichadas = $baseQuery
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->paginate(10)
            ->withQueryString();

        $colaboradoras = Colaboradora::where('activa', true)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        return view('fichadas.index', compact(
            'fichadas',
            'colaboradoras',
            'colaboradora_id',
            'desde',
            'hasta',
            'totalNormalesMin',
            'totalExtrasMin',
            'totalGeneralMin'
        ));
    }

    public function create()
    {
        $colaboradoras = Colaboradora::where('activa', true)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        return view('fichadas.create', compact('colaboradoras'));
    }

    private function validarCuartoHora(string $hora): bool
    {
        $minuto = (int) substr($hora, 3, 2);
        return in_array($minuto, [0, 15, 30, 45], true);
    }

    private function calcularMinutos(string $horaInicio, string $horaFin): int
    {
        [$hi, $mi] = array_map('intval', explode(':', substr($horaInicio, 0, 5)));
        [$hf, $mf] = array_map('intval', explode(':', substr($horaFin, 0, 5)));

        $inicio = ($hi * 60) + $mi;
        $fin = ($hf * 60) + $mf;

        return max(0, $fin - $inicio);
    }

    private function haySuperposicion(int $colaboradoraId, string $fecha, string $horaInicio, string $horaFin, ?int $ignorarId = null): bool
    {
        $query = Fichada::where('colaboradora_id', $colaboradoraId)
            ->whereDate('fecha', $fecha)
            // superposición real:
            // nuevo_inicio < existente_fin  AND  nuevo_fin > existente_inicio
            ->where('hora_inicio', '<', $horaFin)
            ->where('hora_fin', '>', $horaInicio);

        if ($ignorarId) {
            $query->where('id', '!=', $ignorarId);
        }

        return $query->exists();
    }

    private function separarNormalesYExtras(int $minutosTotales, bool $esExtra): array
    {
        if ($esExtra) {
            return [0, $minutosTotales];
        }

        return [$minutosTotales, 0];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'colaboradora_id' => ['required', 'exists:colaboradoras,id'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required'],
            'hora_fin' => ['required'],
            'es_extra' => ['nullable'],
        ]);

        if (
            !$this->validarCuartoHora($data['hora_inicio']) ||
            !$this->validarCuartoHora($data['hora_fin'])
        ) {
            return back()->withErrors([
                'hora_inicio' => 'Las horas deben estar en cuartos: 00, 15, 30 o 45.'
            ])->withInput();
        }

        $minutos = $this->calcularMinutos($data['hora_inicio'], $data['hora_fin']);

        if ($minutos <= 0) {
            return back()->withErrors([
                'hora_fin' => 'La hora de fin debe ser mayor a la hora de inicio.'
            ])->withInput();
        }

        if ($this->haySuperposicion(
            (int)$data['colaboradora_id'],
            $data['fecha'],
            $data['hora_inicio'],
            $data['hora_fin']
        )) {
            return back()->withErrors([
                'hora_inicio' => 'Ese horario se superpone con otra fichada de la misma colaboradora en el mismo día.'
            ])->withInput();
        }

        $esExtra = $request->has('es_extra');
        [$normales, $extras] = $this->separarNormalesYExtras($minutos, $esExtra);

        Fichada::create([
            'colaboradora_id' => $data['colaboradora_id'],
            'fecha' => $data['fecha'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'es_extra' => $esExtra,
            'minutos_trabajados' => $minutos,
            'minutos_normales' => $normales,
            'minutos_extras' => $extras,
        ]);

        return redirect()->route('fichadas.index')->with('ok', 'Fichada registrada correctamente.');
    }

    public function edit(Fichada $fichada)
    {
        $colaboradoras = Colaboradora::where('activa', true)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        return view('fichadas.edit', compact('fichada', 'colaboradoras'));
    }

    public function update(Request $request, Fichada $fichada)
    {
        $data = $request->validate([
            'colaboradora_id' => ['required', 'exists:colaboradoras,id'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required'],
            'hora_fin' => ['required'],
            'es_extra' => ['nullable'],
        ]);

        if (
            !$this->validarCuartoHora($data['hora_inicio']) ||
            !$this->validarCuartoHora($data['hora_fin'])
        ) {
            return back()->withErrors([
                'hora_inicio' => 'Las horas deben estar en cuartos: 00, 15, 30 o 45.'
            ])->withInput();
        }

        $minutos = $this->calcularMinutos($data['hora_inicio'], $data['hora_fin']);

        if ($minutos <= 0) {
            return back()->withErrors([
                'hora_fin' => 'La hora de fin debe ser mayor a la hora de inicio.'
            ])->withInput();
        }

        if ($this->haySuperposicion(
            (int)$data['colaboradora_id'],
            $data['fecha'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $fichada->id
        )) {
            return back()->withErrors([
                'hora_inicio' => 'Ese horario se superpone con otra fichada de la misma colaboradora en el mismo día.'
            ])->withInput();
        }

        $esExtra = $request->has('es_extra');
        [$normales, $extras] = $this->separarNormalesYExtras($minutos, $esExtra);

        $fichada->update([
            'colaboradora_id' => $data['colaboradora_id'],
            'fecha' => $data['fecha'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'es_extra' => $esExtra,
            'minutos_trabajados' => $minutos,
            'minutos_normales' => $normales,
            'minutos_extras' => $extras,
        ]);

        return redirect()->route('fichadas.index')->with('ok', 'Fichada actualizada correctamente.');
    }

    public function destroy(Fichada $fichada)
    {
        $fichada->delete();

        return redirect()->route('fichadas.index')->with('ok', 'Fichada eliminada.');
    }
}