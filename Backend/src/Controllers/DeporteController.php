<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\DeporteRepository;

class DeporteController
{
    private DeporteRepository $repo;
    public function __construct(){ $this->repo=new DeporteRepository(); }

    public function index(Request $req): void
    {
        [$page,$limit] = $this->paginate($req);
        [$data,$total] = $this->repo->all($page,$limit, ['activo'=>1]);
        Response::paginated($data,$page,$limit,$total);
    }
    public function show(Request $req): void
    {
        $d = $this->repo->find((int)$req->params['id']);
        if (!$d) Response::error('NOT_FOUND','Deporte not found',404);
        Response::success($d);
    }
    public function store(Request $req): void
    {
        $this->requireAdmin($req);
        $nombre = trim($req->input('nombre') ?? '');
        if ($nombre==='') Response::error('VALIDATION_ERROR','nombre required',422, [['field'=>'nombre','message'=>'required']]);
        try {
            $id = $this->repo->create(['nombre'=>$nombre,'activo'=>1]);
            Response::success($this->repo->find($id),201);
        } catch (\PDOException $e){ if (str_contains($e->getMessage(),'Duplicate')) Response::error('CONFLICT','Deporte already exists',409); throw $e; }
    }
    public function update(Request $req): void
    {
        $this->requireAdmin($req);
        $id=(int)$req->params['id']; $d=$this->repo->find($id); if(!$d) Response::error('NOT_FOUND','Not found',404);
        $nombre=trim($req->input('nombre')??$d['nombre']); $activo=$req->input('activo')??$d['activo'];
        $this->repo->update($id,['nombre'=>$nombre,'activo'=>$activo?1:0]);
        Response::success($this->repo->find($id));
    }
    public function destroy(Request $req): void
    {
        $this->requireAdmin($req);
        $id=(int)$req->params['id']; if(!$this->repo->find($id)) Response::error('NOT_FOUND','Not found',404);
        $this->repo->delete($id); Response::json(['data'=>['message'=>'Deleted']]);
    }
    private function paginate(Request $req): array { $p=max(1,(int)($req->query('page')??1)); $l=min(100,max(1,(int)($req->query('limit')??20))); return [$p,$l]; }
    private function requireAdmin(Request $req): void { if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403); }
}
