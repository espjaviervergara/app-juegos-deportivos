## Requirements

### Requirement: Build estático para hosting básico
The system SHALL proveer Vite build que genera Frontend/dist/ estático, copiable a Backend/public/app o subcarpeta del hosting, con .env VITE_API_BASE y fallback SPA via .htaccess.

#### Scenario: Build y deploy en hosting básico
- **WHEN** se ejecuta npm run build
- **THEN** genera dist/ con index.html y assets listos para copiar a hosting

#### Scenario: Env apunta a API correcta
- **WHEN** VITE_API_BASE=/api/v1 en prod
- **THEN** fetchs van a mismo dominio sin CORS extra

### Requirement: Configuración Tailwind + Bootstrap
The system SHALL configurar Tailwind y Bootstrap sin colisiones (purge, orden de imports) y responsive.

#### Scenario: Estilos no colisionan
- **WHEN** se renderiza botón Bootstrap con utilidades Tailwind
- **THEN** ambos estilos aplican correctamente

