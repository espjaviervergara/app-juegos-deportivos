ALTER TABLE partidos ADD CONSTRAINT fk_partidos_grupo FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE partidos ADD INDEX idx_partidos_grupo (grupo_id);
