<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AuthMiddleware implements \App\Core\MiddlewareInterface
{
    private array $cfg;
    public function __construct(array $cfg=null){ $this->cfg=$cfg??require __DIR__.'/../../config/app.php'; }
    public function handle(Request $req, callable $next)
    {
        $token = $req->bearerToken();
        if (!$token) Response::error('UNAUTHORIZED','Missing token',401);
        $payload = (new AuthService($this->cfg))->verify($token);
        if (!$payload) Response::error('UNAUTHORIZED','Invalid or expired token',401);
        $req->user = $payload;
        return $next($req);
    }
}
