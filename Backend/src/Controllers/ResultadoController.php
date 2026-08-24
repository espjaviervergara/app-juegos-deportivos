<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\AuditService;

class ResultadoController
{
    public function store(Request $req): void
    {
        $pid=(int)$req->params['id']; $uid=$req->user['sub']; $pdo=Database::pdo();
        // check partido exists and get torneo
        $stmt=$pdo->prepare("SELECT p.*, j.torneo_id FROM partidos p JOIN jornadas j ON j.id=p.jornada_id WHERE p.id=?"); $stmt->execute([$pid]); $part=$stmt->fetch(); if(!$part) Response::error('NOT_FOUND','Partido not found',404);
        // editor must be assigned
        if(($req->user['rol']??'')!=='admin'){
            $chk=$pdo->prepare("SELECT 1 FROM usuario_torneo WHERE usuario_id=? AND torneo_id=?"); $chk->execute([$uid,$part['torneo_id']]); if(!$chk->fetch()) Response::error('FORBIDDEN','Not assigned to tournament',403);
        }
        // check pendiente existente
        $ex=$pdo->prepare("SELECT 1 FROM resultados_propuestos WHERE partido_id=? AND estado='PENDIENTE'"); $ex->execute([$pid]); if($ex->fetch()) Response::error('CONFLICT','Pending result already exists',409);
        $goles=$req->input('goles')??[]; $tarjetas=$req->input('tarjetas')??[]; $faltas=$req->input('faltas')??[]; $obs=$req->input('observaciones');
        // validar jugador pertenece a equipo del partido
        foreach(array_merge($goles,$tarjetas,$faltas) as $it){
            if(!isset($it['jugadorId'])||!isset($it['equipoId'])) Response::error('VALIDATION_ERROR','jugadorId and equipoId required',422);
            $jid=(int)$it['jugadorId']; $eid=(int)$it['equipoId'];
            if(!in_array($eid,[$part['equipoA_id'],$part['equipoB_id']])) Response::error('VALIDATION_ERROR','Equipo not in match',422);
            $chk=$pdo->prepare("SELECT 1 FROM jugadores WHERE id=? AND equipo_id=?"); $chk->execute([$jid,$eid]); if(!$chk->fetch()) Response::error('VALIDATION_ERROR',"Jugador $jid not in equipo $eid",422);
        }
        $datos=json_encode(['goles'=>$goles,'tarjetas'=>$tarjetas,'faltas'=>$faltas,'observaciones'=>$obs], JSON_UNESCAPED_UNICODE);
        // version: max version for this partido +1
        $ver=$pdo->prepare("SELECT COALESCE(MAX(version),0)+1 FROM resultados_propuestos WHERE partido_id=?"); $ver->execute([$pid]); $v=(int)$ver->fetchColumn();
        $pdo->prepare("INSERT INTO resultados_propuestos (partido_id, estado, datos, version, creado_por) VALUES (?,?,?,?,?)")->execute([$pid,'PENDIENTE',$datos,$v,$uid]);
        $id=$pdo->lastInsertId(); AuditService::log($uid,'resultado.propuesto','resultado',$id,$part['torneo_id'],$pid,null,['estado'=>'PENDIENTE','version'=>$v]);
        $stmt=$pdo->prepare("SELECT * FROM resultados_propuestos WHERE id=?"); $stmt->execute([$id]); Response::success($stmt->fetch(),201);
    }

    public function show(Request $req): void
    {
        $pid=(int)$req->params['id']; $pdo=Database::pdo(); $stmt=$pdo->prepare("SELECT * FROM resultados_propuestos WHERE partido_id=? ORDER BY version DESC LIMIT 1"); $stmt->execute([$pid]); $r=$stmt->fetch(); if(!$r) Response::error('NOT_FOUND','No result',404); Response::success($r);
    }

    public function update(Request $req): void
    {
        $pid=(int)$req->params['id']; $pdo=Database::pdo();
        $stmt=$pdo->prepare("SELECT * FROM resultados_propuestos WHERE partido_id=? AND estado='PENDIENTE'"); $stmt->execute([$pid]); $cur=$stmt->fetch(); if(!$cur) Response::error('NOT_FOUND','No pending result',404);
        $ifMatch=$req->header('if-match'); if($ifMatch && (int)$ifMatch !== (int)$cur['version']) Response::error('CONFLICT','Version mismatch',409);
        $goles=$req->input('goles')??json_decode($cur['datos'],true)['goles']??[]; $tarjetas=$req->input('tarjetas')??json_decode($cur['datos'],true)['tarjetas']??[]; $faltas=$req->input('faltas')??json_decode($cur['datos'],true)['faltas']??[];
        $datos=json_encode(['goles'=>$goles,'tarjetas'=>$tarjetas,'faltas'=>$faltas], JSON_UNESCAPED_UNICODE);
        $pdo->prepare("UPDATE resultados_propuestos SET datos=?, updated_at=NOW() WHERE id=?")->execute([$datos,$cur['id']]);
        Response::success($pdo->query("SELECT * FROM resultados_propuestos WHERE id={$cur['id']}")->fetch());
    }

    public function aprobar(Request $req): void
    {
        if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin can approve',403);
        $pid=(int)$req->params['id']; $pdo=Database::pdo();
        $stmt=$pdo->prepare("SELECT r.*, j.torneo_id FROM resultados_propuestos r JOIN partidos p ON p.id=r.partido_id JOIN jornadas j ON j.id=p.jornada_id WHERE r.partido_id=? AND r.estado='PENDIENTE'"); $stmt->execute([$pid]); $r=$stmt->fetch(); if(!$r) Response::error('NOT_FOUND','No pending result',404);
        $pdo->beginTransaction(); try{
            $pdo->prepare("UPDATE resultados_propuestos SET estado='OFICIAL', updated_at=NOW() WHERE id=?")->execute([$r['id']]);
            $pdo->prepare("UPDATE partidos SET estado='finalizado' WHERE id=?")->execute([$pid]);
            AuditService::log($req->user['sub'],'resultado.aprobado','resultado',$r['id'],$r['torneo_id'],$pid,['estado'=>'PENDIENTE'],['estado'=>'OFICIAL']);
            $pdo->commit();
        }catch(\Exception $e){ $pdo->rollBack(); throw $e; }
        Response::success(['estado'=>'OFICIAL','partido_id'=>$pid]);
    }

    public function rechazar(Request $req): void
    {
        if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin can reject',403);
        $pid=(int)$req->params['id']; $motivo=trim($req->input('motivo')??''); if($motivo==='') Response::error('VALIDATION_ERROR','motivo required',422, [['field'=>'motivo','message'=>'required']]);
        $pdo=Database::pdo(); $stmt=$pdo->prepare("SELECT r.*, j.torneo_id FROM resultados_propuestos r JOIN partidos p ON p.id=r.partido_id JOIN jornadas j ON j.id=p.jornada_id WHERE r.partido_id=? AND r.estado='PENDIENTE'"); $stmt->execute([$pid]); $r=$stmt->fetch(); if(!$r) Response::error('NOT_FOUND','No pending result',404);
        $pdo->prepare("UPDATE resultados_propuestos SET estado='RECHAZADO', motivo_rechazo=?, updated_at=NOW() WHERE id=?")->execute([$motivo,$r['id']]);
        AuditService::log($req->user['sub'],'resultado.rechazado','resultado',$r['id'],$r['torneo_id'],$pid,['estado'=>'PENDIENTE'],['estado'=>'RECHAZADO','motivo'=>$motivo]);
        Response::success(['estado'=>'RECHAZADO','motivo'=>$motivo]);
    }
}
