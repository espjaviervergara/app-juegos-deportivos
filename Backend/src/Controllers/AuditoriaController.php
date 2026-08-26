<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class AuditoriaController
{
    public function index(Request $req): void
    {
        if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403);
        $page=max(1,(int)($req->query('page')??1)); $limit=min(100,max(1,(int)($req->query('limit')??20))); $off=($page-1)*$limit;
        $where=[]; $params=[];
        if($req->query('torneoId')){ $where[]='al.torneo_id=?'; $params[]=(int)$req->query('torneoId'); }
        if($req->query('partidoId')){ $where[]='al.partido_id=?'; $params[]=(int)$req->query('partidoId'); }
        if($req->query('usuarioId')){ $where[]='al.usuario_id=?'; $params[]=(int)$req->query('usuarioId'); }
        $w=$where?' WHERE '.implode(' AND ',$where):'';
        $pdo=Database::pdo();
        $stmt=$pdo->prepare("SELECT al.*, u.nombre as usuario_nombre, u.email as usuario_email, t.nombre as torneo_nombre FROM audit_log al LEFT JOIN usuarios u ON u.id=al.usuario_id LEFT JOIN torneos t ON t.id=al.torneo_id $w ORDER BY al.created_at DESC LIMIT $limit OFFSET $off");
        $stmt->execute($params);
        $data=$stmt->fetchAll();
        // no exponer IDs crudos, solo nombres
        foreach($data as &$row){ unset($row['usuario_id']); unset($row['torneo_id']); unset($row['partido_id']); unset($row['entidad_id']); }
        $cnt=$pdo->prepare("SELECT COUNT(*) FROM audit_log $w"); $cnt->execute($params);
        Response::paginated($data,$page,$limit,(int)$cnt->fetchColumn());
    }
}
