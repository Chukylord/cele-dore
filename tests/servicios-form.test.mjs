import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import assert from 'node:assert/strict';
import test from 'node:test';

// Ejecuta la función real de la vista con un DOM mínimo, sin dependencias adicionales.
const vista = readFileSync(new URL('../resources/views/ventas/create.blade.php', import.meta.url), 'utf8');
const codigo = vista.slice(vista.indexOf('let siguienteServicioIndice'), vista.indexOf('function addProductoRow'));

function formulario() {
    const tbody = { children: [], appendChild(row) { this.children.push(row); } };
    const document = {
        querySelector: () => tbody,
        createElement() {
            const campos = new Map();
            return {
                innerHTML: '',
                querySelector(selector) {
                    if (!campos.has(selector)) {
                        campos.set(selector, {
                            value: '', events: {},
                            addEventListener(name, callback) { this.events[name] = callback; },
                        });
                    }
                    return campos.get(selector);
                },
                remove() { tbody.children.splice(tbody.children.indexOf(this), 1); },
            };
        },
    };
    const context = { document, SERVICIOS_MAP: { Corte: 7, Color: 9 }, SERVICIOS_PRECIO: { 7: 1500, 9: 3000 }, recalcular() {} };
    runInNewContext(codigo, context);
    return { add: context.addServicioRow, rows: tbody.children };
}

test('precarga por ID aplica el mismo precio que una selección manual', () => {
    const { add, rows } = formulario();
    add({ servicio_id: 9 });
    add();
    rows[1].querySelector('.serv-text').value = 'Color';
    rows[1].querySelector('.serv-text').events.change();
    for (const row of rows) {
        assert.equal(row.querySelector('.serv-id').value, '9');
        assert.equal(row.querySelector('.precio-serv').value, '3000');
    }
});

test('restauración conserva precio editado, detalle y descuento', () => {
    const { add, rows } = formulario();
    add({ servicio_id: 7, precio: '0', detalle: 'Cambio de color', descuento_pct: '15' }, true);
    assert.equal(rows[0].querySelector('.precio-serv').value, '0');
    assert.equal(rows[0].querySelector('textarea').value, 'Cambio de color');
    assert.equal(rows[0].querySelector('.desc-pct').value, '15');
});

test('quitar una fila y agregar otra conserva índices únicos para el envío', () => {
    const { add, rows } = formulario();
    add({ servicio_id: 7 });
    add({ servicio_id: 9 });
    rows[0].querySelector('.btn-remove-servicio').events.click();
    add({ servicio_id: 7 });
    assert.equal(rows.length, 2);
    assert.match(rows[0].innerHTML, /name="servicios\[1\]\[servicio_id\]"/);
    assert.match(rows[1].innerHTML, /name="servicios\[2\]\[servicio_id\]"/);
});
