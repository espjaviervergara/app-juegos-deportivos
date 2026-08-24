CREATE TABLE IF NOT EXISTS grupos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  torneo_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  orden INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_grupos_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uk_grupos_torneo_nombre (torneo_id, nombre),
  INDEX idx_grupos_torneo (torneo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS grupo_equipo (
  grupo_id INT UNSIGNED NOT NULL,
  equipo_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (grupo_id, equipo_id),
  CONSTRAINT fk_ge_grupo FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ge_equipo FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_ge_equipo (equipo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
