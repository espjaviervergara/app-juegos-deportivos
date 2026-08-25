## Why

La app está funcional en local (Laragon, `localhost:8000` + `localhost:5173`) y todos los cambios están archivados, pero falta dejar documentado y verificado el deploy en Hostinger básico (mismo hosting para API y SPA) para que la IE Ángela María Torres Suárez pueda usarla en producción.

## What Changes

- Documentar y verificar build `Frontend/dist/` copiado a `Backend/public/app` con `.htaccess` SPA fallback.
- Verificar `Backend` en `public_html` con `config/app.php` y `secret.php` de Hostinger, `vendor/` y migraciones.
- Checklist de pruebas en prod: `GET /api/v1/deportes` público, login admin, crear torneo, generar fixture, ver calendario/tabla sin login, y rol estudiante solo lectura.

## Capabilities

### New Capabilities
- `deploy-hostinger`: guía y verificación de deploy en Hostinger básico.

### Modified Capabilities
- Ninguna.

## Impact

- Docs: `Frontend/readmi.md` ya tiene paso a paso; este cambio deja checklist y verificación.
- No cambia código, solo verificación y posible ajuste de `.htaccess` y `config`.
