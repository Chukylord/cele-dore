<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $nombre = trim((string) $request->get('nombre', ''));

        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir', 'desc');

        $allowedSorts = ['nombre', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'created_at';
        $dir = $dir === 'asc' ? 'asc' : 'desc';

        $proveedores = Proveedor::query()
            ->when($nombre !== '', function ($q) use ($nombre) {
                $q->where('nombre', 'like', "%{$nombre}%");
            })
            ->orderBy($sort, $dir)
            ->paginate(10)
            ->withQueryString();

        return view('proveedores.index', compact('proveedores', 'nombre', 'sort', 'dir'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:proveedores,nombre'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.unique' => 'Ese proveedor ya existe.',
        ]);

        $data['nombre'] = trim($data['nombre']);

        \App\Models\Proveedor::create($data);

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor creado correctamente.');
    }

    public function edit(Proveedor $proveedore)
    {
        // Si Laravel te lo inyecta como $proveedore por plural raro, lo manejamos así:
        $proveedor = $proveedore;
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, \App\Models\Proveedor $proveedor)
{
    $data = $request->validate([
        'nombre' => [
            'required', 'string', 'max:255',
            Rule::unique('proveedores', 'nombre')->ignore($proveedor->id),
        ],
    ], [
        'nombre.unique' => 'Ese proveedor ya existe.',
    ]);

    $data['nombre'] = trim($data['nombre']);

    $proveedor->update($data);

    return redirect()->route('proveedores.index')->with('ok', 'Proveedor actualizado correctamente.');
}

    public function destroy(Proveedor $proveedore)
    {
        $proveedore->delete();

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor eliminado.');
    }
}