CREATE TABLE IF NOT EXISTS torneo_equipo (
  torneo_id INT UNSIGNED NOT NULL,
  equipo_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (torneo_id, equipo_id),
  CONSTRAINT fk_te_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_te_equipo FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_te_equipo (equipo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
