<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class ClasificacionController
{
    public function tabla(Request $req): void
    {
        $tid=(int)$req->params['id']; $pdo=Database::pdo();
        // verificar torneo existe
        $chk=$pdo->prepare("SELECT 1 FROM torneos WHERE id=?"); $chk->execute([$tid]); if(!$chk->fetch()) Response::error('NOT_FOUND','Torneo not found',404);
        $page=max(1,(int)($req->query('page')??1)); $limit=min(100,max(1,(int)($req->query('limit')??20)));
        $sql = "
            SELECT e.id, e.nombre,
                COUNT(p.id) as PJ,
                SUM(CASE WHEN (p.equipoA_id=e.id AND JSON_EXTRACT(r.datos,'$.goles') IS NOT NULL) THEN 1 ELSE 0 END) as dummy,
                COALESCE(SUM(CASE WHEN r.estado='OFICIAL' THEN 1 ELSE 0 END),0) as partidos_oficiales
            FROM equipos e
            JOIN torneo_equipo te ON te.equipo_id=e.id AND te.torneo_id=?
            LEFT JOIN partidos p ON (p.equipoA_id=e.id OR p.equipoB_id=e.id)
            LEFT JOIN jornadas j ON j.id=p.jornada_id AND j.torneo_id=?
            LEFT JOIN resultados_propuestos r ON r.partido_id=p.id AND r.estado='OFICIAL'
            GROUP BY e.id
        ";
        // Simpler: compute via PHP aggregation for hosting básico sin JSON complejo
        $equipos = $pdo->prepare("SELECT e.id, e.nombre FROM equipos e JOIN torneo_equipo te ON te.equipo_id=e.id WHERE te.torneo_id=?");
        $equipos->execute([$tid]); $teams=$equipos->fetchAll();
        $tabla=[];
        foreach($teams as $t){
            $eid=$t['id'];
            $q=$pdo->prepare("
                SELECT p.*, r.datos FROM partidos p
                JOIN jornadas j ON j.id=p.jornada_id
                LEFT JOIN resultados_propuestos r ON r.partido_id=p.id AND r.estado='OFICIAL'
                WHERE j.torneo_id=? AND (p.equipoA_id=? OR p.equipoB_id=?) AND r.id IS NOT NULL
            ");
            $q->execute([$tid,$eid,$eid]);
            $pj=0;$pg=0;$pe=0;$pp=0;$gf=0;$gc=0;
            foreach($q->fetchAll() as $row){
                $pj++;
                $datos=json_decode($row['datos'],true);
                $goles=$datos['goles']??[];
                $gfTeam=0;$gcTeam=0;
                foreach($goles as $g){ if((int)$g['equipoId']==$eid) $gfTeam+=(int)($g['cantidad']??1); else $gcTeam+=(int)($g['cantidad']??1); }
                $gf+=$gfTeam; $gc+=$gcTeam;
                if($gfTeam>$gcTeam) $pg++; elseif($gfTeam==$gcTeam) $pe++; else $pp++;
            }
            $ga=$gf-$gc; $puntos=$pg*3+$pe;
            $tabla[]=['equipo_id'=>$eid,'equipo'=>$t['nombre'],'PJ'=>$pj,'PG'=>$pg,'PE'=>$pe,'PP'=>$pp,'GF'=>$gf,'GC'=>$gc,'GA'=>$ga,'puntos'=>$puntos];
        }
        usort($tabla, fn($a,$b)=> $b['puntos']<=>$a['puntos'] ?: $b['GA']<=>$a['GA'] ?: $b['GF']<=>$a['GF']);
        $total=count($tabla); $off=($page-1)*$limit; $data=array_slice($tabla,$off,$limit);
        Response::paginated($data,$page,$limit,$total);
    }

    public function estadisticasEquipo(Request $req): void
    {
        $tid=(int)$req->params['id']; $eid=(int)$req->params['equipoId'];
        $pdo=Database::pdo();
        // similar a tabla pero solo un equipo
        $q=$pdo->prepare("SELECT r.datos FROM partidos p JOIN jornadas j ON j.id=p.jornada_id JOIN resultados_propuestos r ON r.partido_id=p.id AND r.estado='OFICIAL' WHERE j.torneo_id=? AND (p.equipoA_id=? OR p.equipoB_id=?)");
        $q->execute([$tid,$eid,$eid]);
        $gf=0;$gc=0;$pg=0;$pe=0;$pp=0;$pj=0;
        foreach($q->fetchAll() as $row){ $pj++; $datos=json_decode($row['datos'],true); $goles=$datos['goles']??[]; $gfT=0;$gcT=0; foreach($goles as $g){ if((int)$g['equipoId']==$eid) $gfT+=(int)($g['cantidad']??1); else $gcT+=(int)($g['cantidad']??1); } $gf+=$gfT; $gc+=$gcT; if($gfT>$gcT) $pg++; elseif($gfT==$gcT) $pe++; else $pp++; }
        Response::success(['equipo_id'=>$eid,'torneo_id'=>$tid,'PJ'=>$pj,'PG'=>$pg,'PE'=>$pe,'PP'=>$pp,'GF'=>$gf,'GC'=>$gc,'GA'=>$gf-$gc,'puntos'=>$pg*3+$pe]);
    }

    public function estadisticasJugador(Request $req): void
    {
        $tid=(int)$req->params['id']; $jid=(int)$req->params['jugadorId'];
        $pdo=Database::pdo();
        $q=$pdo->prepare("SELECT r.datos FROM partidos p JOIN jornadas j ON j.id=p.jornada_id JOIN resultados_propuestos r ON r.partido_id=p.id AND r.estado='OFICIAL' WHERE j.torneo_id=?");
        $q->execute([$tid]);
        $goles=0;$amarillas=0;$rojas=0;
        foreach($q->fetchAll() as $row){ $datos=json_decode($row['datos'],true); foreach($datos['goles']??[] as $g) if((int)$g['jugadorId']==$jid) $goles+=(int)($g['cantidad']??1); foreach($datos['tarjetas']??[] as $t) if((int)$t['jugadorId']==$jid){ if($t['tipo']=='amarilla') $amarillas++; if($t['tipo']=='roja') $rojas++; } }
        Response::success(['jugador_id'=>$jid,'torneo_id'=>$tid,'goles'=>$goles,'amarillas'=>$amarillas,'rojas'=>$rojas]);
    }
}
