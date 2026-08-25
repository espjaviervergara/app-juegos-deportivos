## ADDED Requirements

### Requirement: Build y deploy verificado en Hostinger
The system SHALL tener Frontend/dist copiado a Backend/public/app y Backend desplegado en public_html con .htaccess SPA y API funcionando en mismo dominio.

#### Scenario: Build copiado y SPA carga
- **WHEN** se accede a https://tudominio.com/app
- **THEN** carga SPA y GET /api/v1/deportes responde 200

#### Scenario: Roles verificados en prod
- **WHEN** estudiante sin login entra a /app
- **THEN** ve programación y tabla sin poder editar; admin puede gestionar tras login
