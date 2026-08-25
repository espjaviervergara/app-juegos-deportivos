## Requirements

### Requirement: Generación Round-Robin ida y ida y vuelta por grupo o sin asignar
The system SHALL generar fixture vía POST /torneos/:id/fixture/generar {tipo: ida|ida_vuelta, ambito: grupo|sin_asignar, jornadaId?} donde tipo ida genera 1 partido por pareja y ida_vuelta genera 2 con local/visita invertido; ambito grupo genera dentro de cada grupo y sin_asignar genera entre todos sin grupo ni jornada si jornadaId null.

#### Scenario: Ida por grupo genera 6 partidos para 2 grupos de 4
- **WHEN** POST /torneos/1/fixture/generar {tipo:"ida", ambito:"grupo"}
- **THEN** 201 con 12 partidos (6 por grupo) con grupo_id asignado y jornada_id null

#### Scenario: Ida y vuelta sin asignar deja sin jornada
- **WHEN** POST {tipo:"ida_vuelta", ambito:"sin_asignar"}
- **THEN** partidos con jornada_id null y fase liga, duplicados invertidos

#### Scenario: Con jornadaId asigna y reparte fechas
- **WHEN** POST {tipo:"ida", ambito:"grupo", jornadaId:1}
- **THEN** partidos con jornada_id=1 y fechaHora repartida

### Requirement: Eliminatoria automática tras liga
The system SHALL tras generar liga preguntar y si se confirma, generar eliminatoria vía POST /torneos/:id/fixture/eliminatoria {numClasificados:2|4|8, jornadaId?} con fase eliminatoria, sin grupo, reasignable.

#### Scenario: Genera semifinal 4 clasificados
- **WHEN** POST /torneos/1/fixture/eliminatoria {numClasificados:4}
- **THEN** 201 con 2 partidos eliminatoria

#### Scenario: Eliminatoria sin jornada queda sin asignar
- **WHEN** POST sin jornadaId
- **THEN** partidos con jornada_id null y fase eliminatoria

