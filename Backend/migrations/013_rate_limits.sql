CREATE TABLE IF NOT EXISTS rate_limits (
  clave VARCHAR(180) NOT NULL,
  window_start DATETIME NOT NULL,
  contador INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (clave, window_start),
  INDEX idx_rl_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
