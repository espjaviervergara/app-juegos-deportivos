<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\TorneoRepository;
use App\Services\AuditService;

class TorneoController
{
    private TorneoRepository $repo;
    public function __construct(){ $this->repo=new TorneoRepository(); }

    public function index(Request $req): void
    {
        $page=max(1,(int)($req->query('page')??1)); $limit=min(100,max(1,(int)($req->query('limit')??20)));
        $filters=[]; if($req->query('deporteId')) $filters['deporte_id']=(int)$req->query('deporteId');
        if($req->query('categoria')) $filters['categoria']=$req->query('categoria');
        [$data,$total]=$this->repo->all($page,$limit,$filters);
        Response::paginated($data,$page,$limit,$total);
    }
    public function show(Request $req): void { $t=$this->repo->find((int)$req->params['id']); if(!$t) Response::error('NOT_FOUND','Torneo not found',404); Response::success($t); }
    public function store(Request $req): void
    {
        $this->requireAdmin($req);
        $nombre=trim($req->input('nombre')??''); $deporteId=(int)($req->input('deporteId')??0);
        $cat=$req->input('categoria'); $formato=$req->input('formato');
        if(!$nombre||!$deporteId||!in_array($cat,['M','F','Mixto'])||!in_array($formato,['liga','eliminatoria','grupos+eliminatoria'])) {
            Response::error('VALIDATION_ERROR','Validation failed',422, [['field'=>'nombre/deporteId/categoria/formato','message'=>'invalid']]);
        }
        $pdo=Database::pdo(); $dep=$pdo->prepare("SELECT activo FROM deportes WHERE id=?"); $dep->execute([$deporteId]); $r=$dep->fetch();
        if(!$r) Response::error('VALIDATION_ERROR','Deporte not found',422);
        if(!$r['activo']) Response::error('VALIDATION_ERROR','Deporte inactive',422);
        $id=$this->repo->create(['deporte_id'=>$deporteId,'nombre'=>$nombre,'categoria'=>$cat,'formato'=>$formato,'estado'=>'activo']);
        $t=$this->repo->find($id); AuditService::log($req->user['sub']??null,'torneo.creado','torneo',$id,$id,null,null,$t); Response::success($t,201);
    }
    public function update(Request $req): void { $this->requireAdmin($req); $id=(int)$req->params['id']; $t=$this->repo->find($id); if(!$t) Response::error('NOT_FOUND','Not found',404); $data=[]; foreach(['nombre','categoria','formato','estado'] as $k) if($req->input($k)!==null) $data[$k]=$req->input($k); if(isset($data['nombre'])) $data['nombre']=trim($data['nombre']); if($data) $this->repo->update($id,$data); Response::success($this->repo->find($id)); }
    public function destroy(Request $req): void { $this->requireAdmin($req); $id=(int)$req->params['id']; if(!$this->repo->find($id)) Response::error('NOT_FOUND','Not found',404); $this->repo->delete($id); Response::json(['data'=>['message'=>'Deleted']]); }
    public function attachEquipo(Request $req): void { $this->requireAdmin($req); $tid=(int)$req->params['id']; $eid=(int)($req->input('equipoId')??0); if(!$eid) Response::error('VALIDATION_ERROR','equipoId required',422); $pdo=Database::pdo(); try{ $pdo->prepare("INSERT INTO torneo_equipo (torneo_id, equipo_id) VALUES (?,?)")->execute([$tid,$eid]); }catch(\PDOException $e){ if(str_contains($e->getMessage(),'Duplicate')) Response::error('CONFLICT','Already attached',409); throw $e; } Response::success(['torneo_id'=>$tid,'equipo_id'=>$eid],201); }
    public function detachEquipo(Request $req): void { $this->requireAdmin($req); $tid=(int)$req->params['id']; $eid=(int)$req->params['equipoId']; Database::pdo()->prepare("DELETE FROM torneo_equipo WHERE torneo_id=? AND equipo_id=?")->execute([$tid,$eid]); Response::json(['data'=>['message'=>'Detached']]); }
    public function attachEditor(Request $req): void { $this->requireAdmin($req); $tid=(int)$req->params['id']; $uid=(int)($req->input('usuarioId')??0); if(!$uid) Response::error('VALIDATION_ERROR','usuarioId required',422); try{ Database::pdo()->prepare("INSERT INTO usuario_torneo (usuario_id, torneo_id) VALUES (?,?)")->execute([$uid,$tid]); }catch(\PDOException $e){ if(str_contains($e->getMessage(),'Duplicate')) Response::error('CONFLICT','Already assigned',409); throw $e; } Response::success(['usuario_id'=>$uid,'torneo_id'=>$tid],201); }
    public function detachEditor(Request $req): void { $this->requireAdmin($req); $tid=(int)$req->params['id']; $uid=(int)$req->params['usuarioId']; Database::pdo()->prepare("DELETE FROM usuario_torneo WHERE usuario_id=? AND torneo_id=?")->execute([$uid,$tid]); Response::json(['data'=>['message'=>'Removed']]); }
    public function misTorneos(Request $req): void { $uid=$req->user['sub']; $pdo=Database::pdo(); $stmt=$pdo->prepare("SELECT t.* FROM torneos t JOIN usuario_torneo ut ON ut.torneo_id=t.id WHERE ut.usuario_id=?"); $stmt->execute([$uid]); Response::success($stmt->fetchAll()); }
    private function requireAdmin(Request $req): void { if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403); }
}
