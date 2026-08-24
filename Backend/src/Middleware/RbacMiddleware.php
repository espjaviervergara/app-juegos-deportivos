<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class RbacMiddleware implements \App\Core\MiddlewareInterface
{
    private array $cfg;
    public function __construct(array $cfg=null){ $this->cfg=$cfg??require __DIR__.'/../../config/app.php'; }
    public function handle(Request $req, callable $next)
    {
        $user = $req->user;
        if (!$user) Response::error('UNAUTHORIZED','No user',401);
        $rol = $user['rol'] ?? 'editor';
        // admin bypass
        if ($rol === 'admin') return $next($req);
        // editor: check assignment for torneo-specific routes
        $torneoId = $req->params['id'] ?? $req->params['torneoId'] ?? null;
        // try to infer torneo from partido/jornada
        if (!$torneoId && isset($req->params['id'])) {
            // partido/jornada: resolve torneo
            $pdo = Database::pdo();
            if (str_contains($req->path,'/partidos/')) {
                $pid = (int)$req->params['id'];
                $t = $pdo->prepare("SELECT j.torneo_id FROM partidos p JOIN jornadas j ON j.id=p.jornada_id WHERE p.id=?");
                $t->execute([$pid]); $torneoId = $t->fetchColumn();
            } elseif (str_contains($req->path,'/jornadas/')) {
                $jid = (int)$req->params['id'];
                $t = $pdo->prepare("SELECT torneo_id FROM jornadas WHERE id=?");
                $t->execute([$jid]); $torneoId = $t->fetchColumn();
            }
        }
        if ($torneoId) {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare("SELECT 1 FROM usuario_torneo WHERE usuario_id=? AND torneo_id=?");
            $stmt->execute([$user['sub'], $torneoId]);
            if (!$stmt->fetch()) Response::error('FORBIDDEN','Not assigned to tournament',403);
        } else {
            // for non-torneo scoped writes, editor forbidden (deportes, torneos creation, etc.)
            if (in_array($req->method, ['POST','PUT','DELETE']) && !str_contains($req->path,'/resultados')) {
                Response::error('FORBIDDEN','Editor cannot perform this action',403);
            }
        }
        return $next($req);
    }
}
