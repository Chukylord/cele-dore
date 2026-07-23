function round2(value) {
    return Math.round((Number(value || 0) + Number.EPSILON) * 100) / 100;
}

function money(value) {
    return '$' + round2(value).toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function parseMoneyText(text) {
    const raw = String(text || '').replace(/[^0-9,.-]/g, '');

    if (!raw) return 0;

    if (raw.includes(',')) {
        return Number(raw.replace(/\./g, '').replace(',', '.')) || 0;
    }

    return Number(raw) || 0;
}

export function initNuevaVentaPagosParciales() {
    const form = document.getElementById('formVenta');
    const tipoPago = document.getElementById('tipo_pago');
    const pendienteOriginal = document.getElementById('pendiente_pago');

    if (!form || !tipoPago || !pendienteOriginal || form.dataset.pagosParcialesReady === '1') {
        return;
    }

    form.dataset.pagosParcialesReady = '1';

    const condicionUi = document.getElementById('condicion_pago_ui');
    const condicionHidden = document.getElementById('condicion_pago');
    const montoBox = document.getElementById('monto_pago_parcial_box');
    const montoInput = document.getElementById('monto_pago');
    const totalBaseEl = document.getElementById('totalBaseResumen');
    const totalFinalEl = document.getElementById('totalFinal');
    const recargoEl = document.getElementById('recargoTarjetaResumen');
    const tituloTotal = totalFinalEl?.previousElementSibling;
    const estadoCombinado = document.getElementById('estadoDistribucionPago');

    const resumenParcial = document.getElementById('resumen_pago_parcial');

    if (!condicionUi || !condicionHidden || !montoBox || !montoInput || !resumenParcial) {
        return;
    }

    condicionUi.value = pendienteOriginal.checked ? 'pendiente' : 'completo';
    condicionHidden.value = condicionUi.value;

    const totalBaseActual = () => parseMoneyText(totalBaseEl?.textContent);

    const datosPagoActual = () => {
        const condicion = condicionUi.value;
        const totalBase = totalBaseActual();
        const tipo = tipoPago.value;

        if (condicion === 'pendiente') {
            return { condicion, totalBase, basePago: 0, recargo: 0, saldo: totalBase };
        }

        if (condicion === 'completo' && tipo !== 'combinado') {
            const tarjetaBase = tipo === 'tarjeta' ? totalBase : 0;
            return {
                condicion,
                totalBase,
                basePago: totalBase,
                recargo: round2(tarjetaBase * 0.20),
                saldo: 0,
            };
        }

        if (tipo === 'combinado') {
            const efectivo = Number(document.getElementById('pago_efectivo')?.value || 0);
            const transferencia = Number(document.getElementById('pago_transferencia')?.value || 0);
            const tarjeta = Number(document.getElementById('pago_tarjeta')?.value || 0);
            const basePago = round2(efectivo + transferencia + tarjeta);

            return {
                condicion,
                totalBase,
                basePago,
                recargo: round2(tarjeta * 0.20),
                saldo: round2(totalBase - basePago),
            };
        }

        const basePago = Number(montoInput.value || 0);
        const tarjetaBase = tipo === 'tarjeta' ? basePago : 0;

        return {
            condicion,
            totalBase,
            basePago: round2(basePago),
            recargo: round2(tarjetaBase * 0.20),
            saldo: round2(totalBase - basePago),
        };
    };

    const actualizarInterfaz = () => {
        condicionHidden.value = condicionUi.value;

        const esPendiente = condicionUi.value === 'pendiente';
        const esParcial = condicionUi.value === 'parcial';
        const esCombinado = tipoPago.value === 'combinado';
        const cambioPendiente = pendienteOriginal.checked !== esPendiente;

        pendienteOriginal.checked = esPendiente;

        if (cambioPendiente) {
            pendienteOriginal.dispatchEvent(new Event('change', { bubbles: true }));
        }

        montoBox.classList.toggle('hidden', !esParcial || esCombinado);
        montoInput.disabled = !esParcial || esCombinado;
        resumenParcial.classList.toggle('hidden', !esParcial);

        window.setTimeout(() => {
            const datos = datosPagoActual();

            if (datos.condicion === 'pendiente') {
                if (tituloTotal) tituloTotal.textContent = 'TOTAL DE LA VENTA';
                if (recargoEl) recargoEl.textContent = money(0);
                if (totalFinalEl) totalFinalEl.textContent = money(datos.totalBase);
                return;
            }

            if (datos.condicion === 'completo') {
                if (tituloTotal) tituloTotal.textContent = 'TOTAL A COBRAR';
                return;
            }

            if (tituloTotal) tituloTotal.textContent = 'COBRO DE HOY';
            if (recargoEl) recargoEl.textContent = money(datos.recargo);
            if (totalFinalEl) totalFinalEl.textContent = money(datos.basePago + datos.recargo);

            const baseAbonada = document.getElementById('base_abonada_hoy');
            const saldoPendiente = document.getElementById('saldo_base_pendiente');

            if (baseAbonada) baseAbonada.textContent = money(datos.basePago);
            if (saldoPendiente) saldoPendiente.textContent = money(Math.max(datos.saldo, 0));

            if (esCombinado && estadoCombinado) {
                if (datos.basePago <= 0) {
                    estadoCombinado.textContent = 'Ingresá los importes que abona hoy.';
                } else if (datos.saldo < -0.01) {
                    estadoCombinado.textContent = 'El pago supera el total por: ' + money(Math.abs(datos.saldo));
                } else {
                    estadoCombinado.textContent = 'Pago parcial correcto. Quedará pendiente: ' + money(datos.saldo);
                }
            }
        }, 0);
    };

    condicionUi.addEventListener('change', actualizarInterfaz);
    tipoPago.addEventListener('change', actualizarInterfaz);
    montoInput.addEventListener('input', actualizarInterfaz);

    document.querySelectorAll('.pago-combinado').forEach(input => {
        input.addEventListener('input', actualizarInterfaz);
    });

    if (totalBaseEl) {
        new MutationObserver(actualizarInterfaz).observe(totalBaseEl, {
            childList: true,
            characterData: true,
            subtree: true,
        });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const datos = datosPagoActual();

        if (datos.totalBase <= 0) {
            alert('Agregá al menos un servicio o producto con importe mayor a $0.');
            return;
        }

        if (datos.condicion === 'pendiente') {
            form.submit();
            return;
        }

        if (tipoPago.value === 'combinado') {
            const valores = Array.from(document.querySelectorAll('.pago-combinado'))
                .filter(input => !input.disabled)
                .map(input => Number(input.value || 0));

            if (valores.filter(value => value > 0).length < 2) {
                alert('Para pago combinado usá al menos dos formas de pago.');
                return;
            }
        }

        if (datos.basePago <= 0) {
            alert('Ingresá cuánto abona la clienta.');
            return;
        }

        if (datos.basePago - datos.totalBase > 0.01) {
            alert('El pago no puede superar el total base de la venta.');
            return;
        }

        if (datos.condicion === 'completo' && Math.abs(datos.basePago - datos.totalBase) > 0.01) {
            alert('Para pago completo, la distribución debe cubrir todo el total base.');
            return;
        }

        form.submit();
    }, true);

    actualizarInterfaz();
}
