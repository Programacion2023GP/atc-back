# Memoria del proyecto

- Producto: API del Sistema de Atención Ciudadana de Gómez Palacio.
- Componente hermano: `../atc-front` (React 18/Vite).
- Stack: Laravel 9, Sanctum, MySQL y almacenamiento local de evidencias bajo `public/GomezApp`.
- Dominios: reportes ATC y solicitudes de Secretaría Particular.
- Estados ATC confirmados: `ALTA`, `EN TRAMITE`, `NO PROCEDE`, `TERMINADO`.
- Regla SP: respuesta + primera evidencia completan; más de cinco días hábiles alimentan incumplimiento.
- Permisos: listas por rol/menú; la aplicación fina se observa en frontend y falta reforzarla en backend.
- Estado verificado 2026-09-30: 109 rutas; 2 pruebas de plantilla pasan con avisos deprecados.
- Riesgos prioritarios: endpoints públicos sensibles, token móvil compartido, migraciones incompletas frente al esquema usado, ausencia de transacciones/FK y pruebas de negocio.
- Fuente extensa: `CONTEXTO_SISTEMA.md`.

