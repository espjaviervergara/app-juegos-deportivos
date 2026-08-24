<?php
namespace App\Repositories;
class UsuarioRepository extends BaseRepository {
  protected string $table = 'usuarios';
  public function findByEmail(string $email): ?array {
    $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE email=?');
    $stmt->execute([$email]); return $stmt->fetch() ?: null;
  }
}
