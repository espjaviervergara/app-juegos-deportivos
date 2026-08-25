<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class FixtureController
{
    public function generar(Request $req): void
    {
        $this->requireAdmin($req);
        $tid=(int)$req->params['id'];
        $tipo=$req->input('tipo')??'ida'; // ida|ida_vuelta
        $ambito=$req->input('ambito')??'grupo'; // grupo|sin_asignar
        $jornadaId=$req->input('jornadaId') ? (int)$req->input('jornadaId') : null;
        if(!in_array($tipo,['ida','ida_vuelta'])) Response::error('VALIDATION_ERROR','tipo ida|ida_vuelta',422);
        if(!in_array($ambito,['grupo','sin_asignar'])) Response::error('VALIDATION_ERROR','ambito grupo|sin_asignar',422);
        $pdo=Database::pdo();
        if(!$pdo->query("SELECT 1 FROM torneos WHERE id=$tid")->fetch()) Response::error('NOT_FOUND','Torneo not found',404);
        if($jornadaId){
            $j=$pdo->prepare("SELECT torneo_id FROM jornadas WHERE id=?"); $j->execute([$jornadaId]); $tj=$j->fetchColumn();
            if(!$tj || $tj!=$tid) Response::error('VALIDATION_ERROR','Jornada no pertenece a torneo',422);
        }
        // obtener grupos y equipos
        $grupos=[];
        if($ambito==='grupo'){
            $stmt=$pdo->prepare("SELECT * FROM grupos WHERE torneo_id=? ORDER BY orden"); $stmt->execute([$tid]); $grupos=$stmt->fetchAll();
            if(!$grupos) Response::error('VALIDATION_ERROR','Torneo sin grupos, usa sin_asignar o crea grupos',422);
        }
        $partidosCreados=[];
        $pdo->beginTransaction(); try{
            if($ambito==='grupo'){
                foreach($grupos as $g){
                    $eq=$pdo->prepare("SELECT equipo_id FROM grupo_equipo WHERE grupo_id=? ORDER BY equipo_id"); $eq->execute([$g['id']]); $equipos=$eq->fetchAll(\PDO::FETCH_COLUMN);
                    $pairs=$this->roundRobin($equipos);
                    foreach($pairs as $pair){
                        $partidosCreados[]=$this->crearPartido($pdo,$jornadaId,$pair[0],$pair[1],$g['id'],'liga');
                        if($tipo==='ida_vuelta'){
                            $partidosCreados[]=$this->crearPartido($pdo,$jornadaId,$pair[1],$pair[0],$g['id'],'liga');
                        }
                    }
                }
            } else {
                // sin_asignar: entre todos los equipos del torneo
                $eq=$pdo->prepare("SELECT equipo_id FROM torneo_equipo WHERE torneo_id=? ORDER BY equipo_id"); $eq->execute([$tid]); $equipos=$eq->fetchAll(\PDO::FETCH_COLUMN);
                $pairs=$this->roundRobin($equipos);
                foreach($pairs as $pair){
                    $partidosCreados[]=$this->crearPartido($pdo,$jornadaId,$pair[0],$pair[1],null,'liga');
                    if($tipo==='ida_vuelta'){
                        $partidosCreados[]=$this->crearPartido($pdo,$jornadaId,$pair[1],$pair[0],null,'liga');
                    }
                }
            }
            $pdo->commit();
        }catch(\Exception $e){ $pdo->rollBack(); throw $e; }
        Response::success(['creados'=>count($partidosCreados), 'partidos'=>$partidosCreados],201);
    }

    public function eliminatoria(Request $req): void
    {
        $this->requireAdmin($req);
        $tid=(int)$req->params['id'];
        $num=(int)($req->input('numClasificados')??4);
        $jornadaId=$req->input('jornadaId') ? (int)$req->input('jornadaId') : null;
        if(!in_array($num,[2,4,8])) Response::error('VALIDATION_ERROR','numClasificados 2|4|8',422);
        $pdo=Database::pdo();
        if($jornadaId){
            $j=$pdo->prepare("SELECT torneo_id FROM jornadas WHERE id=?"); $j->execute([$jornadaId]); $tj=$j->fetchColumn();
            if(!$tj || $tj!=$tid) Response::error('VALIDATION_ERROR','Jornada no pertenece',422);
        }
        // clasificados: top 1 de cada grupo o top N de tabla
        $clasificados=[];
        $grupos=$pdo->prepare("SELECT id FROM grupos WHERE torneo_id=? ORDER BY orden"); $grupos->execute([$tid]); $gids=$grupos->fetchAll(\PDO::FETCH_COLUMN);
        if($gids){
            // por grupo: tomar primer equipo de cada grupo (orden id) hasta num
            foreach($gids as $gid){
                $eq=$pdo->prepare("SELECT equipo_id FROM grupo_equipo WHERE grupo_id=? ORDER BY equipo_id LIMIT 1"); $eq->execute([$gid]); $eid=$eq->fetchColumn();
                if($eid) $clasificados[]=(int)$eid;
                if(count($clasificados)>=$num) break;
            }
            // si faltan, completar con equipos del torneo
            if(count($clasificados)<$num){
                $eq=$pdo->prepare("SELECT equipo_id FROM torneo_equipo WHERE torneo_id=? AND equipo_id NOT IN (".implode(',', array_fill(0,count($clasificados),'?')).") ORDER BY equipo_id LIMIT ".($num-count($clasificados)));
                $eq->execute(array_merge([$tid],$clasificados)); // need fix
                // simpler: fetch all and filter
                $all=$pdo->prepare("SELECT equipo_id FROM torneo_equipo WHERE torneo_id=? ORDER BY equipo_id"); $all->execute([$tid]); $allIds=$all->fetchAll(\PDO::FETCH_COLUMN);
                foreach($allIds as $eid){ if(!in_array($eid,$clasificados) && count($clasificados)<$num) $clasificados[]=$eid; }
            }
        } else {
            $eq=$pdo->prepare("SELECT equipo_id FROM torneo_equipo WHERE torneo_id=? ORDER BY equipo_id LIMIT $num"); $eq->execute([$tid]); $clasificados=$eq->fetchAll(\PDO::FETCH_COLUMN);
        }
        if(count($clasificados)<$num) Response::error('VALIDATION_ERROR','Not enough teams',422);
        // generar pares: 0 vs 1, 2 vs 3 ...
        $partidos=[];
        $pdo->beginTransaction(); try{
            for($i=0;$i<$num;$i+=2){
                $a=$clasificados[$i]; $b=$clasificados[$i+1]??$clasificados[0];
                $partidos[]=$this->crearPartido($pdo,$jornadaId,$a,$b,null,'eliminatoria');
            }
            $pdo->commit();
        }catch(\Exception $e){ $pdo->rollBack(); throw $e; }
        Response::success(['creados'=>count($partidos), 'partidos'=>$partidos],201);
    }

    public function sinAsignar(Request $req): void
    {
        $tid=(int)$req->params['id'];
        $pdo=Database::pdo();
        $stmt=$pdo->prepare("SELECT p.*, ea.nombre as equipoA_nombre, eb.nombre as equipoB_nombre, g.nombre as grupo_nombre FROM partidos p JOIN equipos ea ON ea.id=p.equipoA_id JOIN equipos eb ON eb.id=p.equipoB_id LEFT JOIN grupos g ON g.id=p.grupo_id WHERE p.jornada_id IS NULL AND p.equipoA_id IN (SELECT equipo_id FROM torneo_equipo WHERE torneo_id=?) ORDER BY p.id");
        $stmt->execute([$tid]); Response::success($stmt->fetchAll());
    }

    private function roundRobin(array $equipos): array
    {
        $pairs=[];
        $n=count($equipos);
        for($i=0;$i<$n;$i++) for($j=$i+1;$j<$n;$j++) $pairs[]=[$equipos[$i],$equipos[$j]];
        return $pairs;
    }
    private function crearPartido($pdo,$jornadaId,$a,$b,$grupoId,$fase){
        $fecha = $jornadaId ? $pdo->prepare("SELECT fecha FROM jornadas WHERE id=?") : null;
        $fechaHora=null;
        if($jornadaId){
            $st=$pdo->prepare("SELECT fecha FROM jornadas WHERE id=?"); $st->execute([$jornadaId]); $fecha=$st->fetchColumn();
            $fechaHora=$fecha." 18:00:00";
        }
        $pdo->prepare("INSERT INTO partidos (jornada_id, equipoA_id, equipoB_id, fechaHora, grupo_id, fase) VALUES (?,?,?,?,?,?)")->execute([$jornadaId,$a,$b,$fechaHora?:date('Y-m-d H:i:s'),$grupoId,$fase]);
        $id=$pdo->lastInsertId();
        $stmt=$pdo->prepare("SELECT p.*, ea.nombre as equipoA_nombre, eb.nombre as equipoB_nombre FROM partidos p JOIN equipos ea ON ea.id=p.equipoA_id JOIN equipos eb ON eb.id=p.equipoB_id WHERE p.id=?");
        $stmt->execute([$id]); return $stmt->fetch();
    }
    private function requireAdmin($req): void { if(($req->user['rol']??'')!=='admin') \App\Core\Response::error('FORBIDDEN','Only admin',403); }
}
