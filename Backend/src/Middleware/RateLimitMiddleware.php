<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class RateLimitMiddleware implements \App\Core\MiddlewareInterface
{
    private array $cfg;
    public function __construct(array $cfg=null){ $this->cfg=$cfg??require __DIR__.'/../../config/app.php'; }
    public function handle(Request $req, callable $next)
    {
        $isWrite = in_array($req->method, ['POST','PUT','DELETE','PATCH']);
        $key = $isWrite ? ('user:'.($req->user['sub'] ?? 'anon')) : ('ip:'.($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'));
        $limit = $isWrite ? $this->cfg['rate_limit']['write_per_min'] : $this->cfg['rate_limit']['read_per_min'];
        $window = $this->cfg['rate_limit']['window_seconds'];
        $now = time();
        $windowStart = date('Y-m-d H:i:s', $now - ($now % $window));
        $pdo = Database::pdo();
        // purge old windows opportunistically is done in migrate.php, also here cleanup
        $pdo->prepare("INSERT INTO rate_limits (clave, window_start, contador) VALUES (?,?,1) ON DUPLICATE KEY UPDATE contador=contador+1")->execute([$key,$windowStart]);
        $cnt = $pdo->prepare("SELECT contador FROM rate_limits WHERE clave=? AND window_start=?");
        $cnt->execute([$key,$windowStart]);
        $c = (int)$cnt->fetchColumn();
        if ($c > $limit) {
            $retry = $window - ($now % $window);
            header("Retry-After: $retry");
            Response::error('RATE_LIMITED','Too many requests',429);
        }
        return $next($req);
    }
}
