CREATE TABLE IF NOT EXISTS resultados_propuestos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  partido_id INT UNSIGNED NOT NULL,
  estado ENUM('PENDIENTE','RECHAZADO','OFICIAL') NOT NULL DEFAULT 'PENDIENTE',
  motivo_rechazo TEXT NULL,
  datos JSON NOT NULL,
  version INT UNSIGNED NOT NULL DEFAULT 1,
  creado_por INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_resultados_partido FOREIGN KEY (partido_id) REFERENCES partidos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_resultados_creador FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uk_resultados_partido_estado (partido_id, estado),
  INDEX idx_resultados_estado (estado),
  INDEX idx_resultados_partido (partido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
