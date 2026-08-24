<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class JugadorController
{
    public function index(Request $req): void
    {
        $eid=(int)$req->params['id']; $pdo=Database::pdo(); $stmt=$pdo->prepare("SELECT * FROM jugadores WHERE equipo_id=? ORDER BY dorsal"); $stmt->execute([$eid]); Response::success($stmt->fetchAll());
    }
    public function store(Request $req): void
    {
        $this->requireAdmin($req); $eid=(int)$req->params['id']; $pdo=Database::pdo();
        if(!$pdo->query("SELECT 1 FROM equipos WHERE id=$eid")->fetch()) Response::error('NOT_FOUND','Equipo not found',404);
        $nombre=trim($req->input('nombre')??''); $dorsal=$req->input('dorsal'); if(!$nombre) Response::error('VALIDATION_ERROR','nombre required',422);
        if($dorsal!==null) $dorsal=(int)$dorsal;
        try{ $pdo->prepare("INSERT INTO jugadores (equipo_id, nombre, dorsal) VALUES (?,?,?)")->execute([$eid,$nombre,$dorsal]); $id=$pdo->lastInsertId(); $stmt=$pdo->prepare("SELECT * FROM jugadores WHERE id=?"); $stmt->execute([$id]); Response::success($stmt->fetch(),201); }catch(\PDOException $e){ if(str_contains($e->getMessage(),'Duplicate')) Response::error('CONFLICT','Dorsal already exists for team',409); throw $e; }
    }
    public function update(Request $req): void { $this->requireAdmin($req); $id=(int)$req->params['id']; $pdo=Database::pdo(); $j=$pdo->prepare("SELECT * FROM jugadores WHERE id=?"); $j->execute([$id]); if(!$j->fetch()) Response::error('NOT_FOUND','Jugador not found',404); $data=[]; if($req->input('nombre')!==null) $data['nombre']=trim($req->input('nombre')); if($req->input('dorsal')!==null) $data['dorsal']=(int)$req->input('dorsal'); if($data){ $sets=implode(',',array_map(fn($k)=>"$k=?",array_keys($data))); $pdo->prepare("UPDATE jugadores SET $sets WHERE id=?")->execute([...array_values($data),$id]); } $stmt=$pdo->prepare("SELECT * FROM jugadores WHERE id=?"); $stmt->execute([$id]); Response::success($stmt->fetch()); }
    public function destroy(Request $req): void { $this->requireAdmin($req); $id=(int)$req->params['id']; Database::pdo()->prepare("DELETE FROM jugadores WHERE id=?")->execute([$id]); Response::json(['data'=>['message'=>'Deleted']]); }
    private function requireAdmin(Request $req): void { if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403); }
}
