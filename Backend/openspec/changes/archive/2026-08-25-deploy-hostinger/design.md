## Context

App completa en local, con 5 cambios archivados. Hostinger básico solo permite PHP+MySQL y estático, sin Node. Frontend y backend deben ir al mismo dominio para evitar CORS extra.

## Goals / Non-Goals

**Goals:**
- Build estático verificado y copiado correctamente.
- Backend con docroot correcto y migraciones en Hostinger.
- Verificación de endpoints públicos y con auth.

**Non-Goals:**
- CI/CD automático ni Docker.

## Decisions

**D1 — `Frontend/dist` → `Backend/public/app` y luego a `public_html/app` en Hostinger**
*Razón:* Mismo dominio `/api` y `/app`, `.htaccess` con fallback SPA. Alternativa Vercel descartada por requisito mismo hosting.

**D2 — Checklist de verificación**
*Razón:* Probar sin login (calendario/tabla) y con login (admin crea, editor propone) para asegurar roles.

## Risks / Trade-offs

- **Docroot mal configurado** → Mitigación: mover `public/index.php` + `.htaccess` a `public_html/` y proteger `config/`.
- **CORS si frontend en subdominio** → Mitigación: mismo dominio.

## Migration Plan

1. `npm run build` y copiar.
2. Subir Backend y ejecutar migraciones vía phpMyAdmin.
3. Probar endpoints.

## Open Questions

- ¿Dominio final `tudominio.com` o subcarpeta?
