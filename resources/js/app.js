import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

import { initNuevaVentaPagosParciales } from './ventas-create-pagos';
import { initListadoVentasPagosParciales } from './ventas-index-pagos';
import { initClientesDniEnVenta } from './ventas-clientes-dni';
import { initFacturadorLocal } from './facturador-local';

window.FullCalendar = {
    Calendar,
    dayGridPlugin,
    timeGridPlugin,
    interactionPlugin,
};

document.addEventListener('DOMContentLoaded', function () {
    initNuevaVentaPagosParciales();
    initListadoVentasPagosParciales();
    initClientesDniEnVenta();
    initFacturadorLocal();
});
