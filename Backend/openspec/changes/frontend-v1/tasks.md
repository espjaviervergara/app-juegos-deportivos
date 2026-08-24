## 1. Scaffold y build

- [ ] 1.1 Crear Frontend con Vite + React, instalar react-router-dom, tailwindcss, bootstrap, configurar tailwind.config.js, postcss, vite.config.js con proxy /api
- [ ] 1.2 Configurar .env.example (VITE_API_BASE=/api/v1), estructura src/services/api.js, AuthContext, router.jsx, Layout con Tailwind+Bootstrap sin colisiones
- [ ] 1.3 Build estático a Frontend/dist/ y .htaccess fallback SPA para hosting básico (mismo dominio)

## 2. Auth y layout

- [ ] 2.1 Implementar api.js con interceptor Bearer + refresh silencioso (cola 401), login/logout, persistencia user
- [ ] 2.2 Guards por rol y torneo (RequireRole, RequireTorneo) + manejo 401/403/429 con toasts y redirección
- [ ] 2.3 Dashboard único con sidebar filtrada por permisos, responsive, skeletons y empty states

## 3. Vistas públicas

- [ ] 3.1 Listado torneos paginado con filtros deporte/categoria (GET /torneos) sin auth
- [ ] 3.2 Detalle torneo con tabs Equipos/Jornadas/Calendario/Clasificación
- [ ] 3.3 Calendario cards por jornada (GET /calendario) paginado con badges PENDIENTE/RECHAZADO/OFICIAL
- [ ] 3.4 Tabla clasificación clásica PJ/PG/PE/PP/GF/GC/GA/Pts + stats equipo/jugador en modal

## 4. Gestión admin

- [ ] 4.1 CRUD deportes (solo admin) con validación y 409 duplicado
- [ ] 4.2 CRUD torneos (M/F/Mixto, formato, deporte activo) + attach/detach equipos y attach/detach editores
- [ ] 4.3 CRUD equipos y jugadores (dorsal único) + listado por torneo
- [ ] 4.4 CRUD jornadas y partidos (validación solapamiento 409, reasignación entre jornadas)

## 5. Resultados y auditoría

- [ ] 5.1 Propuesta resultados por editor (goles/tarjetas, 409 si pendiente) + If-Match version
- [ ] 5.2 Aprobación/rechazo por admin (motivo obligatorio, 403 para editor) + reenvío tras RECHAZADO
- [ ] 5.3 Vista auditoría solo admin con filtros torneo/partido/usuario y paginación

## 6. QA y deploy

- [ ] 6.1 Smoke: login, refresh, RBAC, solapamiento 409, pendiente único 409, rechazo sin motivo 422
- [ ] 6.2 Verificación hosting básico: npm run build, copiar dist a Backend/public/app, probar en Laragon con API real y CORS
