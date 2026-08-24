CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NULL,
  accion VARCHAR(80) NOT NULL,
  entidad VARCHAR(60) NOT NULL,
  entidad_id INT UNSIGNED NULL,
  torneo_id INT UNSIGNED NULL,
  partido_id INT UNSIGNED NULL,
  antes JSON NULL,
  despues JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_usuario (usuario_id),
  INDEX idx_audit_torneo (torneo_id),
  INDEX idx_audit_partido (partido_id),
  INDEX idx_audit_accion (accion),
  INDEX idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
