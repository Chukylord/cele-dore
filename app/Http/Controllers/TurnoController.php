<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Colaboradora;
use App\Models\Turno;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    public function index()
    {
        $clientes = Cliente::orderBy('apellido')->orderBy('nombre')->get();
        $colaboradoras = Colaboradora::where('activa', true)->orderBy('apellido')->orderBy('nombre')->get();

        return view('turnos.index', compact('clientes', 'colaboradoras'));
    }

    public function eventos()
    {
        $turnos = Turno::with(['cliente', 'colaboradora'])->get();

        $eventos = $turnos->map(function ($t) {
            $cliente = $t->cliente ? ($t->cliente->nombre . ' ' . $t->cliente->apellido) : 'Cliente';
            $titulo = $t->titulo ? $t->titulo . ' - ' . $cliente : $cliente;

            $color = match ($t->estado) {
                'confirmado' => '#16a34a',
                'cancelado'  => '#dc2626',
                'atendido'   => '#2563eb',
                default      => '#f59e0b', // pendiente
            };

            return [
                'id' => $t->id,
                'title' => $titulo,
                'start' => $t->inicio?->format('Y-m-d\TH:i:s'),
                'end' => $t->fin?->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'detalle' => $t->detalle,
                    'estado' => $t->estado,
                    'cliente' => $cliente,
                    'cliente_id' => $t->cliente_id,
                    'colaboradora' => $t->colaboradora ? ($t->colaboradora->nombre . ' ' . $t->colaboradora->apellido) : '-',
                    'colaboradora_id' => $t->colaboradora_id,
                    'titulo' => $t->titulo,
                ],
            ];
        });

        return response()->json($eventos);
    }

    public function show(Turno $turno)
    {
        return response()->json($turno);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'colaboradora_id' => ['nullable', 'exists:colaboradoras,id'],
            'titulo' => ['nullable', 'string', 'max:255'],
            'detalle' => ['nullable', 'string'],
            'inicio' => ['required', 'date', 'after_or_equal:now'],
            'fin' => ['nullable', 'date', 'after_or_equal:inicio'],
            'estado' => ['required', 'in:pendiente,confirmado,cancelado,atendido'],
        ]);

        Turno::create($data);

        return redirect()->route('turnos.index')->with('ok', 'Turno creado correctamente.');
    }

    public function update(Request $request, Turno $turno)
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'colaboradora_id' => ['nullable', 'exists:colaboradoras,id'],
            'titulo' => ['nullable', 'string', 'max:255'],
            'detalle' => ['nullable', 'string'],
            'inicio' => ['required', 'date'],
            'fin' => ['nullable', 'date', 'after_or_equal:inicio'],
            'estado' => ['required', 'in:pendiente,confirmado,cancelado,atendido'],
        ]);

        $turno->update($data);

        return redirect()->route('turnos.index')->with('ok', 'Turno actualizado correctamente.');
    }

    public function destroy(Turno $turno)
    {
        $turno->delete();

        return redirect()->route('turnos.index')->with('ok', 'Turno eliminado.');
    }
}