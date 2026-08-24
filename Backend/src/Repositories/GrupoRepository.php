<?php
namespace App\Repositories;
use App\Core\Database;
class GrupoRepository extends BaseRepository {
  protected string $table='grupos';
  public function porTorneo(int $torneoId): array {
    $stmt=Database::pdo()->prepare("SELECT * FROM grupos WHERE torneo_id=? ORDER BY orden");
    $stmt->execute([$torneoId]); return $stmt->fetchAll();
  }
  public function equipos(int $grupoId): array {
    $stmt=Database::pdo()->prepare("SELECT e.* FROM equipos e JOIN grupo_equipo ge ON ge.equipo_id=e.id WHERE ge.grupo_id=?");
    $stmt->execute([$grupoId]); return $stmt->fetchAll();
  }
}
