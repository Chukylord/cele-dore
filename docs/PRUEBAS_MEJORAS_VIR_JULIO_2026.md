# Checklist de pruebas

## Ventas y pagos parciales

1. Crear una venta de $100.000 con pago parcial de $40.000 en efectivo.
2. Confirmar que el saldo quede en $60.000.
3. Registrar otro pago de $20.000 por transferencia.
4. Confirmar que el saldo quede en $40.000.
5. Registrar el saldo con tarjeta y verificar que el 20% se aplique solo sobre esos $40.000.
6. Confirmar que la venta quede como pagada.
7. Revisar que cada cobro figure en la caja del día correspondiente.

## Caja diaria

1. Ingresar con una cuenta de empleada.
2. Abrir la caja.
3. Registrar un gasto en efectivo.
4. Verificar que el gasto descuente el efectivo esperado.
5. Confirmar que la empleada no pueda abrir el historial general de gastos.
6. Cerrar la caja desde la cuenta de empleada.
7. Verificar que quede registrado quién cerró la caja.

## Clientes y DNI

1. Editar una clienta existente y agregar su DNI.
2. Crear una venta y verificar que el selector muestre apellido, nombre y DNI.
3. Probar el botón Copiar DNI.
4. Intentar cargar un DNI duplicado y confirmar que el sistema lo rechace.

## Productos

1. Escanear un código de barras desde Productos.
2. Confirmar que muestre el producto correcto y su stock.
3. Registrar una unidad para uso interno.
4. Verificar que baje stock de venta y aumente stock de peluquería.

## Informes

1. Filtrar un mes completo.
2. Comparar cantidades de servicios con ventas cargadas.
3. Comparar unidades de productos vendidas.
4. Revisar total de atenciones y clientas diferentes.

## Facturador

1. Ejecutar `tools/instalar-protocolo-facturador.ps1` una sola vez.
2. Abrir Nueva venta.
3. Presionar Abrir Facturador.
4. Aceptar el aviso de Chrome o Windows la primera vez.
5. Confirmar que se abra la aplicación instalada.
