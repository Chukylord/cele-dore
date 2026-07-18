async function copiarTexto(texto) {
    try {
        await navigator.clipboard.writeText(texto);
    } catch (error) {
        const textarea = document.createElement('textarea');
        textarea.value = texto;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        textarea.remove();
    }
}

export async function initClientesDniEnVenta() {
    const form = document.getElementById('formVenta');
    const input = document.getElementById('cliente_buscar');
    const hidden = document.getElementById('cliente_id');
    const datalist = document.getElementById('datalist_clientes');

    if (!form || !input || !hidden || !datalist || input.dataset.dniReady === '1') {
        return;
    }

    input.dataset.dniReady = '1';

    const info = document.createElement('div');
    info.className = 'mt-2 hidden rounded-xl border border-slate-600 bg-slate-800 px-3 py-2 text-sm';
    info.innerHTML = `
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <span class="text-slate-400">DNI:</span>
                <strong class="dni-seleccionado text-white ml-1">-</strong>
            </div>
            <button type="button"
                    class="copiar-dni-venta rounded-lg border border-slate-600 px-3 py-1 text-xs text-white hover:bg-slate-700">
                Copiar DNI
            </button>
        </div>
    `;

    hidden.after(info);

    const dniTexto = info.querySelector('.dni-seleccionado');
    const copiarBtn = info.querySelector('.copiar-dni-venta');
    const url = form.action.replace(/\/ventas\/?$/, '/clientes/busqueda-venta');

    let clientes = [];
    const porLabel = new Map();
    const porId = new Map();

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) return;

        clientes = await response.json();
    } catch (error) {
        return;
    }

    datalist.innerHTML = '';

    clientes.forEach(cliente => {
        porLabel.set(cliente.label, cliente);
        porId.set(String(cliente.id), cliente);

        const option = document.createElement('option');
        option.value = cliente.label;
        datalist.appendChild(option);
    });

    const mostrarCliente = cliente => {
        if (!cliente) {
            info.classList.add('hidden');
            dniTexto.textContent = '-';
            copiarBtn.disabled = true;
            copiarBtn.dataset.dni = '';
            return;
        }

        info.classList.remove('hidden');

        if (cliente.dni) {
            dniTexto.textContent = cliente.dni;
            dniTexto.className = 'dni-seleccionado text-white ml-1';
            copiarBtn.disabled = false;
            copiarBtn.dataset.dni = cliente.dni;
            copiarBtn.textContent = 'Copiar DNI';
        } else {
            dniTexto.textContent = 'Sin DNI cargado';
            dniTexto.className = 'dni-seleccionado text-amber-300 ml-1';
            copiarBtn.disabled = true;
            copiarBtn.dataset.dni = '';
            copiarBtn.textContent = 'Completar en Clientes';
        }
    };

    const sincronizar = () => {
        const cliente = porLabel.get((input.value || '').trim());

        hidden.value = cliente ? String(cliente.id) : '';
        mostrarCliente(cliente || null);
    };

    input.addEventListener('change', sincronizar);
    input.addEventListener('blur', sincronizar);

    copiarBtn.addEventListener('click', async function () {
        const dni = this.dataset.dni || '';
        if (!dni) return;

        await copiarTexto(dni);
        const original = this.textContent;
        this.textContent = 'DNI copiado';
        setTimeout(() => this.textContent = original, 1200);
    });

    const clienteInicial = porId.get(String(hidden.value || ''));

    if (clienteInicial) {
        input.value = clienteInicial.label;
        hidden.value = String(clienteInicial.id);
        mostrarCliente(clienteInicial);
    }
}
