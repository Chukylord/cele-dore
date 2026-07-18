# Mejoras Vir Tisone — julio 2026

## Funcionalidades incluidas

- Pagos parciales al crear una venta.
- Varios cobros posteriores sobre una misma venta.
- Saldo pendiente calculado automáticamente.
- Recargo del 20% aplicado solamente a la parte abonada con tarjeta.
- Caja diaria disponible para empleadas.
- Cierre de caja disponible para empleadas, registrando quién la cerró.
- Registro rápido de gastos desde la caja, sin acceso de empleadas al historial general.
- Gastos en efectivo descontados del efectivo esperado.
- DNI de clientas en altas, listados y selector de nueva venta.
- Botón para copiar DNI.
- Escáner de productos para consumo interno de peluquería.
- Informe de cantidades de servicios, productos, atenciones y clientas diferentes.
- Apertura del Facturador local mediante el protocolo `virfacturador://`.

## Instalación local del Facturador

En la computadora de Vir, ejecutar una sola vez:

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\instalar-protocolo-facturador.ps1
```

El script busca en el Escritorio un acceso directo cuyo nombre contenga `Facturador` y registra el protocolo local para abrirlo desde el sistema.

## Comandos al probar la rama

```powershell
git switch mejoras-vir-julio-2026
git pull
php artisan migrate
npm run build
php artisan optimize:clear
```

No usar `php artisan migrate:fresh`, porque elimina los datos existentes.
