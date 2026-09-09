<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Colaboradora;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TurnoVentaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Base aislada: las migraciones históricas con MODIFY ENUM no soportan SQLite.
        // Solo esta suite reproduce su esquema final; no altera migraciones ni la BD de la app.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (glob(database_path('migrations/*.php')) as $path) {
            $name = basename($path);
            if ($name === '2026_06_06_174406_update_metodo_pago_enum_in_ventas_table.php') {
                continue;
            }
            if ($name === '2026_06_23_000002_add_payment_structure_to_ventas_table.php') {
                Schema::table('ventas', function (Blueprint $table) {
                    $table->decimal('total_base', 12, 2)->default(0);
                    $table->decimal('recargo_tarjeta', 12, 2)->default(0);
                    $table->string('metodo_pago', 30)->nullable()->default(null)->change();
                });
                continue;
            }
            (require $path)->up();
        }

        $this->actingAs(User::factory()->create());
    }

    private function turno(bool $conColaboradora = true): Turno
    {
        return Turno::create([
            'cliente_id' => Cliente::create(['nombre' => 'Ana', 'apellido' => 'Pérez'])->id,
            'colaboradora_id' => $conColaboradora
                ? Colaboradora::create(['nombre' => 'Laura', 'apellido' => 'Gómez'])->id
                : null,
            'inicio' => '2026-09-10 10:30:00',
            'estado' => 'confirmado',
        ]);
    }

    private function datos(Turno $turno): array
    {
        return [
            'fecha' => '2026-09-10 10:30:00',
            'tipo_cliente' => 'cliente',
            'cliente_id' => $turno->cliente_id,
            'vendedora_id' => $turno->colaboradora_id,
            'tipo_pago' => 'efectivo',
            'servicios' => [[
                'servicio_id' => Servicio::create(['nombre' => 'Corte', 'precio' => 1000])->id,
                'precio' => 1000,
            ]],
        ];
    }

    public function test_venta_manual_sigue_funcionando_y_no_cambia_turnos(): void
    {
        $turno = $this->turno();
        $this->get(route('ventas.create'))->assertOk()->assertViewHas('turno', null)
            ->assertDontSee('Venta generada desde turno');
        $this->post(route('ventas.store'), $this->datos($turno))
            ->assertSessionHasNoErrors()->assertRedirect(route('ventas.index'));
        $this->assertNull(Venta::sole()->turno_id);
        $this->assertSame('confirmado', $turno->fresh()->estado);
        $this->assertDatabaseHas('venta_pagos', ['monto' => 1000, 'metodo_pago' => 'efectivo']);
    }

    public function test_precarga_por_ids_y_no_por_parametros_de_clienta_o_vendedora(): void
    {
        $turno = $this->turno();
        $this->get(route('ventas.create', ['turno_id' => $turno->id, 'cliente_id' => 999, 'vendedora_id' => 999]))
            ->assertOk()->assertViewHas('turno', fn ($origen) => $origen->is($turno))
            ->assertSee('Venta generada desde turno')->assertSee('Clienta: Ana Pérez')
            ->assertSee('Turno: 10/09/2026 10:30')
            ->assertSee('id="cliente_id" value="'.$turno->cliente_id.'"', false)
            ->assertSee('value="'.$turno->colaboradora_id.'"'."\n".'                                data-pct="10"'."\n".'                            selected', false);
    }

    public function test_precarga_sin_colaboradora_y_con_colaboradora_inactiva(): void
    {
        $turno = $this->turno(false);
        $this->get(route('ventas.create', ['turno_id' => $turno->id]))->assertOk()
            ->assertViewHas('turno', fn ($origen) => $origen->colaboradora_id === null);
        $colaboradora = Colaboradora::create(['nombre' => 'Inactiva', 'activa' => false]);
        $turno->update(['colaboradora_id' => $colaboradora->id]);
        $this->get(route('ventas.create', ['turno_id' => $turno->id]))->assertOk()
            ->assertViewHas('vendedoras', fn ($lista) => $lista->contains('id', $colaboradora->id))
            ->assertViewHas('colaboradoras', fn ($lista) => !$lista->contains('id', $colaboradora->id));
    }

    public function test_guarda_vinculo_estado_y_permite_cambios_manuales(): void
    {
        $turno = $this->turno();
        $datos = $this->datos($turno);
        $datos['turno_id'] = $turno->id;
        $datos['cliente_id'] = Cliente::create(['nombre' => 'Otra'])->id;
        $datos['vendedora_id'] = null;
        $this->post(route('ventas.store'), $datos)->assertSessionHasNoErrors();
        $venta = Venta::sole();
        $this->assertTrue($venta->turno->is($turno));
        $this->assertTrue($turno->fresh()->venta->is($venta));
        $this->assertSame('atendido', $turno->fresh()->estado);
        $this->assertEquals($datos['cliente_id'], $venta->cliente_id);
        $this->assertNull($venta->vendedora_id);
    }

    public function test_venta_pendiente_tambien_marca_atendido(): void
    {
        $turno = $this->turno(false);
        $this->post(route('ventas.store'), array_merge($this->datos($turno), [
            'turno_id' => $turno->id, 'condicion_pago' => 'pendiente',
        ]))->assertSessionHasNoErrors();
        $this->assertSame('atendido', $turno->fresh()->estado);
        $this->assertTrue(Venta::sole()->pendiente_pago);
        $this->assertDatabaseCount('venta_pagos', 0);
    }

    public function test_rechaza_duplicado_y_redirige_a_venta_existente(): void
    {
        $turno = $this->turno();
        $datos = $this->datos($turno) + ['turno_id' => $turno->id];
        $this->post(route('ventas.store'), $datos)->assertSessionHasNoErrors();
        $this->post(route('ventas.store'), $datos)->assertSessionHasErrors('turno_id');
        $this->assertDatabaseCount('ventas', 1);
        $this->assertDatabaseCount('venta_pagos', 1);
        $this->get(route('ventas.create', ['turno_id' => $turno->id]))
            ->assertRedirect(route('ventas.show', Venta::sole()));
    }

    public function test_indice_unico_impide_duplicados_fuera_del_controlador(): void
    {
        $turno = $this->turno();
        $this->post(route('ventas.store'), $this->datos($turno) + ['turno_id' => $turno->id]);
        $this->expectException(QueryException::class);
        Venta::sole()->replicate()->save();
    }

    public function test_fallo_de_pago_revierte_venta_y_estado_del_turno(): void
    {
        $turno = $this->turno();
        $this->post(route('ventas.store'), array_merge($this->datos($turno), [
            'turno_id' => $turno->id,
            'tipo_pago' => 'combinado',
            'pagos' => ['efectivo' => 100, 'transferencia' => 100],
        ]))->assertSessionHasErrors('pagos');
        $this->assertSame('confirmado', $turno->fresh()->estado);
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('venta_servicios', 0);
        $this->assertDatabaseCount('venta_pagos', 0);
    }

    public function test_valida_turno_en_get_y_post(): void
    {
        $turno = $this->turno();
        foreach ([9999, 'abc', ['1']] as $id) {
            $this->getJson(route('ventas.create', ['turno_id' => $id]))->assertUnprocessable()
                ->assertJsonValidationErrors('turno_id');
            $this->postJson(route('ventas.store'), $this->datos($turno) + ['turno_id' => $id])
                ->assertUnprocessable()->assertJsonValidationErrors('turno_id');
        }
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_rechaza_turno_con_clienta_inexistente(): void
    {
        $turno = $this->turno();
        $datos = $this->datos($turno);
        Schema::withoutForeignKeyConstraints(fn () => DB::table('turnos')->where('id', $turno->id)->update(['cliente_id' => 9999]));
        $this->getJson(route('ventas.create', ['turno_id' => $turno->id]))->assertUnprocessable()
            ->assertJsonValidationErrors('turno_id');
        $this->postJson(route('ventas.store'), $datos + ['turno_id' => $turno->id])->assertUnprocessable()
            ->assertJsonValidationErrors('turno_id');
    }

    public function test_eventos_incluyen_venta_y_eliminar_venta_conserva_estado(): void
    {
        $turno = $this->turno();
        $this->getJson(route('turnos.eventos'))->assertJsonPath('0.extendedProps.venta_id', null);
        $this->post(route('ventas.store'), $this->datos($turno) + ['turno_id' => $turno->id]);
        $venta = Venta::sole();
        $this->getJson(route('turnos.eventos'))->assertJsonPath('0.extendedProps.venta_id', $venta->id);
        $this->delete(route('ventas.destroy', $venta))->assertRedirect(route('ventas.index'));
        $this->assertSame('atendido', $turno->fresh()->estado);
        $this->assertNull($turno->fresh()->venta);
    }

    public function test_eliminar_turno_conserva_venta_con_fk_nula(): void
    {
        $turno = $this->turno();
        $this->post(route('ventas.store'), $this->datos($turno) + ['turno_id' => $turno->id]);
        $turno->delete();
        $this->assertNull(Venta::sole()->turno_id);
    }

    public function test_retorno_con_errores_conserva_origen_y_selecciones_manuales(): void
    {
        $turno = $this->turno();
        $otra = Cliente::create(['nombre' => 'Beatriz', 'apellido' => 'López']);
        $this->withSession(['_old_input' => ['cliente_id' => $otra->id, 'vendedora_id' => null]])
            ->get(route('ventas.create', ['turno_id' => $turno->id]))->assertOk()
            ->assertSee('id="cliente_id" value="'.$otra->id.'"', false)
            ->assertSee('López Beatriz -', false)
            ->assertSee('name="turno_id" value="'.$turno->id.'"', false);
    }

    public function test_modal_recupera_venta_existente_tras_error_de_turno(): void
    {
        $turno = $this->turno();
        $this->post(route('ventas.store'), $this->datos($turno) + ['turno_id' => $turno->id]);
        $this->withSession(['_old_input' => ['turno_id' => $turno->id]])
            ->get(route('turnos.index'))->assertOk()
            ->assertViewHas('ventaAnteriorId', Venta::sole()->id);
    }

    public function test_migracion_puede_revertirse_y_reaplicarse(): void
    {
        $migration = require database_path('migrations/2026_09_09_000001_add_turno_id_to_ventas_table.php');
        $migration->down();
        $this->assertFalse(Schema::hasColumn('ventas', 'turno_id'));
        $migration->up();
        $this->assertTrue(Schema::hasColumn('ventas', 'turno_id'));
    }
}
