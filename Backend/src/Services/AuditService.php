<?php
namespace App\Services;

use App\Core\Database;

class AuditService
{
    public static function log(?int $usuarioId, string $accion, string $entidad, ?int $entidadId, ?int $torneoId, ?int $partidoId, $antes=null, $despues=null): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("INSERT INTO audit_log (usuario_id, accion, entidad, entidad_id, torneo_id, partido_id, antes, despues) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $usuarioId,$accion,$entidad,$entidadId,$torneoId,$partidoId,
            $antes?json_encode($antes, JSON_UNESCAPED_UNICODE):null,
            $despues?json_encode($despues, JSON_UNESCAPED_UNICODE):null
        ]);
    }
}
