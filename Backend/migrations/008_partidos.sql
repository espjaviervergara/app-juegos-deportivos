CREATE TABLE IF NOT EXISTS partidos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  jornada_id INT UNSIGNED NOT NULL,
  equipoA_id INT UNSIGNED NOT NULL,
  equipoB_id INT UNSIGNED NOT NULL,
  fechaHora DATETIME NOT NULL,
  estado ENUM('programado','en_juego','finalizado','suspendido') NOT NULL DEFAULT 'programado',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_partidos_jornada FOREIGN KEY (jornada_id) REFERENCES jornadas(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_partidos_equipoA FOREIGN KEY (equipoA_id) REFERENCES equipos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_partidos_equipoB FOREIGN KEY (equipoB_id) REFERENCES equipos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_partidos_jornada (jornada_id),
  INDEX idx_partidos_fechaHora (fechaHora),
  INDEX idx_partidos_equipoA_fecha (equipoA_id, fechaHora),
  INDEX idx_partidos_equipoB_fecha (equipoB_id, fechaHora),
  CONSTRAINT chk_equipos_distintos CHECK (equipoA_id <> equipoB_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
