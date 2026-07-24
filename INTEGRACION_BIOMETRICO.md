# Integracion directa con reloj biometrico

Fecha de analisis: 18 de julio de 2026

## Conclusion rapida

Si es posible evitar el Excel, pero la via exacta depende del equipo de huella dactilar.
La mejor alternativa no se decide por software primero, sino por la tecnologia que ya trae el reloj.

## Caminos recomendados

1. `sql`
Si el biometrico guarda marcaciones en una base de datos local o en un servidor intermedio, esta suele ser la mejor opcion.
Ventajas: estable, auditable y facil de sincronizar por lotes.

2. `api`
Si el proveedor ofrece API o SDK oficial, esta es la opcion mas limpia a nivel de soporte.
Ventajas: menor riesgo de romper compatibilidad con futuras actualizaciones del dispositivo.

3. `zk`
Si el equipo es compatible con ZKTeco o protocolo similar, se puede construir un extractor directo por IP.
Ventajas: elimina el Excel y permite sincronizacion automatica.
Riesgos: depende bastante del modelo exacto y de la red local.

4. `archivo`
Es el plan de respaldo.
Se mantiene por seguridad mientras se valida la conexion directa.

## Datos que hacen falta para implementarlo

- Marca y modelo del equipo biometrico.
- Si el equipo exporta por red, USB, API o base de datos.
- IP, puerto y credenciales si trabaja en red.
- Si existe software del proveedor instalado en algun equipo.
- Nombre del motor de base de datos si el proveedor guarda logs en SQL Server, MySQL u otro.

## Recomendacion para este proyecto

Primero mantener `archivo` como respaldo.
En paralelo, preparar la integracion directa con uno de estos dos caminos:

- `sql`, si el proveedor ya deja las marcaciones en una base intermedia.
- `zk`, si el reloj expone conexion IP directa y es compatible.

## Preparacion dejada en el proyecto

Ya se agrego:

- `config/biometrico.php`
- `app/Services/ConexionBiometricoService.php`
- variables `BIOMETRICO_*` en `.env.example`

Eso deja listo el punto de entrada para pasar de Excel a sincronizacion automatica cuando tengamos los datos del dispositivo.
