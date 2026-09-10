<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Turno;
use App\Models\Venta;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    private function normalizarObservacion(?string $observacion): ?string
    {
        $observacion = trim((string) $observacion);

        if ($observacion === '') {
            return null;
        }

        $lineas = preg_split('/\r\n|\r|\n/', $observacion);

        $lineas = array_map(function ($linea) {
            return trim($linea);
        }, $lineas);

        $lineas = array_values(array_filter($lineas, function ($linea) {
            return $linea !== '';
        }));

        return count($lineas)
            ? implode(PHP_EOL, $lineas)
            : null;
    }

    /**
     * Normaliza el teléfono solamente para compararlo.
     * El teléfono original se sigue guardando tal como lo escribe la usuaria.
     */
    private function normalizarTelefono(?string $telefono): string
    {
        $telefono = preg_replace('/\D+/', '', (string) $telefono);

        if ($telefono === '') {
            return '';
        }

        // Ej: +54 9 341 1234567
        if (str_starts_with($telefono, '549') && strlen($telefono) > 10) {
            $telefono = substr($telefono, 3);
        }
        // Ej: +54 341 1234567
        elseif (str_starts_with($telefono, '54') && strlen($telefono) > 10) {
            $telefono = substr($telefono, 2);
        }

        // Ej: 0341... -> 341...
        if (str_starts_with($telefono, '0') && strlen($telefono) > 10) {
            $telefono = substr($telefono, 1);
        }

        // Nos quedamos con los últimos 10 dígitos para comparar
        if (strlen($telefono) > 10) {
            $telefono = substr($telefono, -10);
        }

        return $telefono;
    }

    /**
     * Busca otra clienta que tenga el mismo teléfono.
     */
    private function buscarClienteConTelefono(
        string $telefono,
        ?int $ignorarClienteId = null
    ): ?Cliente {
        $telefonoNormalizado = $this->normalizarTelefono($telefono);

        if ($telefonoNormalizado === '') {
            return null;
        }

        return Cliente::query()
            ->select('id', 'nombre', 'apellido', 'telefono')
            ->when($ignorarClienteId !== null, function ($query) use ($ignorarClienteId) {
                $query->where('id', '!=', $ignorarClienteId);
            })
            ->get()
            ->first(function ($cliente) use ($telefonoNormalizado) {
                return $this->normalizarTelefono($cliente->telefono)
                    === $telefonoNormalizado;
            });
    }

    public function index(Request $request)
    {
        $nombre = trim((string) $request->get('nombre', ''));
        $apellido = trim((string) $request->get('apellido', ''));

        $sort = $request->get('sort', 'ultima_compra');
        $dir = $request->get('dir', 'desc');

        $allowedSorts = [
            'nombre',
            'apellido',
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

            ->when(
                $sort === 'ultima_compra',
                function ($q) use ($dir) {
                    $q->orderByRaw('ultima_compra IS NULL ASC')
                        ->orderBy('ultima_compra', $dir);
                },
                function ($q) use ($sort, $dir) {
                    $q->orderBy($sort, $dir);
                }
            )

            ->paginate(10)
            ->withQueryString();

        return view(
            'clientes.index',
            compact(
                'clientes',
                'nombre',
                'apellido',
                'sort',
                'dir'
            )
        );
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function show(Cliente $cliente)
    {
        $ventas = Venta::query()
            ->where('cliente_id', $cliente->id)
            ->with([
                'servicios.servicio',
                'productos.producto',
                'vendedora',
            ])
            ->orderBy('fecha', 'desc')
            ->get();

        $historial = $ventas->map(function ($v) {
            $serviciosTxt = $v->servicios
                ->map(function ($s) {
                    return $s->servicio?->nombre
                        . ' ($'
                        . number_format((float) $s->precio, 2, ',', '.')
                        . ')';
                })
                ->implode(', ');

            $productosTxt = $v->productos
                ->map(function ($p) {
                    $nombre = trim(
                        ($p->producto?->marca ?? '')
                        . ' - '
                        . ($p->producto?->tipo ?? '')
                        . ' '
                        . ($p->producto?->contenido ?? '')
                    );

                    return $p->cantidad
                        . ' x '
                        . $nombre
                        . ' ($'
                        . number_format((float) $p->precio_unitario, 2, ',', '.')
                        . ')';
                })
                ->implode(', ');

            return [
                'fecha' => $v->fecha
                    ? $v->fecha->format('d/m/Y H:i')
                    : '',

                'servicios' => $v->subtotal_servicios,
                'productos' => $v->subtotal_productos,
                'total' => $v->total,

                'detalle_servicios' => $serviciosTxt ?: '-',
                'detalle_productos' => $productosTxt ?: '-',

                'vendedora' => $v->vendedora
                    ? $v->vendedora->nombre . ' ' . $v->vendedora->apellido
                    : '-',

                'metodo' => $v->metodo_pago,
                'venta_id' => $v->id,
            ];
        });

        return view(
            'clientes.show',
            compact('cliente', 'historial')
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'apellido' => [
                'required',
                'string',
                'max:255',
            ],

            'telefono' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'observacion' => [
                'nullable',
                'string',
            ],

            'confirmar_telefono_duplicado' => [
                'nullable',
                'boolean',
            ],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.min' => 'El teléfono debe tener al menos 8 caracteres.',
            'telefono.max' => 'El teléfono no puede superar los 20 caracteres.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, +, -, y paréntesis.',
        ]);

        $data['nombre'] = trim($data['nombre']);
        $data['apellido'] = trim($data['apellido']);
        $data['telefono'] = trim($data['telefono']);

        /*
         * Si todavía no confirmó, buscamos si ese teléfono
         * pertenece a otra clienta.
         */
        if (!$request->boolean('confirmar_telefono_duplicado')) {
            $duplicado = $this->buscarClienteConTelefono(
                $data['telefono']
            );

            if ($duplicado) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('cliente_duplicado', [
                        'id' => $duplicado->id,
                        'nombre' => $duplicado->nombre,
                        'apellido' => $duplicado->apellido,
                        'telefono' => $duplicado->telefono,
                    ]);
            }
        }

        unset($data['confirmar_telefono_duplicado']);

        $data['observacion'] = $this->normalizarObservacion(
            $data['observacion'] ?? null
        );

        Cliente::create($data);

        return redirect()
            ->route('clientes.index')
            ->with(
                'ok',
                'Cliente creado correctamente.'
            );
    }

    public function edit(Cliente $cliente)
    {
        return view(
            'clientes.edit',
            compact('cliente')
        );
    }

    public function update(
        Request $request,
        Cliente $cliente
    ) {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'apellido' => [
                'required',
                'string',
                'max:255',
            ],

            'telefono' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'observacion' => [
                'nullable',
                'string',
            ],

            'confirmar_telefono_duplicado' => [
                'nullable',
                'boolean',
            ],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.min' => 'El teléfono debe tener al menos 8 caracteres.',
            'telefono.max' => 'El teléfono no puede superar los 20 caracteres.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, +, -, y paréntesis.',
        ]);

        $data['nombre'] = trim($data['nombre']);
        $data['apellido'] = trim($data['apellido']);
        $data['telefono'] = trim($data['telefono']);

        /*
         * Al editar buscamos coincidencias,
         * pero ignoramos la propia clienta.
         */
        if (!$request->boolean('confirmar_telefono_duplicado')) {
            $duplicado = $this->buscarClienteConTelefono(
                $data['telefono'],
                $cliente->id
            );

            if ($duplicado) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('cliente_duplicado', [
                        'id' => $duplicado->id,
                        'nombre' => $duplicado->nombre,
                        'apellido' => $duplicado->apellido,
                        'telefono' => $duplicado->telefono,
                    ]);
            }
        }

        unset($data['confirmar_telefono_duplicado']);

        $data['observacion'] = $this->normalizarObservacion(
            $data['observacion'] ?? null
        );

        $cliente->update($data);

        return redirect()
            ->route('clientes.index')
            ->with(
                'ok',
                'Cliente actualizado correctamente.'
            );
    }

    public function destroy(Cliente $cliente)
    {
        $tieneTurnos = Turno::where(
            'cliente_id',
            $cliente->id
        )->exists();

        $tieneVentas = Venta::where(
            'cliente_id',
            $cliente->id
        )->exists();

        if ($tieneTurnos || $tieneVentas) {
            return redirect()
                ->route('clientes.index')
                ->with(
                    'ok',
                    'No se puede eliminar el cliente porque tiene turnos o ventas asociados.'
                );
        }

        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with(
                'ok',
                'Cliente eliminado.'
            );
    }
}