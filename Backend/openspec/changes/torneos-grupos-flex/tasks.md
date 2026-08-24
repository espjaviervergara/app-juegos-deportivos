## 1. Base de datos

- [ ] 1.1 Crear migración 015_grupos.sql: tablas grupos(id, torneo_id, nombre, orden) y grupo_equipo(grupo_id, equipo_id) con FKs y UNIQUEs
- [ ] 1.2 Alterar partidos: añadir columna grupo_id nullable FK a grupos, índice
- [ ] 1.3 Probar migración fresh y seed

## 2. API Grupos

- [ ] 2.1 Crear GrupoRepository y GrupoService con validaciones
- [ ] 2.2 Implementar POST /torneos/:id/grupos, GET /torneos/:id/grupos, DELETE /grupos/:id
- [ ] 2.3 Implementar POST /grupos/:id/equipos, DELETE /grupos/:id/equipos/:equipoId, PUT /grupos/reagrupar
- [ ] 2.4 Implementar POST /torneos/:id/grupos/auto {numGrupos, replace} con reparto Round-Robin A/B/C

## 3. API Jornadas/Partidos flexible

- [ ] 3.1 Modificar PartidoService para aceptar grupoId opcional y validar pertenencia a grupo
- [ ] 3.2 Modificar GET /torneos/:id/calendario para incluir grupo_nombre y agrupar
- [ ] 3.3 Permitir PUT /torneos/:id para cambiar formato en cualquier momento

## 4. Frontend TorneoDetalle

- [ ] 4.1 Añadir tab Grupos en TorneoDetalle: lista, crear manual, auto (input numGrupos), reagrupar con multiselect de equipos del torneo
- [ ] 4.2 Actualizar Jornada form con select Grupo opcional
- [ ] 4.3 Actualizar Partido form: select Grupo filtra equipos, selects de BD para equipos/jugadores
- [ ] 4.4 Calendario: agrupar cards por jornada luego por grupo con badge

## 5. QA

- [ ] 5.1 Probar flujo manual: crear 2 grupos, asignar equipos, crear partido con grupo válido e inválido (422), jornada con varios grupos
- [ ] 5.2 Probar flujo auto: generar 4 grupos A-D, reagrupar equipo, verificar calendario
- [ ] 5.3 Verificar build Frontend y push
