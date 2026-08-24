# App Juegos Deportivos — Frontend

> SPA Vite + React + Tailwind + Bootstrap. Dashboard único con permisos que consume `Backend` (`/api/v1/`). Calendario en cards por jornada, clasificación en tabla clásica. Build estático para Hostinger básico (mismo hosting que el backend).

## 1. Resumen de la app

**Público (sin login):**
- Listado torneos paginado con filtros `deporte` y `categoría M/F/Mixto`
- Detalle torneo con tabs: Equipos / Jornadas / Calendario / Clasificación
- Calendario como **cards por jornada** (nro + fecha → lista de partidos con `equipoA vs equipoB`, `fechaHora`, badge `PENDIENTE/RECHAZADO/OFICIAL`)
- Clasificación como **tabla clásica** `PJ/PG/PE/PP/GF/GC/GA/Pts` ordenada `puntos → GA → GF`, con modal de stats por equipo/jugador

**Privado (con login `Bearer` + refresh httpOnly):**
- **Dashboard único** con sidebar filtrada: `admin` ve todo, `editor` solo `Mis Torneos` y partidos asignados (vía `usuario_torneo`). Guards muestran 403 amigable.
- **Admin:** CRUD deportes, torneos (M/F/Mixto, formato), equipos/jugadores (dorsal único), jornadas/partidos (valida solapamiento 409 <120m, reasignable entre jornadas), attach/detach equipos y editores.
- **Editor:** propone resultados `goles/tarjetas` por jugador/equipo (`POST /partidos/:id/resultados` → 409 si ya hay PENDIENTE).
- **Admin:** aprueba (`/aprobar`) o rechaza con motivo (`/rechazar` → 422 sin motivo) → `OFICIAL` recalcula clasificación, `RECHAZADO` permite reenvío `version++`.
- **Auditoría** solo admin: filtros `torneoId/partidoId/usuarioId` paginados.

Intercepción 401 con refresh silencioso encolado, toasts para 409/422/429, skeletons y empty states.

## 2. Stack y estructura

```
Frontend/
├── src/
│   ├── services/api.js        → fetch wrapper: Bearer, 401→refresh, Retry-After
│   ├── contexts/AuthContext   → user {id, rol}, misTorneos, login/logout
│   ├── router.jsx             → React Router + RequireRole / RequireTorneo
│   ├── components/Layout      → sidebar/dashboard único, toasts
│   ├── pages/                 → Login, Torneos, TorneoDetalle, Calendario, Clasificacion, Gestion, Resultados, Auditoria
│   └── components/            → Cards Jornada, Tabla Clasificación, Modales
├── vite.config.js             → proxy /api → http://localhost:8000 en dev
├── tailwind.config.js + postcss
├── .env.example               → VITE_API_BASE=/api/v1
└── dist/                      → build estático para Hostinger
```

Tailwind para layout/utilidades + Bootstrap para componentes (table, badge, modal, pagination) con purge para no colisionar.

## 3. Cómo ejecutar toda la aplicación en local (Laragon)

**Requisitos:** Laragon (Apache + MySQL + PHP 8.1), Node 18+, Git

```powershell
# 1) Clonar
git clone https://github.com/espjaviervergara/app-juegos-deportivos.git
cd app-juegos-deportivos

# 2) Backend - BD
# Crear BD app_juegos_deportivos en HeidiSQL con charset utf8mb4
# Ejecutar DDL y seed:
#   Backend/BD.md  → copiar a HeidiSQL (o php Backend/migrate.php up)
#   Backend/BDseed.md → 2 torneos, 10 equipos, 100 jugadores, admin admin@juegos.local / Admin123!

# Backend - config
Copy-Item Backend/config/secret.php Backend/config/secret.local.php  # si existe .local
# Editar Backend/config/app.php si tu MySQL usa otro pass/puerto
# Asegurar .htaccess en Backend/public/.htaccess ya está

# Backend - docroot en Laragon
# Laragon → Menu → Apache → httpd.conf → DocumentRoot "C:/laragon/www/app-juegos-deportivos/Backend/public"
# O crear VirtualHost: app-juegos.test → C:/laragon/www/app-juegos-deportivos/Backend/public

# Probar API
curl http://app-juegos.test/api/v1/deportes
curl -X POST http://app-juegos.test/api/v1/auth/login -H "Content-Type: application/json" -d '{"email":"admin@juegos.local","password":"Admin123!"}'

# 3) Frontend
cd Frontend
cp .env.example .env
# .env contiene: VITE_API_BASE=http://app-juegos.test/api/v1  (dev con proxy) o /api/v1 en prod
npm install
npm run dev      # http://localhost:5173 (proxy vite a /api)

# Build para probar estático local
npm run build
# dist/ se genera; copiar a Backend/public/app para probar en mismo dominio:
Copy-Item -Recurse dist/* ../Backend/public/app/
# Visitar http://app-juegos.test/app
```

**Flujo de prueba rápida:**
1. Login como admin → CRUD torneos → asignar editor a torneo 1
2. Crear jornadas y partidos (probar solapamiento 409 cambiando hora <120m)
3. Login como editor → proponer resultado → como admin aprobar → ver clasificación recalcular
4. Probar lectura pública sin login: /torneos/:id/calendario y /clasificaciones

## 4. Paso a paso para subir a Hostinger (hosting básico compartido)

Hostinger hPanel: **solo PHP + MySQL + estático**, sin Node en prod. Backend y frontend van al **mismo dominio** (ej. `tudominio.com`).

### 4.1 Preparar build local
```powershell
cd Frontend
npm run build          # genera Frontend/dist/
cd ../Backend
# Opcional: composer install --no-dev  (si usas firebase/php-jwt, o deja Jwt artesanal)
```

### 4.2 Crear BD en Hostinger
1. hPanel → **Bases de datos → Nueva base** → nombre `uXXXX_app_juegos`, usuario y pass.
2. **phpMyAdmin** → Importar → pega contenido de `Backend/BD.md` (DDL) → Ejecutar.
3. Importar `Backend/BDseed.md` → admin + deportes + torneos iniciales.
4. Anota `host`, `dbname`, `user`, `pass` que da Hostinger (host suele ser `localhost` o `mysql.hostinger.com`).

### 4.3 Subir Backend
1. hPanel → **Administrador de archivos** o **FTP** (FileZilla) → `public_html/`
2. Borra `default.html` si existe.
3. Sube **contenido de `Backend/`** (no la carpeta, sino su contenido) manteniendo:
```
public_html/
├── public/            → ESTE será el docroot real (ver paso 4.4)
│   ├── index.php
│   ├── .htaccess
│   └── app/           → aquí irá el frontend (dist)
├── src/
├── config/
│   ├── app.php        → edita con datos de BD de Hostinger
│   └── secret.php     → pon secret largo aleatorio (32+ chars), NUNCA el de dev
├── migrations/
└── vendor/            → si usas composer, sube vendor/ o ejecuta composer install vía SSH si Hostinger lo permite
```
4. Editar `public_html/config/app.php` en Hostinger:
```php
'db' => [
  'host' => 'localhost',
  'database' => 'uXXXX_app_juegos',
  'username' => 'uXXXX_user',
  'password' => 'tu-pass-hostinger',
],
'jwt' => ['secret' => require __DIR__.'/secret.php', ...],
'cors' => ['allowed_origins' => ['https://tudominio.com'], ...],
```
5. Docroot: Hostinger sirve desde `public_html/`, pero nuestro front controller está en `public_html/public/`. Dos opciones:
   - **Opción A (recomendada):** mover todo de `public/` a `public_html/` (index.php + .htaccess en raíz) y `src/`/`config/` un nivel arriba fuera de `public_html` si el plan lo permite (Hostinger Business permite `public_html` como docroot únicamente, así que deja `src/` dentro pero protégelo).
   - **Opción B:** en hPanel → **Avanzado → Configuración PHP → docroot** no existe en básico; así que usa **Option A** y añade en `public_html/.htaccess`:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```
   Y protege `config/` y `src/` con `.htaccess` `Require all denied` si quedan dentro de `public_html`.

### 4.4 Subir Frontend
1. Local: `Frontend/dist/` ya está buildeado.
2. En `public_html/` crea carpeta `app/`:
```
public_html/app/
├── index.html
└── assets/
    ├── index-XXXXX.js
    └── index-XXXXX.css
```
3. Sube **todo `Frontend/dist/*` a `public_html/app/`**.
4. Crea/edita `public_html/.htaccess` para SPA fallback:
```apache
RewriteEngine On
# API va a index.php
RewriteCond %{REQUEST_URI} ^/api/ [OR]
RewriteCond %{REQUEST_FILENAME} -f
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
# Frontend SPA
RewriteRule ^app/(.*)$ app/index.html [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```
5. En `Frontend/.env` de prod (ya buildeado) debe ser `VITE_API_BASE=/api/v1` (mismo dominio, evita CORS). Si lo cambias, rebuildea y resube `dist/`.

### 4.5 Probar en producción
```bash
curl https://tudominio.com/api/v1/deportes
# Login
curl -X POST https://tudominio.com/api/v1/auth/login -H "Content-Type: application/json" -d '{"email":"admin@juegos.local","password":"Admin123!"}'
```
Visitar `https://tudominio.com/app` → login → probar CRUD y flujo PENDIENTE→OFICIAL.

### 4.6 Checklist final Hostinger
- [ ] BD importada y `config/app.php` con credenciales Hostinger
- [ ] `config/secret.php` con secret prod (no el de dev)
- [ ] `public_html/index.php` + `.htaccess` funcionando (`/api/v1/deportes` responde)
- [ ] `public_html/app/index.html` carga (frontend)
- [ ] HTTPS forzado en hPanel → **SSL → Forzar HTTPS**
- [ ] Cambiar password admin tras primer login

**Actualizar:** para nuevo deploy, `npm run build` local → resubir `dist/` a `public_html/app/` y si hay cambios de API, resubir `src/` y ejecutar migraciones nuevas vía phpMyAdmin.

---
*Docs fuente: `Backend/readmi.md` (API), `Backend/BD.md`/`BDseed.md` (BD), `Backend/openspec/` (specs `torneos-api-v1` y `frontend-v1`).*
