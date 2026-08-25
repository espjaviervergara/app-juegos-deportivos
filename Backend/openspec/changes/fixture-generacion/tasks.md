## 1. Base de datos

- [x] 1.1 Alterar partidos: jornada_id nullable y añadir fase ENUM(liga,eliminatoria) con índice
- [x] 1.2 Probar migración y rollback

## 2. API Fixture

- [x] 2.1 Crear FixtureController con POST /torneos/:id/fixture/generar (ida/ida_vuelta, grupo/sin_asignar, jornadaId?)
- [x] 2.2 Implementar lógica Round-Robin por grupo y sin asignar, con reparto de grupo_id y jornada
- [x] 2.3 Crear POST /torneos/:id/fixture/eliminatoria y GET /torneos/:id/partidos/sin-asignar
- [x] 2.4 Permitir PUT /partidos/:id con jornadaId nullable para reasignar y validar

## 3. Frontend Wizard

- [x] 3.1 Añadir botón Gestionar solo admin en Home y Torneos cards
- [x] 3.2 Crear wizard en TorneoDetalle Jornadas: pasos tipo/ámbito/jornada + Generar
- [x] 3.3 Añadir modal pregunta eliminatoria tras generar y generar si confirma
- [x] 3.4 Calendario: sección Sin asignar y reasignar con select Jornada + fechaHora

## 4. QA

- [x] 4.1 Probar ida por grupo sin asignar y ida_y_vuelta sin asignar
- [x] 4.2 Probar reasignar desde calendario y jornada, y eliminatoria auto 4
- [x] 4.3 Build y push

