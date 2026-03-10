<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;

class GastoController extends Controller
{
    public function index(Request $request)
    {
        $desde = trim((string) $request->get('desde', ''));
        $hasta = trim((string) $request->get('hasta', ''));
        $categoria = trim((string) $request->get('categoria', ''));

        $query = Gasto::query();

        if ($desde !== '') {
            $query->whereDate('fecha', '>=', $desde);
        }

        if ($hasta !== '') {
            $query->whereDate('fecha', '<=', $hasta);
        }

        if ($categoria !== '') {
            $query->where('categoria', 'like', "%{$categoria}%");
        }

        $gastos = $query
            ->orderBy('fecha', 'desc')
            ->paginate(10)
            ->withQueryString();

        $totalFiltro = (clone $query)->sum('monto');

        return view('gastos.index', compact('gastos', 'desde', 'hasta', 'categoria', 'totalFiltro'));
    }

    public function create()
    {
        return view('gastos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'categoria' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0'],
        ], [
            'fecha.required' => 'La fecha es obligatoria.',
            'categoria.required' => 'La categoría es obligatoria.',
            'monto.required' => 'El monto es obligatorio.',
        ]);

        $data['categoria'] = trim($data['categoria']);
        $data['descripcion'] = isset($data['descripcion']) ? trim($data['descripcion']) : null;

        Gasto::create($data);

        return redirect()->route('gastos.index')->with('ok', 'Gasto registrado correctamente.');
    }

    public function edit(Gasto $gasto)
    {
        return view('gastos.edit', compact('gasto'));
    }

    public function update(Request $request, Gasto $gasto)
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'categoria' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0'],
        ]);

        $data['categoria'] = trim($data['categoria']);
        $data['descripcion'] = isset($data['descripcion']) ? trim($data['descripcion']) : null;

        $gasto->update($data);

        return redirect()->route('gastos.index')->with('ok', 'Gasto actualizado correctamente.');
    }

    public function destroy(Gasto $gasto)
    {
        $gasto->delete();

        return redirect()->route('gastos.index')->with('ok', 'Gasto eliminado.');
    }
}