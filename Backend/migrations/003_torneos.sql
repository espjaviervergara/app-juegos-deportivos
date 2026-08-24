CREATE TABLE IF NOT EXISTS torneos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  deporte_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  categoria ENUM('M','F','Mixto') NOT NULL,
  formato ENUM('liga','eliminatoria','grupos+eliminatoria') NOT NULL,
  estado ENUM('borrador','activo','finalizado','archivado') NOT NULL DEFAULT 'activo',
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_torneos_deporte FOREIGN KEY (deporte_id) REFERENCES deportes(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_torneos_deporte (deporte_id),
  INDEX idx_torneos_categoria (categoria),
  INDEX idx_torneos_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
