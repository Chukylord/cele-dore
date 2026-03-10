<?php

namespace App\Http\Controllers;

use App\Models\Colaboradora;
use Illuminate\Http\Request;

class ColaboradoraController extends Controller
{
    public function index(Request $request)
    {
        $nombre   = trim((string) $request->get('nombre', ''));
        $apellido = trim((string) $request->get('apellido', ''));
        $activa   = $request->get('activa', ''); // '' | '1' | '0'

        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir', 'desc');

        $allowedSorts = ['nombre', 'apellido', 'activa', 'comision_pct', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'created_at';
        $dir = $dir === 'asc' ? 'asc' : 'desc';

        $colaboradoras = Colaboradora::query()
            ->when($nombre !== '', function ($q) use ($nombre) {
                $q->where('nombre', 'like', "%{$nombre}%");
            })
            ->when($apellido !== '', function ($q) use ($apellido) {
                $q->where('apellido', 'like', "%{$apellido}%");
            })
            ->when($activa !== '', function ($q) use ($activa) {
                $q->where('activa', $activa === '1');
            })
            ->orderBy($sort, $dir)
            ->paginate(10)
            ->withQueryString();

        return view('colaboradoras.index', compact('colaboradoras', 'nombre', 'apellido', 'activa', 'sort', 'dir'));
    }

    public function create()
    {
        return view('colaboradoras.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:255'],
            'apellido'     => ['nullable', 'string', 'max:255'],
            'telefono'     => ['nullable', 'string', 'max:50'],
            'activa'       => ['nullable'], // checkbox
            'comision_pct' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $data['activa'] = $request->has('activa');

        Colaboradora::create($data);

        return redirect()->route('colaboradoras.index')->with('ok', 'Colaboradora creada correctamente.');
    }

    public function edit(Colaboradora $colaboradora)
    {
        return view('colaboradoras.edit', compact('colaboradora'));
    }

    public function update(Request $request, Colaboradora $colaboradora)
    {
        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:255'],
            'apellido'     => ['nullable', 'string', 'max:255'],
            'telefono'     => ['nullable', 'string', 'max:50'],
            'activa'       => ['nullable'],
            'comision_pct' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $data['activa'] = $request->has('activa');

        $colaboradora->update($data);

        return redirect()->route('colaboradoras.index')->with('ok', 'Colaboradora actualizada correctamente.');
    }

    public function destroy(Colaboradora $colaboradora)
    {
        $colaboradora->delete();

        return redirect()->route('colaboradoras.index')->with('ok', 'Colaboradora eliminada.');
    }
}