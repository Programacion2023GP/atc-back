# Instrucciones operativas para `atc-back`

## Arquitectura y alcance

- Esta raíz contiene la API Laravel 9 del Sistema de Atención Ciudadana; la SPA hermana está en `../atc-front`.
- Las rutas del producto viven bajo `/api/gomezapp` y se declaran en `routes/gomezapp.routes.php`.
- Consulta `CONTEXTO_SISTEMA.md` antes de modificar reportes ATC, Secretaría Particular, roles, vistas SQL o contratos HTTP.

## Invariantes de dominio

- Reportes ATC nuevos: `id_estatus=1` (`ALTA`). Catálogo confirmado: `ALTA`, `EN TRAMITE`, `NO PROCEDE`, `TERMINADO`.
- Una respuesta ATC reemplaza la anterior; eliminarla restablece `ALTA` y limpia sus adjuntos.
- SP solo se completa cuando hay respuesta y primera evidencia. Conserva `COMPLETA - FUERA DE TIEMPO` para solicitudes atrasadas.
- La vista de incumplimiento usa un umbral mayor a cinco días hábiles y actualmente solo excluye fines de semana.
- Respeta bajas lógicas donde el modelo usa `active`/`deleted_at`; no conviertas una baja lógica en borrado físico sin decisión explícita.
- No cambies IDs sembrados, roles especiales ni significado de permisos sin coordinar migraciones, menús y frontend.

## Seguridad y datos

- Mantén las rutas operativas bajo `auth:sanctum`; cualquier excepción pública requiere revisión explícita.
- No confíes en los permisos visuales de la SPA como autorización. Para trabajo nuevo, aplica autorización en servidor.
- Valida payloads y archivos (tipo, tamaño, presencia y propiedad del registro) antes de persistir.
- Usa transacciones para escrituras compuestas de usuario/reporte/asunto/respuesta.
- No leas, imprimas, versionees ni copies secretos de `.env`; no incluyas datos personales en logs o fixtures.
- La conexión de dominio usada por modelos es `mysql_gomezapp`; verifica la conexión correcta en consultas directas.

## Esquema y contratos

- No asumas que la base puede reconstruirse hoy solo con migraciones: existe desfase documentado entre columnas usadas y migraciones visibles. Todo cambio de esquema nuevo sí debe tener migración reversible.
- Conserva compatibilidad con MySQL o documenta/reemplaza las vistas que usan funciones específicas del motor.
- Antes de cambiar una ruta o payload, busca sus consumidores en `../atc-front/src/context` y `../atc-front/src/components`.
- No uses `MenuController copy.php` como fuente activa.

## Verificación

- Ejecuta `php artisan route:list --path=api/gomezapp` tras cambiar rutas.
- Ejecuta `php artisan test`; la suite actual solo tiene pruebas de plantilla, por lo que agrega pruebas de negocio para cambios funcionales.
- Para cambios HTTP compartidos, ejecuta también `npm run build` en `../atc-front` y prueba el flujo completo.
- Distingue avisos deprecados preexistentes de fallos introducidos.

