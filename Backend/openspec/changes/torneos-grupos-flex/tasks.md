## 1. Base de datos

- [x] 1.1 Crear migración 015_grupos.sql: tablas grupos(id, torneo_id, nombre, orden) y grupo_equipo(grupo_id, equipo_id) con FKs y UNIQUEs
- [x] 1.2 Alterar partidos: añadir columna grupo_id nullable FK a grupos, índice
- [x] 1.3 Probar migración fresh y seed

## 2. API Grupos

- [x] 2.1 Crear GrupoRepository y GrupoService con validaciones
- [x] 2.2 Implementar POST /torneos/:id/grupos, GET /torneos/:id/grupos, DELETE /grupos/:id
- [x] 2.3 Implementar POST /grupos/:id/equipos, DELETE /grupos/:id/equipos/:equipoId, PUT /grupos/reagrupar
- [x] 2.4 Implementar POST /torneos/:id/grupos/auto {numGrupos, replace} con reparto Round-Robin A/B/C

## 3. API Jornadas/Partidos flexible

- [x] 3.1 Modificar PartidoService para aceptar grupoId opcional y validar pertenencia a grupo
- [x] 3.2 Modificar GET /torneos/:id/calendario para incluir grupo_nombre y agrupar
- [x] 3.3 Permitir PUT /torneos/:id para cambiar formato en cualquier momento

## 4. Frontend TorneoDetalle

- [x] 4.1 Añadir tab Grupos en TorneoDetalle: lista, crear manual, auto (input numGrupos), reagrupar con multiselect de equipos del torneo
- [x] 4.2 Actualizar Jornada form con select Grupo opcional
- [x] 4.3 Actualizar Partido form: select Grupo filtra equipos, selects de BD para equipos/jugadores
- [x] 4.4 Calendario: agrupar cards por jornada luego por grupo con badge

## 5. QA

- [x] 5.1 Probar flujo manual: crear 2 grupos, asignar equipos, crear partido con grupo válido e inválido (422), jornada con varios grupos
- [x] 5.2 Probar flujo auto: generar 4 grupos A-D, reagrupar equipo, verificar calendario
- [x] 5.3 Verificar build Frontend y push

