<?php

namespace Tests\Unit;

use App\Http\Controllers\ProductoController;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

class ProductoReturnToTest extends TestCase
{
    #[DataProvider('retornosProvider')]
    public function test_solo_acepta_retornos_al_listado_de_productos(string $returnTo, ?string $esperado): void
    {
        $request = Request::create('/productos/1/edit', 'GET', ['return_to' => $returnTo]);
        $metodo = new ReflectionMethod(ProductoController::class, 'urlRetornoSegura');

        $this->assertSame($esperado, $metodo->invoke(new ProductoController, $request));
    }

    public static function retornosProvider(): array
    {
        return [
            'listado con filtros y pagina' => ['/productos?marca=Rigenol&sort=marca&dir=asc&page=2', '/productos?marca=Rigenol&sort=marca&dir=asc&page=2'],
            'sin retorno' => ['', null],
            'url externa' => ['https://example.com/productos', null],
            'url protocol relative' => ['//example.com/productos', null],
            'otra ruta interna' => ['/clientes?page=2', null],
            'fragmento' => ['/productos#otra-seccion', null],
            'barra invertida' => ['/productos\\@example.com', null],
        ];
    }
}
