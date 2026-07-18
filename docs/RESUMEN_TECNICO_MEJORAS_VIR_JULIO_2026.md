# Resumen técnico

## Base de datos

Nuevas migraciones:

- DNI en `clientes`.
- Medio de pago, usuario e indicador de impacto en caja para `gastos`.

## Ventas

La tabla `venta_pagos` se utiliza como historial de cobros. Una venta puede tener varios registros de pago y conserva saldo pendiente hasta cubrir el total base.

## Caja

Los ingresos se calculan por `venta_pagos.fecha_pago`. Los gastos que modifican la caja se identifican con `impacta_caja = true`.

## Compatibilidad

Las ventas antiguas pagadas que no poseen registros en `venta_pagos` continúan considerándose canceladas y se mantienen en los informes históricos.
