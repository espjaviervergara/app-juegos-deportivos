# Smoke tests
- POST /api/v1/auth/login -> 200 + refresh cookie
- POST /api/v1/auth/refresh -> rotation
- GET /api/v1/deportes -> 200 public
- POST /api/v1/deportes as editor -> 403, as admin -> 201
- POST /api/v1/torneos -> 201
- POST overlapping partidos -> 409
- POST resultados PENDIENTE -> 201, segundo -> 409, aprobar -> 200, reenvio tras RECHAZADO -> 201
