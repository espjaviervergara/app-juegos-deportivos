ALTER TABLE partidos MODIFY jornada_id INT UNSIGNED NULL;
ALTER TABLE partidos ADD COLUMN fase ENUM('liga','eliminatoria') NOT NULL DEFAULT 'liga' AFTER grupo_id;
ALTER TABLE partidos ADD INDEX idx_partidos_fase (fase);
