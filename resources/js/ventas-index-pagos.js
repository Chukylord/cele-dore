function round2(value) {
    return Math.round((Number(value || 0) + Number.EPSILON) * 100) / 100;
}

function money(value) {
    return '$' + round2(value).toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

async function prepararModalCobroParcial(modal) {
    const form = modal?.querySelector('.form-cobro');

    if (!form || form.dataset.pagoParcialReady === '1') return;

    form.dataset.pagoParcialReady = '1';

    const selectTipo = form.querySelector('.tipo-pago-cobro');
    const resumenExistente = form.querySelector('.resumen-cobro');

    const resumenSaldo = document.createElement('div');
    resumenSaldo.className = 'mb-4 grid grid-cols-1 md:grid-cols-3 gap-3';
    resumenSaldo.innerHTML = `
        <div class="rounded-xl border bg-slate-50 p-3">
            <div class="text-xs text-slate-500">Total base venta</div>
            <div class="font-bold saldo-total-venta">$0,00</div>
        </div>
        <div class="rounded-xl border bg-green-50 p-3">
            <div class="text-xs text-green-700">Ya abonado</div>
            <div class="font-bold text-green-800 saldo-ya-abonado">$0,00</div>
        </div>
        <div class="rounded-xl border bg-amber-50 p-3">
            <div class="text-xs text-amber-700">Saldo pendiente</div>
            <div class="font-bold text-amber-800 saldo-pendiente-actual">$0,00</div>
        </div>
    `;

    selectTipo.parentElement?.before(resumenSaldo);

    const montoSimpleBox = document.createElement('div');
    montoSimpleBox.className = 'mt-4';
    montoSimpleBox.innerHTML = `
        <label class="text-sm font-semibold text-slate-700">Importe base que abona ahora *</label>
        <input type="number"
               step="0.01"
               min="0.01"
               name="monto_pago"
               class="monto-pago-simple mt-1 w-full rounded-xl border-slate-300 focus:border-slate-500 focus:ring-slate-500">
        <div class="text-xs text-slate-500 mt-1">
            Podés cobrar todo el saldo o solamente una parte.
        </div>
    `;

    selectTipo.parentElement?.after(montoSimpleBox);

    const montoSimple = montoSimpleBox.querySelector('.monto-pago-simple');
    let datosSaldo = null;

    const urlSaldo = form.action.replace(/\/marcar-pagado\/?$/, '/saldo');

    try {
        const response = await fetch(urlSaldo, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) throw new Error('No se pudo consultar el saldo.');

        datosSaldo = await response.json();
        form.dataset.totalBase = String(datosSaldo.saldo_base || 0);
        montoSimple.value = Number(datosSaldo.saldo_base || 0).toFixed(2);

        resumenSaldo.querySelector('.saldo-total-venta').textContent = money(datosSaldo.total_base);
        resumenSaldo.querySelector('.saldo-ya-abonado').textContent = money(datosSaldo.pagado_base);
        resumenSaldo.querySelector('.saldo-pendiente-actual').textContent = money(datosSaldo.saldo_base);
    } catch (error) {
        const saldoFallback = Number(form.dataset.totalBase || 0);
        datosSaldo = {
            total_base: saldoFallback,
            pagado_base: 0,
            saldo_base: saldoFallback,
        };
        montoSimple.value = saldoFallback.toFixed(2);
    }

    const actualizarCobro = () => {
        const saldo = Number(datosSaldo?.saldo_base || 0);
        const tipo = selectTipo.value;
        const combinado = tipo === 'combinado';
        const boxCombinado = form.querySelector('.box-combinado-cobro');
        const inputsCombinados = Array.from(form.querySelectorAll('.pago-cobro'));

        montoSimpleBox.classList.toggle('hidden', combinado);
        montoSimple.disabled = combinado;
        boxCombinado?.classList.toggle('hidden', !combinado);
        inputsCombinados.forEach(input => input.disabled = !combinado);

        let basePago = Number(montoSimple.value || 0);
        let tarjetaBase = tipo === 'tarjeta' ? basePago : 0;

        if (combinado) {
            basePago = inputsCombinados.reduce((total, input) => total + Number(input.value || 0), 0);
            const tarjetaInput = form.querySelector('input[name="pagos[tarjeta]"]');
            tarjetaBase = Number(tarjetaInput?.value || 0);
        }

        basePago = round2(basePago);
        const recargo = round2(tarjetaBase * 0.20);
        const saldoPosterior = round2(saldo - basePago);

        const recargoEl = form.querySelector('.recargo-cobro');
        const totalEl = form.querySelector('.total-cobro');
        const estadoEl = form.querySelector('.estado-cobro');

        if (recargoEl) recargoEl.textContent = money(recargo);
        if (totalEl) totalEl.textContent = money(basePago + recargo);

        if (estadoEl && combinado) {
            if (basePago <= 0) {
                estadoEl.textContent = 'Ingresá los importes que abona ahora.';
                estadoEl.className = 'estado-cobro mt-3 rounded-xl border px-3 py-2 text-sm text-slate-600';
            } else if (saldoPosterior < -0.01) {
                estadoEl.textContent = 'El pago supera el saldo por: ' + money(Math.abs(saldoPosterior));
                estadoEl.className = 'estado-cobro mt-3 rounded-xl border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700';
            } else if (saldoPosterior <= 0.01) {
                estadoEl.textContent = 'Este pago cancela completamente la venta.';
                estadoEl.className = 'estado-cobro mt-3 rounded-xl border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-700';
            } else {
                estadoEl.textContent = 'Después de este pago quedará pendiente: ' + money(saldoPosterior);
                estadoEl.className = 'estado-cobro mt-3 rounded-xl border border-blue-300 bg-blue-50 px-3 py-2 text-sm text-blue-700';
            }
        }

        if (resumenExistente) {
            const primerStrong = resumenExistente.querySelector('strong');
            if (primerStrong) primerStrong.textContent = money(saldo);
        }
    };

    selectTipo.addEventListener('change', actualizarCobro);
    montoSimple.addEventListener('input', actualizarCobro);
    form.querySelectorAll('.pago-cobro').forEach(input => {
        input.addEventListener('input', actualizarCobro);
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        const saldo = Number(datosSaldo?.saldo_base || 0);
        const combinado = selectTipo.value === 'combinado';
        const inputs = Array.from(form.querySelectorAll('.pago-cobro'));

        let basePago = Number(montoSimple.value || 0);

        if (combinado) {
            const valores = inputs.map(input => Number(input.value || 0));
            basePago = valores.reduce((total, value) => total + value, 0);

            if (valores.filter(value => value > 0).length < 2) {
                alert('Para pago combinado usá al menos dos formas de pago.');
                return;
            }
        }

        if (basePago <= 0) {
            alert('Ingresá un importe mayor a $0.');
            return;
        }

        if (basePago - saldo > 0.01) {
            alert('El pago no puede superar el saldo pendiente.');
            return;
        }

        form.submit();
    }, true);

    actualizarCobro();
}

export function initListadoVentasPagosParciales() {
    document.addEventListener('click', function (event) {
        const boton = event.target.closest('[data-open-cobro]');

        if (!boton) return;

        window.setTimeout(() => {
            const modal = document.getElementById('modal-cobro-' + boton.dataset.openCobro);
            prepararModalCobroParcial(modal);
        }, 0);
    });
}
