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
        if($req->query('torneoId')){ $where[]='torneo_id=?'; $params[]=(int)$req->query('torneoId'); }
        if($req->query('partidoId')){ $where[]='partido_id=?'; $params[]=(int)$req->query('partidoId'); }
        if($req->query('usuarioId')){ $where[]='usuario_id=?'; $params[]=(int)$req->query('usuarioId'); }
        $w=$where?' WHERE '.implode(' AND ',$where):'';
        $pdo=Database::pdo();
        $stmt=$pdo->prepare("SELECT * FROM audit_log $w ORDER BY created_at DESC LIMIT $limit OFFSET $off");
        $stmt->execute($params);
        $data=$stmt->fetchAll();
        $cnt=$pdo->prepare("SELECT COUNT(*) FROM audit_log $w"); $cnt->execute($params);
        Response::paginated($data,$page,$limit,(int)$cnt->fetchColumn());
    }
}
