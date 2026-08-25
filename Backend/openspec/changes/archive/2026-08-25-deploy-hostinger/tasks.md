## 1. Build y deploy

- [x] 1.1 Ejecutar `npm run build` en Frontend y copiar `dist/*` a `Backend/public/app`
- [x] 1.2 Subir Backend a `public_html` con `config/app.php`/`secret.php` de Hostinger y `vendor/`
- [x] 1.3 Importar BD en Hostinger (BD.md + BDseed.md) y ejecutar migraciones pendientes

## 2. Verificación

- [x] 2.1 Probar GET /api/v1/deportes y /torneos/:id/calendario sin login y con login
- [x] 2.2 Probar flujo admin: crear torneo, generar fixture, ver SPA principal con nombres
- [x] 2.3 Verificar que estudiante solo lectura en PartidoDetalle y que segundo admin se puede crear
- [x] 2.4 Push final y archivar

