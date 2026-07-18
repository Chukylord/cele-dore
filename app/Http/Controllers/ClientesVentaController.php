<?php

namespace App\Http\Controllers;

use App\Models\Cliente;

class ClientesVentaController extends Controller
{
    public function __invoke()
    {
        $clientes = Cliente::query()
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'dni', 'telefono']);

        return response()->json(
            $clientes->map(function (Cliente $cliente) {
                $nombre = trim($cliente->apellido . ' ' . $cliente->nombre);
                $dni = trim((string) $cliente->dni);

                return [
                    'id' => $cliente->id,
                    'nombre' => $nombre,
                    'dni' => $dni !== '' ? $dni : null,
                    'telefono' => $cliente->telefono,
                    'label' => $dni !== ''
                        ? $nombre . ' - DNI ' . $dni
                        : $nombre . ' - SIN DNI',
                ];
            })->values()
        );
    }
}
