<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class UsuarioController
{
    public function index(Request $req): void
    {
        if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403);
        $stmt=Database::pdo()->query("SELECT id,nombre,email,rol,activo,created_at FROM usuarios ORDER BY id");
        Response::success($stmt->fetchAll());
    }
    public function store(Request $req): void
    {
        if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403);
        $nombre=trim($req->input('nombre')??''); $email=trim($req->input('email')??''); $pass=$req->input('password')??''; $rol=$req->input('rol')??'editor';
        if(!$nombre||!$email||!$pass) Response::error('VALIDATION_ERROR','nombre, email, password required',422);
        if(!in_array($rol,['admin','editor'])) Response::error('VALIDATION_ERROR','rol admin|editor',422);
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) Response::error('VALIDATION_ERROR','email invalid',422);
        $hash=password_hash($pass, PASSWORD_BCRYPT);
        try{
            $pdo=Database::pdo(); $pdo->prepare("INSERT INTO usuarios (nombre,email,password_hash,rol) VALUES (?,?,?,?)")->execute([$nombre,$email,$hash,$rol]);
            $id=$pdo->lastInsertId(); $stmt=$pdo->prepare("SELECT id,nombre,email,rol FROM usuarios WHERE id=?"); $stmt->execute([$id]); Response::success($stmt->fetch(),201);
        }catch(\PDOException $e){ if(str_contains($e->getMessage(),'Duplicate')) Response::error('CONFLICT','Email ya existe',409); throw $e; }
    }
    public function destroy(Request $req): void
    {
        if(($req->user['rol']??'')!=='admin') Response::error('FORBIDDEN','Only admin',403);
        $id=(int)$req->params['id']; if($id=== (int)$req->user['sub']) Response::error('VALIDATION_ERROR','No puedes borrarte',422);
        Database::pdo()->prepare("DELETE FROM usuarios WHERE id=?")->execute([$id]);
        Response::json(['data'=>['message'=>'Deleted']]);
    }
}
