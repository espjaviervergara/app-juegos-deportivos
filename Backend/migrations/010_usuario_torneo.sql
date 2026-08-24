CREATE TABLE IF NOT EXISTS usuario_torneo (
  usuario_id INT UNSIGNED NOT NULL,
  torneo_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id, torneo_id),
  CONSTRAINT fk_ut_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ut_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_ut_torneo (torneo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
