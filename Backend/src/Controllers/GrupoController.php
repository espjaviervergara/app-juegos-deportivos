<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use PDO;

class GrupoController
{
    public function index(Request $req): void {
        $tid=(int)$req->params['id']; $stmt=Database::pdo()->prepare("SELECT * FROM grupos WHERE torneo_id=? ORDER BY orden"); $stmt->execute([$tid]);
        $grupos=$stmt->fetchAll();
        foreach($grupos as &$g){
            $st=Database::pdo()->prepare("SELECT e.id,e.nombre FROM equipos e JOIN grupo_equipo ge ON ge.equipo_id=e.id WHERE ge.grupo_id=?");
            $st->execute([$g['id']]); $g['equipos']=$st->fetchAll();
        }
        Response::success($grupos);
    }
    public function store(Request $req): void {
        $this->requireAdmin($req); $tid=(int)$req->params['id']; $nombre=trim($req->input('nombre')??'');
        if(!$nombre) Response::error('VALIDATION_ERROR','nombre required',422);
        $pdo=Database::pdo();
        // check torneo exists
        if(!$pdo->query("SELECT 1 FROM torneos WHERE id=$tid")->fetch()) Response::error('NOT_FOUND','Torneo not found',404);
        $ord=$pdo->prepare("SELECT COALESCE(MAX(orden),0)+1 FROM grupos WHERE torneo_id=?"); $ord->execute([$tid]); $orden=(int)$ord->fetchColumn();
        try{ $pdo->prepare("INSERT INTO grupos (torneo_id,nombre,orden) VALUES (?,?,?)")->execute([$tid,$nombre,$orden]); $id=$pdo->lastInsertId(); $stmt=$pdo->prepare("SELECT * FROM grupos WHERE id=?"); $stmt->execute([$id]); Response::success($stmt->fetch(),201); }
        catch(\PDOException $e){ if(str_contains($e->getMessage(),'Duplicate')) Response::error('CONFLICT','Grupo ya existe en torneo',409); throw $e; }
    }
    public function destroy(Request $req): void {
        $this->requireAdmin($req); $gid=(int)$req->params['id'];
        $pdo=Database::pdo();
        $cnt=$pdo->prepare("SELECT COUNT(*) FROM grupo_equipo WHERE grupo_id=?"); $cnt->execute([$gid]); if((int)$cnt->fetchColumn()>0) Response::error('CONFLICT','Grupo tiene equipos',409);
        $cnt2=$pdo->prepare("SELECT COUNT(*) FROM partidos WHERE grupo_id=?"); $cnt2->execute([$gid]); if((int)$cnt2->fetchColumn()>0) Response::error('CONFLICT','Grupo tiene partidos',409);
        $pdo->prepare("DELETE FROM grupos WHERE id=?")->execute([$gid]); Response::json(['data'=>['message'=>'Deleted']]);
    }
    public function addEquipo(Request $req): void {
        $this->requireAdmin($req); $gid=(int)$req->params['id']; $eid=(int)($req->input('equipoId')??0); if(!$eid) Response::error('VALIDATION_ERROR','equipoId required',422);
        $pdo=Database::pdo();
        $g=$pdo->prepare("SELECT torneo_id FROM grupos WHERE id=?"); $g->execute([$gid]); $torneo=$g->fetchColumn(); if(!$torneo) Response::error('NOT_FOUND','Grupo not found',404);
        // equipo debe estar en torneo
        $chk=$pdo->prepare("SELECT 1 FROM torneo_equipo WHERE torneo_id=? AND equipo_id=?"); $chk->execute([$torneo,$eid]); if(!$chk->fetch()) Response::error('VALIDATION_ERROR','Equipo no está en torneo',422);
        // no estar en otro grupo mismo torneo
        $chk2=$pdo->prepare("SELECT 1 FROM grupo_equipo ge JOIN grupos g ON g.id=ge.grupo_id WHERE g.torneo_id=? AND ge.equipo_id=?"); $chk2->execute([$torneo,$eid]); if($chk2->fetch()) Response::error('CONFLICT','Equipo ya está en otro grupo del torneo',409);
        try{ $pdo->prepare("INSERT INTO grupo_equipo (grupo_id, equipo_id) VALUES (?,?)")->execute([$gid,$eid]); Response::success(['grupo_id'=>$gid,'equipo_id'=>$eid],201); }
        catch(\PDOException $e){ if(str_contains($e->getMessage(),'Duplicate')) Response::error('CONFLICT','Ya en grupo',409); throw $e; }
    }
    public function removeEquipo(Request $req): void {
        $this->requireAdmin($req); $gid=(int)$req->params['id']; $eid=(int)$req->params['equipoId'];
        Database::pdo()->prepare("DELETE FROM grupo_equipo WHERE grupo_id=? AND equipo_id=?")->execute([$gid,$eid]); Response::json(['data'=>['message'=>'Removed']]);
    }
    public function auto(Request $req): void {
        $this->requireAdmin($req); $tid=(int)$req->params['id']; $num=(int)($req->input('numGrupos')??0); $replace=$req->input('replace')?true:false;
        if($num<2||$num>20) Response::error('VALIDATION_ERROR','numGrupos 2-20',422);
        $pdo=Database::pdo();
        $pdo->beginTransaction(); try{
            if($replace){ $pdo->prepare("DELETE FROM grupo_equipo WHERE grupo_id IN (SELECT id FROM grupos WHERE torneo_id=?)")->execute([$tid]); $pdo->prepare("DELETE FROM grupos WHERE torneo_id=?")->execute([$tid]); }
            else{
                $cnt=$pdo->prepare("SELECT COUNT(*) FROM grupos WHERE torneo_id=?"); $cnt->execute([$tid]); if((int)$cnt->fetchColumn()>0) Response::error('CONFLICT','Torneo ya tiene grupos, usa replace:true',409);
            }
            // obtener equipos del torneo
            $eq=$pdo->prepare("SELECT equipo_id FROM torneo_equipo WHERE torneo_id=? ORDER BY equipo_id"); $eq->execute([$tid]); $equipos=$eq->fetchAll(PDO::FETCH_COLUMN);
            if(count($equipos)<$num) Response::error('VALIDATION_ERROR','Not enough teams',422);
            $grupos=[];
            for($i=0;$i<$num;$i++){
                $nombre=chr(65+$i); if($i>=26) $nombre=chr(65+intdiv($i,26)-1).chr(65+($i%26));
                $pdo->prepare("INSERT INTO grupos (torneo_id,nombre,orden) VALUES (?,?,?)")->execute([$tid,"Grupo $nombre",$i+1]); $grupos[]=(int)$pdo->lastInsertId();
            }
            foreach($equipos as $idx=>$eid){
                $gid=$grupos[$idx % $num];
                $pdo->prepare("INSERT INTO grupo_equipo (grupo_id, equipo_id) VALUES (?,?)")->execute([$gid,$eid]);
            }
            $pdo->commit();
            $stmt=$pdo->prepare("SELECT * FROM grupos WHERE torneo_id=? ORDER BY orden"); $stmt->execute([$tid]); Response::success($stmt->fetchAll(),201);
        }catch(\Exception $e){ $pdo->rollBack(); throw $e; }
    }
    public function reagrupar(Request $req): void {
        $this->requireAdmin($req); $movs=$req->input('movimientos')??[]; if(!is_array($movs)||!count($movs)) Response::error('VALIDATION_ERROR','movimientos required',422);
        $pdo=Database::pdo(); $pdo->beginTransaction(); try{
            foreach($movs as $m){
                $eid=(int)($m['equipoId']??0); $from=(int)($m['fromGrupoId']??0); $to=(int)($m['toGrupoId']??0);
                if(!$eid||!$from||!$to) Response::error('VALIDATION_ERROR','equipoId/from/to required',422);
                // validar equipos y grupos mismo torneo
                $torFrom=$pdo->prepare("SELECT torneo_id FROM grupos WHERE id=?"); $torFrom->execute([$from]); $t1=$torFrom->fetchColumn();
                $torTo=$pdo->prepare("SELECT torneo_id FROM grupos WHERE id=?"); $torTo->execute([$to]); $t2=$torTo->fetchColumn();
                if(!$t1||!$t2||$t1!=$t2) Response::error('VALIDATION_ERROR','Grupos de torneos distintos',422);
                $pdo->prepare("DELETE FROM grupo_equipo WHERE grupo_id=? AND equipo_id=?")->execute([$from,$eid]);
                // check not already in other group
                $chk=$pdo->prepare("SELECT 1 FROM grupo_equipo ge JOIN grupos g ON g.id=ge.grupo_id WHERE g.torneo_id=? AND ge.equipo_id=?"); $chk->execute([$t1,$eid]); if($chk->fetch()) Response::error('CONFLICT','Equipo ya en otro grupo',409);
                $pdo->prepare("INSERT INTO grupo_equipo (grupo_id, equipo_id) VALUES (?,?)")->execute([$to,$eid]);
            }
            $pdo->commit(); Response::success(['message'=>'Reagrupado']);
        }catch(\Exception $e){ $pdo->rollBack(); throw $e; }
    }
    private function requireAdmin(Request $req): void { if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403); }
}
