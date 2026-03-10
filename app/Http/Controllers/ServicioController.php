<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $nombre = trim((string) $request->get('nombre', ''));

        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir', 'desc');

        $allowedSorts = ['nombre', 'precio', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'created_at';
        $dir = $dir === 'asc' ? 'asc' : 'desc';

        $servicios = Servicio::query()
            ->when($nombre !== '', function ($q) use ($nombre) {
                $q->where('nombre', 'like', "%{$nombre}%");
            })
            ->orderBy($sort, $dir)
            ->paginate(10)
            ->withQueryString();

        return view('servicios.index', compact('servicios', 'nombre', 'sort', 'dir'));
    }

    public function create()
    {
        return view('servicios.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:servicios,nombre'],
            'precio' => ['required', 'numeric', 'min:0'],
        ], [
            'nombre.unique' => 'Ese servicio ya existe.',
        ]);

        $data['nombre'] = trim($data['nombre']);

        \App\Models\Servicio::create($data);

        return redirect()->route('servicios.index')->with('ok', 'Servicio creado correctamente.');
    }

    public function edit(Servicio $servicio)
    {
        return view('servicios.edit', compact('servicio'));
    }

    public function update(Request $request, \App\Models\Servicio $servicio)
    {
        $data = $request->validate([
            'nombre' => [
                'required', 'string', 'max:255',
                Rule::unique('servicios', 'nombre')->ignore($servicio->id),
            ],
            'precio' => ['required', 'numeric', 'min:0'],
        ], [
            'nombre.unique' => 'Ese servicio ya existe.',
        ]);

        $data['nombre'] = trim($data['nombre']);

        $servicio->update($data);

        return redirect()->route('servicios.index')->with('ok', 'Servicio actualizado correctamente.');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('servicios.index')->with('ok', 'Servicio eliminado.');
    }
}