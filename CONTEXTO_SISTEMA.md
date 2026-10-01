# Contexto del Sistema de Atención Ciudadana

Última revisión: 2026-09-30  
Alcance comprobado: este backend `atc-back` y la SPA hermana `../atc-front`.  
Estado: fuente de verdad derivada del código, rutas, migraciones, seeders y verificaciones disponibles. No representa el contenido de una base de datos viva.

## 1. Resumen ejecutivo

El sistema gestiona atención ciudadana para el municipio de Gómez Palacio. La SPA administrativa captura y consulta información; esta API autentica usuarios, conserva catálogos y relaciones organizacionales, registra reportes/solicitudes, respuestas y evidencias, y alimenta tableros y reportes.

Hay dos dominios operativos:

- **ATC:** reportes de ciudadanos, georreferenciados y clasificados por dependencia, asunto, servicio, jornada, origen y estatus.
- **Secretaría Particular (SP):** solicitudes dirigidas a dependencias con respuesta, evidencia, papelería y medición de incumplimiento.
- **Solicitudes Internas:** oficios emitidos por departamentos habilitados —inicialmente Oficialía Mayor— hacia otras dependencias, con folio anual, historial de estados, ciclos de SLA, respuestas y evidencias en tablas y endpoints independientes de ATC/SP.

## 2. Arquitectura

| Capa | Implementación |
|---|---|
| Cliente | React 18/Vite en `../atc-front`; Axios con Bearer token y generación local de PDF/Excel. |
| API | PHP `^8.0.2`, Laravel `^9.19`, rutas bajo `/api/gomezapp`. |
| Autenticación | Laravel Sanctum; rutas operativas agrupadas con `auth:sanctum`. |
| Persistencia | MySQL mediante la conexión de dominio `mysql_gomezapp`; tablas y vistas SQL. |
| Archivos | Evidencias movidas a directorios dentro de `public/GomezApp`. |
| Integraciones | Correo para recuperación, API municipal de CP/comunidades desde la SPA y endpoints para aplicación móvil. |

## 3. Módulos del backend

| Módulo | Controladores/modelos principales | Responsabilidad |
|---|---|---|
| Identidad | `UserController`, `User` | Login/logout, alta, recuperación, CRUD y tokens. |
| Roles y menús | `RoleController`, `MenuController` | Matriz de permisos almacenada como listas de IDs de menú, navegación y activación. |
| Organización | `DepartmentController`, `UsuariosDepController`, `AsuntosDepController` | Dependencias, usuarios asignados y asuntos atendidos. |
| Catálogos | `appController`, `JornadaController`, `ServiceController`, `OrigenController`, `EstatusController` | Asuntos, jornadas, servicios, orígenes y estados. |
| Reportes ATC | `ReportController`, `AtcAppController`, modelos `Report*` | Alta/edición, respuesta, evidencias, consultas, app móvil y baja lógica. |
| Secretaría Particular | `SParticularController`, modelos `SParticular`, `SpRequests` | Solicitudes, respuesta, evidencias, papelería, cierre e incumplimiento. |
| Reporteo | `VConcentradoController` y vistas SQL | Concentrados, tarjetas, estadísticas, filtros y vistas para la SPA. |

## 4. Actores y autorización

Los seeders definen `SuperAdmin`, `Administrador`, `Ciudadano`, `Encargado`, `SecretariaP`, `CapturaSP` y `EncargadaSP`. Los roles almacenan `read`, `create`, `update`, `delete` y `more_permissions`; sus valores son `todas`, listas CSV de IDs de menú o permisos textuales.

- SuperAdmin/Administrador configuran sistema y catálogos.
- Encargado atiende reportes relacionados con sus dependencias.
- SecretariaP, CapturaSP y EncargadaSP operan el dominio SP con alcances distintos.
- Ciudadano identifica al solicitante/reporter; no se comprobó que acceda a la SPA administrativa.
- La aplicación móvil consulta reportes de la dependencia codificada como ID 15 y envía respuestas con un token compartido de ambiente.

Sanctum autentica las rutas agrupadas. No se encontró middleware o policy que imponga en el servidor la matriz `read/create/update/delete`; la SPA calcula esos permisos y oculta/redirige opciones. Por tanto, la autorización fina del servidor es un vacío de seguridad comprobado.

## 5. Modelo de datos funcional

- `users.role_id` identifica el rol; `usuarios_departamentos` asigna usuarios a dependencias.
- `departamentos_asuntos` define qué asuntos atiende cada dependencia.
- `reportes` es el encabezado ATC; `reportes_asuntos` enlaza servicio/asunto/jornada/observación; `reportes_respuestas` almacena la respuesta.
- `sp_requests` incluye datos del solicitante, destino, asunto, respuesta, evidencias, papelería, finalización y estatus textual.
- Vistas como `reportes_view`, `info_cards`, `vw_concentrado_atc`, `sprequest`, `incumplimiento` y `solicitudesxestatus` sirven a consultas y tableros.

Las migraciones visibles no establecen claves foráneas para las relaciones de dominio. Tampoco reproducen todas las columnas utilizadas por controladores y vistas; la base operativa parece incorporar cambios manuales o migraciones ausentes.

## 6. Flujos principales

### 6.1 Sesión

1. `POST /login` valida credenciales y usuario activo.
2. Crea un token Sanctum; el rol se incluye como ability y en los datos devueltos.
3. La SPA envía el token a rutas protegidas.
4. `POST /logout` elimina tokens del usuario autenticado.
5. Cambiar permisos de rol revoca tokens cuyas abilities contienen el nombre del rol.

La recuperación genera una contraseña y envía correo mediante `RecuperarContraseña`.

### 6.2 Creación de reporte ATC

1. La SPA envía ciudadano, domicilio/geolocalización, origen, dependencia, asunto, servicio, jornada y observaciones.
2. `ReportController::saveReport` puede crear un usuario `Ciudadano` y uno o varios reportes si el payload contiene varios asuntos.
3. Cada reporte inicia en `id_estatus=1` (`ALTA`) y crea su fila en `reportes_asuntos`.
4. La API devuelve datos consolidados desde `ReportView`.
5. Evidencias adicionales se adjuntan mediante `/reports/imgsAttach/{id}`.

La operación no está envuelta en una transacción visible. En creación de ciudadano se usa una contraseña inicial fija, lo cual requiere revisión de seguridad.

### 6.3 Atención de reporte ATC

1. Reportes se consultan con filtros de fechas, estatus, dependencia, servicio y jornada.
2. Usuarios-dependencias limitan los reportes visibles desde la SPA y algunas consultas backend.
3. `saveResponse` reemplaza la respuesta anterior y asigna el estatus solicitado.
4. `deleteResponse` borra respuesta, limpia adjuntos y restablece `ALTA`.
5. `destroy` realiza baja lógica del reporte.

Estados sembrados: `ALTA`, `EN TRAMITE`, `NO PROCEDE`, `TERMINADO`. Orígenes: `Modulo`, `Telefono`, `APP`. Servicios: `Queja`, `Informacion`, `Solicitudes`, `Audiencia Publica`.

### 6.4 Solicitud de Secretaría Particular

1. `store` registra la solicitud activa en `ALTA` y la asigna a dependencia/asunto.
2. SuperAdmin puede consultar todas; otros usuarios consultan las de sus dependencias asignadas.
3. `response` almacena respuesta; `attachImgs` almacena evidencias y `stationeryImgs` papelería.
4. Respuesta más primera evidencia marcan la solicitud completa.
5. Una solicitud atrasada pasa de `ALTA - FUERA DE TIEMPO` a `COMPLETA - FUERA DE TIEMPO`; las demás pasan a `COMPLETA`.
6. `destroy` efectúa baja lógica.

### 6.5 Incumplimiento

La vista `incumplimiento` calcula días entre creación y finalización o fecha actual, descontando fines de semana. Incluye solicitudes activas en `ALTA` que superan cinco días hábiles y casos completados fuera de ese plazo conforme a la expresión SQL. No se descuentan festivos.

### 6.6 Reporteo e integración móvil

- `reportsview`, `reporteador` y `vw_concentrado_atc` agregan y filtran reportes para tablas, mapas y exportaciones.
- Los roles con IDs 1, 5 y 10 tienen tratamiento especial al filtrar dependencias en `reportsviewById`; el rol 10 no existe en el seeder actual.
- `/reports/sp` y `/app/saveresponse` están fuera de Sanctum y comparan un token compartido configurado en ambiente.

## 7. Reglas e invariantes

- Solo usuarios activos pueden iniciar sesión.
- Los reportes ATC nuevos comienzan en `ALTA`.
- Una respuesta ATC reemplaza la anterior.
- Eliminar una respuesta devuelve el reporte a `ALTA` y elimina adjuntos de respuesta.
- SP solo se completa con respuesta y primera evidencia.
- El umbral SP es mayor a cinco días hábiles, sin festivos.
- Usuarios, roles, menús, asuntos y reportes se desactivan habitualmente por baja lógica; relaciones se eliminan físicamente.
- Los archivos de evidencia no deben exponerse fuera de las rutas previstas ni registrar datos sensibles en logs.

## 8. Contrato de rutas

`php artisan route:list --path=api/gomezapp` enumera 109 rutas. Son públicas: `/login`, `/signup`, `POST /users`, `/users/recovery`, GET/POST `/reports/sp` y `/app/saveresponse`. El resto del archivo `routes/gomezapp.routes.php` está dentro de `auth:sanctum`.

La SPA contiene llamadas que no coinciden con este contrato, incluidas `/dashboard`, `/menus/counterOfMenus`, `/users/changePasswordAuth` y `/roles/destoy/{id}`. Deben tratarse como deuda o código muerto hasta verificar su uso.

## 9. Dependencias y límites operativos

- Las vistas usan funciones específicas de MySQL (`TO_DAYS`, `WEEK`, `WEEKDAY`, `CASE`), por lo que una migración de motor requiere reescritura.
- El correo, base de datos, CORS, Sanctum y token móvil dependen de configuración de ambiente.
- Evidencias se guardan en disco público local; no se observó validación uniforme de tipo/tamaño ni almacenamiento externo.
- Los secretos permanecen en `.env`; este archivo no está versionado y no debe copiarse a documentación o pruebas.
- El repositorio incluye `vendor`; las dependencias instaladas pueden no coincidir con otro ambiente si no se reinstalan desde `composer.lock`.

## 10. Contradicciones, riesgos y pendientes

1. Aplicar autorización de rol/acción también en el servidor.
2. Revisar si `/signup` y `POST /users` deben ser públicos.
3. Sustituir o endurecer el token móvil compartido.
4. Crear migraciones para todas las columnas/vistas que el código usa y agregar claves/índices/únicos adecuados.
5. Incorporar transacciones para altas y actualizaciones compuestas.
6. Uniformar validación de requests, carga de archivos y respuestas de error.
7. Resolver divergencias de endpoints y URLs de menú con la SPA.
8. Confirmar la identidad y permisos del rol ID 10.
9. Definir retención, tamaño, MIME y protección de evidencias.
10. Añadir pruebas de autenticación, autorización, reportes, SP, archivos e incumplimiento.
11. Determinar si el cálculo de días debe contemplar festivos.
12. Retirar archivos duplicados como `MenuController copy.php` cuando se confirme que no son necesarios.

## 11. Evidencia principal

- Contrato HTTP: `routes/api.php`, `routes/gomezapp.routes.php`.
- Autenticación/usuarios: `app/Http/Controllers/UserController.php`.
- Reportes: `app/Http/Controllers/GomezApp/ReportController.php`.
- Secretaría Particular: `app/Http/Controllers/GomezApp/SParticularController.php`.
- Roles/menús: `RoleController.php`, `MenuController.php`.
- Esquema y vistas: `database/migrations`.
- Catálogos y roles iniciales: `database/seeders/GomezApp`.
- Cliente: `../atc-front/src/routes` y `../atc-front/src/context`.

## 12. Verificación del 2026-09-30

- `php artisan route:list --path=api/gomezapp`: correcto, 109 rutas.
- `php artisan test`: 2 pruebas de plantilla correctas; aparecen avisos deprecados de Collision con la versión local de PHP.
- La SPA compila con `npm run build`, con advertencias y un chunk principal cercano a 11.36 MB.
- `npm run lint` en la SPA falla con 2,602 errores y 148 advertencias.
- No se leyeron ni documentaron valores de `.env`.

