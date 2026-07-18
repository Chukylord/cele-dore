export function initFacturadorLocal() {
    const form = document.getElementById('formVenta');

    if (!form) return;

    const enlaces = Array.from(form.querySelectorAll('a'));
    const botonFacturar = enlaces.find(enlace => {
        const texto = (enlace.textContent || '').trim().toLowerCase();
        const href = enlace.getAttribute('href') || '';

        return texto.includes('facturar') || href.includes('afip.gob.ar');
    });

    if (!botonFacturar) return;

    botonFacturar.href = 'virfacturador://abrir';
    botonFacturar.removeAttribute('target');
    botonFacturar.title = 'Abrir el Facturador instalado en esta computadora';
    botonFacturar.textContent = '🧾 Abrir Facturador';

    const ayuda = document.createElement('div');
    ayuda.className = 'w-full text-right text-xs text-slate-400 mt-1';
    ayuda.textContent = 'La primera vez, Windows o Chrome puede pedir confirmación.';

    botonFacturar.parentElement?.appendChild(ayuda);
}
