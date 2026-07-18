<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    private function normalizarObservacion(?string $observacion): ?string
    {
        $observacion = trim((string) $observacion);

        if ($observacion === '') {
            return null;
        }

        $lineas = preg_split('/\r\n|\r|\n/', $observacion);
        $lineas = array_map(fn ($linea) => trim($linea), $lineas);
        $lineas = array_values(array_filter($lineas, fn ($linea) => $linea !== ''));

        return count($lineas) ? implode(PHP_EOL, $lineas) : null;
    }

    private function normalizarDni(?string $dni): ?string
    {
        $dni = preg_replace('/\D+/', '', (string) $dni);

        return $dni !== '' ? $dni : null;
    }

    public function index(Request $request)
    {
        $nombre = trim((string) $request->get('nombre', ''));
        $apellido = trim((string) $request->get('apellido', ''));
        $dni = trim((string) $request->get('dni', ''));

        $sort = $request->get('sort', 'ultima_compra');
        $dir = $request->get('dir', 'desc');

        $allowedSorts = [
            'nombre',
            'apellido',
            'dni',
            'ultima_compra',
            'prod_total',
            'serv_total',
            'created_at',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'ultima_compra';
        }

        $dir = $dir === 'asc' ? 'asc' : 'desc';

        $clientes = Cliente::query()
            ->select('clientes.*')
            ->selectSub(function ($q) {
                $q->from('ventas')
                    ->selectRaw('MAX(fecha)')
                    ->whereColumn('ventas.cliente_id', 'clientes.id');
            }, 'ultima_compra')
            ->selectSub(function ($q) {
                $q->from('ventas')
                    ->selectRaw('COALESCE(SUM(subtotal_productos), 0)')
                    ->whereColumn('ventas.cliente_id', 'clientes.id');
            }, 'prod_total')
            ->selectSub(function ($q) {
                $q->from('ventas')
                    ->selectRaw('COALESCE(SUM(subtotal_servicios), 0)')
                    ->whereColumn('ventas.cliente_id', 'clientes.id');
            }, 'serv_total')
            ->when($nombre !== '', function ($q) use ($nombre) {
                $q->where('nombre', 'like', "%{$nombre}%");
            })
            ->when($apellido !== '', function ($q) use ($apellido) {
                $q->where('apellido', 'like', "%{$apellido}%");
            })
            ->when($dni !== '', function ($q) use ($dni) {
                $dniLimpio = preg_replace('/\D+/', '', $dni);
                $q->where('dni', 'like', "%{$dniLimpio}%");
            })
            ->when($sort === 'ultima_compra', function ($q) use ($dir) {
                $q->orderByRaw('ultima_compra IS NULL ASC')
                    ->orderBy('ultima_compra', $dir);
            }, function ($q) use ($sort, $dir) {
                $q->orderBy($sort, $dir);
            })
            ->paginate(10)
            ->withQueryString();

        return view('clientes.index', compact(
            'clientes',
            'nombre',
            'apellido',
            'dni',
            'sort',
            'dir'
        ));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function show(Cliente $cliente)
    {
        $ventas = \App\Models\Venta::query()
            ->where('cliente_id', $cliente->id)
            ->with([
                'servicios.servicio',
                'productos.producto',
                'vendedora',
            ])
            ->orderBy('fecha', 'desc')
            ->get();

        $historial = $ventas->map(function ($venta) {
            $serviciosTxt = $venta->servicios->map(function ($servicioVenta) {
                return $servicioVenta->servicio?->nombre
                    . ' ($'
                    . number_format((float) $servicioVenta->precio, 2, ',', '.')
                    . ')';
            })->implode(', ');

            $productosTxt = $venta->productos->map(function ($productoVenta) {
                $nombre = trim(
                    ($productoVenta->producto?->marca ?? '')
                    . ' - '
                    . ($productoVenta->producto?->tipo ?? '')
                    . ' '
                    . ($productoVenta->producto?->contenido ?? '')
                );

                return $productoVenta->cantidad
                    . ' x '
                    . $nombre
                    . ' ($'
                    . number_format((float) $productoVenta->precio_unitario, 2, ',', '.')
                    . ')';
            })->implode(', ');

            return [
                'fecha' => $venta->fecha ? $venta->fecha->format('d/m/Y H:i') : '',
                'servicios' => $venta->subtotal_servicios,
                'productos' => $venta->subtotal_productos,
                'total' => $venta->total,
                'detalle_servicios' => $serviciosTxt ?: '-',
                'detalle_productos' => $productosTxt ?: '-',
                'vendedora' => $venta->vendedora
                    ? ($venta->vendedora->nombre . ' ' . $venta->vendedora->apellido)
                    : '-',
                'metodo' => $venta->metodo_pago,
                'venta_id' => $venta->id,
            ];
        });

        return view('clientes.show', compact('cliente', 'historial'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'dni' => [
                'nullable',
                'string',
                'min:7',
                'max:11',
                'regex:/^[0-9.\s]+$/',
                'unique:clientes,dni',
            ],
            'telefono' => ['required', 'string', 'min:8', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'observacion' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'dni.min' => 'El DNI debe tener al menos 7 números.',
            'dni.max' => 'El DNI no puede superar los 11 números.',
            'dni.regex' => 'El DNI solo puede contener números, puntos y espacios.',
            'dni.unique' => 'Ya existe una clienta con ese DNI.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.min' => 'El teléfono debe tener al menos 8 caracteres.',
            'telefono.max' => 'El teléfono no puede superar los 20 caracteres.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, +, -, y paréntesis.',
        ]);

        $data['nombre'] = trim($data['nombre']);
        $data['apellido'] = trim($data['apellido']);
        $data['dni'] = $this->normalizarDni($data['dni'] ?? null);
        $data['telefono'] = trim($data['telefono']);
        $data['observacion'] = $this->normalizarObservacion($data['observacion'] ?? null);

        Cliente::create($data);

        return redirect()->route('clientes.index')->with('ok', 'Cliente creado correctamente.');
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'dni' => [
                'nullable',
                'string',
                'min:7',
                'max:11',
                'regex:/^[0-9.\s]+$/',
                Rule::unique('clientes', 'dni')->ignore($cliente->id),
            ],
            'telefono' => ['required', 'string', 'min:8', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'observacion' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'dni.min' => 'El DNI debe tener al menos 7 números.',
            'dni.max' => 'El DNI no puede superar los 11 números.',
            'dni.regex' => 'El DNI solo puede contener números, puntos y espacios.',
            'dni.unique' => 'Ya existe una clienta con ese DNI.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.min' => 'El teléfono debe tener al menos 8 caracteres.',
            'telefono.max' => 'El teléfono no puede superar los 20 caracteres.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, +, -, y paréntesis.',
        ]);

        $data['nombre'] = trim($data['nombre']);
        $data['apellido'] = trim($data['apellido']);
        $data['dni'] = $this->normalizarDni($data['dni'] ?? null);
        $data['telefono'] = trim($data['telefono']);
        $data['observacion'] = $this->normalizarObservacion($data['observacion'] ?? null);

        $cliente->update($data);

        return redirect()->route('clientes.index')->with('ok', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $tieneTurnos = \App\Models\Turno::where('cliente_id', $cliente->id)->exists();
        $tieneVentas = \App\Models\Venta::where('cliente_id', $cliente->id)->exists();

        if ($tieneTurnos || $tieneVentas) {
            return redirect()
                ->route('clientes.index')
                ->with('ok', 'No se puede eliminar el cliente porque tiene turnos o ventas asociados.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')->with('ok', 'Cliente eliminado.');
    }
}
