<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\AuditService;

class PartidoController
{
    public function porJornada(Request $req): void { $jid=(int)$req->params['id']; $pdo=Database::pdo(); $stmt=$pdo->prepare("SELECT p.*, ea.nombre as equipoA_nombre, eb.nombre as equipoB_nombre FROM partidos p JOIN equipos ea ON ea.id=p.equipoA_id JOIN equipos eb ON eb.id=p.equipoB_id WHERE p.jornada_id=? ORDER BY p.fechaHora"); $stmt->execute([$jid]); Response::success($stmt->fetchAll()); }
    public function show(Request $req): void { $pdo=Database::pdo(); $stmt=$pdo->prepare("SELECT p.*, ea.nombre as equipoA_nombre, eb.nombre as equipoB_nombre FROM partidos p JOIN equipos ea ON ea.id=p.equipoA_id JOIN equipos eb ON eb.id=p.equipoB_id WHERE p.id=?"); $stmt->execute([(int)$req->params['id']]); $p=$stmt->fetch(); if(!$p) Response::error('NOT_FOUND','Partido not found',404); Response::success($p); }
    public function store(Request $req): void
    {
        $this->requireAdmin($req); $jid=(int)$req->params['id']; $pdo=Database::pdo();
        $j=$pdo->prepare("SELECT torneo_id FROM jornadas WHERE id=?"); $j->execute([$jid]); $torneo=$j->fetchColumn(); if(!$torneo) Response::error('NOT_FOUND','Jornada not found',404);
        $a=(int)($req->input('equipoAId')??0); $b=(int)($req->input('equipoBId')??0); $fh=$req->input('fechaHora');
        if(!$a||!$b||!$fh) Response::error('VALIDATION_ERROR','equipoAId, equipoBId, fechaHora required',422);
        if($a===$b) Response::error('VALIDATION_ERROR','Teams must be different',422);
        // equipos pertenecen a torneo
        foreach([$a,$b] as $eid){ $chk=$pdo->prepare("SELECT 1 FROM torneo_equipo WHERE torneo_id=? AND equipo_id=?"); $chk->execute([$torneo,$eid]); if(!$chk->fetch()) Response::error('VALIDATION_ERROR',"Equipo $eid not in torneo",422); }
        $this->checkOverlap($a,$fh); $this->checkOverlap($b,$fh);
        $pdo->prepare("INSERT INTO partidos (jornada_id, equipoA_id, equipoB_id, fechaHora) VALUES (?,?,?,?)")->execute([$jid,$a,$b,$fh]);
        $id=$pdo->lastInsertId(); AuditService::log($req->user['sub']??null,'partido.creado','partido',$id,$torneo,$id,null,['jornada_id'=>$jid,'equipoA'=>$a,'equipoB'=>$b,'fechaHora'=>$fh]);
        $stmt=$pdo->prepare("SELECT * FROM partidos WHERE id=?"); $stmt->execute([$id]); Response::success($stmt->fetch(),201);
    }
    public function update(Request $req): void
    {
        $this->requireAdmin($req); $id=(int)$req->params['id']; $pdo=Database::pdo();
        $stmt=$pdo->prepare("SELECT p.*, j.torneo_id FROM partidos p JOIN jornadas j ON j.id=p.jornada_id WHERE p.id=?"); $stmt->execute([$id]); $p=$stmt->fetch(); if(!$p) Response::error('NOT_FOUND','Not found',404);
        $jid=$req->input('jornadaId')??$p['jornada_id']; $a=$req->input('equipoAId')??$p['equipoA_id']; $b=$req->input('equipoBId')??$p['equipoB_id']; $fh=$req->input('fechaHora')??$p['fechaHora'];
        if($a==$b) Response::error('VALIDATION_ERROR','Teams must be different',422);
        // validar pertenencia
        $torneo=$p['torneo_id']; if($jid!=$p['jornada_id']){ $j=$pdo->prepare("SELECT torneo_id FROM jornadas WHERE id=?"); $j->execute([$jid]); $nt=$j->fetchColumn(); if(!$nt) Response::error('NOT_FOUND','Jornada not found',404); $torneo=$nt; }
        foreach([$a,$b] as $eid){ $chk=$pdo->prepare("SELECT 1 FROM torneo_equipo WHERE torneo_id=? AND equipo_id=?"); $chk->execute([$torneo,$eid]); if(!$chk->fetch()) Response::error('VALIDATION_ERROR',"Equipo $eid not in torneo",422); }
        $this->checkOverlap($a,$fh,$id); $this->checkOverlap($b,$fh,$id);
        $pdo->prepare("UPDATE partidos SET jornada_id=?, equipoA_id=?, equipoB_id=?, fechaHora=? WHERE id=?")->execute([$jid,$a,$b,$fh,$id]);
        $s=$pdo->prepare("SELECT * FROM partidos WHERE id=?"); $s->execute([$id]); Response::success($s->fetch());
    }
    public function destroy(Request $req): void { $this->requireAdmin($req); Database::pdo()->prepare("DELETE FROM partidos WHERE id=?")->execute([(int)$req->params['id']]); Response::json(['data'=>['message'=>'Deleted']]); }

    private function checkOverlap(int $equipoId, string $fechaHora, ?int $excludeId=null): void
    {
        $pdo=Database::pdo(); $cfg=require __DIR__.'/../../config/app.php'; $buf=(int)($cfg['overlap_buffer_minutes']??120);
        $sql="SELECT 1 FROM partidos WHERE (equipoA_id=? OR equipoB_id=?) AND ABS(TIMESTAMPDIFF(MINUTE, fechaHora, ?)) < ? ";
        $params=[$equipoId,$equipoId,$fechaHora,$buf];
        if($excludeId){ $sql.=" AND id<>?"; $params[]=$excludeId; }
        $sql.=" LIMIT 1"; $stmt=$pdo->prepare($sql); $stmt->execute($params);
        if($stmt->fetch()) Response::error('CONFLICT','Team has overlapping match',409);
    }
    private function requireAdmin(Request $req): void { if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403); }
}
