## 1. Base de datos

- [ ] 1.1 Alterar partidos: jornada_id nullable y añadir fase ENUM(liga,eliminatoria) con índice
- [ ] 1.2 Probar migración y rollback

## 2. API Fixture

- [ ] 2.1 Crear FixtureController con POST /torneos/:id/fixture/generar (ida/ida_vuelta, grupo/sin_asignar, jornadaId?)
- [ ] 2.2 Implementar lógica Round-Robin por grupo y sin asignar, con reparto de grupo_id y jornada
- [ ] 2.3 Crear POST /torneos/:id/fixture/eliminatoria y GET /torneos/:id/partidos/sin-asignar
- [ ] 2.4 Permitir PUT /partidos/:id con jornadaId nullable para reasignar y validar

## 3. Frontend Wizard

- [ ] 3.1 Añadir botón Gestionar solo admin en Home y Torneos cards
- [ ] 3.2 Crear wizard en TorneoDetalle Jornadas: pasos tipo/ámbito/jornada + Generar
- [ ] 3.3 Añadir modal pregunta eliminatoria tras generar y generar si confirma
- [ ] 3.4 Calendario: sección Sin asignar y reasignar con select Jornada + fechaHora

## 4. QA

- [ ] 4.1 Probar ida por grupo sin asignar y ida_y_vuelta sin asignar
- [ ] 4.2 Probar reasignar desde calendario y jornada, y eliminatoria auto 4
- [ ] 4.3 Build y push
