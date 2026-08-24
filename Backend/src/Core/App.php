<?php
namespace App\Core;

class App
{
    private Router $router;
    private array $config;

    public function __construct(Router $router, array $config)
    {
        $this->router = $router;
        $this->config = $config;
    }

    public function run(): void
    {
        Response::cors($this->config['cors'] ?? []);
        // security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer');

        $req = new Request();
        $match = $this->router->dispatch($req);

        if (isset($match['error'])) {
            if ($match['error'] === 405) Response::error('METHOD_NOT_ALLOWED','Method not allowed',405);
            Response::error('NOT_FOUND','Not found',404);
        }

        $req->params = $match['params'];
        $handler = $match['handler'];
        $middleware = $match['middleware'];

        $next = function(Request $r) use ($handler) {
            if (is_array($handler)) {
                [$class,$method] = $handler;
                $inst = new $class();
                return $inst->$method($r);
            }
            return $handler($r);
        };

        // build middleware chain (reverse)
        foreach (array_reverse($middleware) as $mwClass) {
            $mw = new $mwClass($this->config);
            $prev = $next;
            $next = fn(Request $r) => $mw->handle($r, $prev);
        }

        $next($req);
    }
}
