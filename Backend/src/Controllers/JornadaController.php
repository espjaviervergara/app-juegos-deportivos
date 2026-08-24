<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class JornadaController
{
    public function index(Request $req): void
    {
        $tid=(int)$req->params['id']; $pdo=Database::pdo();
        $page=max(1,(int)($req->query('page')??1)); $limit=min(100,max(1,(int)($req->query('limit')??20)));
        $off=($page-1)*$limit; $stmt=$pdo->prepare("SELECT * FROM jornadas WHERE torneo_id=? ORDER BY nro LIMIT $limit OFFSET $off"); $stmt->execute([$tid]);
        $data=$stmt->fetchAll(); $cnt=$pdo->prepare("SELECT COUNT(*) FROM jornadas WHERE torneo_id=?"); $cnt->execute([$tid]); Response::paginated($data,$page,$limit,(int)$cnt->fetchColumn());
    }
    public function show(Request $req): void { $pdo=Database::pdo(); $stmt=$pdo->prepare("SELECT * FROM jornadas WHERE id=?"); $stmt->execute([(int)$req->params['id']]); $j=$stmt->fetch(); if(!$j) Response::error('NOT_FOUND','Jornada not found',404); Response::success($j); }
    public function store(Request $req): void
    {
        $this->requireAdmin($req); $tid=(int)$req->params['id']; $nro=(int)($req->input('nro')??0); $fecha=$req->input('fecha'); if(!$nro||!$fecha) Response::error('VALIDATION_ERROR','nro and fecha required',422);
        $pdo=Database::pdo(); try{ $pdo->prepare("INSERT INTO jornadas (torneo_id, nro, fecha, nombre) VALUES (?,?,?,?)")->execute([$tid,$nro,$fecha,$req->input('nombre')]); $id=$pdo->lastInsertId(); $stmt=$pdo->prepare("SELECT * FROM jornadas WHERE id=?"); $stmt->execute([$id]); Response::success($stmt->fetch(),201); }catch(\PDOException $e){ if(str_contains($e->getMessage(),'Duplicate')) Response::error('CONFLICT','Nro already exists',409); throw $e; }
    }
    public function update(Request $req): void { $this->requireAdmin($req); $id=(int)$req->params['id']; $pdo=Database::pdo(); $stmt=$pdo->prepare("SELECT * FROM jornadas WHERE id=?"); $stmt->execute([$id]); if(!$stmt->fetch()) Response::error('NOT_FOUND','Not found',404); $data=[]; foreach(['nro','fecha','nombre'] as $k) if($req->input($k)!==null) $data[$k]=$req->input($k); if($data){ $sets=implode(',',array_map(fn($k)=>"$k=?",array_keys($data))); $pdo->prepare("UPDATE jornadas SET $sets WHERE id=?")->execute([...array_values($data),$id]); } $s=$pdo->prepare("SELECT * FROM jornadas WHERE id=?"); $s->execute([$id]); Response::success($s->fetch()); }
    public function destroy(Request $req): void { $this->requireAdmin($req); Database::pdo()->prepare("DELETE FROM jornadas WHERE id=?")->execute([(int)$req->params['id']]); Response::json(['data'=>['message'=>'Deleted']]); }
    public function calendario(Request $req): void
    {
        $tid=(int)$req->params['id']; $pdo=Database::pdo();
        $page=max(1,(int)($req->query('page')??1)); $limit=min(100,max(1,(int)($req->query('limit')??20))); $off=($page-1)*$limit;
        $stmt=$pdo->prepare("SELECT p.*, j.nro as jornada_nro, ea.nombre as equipoA_nombre, eb.nombre as equipoB_nombre, g.nombre as grupo_nombre FROM partidos p JOIN jornadas j ON j.id=p.jornada_id JOIN equipos ea ON ea.id=p.equipoA_id JOIN equipos eb ON eb.id=p.equipoB_id LEFT JOIN grupos g ON g.id=p.grupo_id WHERE j.torneo_id=? ORDER BY p.fechaHora LIMIT $limit OFFSET $off");
        $stmt->execute([$tid]); $data=$stmt->fetchAll();
        $cnt=$pdo->prepare("SELECT COUNT(*) FROM partidos p JOIN jornadas j ON j.id=p.jornada_id WHERE j.torneo_id=?"); $cnt->execute([$tid]);
        Response::paginated($data,$page,$limit,(int)$cnt->fetchColumn());
    }
    private function requireAdmin(Request $req): void { if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403); }
}
