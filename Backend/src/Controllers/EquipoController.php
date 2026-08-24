<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\EquipoRepository;

class EquipoController
{
    private EquipoRepository $repo;
    public function __construct(){ $this->repo=new EquipoRepository(); }
    public function index(Request $req): void
    {
        $page=max(1,(int)($req->query('page')??1)); $limit=min(100,max(1,(int)($req->query('limit')??20)));
        $filters=[]; if($req->query('torneoId')) { $pdo=Database::pdo(); $stmt=$pdo->prepare("SELECT e.* FROM equipos e JOIN torneo_equipo te ON te.equipo_id=e.id WHERE te.torneo_id=? LIMIT $limit OFFSET ".(($page-1)*$limit)); $stmt->execute([(int)$req->query('torneoId')]); $data=$stmt->fetchAll(); $cnt=$pdo->prepare("SELECT COUNT(*) FROM torneo_equipo WHERE torneo_id=?"); $cnt->execute([(int)$req->query('torneoId')]); Response::paginated($data,$page,$limit,(int)$cnt->fetchColumn()); }
        [$data,$total]=$this->repo->all($page,$limit); Response::paginated($data,$page,$limit,$total);
    }
    public function porTorneo(Request $req): void { $tid=(int)$req->params['id']; $pdo=Database::pdo(); $stmt=$pdo->prepare("SELECT e.* FROM equipos e JOIN torneo_equipo te ON te.equipo_id=e.id WHERE te.torneo_id=?"); $stmt->execute([$tid]); Response::success($stmt->fetchAll()); }
    public function show(Request $req): void { $e=$this->repo->find((int)$req->params['id']); if(!$e) Response::error('NOT_FOUND','Equipo not found',404); Response::success($e); }
    public function store(Request $req): void { $this->requireAdmin($req); $nombre=trim($req->input('nombre')??''); if(!$nombre) Response::error('VALIDATION_ERROR','nombre required',422); try{ $id=$this->repo->create(['nombre'=>$nombre,'escudo_path'=>$req->input('escudo_path')]); Response::success($this->repo->find($id),201);}catch(\PDOException $e){ if(str_contains($e->getMessage(),'Duplicate')) Response::error('CONFLICT','Equipo exists',409); throw $e; } }
    public function update(Request $req): void { $this->requireAdmin($req); $id=(int)$req->params['id']; if(!$this->repo->find($id)) Response::error('NOT_FOUND','Not found',404); $data=[]; if($req->input('nombre')!==null) $data['nombre']=trim($req->input('nombre')); if($req->input('escudo_path')!==null) $data['escudo_path']=$req->input('escudo_path'); if($data) $this->repo->update($id,$data); Response::success($this->repo->find($id)); }
    public function destroy(Request $req): void { $this->requireAdmin($req); $id=(int)$req->params['id']; if(!$this->repo->find($id)) Response::error('NOT_FOUND','Not found',404); $this->repo->delete($id); Response::json(['data'=>['message'=>'Deleted']]); }
    private function requireAdmin(Request $req): void { if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403); }
}
