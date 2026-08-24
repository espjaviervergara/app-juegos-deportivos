<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable|array $handler, array $middleware = []): void
    {
        $method = strtoupper($method);
        // normalize pattern: ensure leading /, no trailing slash except root
        $pattern = '/' . ltrim($pattern, '/');
        $pattern = rtrim($pattern, '/') ?: '/';
        // convert {param} to regex
        $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        $this->routes[] = compact('method','pattern','regex','handler','middleware');
    }

    public function get(string $p, $h, array $m=[]): void { $this->add('GET',$p,$h,$m); }
    public function post(string $p, $h, array $m=[]): void { $this->add('POST',$p,$h,$m); }
    public function put(string $p, $h, array $m=[]): void { $this->add('PUT',$p,$h,$m); }
    public function delete(string $p, $h, array $m=[]): void { $this->add('DELETE',$p,$h,$m); }
    public function patch(string $p, $h, array $m=[]): void { $this->add('PATCH',$p,$h,$m); }

    public static function normalizePath(string $path): string
    {
        // decode, collapse //, remove /./ and resolve /../, trim trailing /
        $path = rawurldecode($path);
        // collapse multiple slashes
        $path = preg_replace('#//+#', '/', $path);
        // reject traversal
        if (str_contains($path, '..')) {
            // resolve .. safely
            $parts = explode('/', $path);
            $stack = [];
            foreach ($parts as $p) {
                if ($p === '..') { array_pop($stack); }
                elseif ($p !== '.' && $p !== '') { $stack[] = $p; }
            }
            $path = '/' . implode('/', $stack);
        }
        $path = rtrim($path, '/') ?: '/';
        if (!str_starts_with($path, '/')) $path = '/' . $path;
        return $path;
    }

    public function dispatch(Request $req): array
    {
        $path = self::normalizePath($req->path);
        $matchedPath = false;
        foreach ($this->routes as $r) {
            if (preg_match($r['regex'], $path, $m)) {
                $matchedPath = true;
                if ($r['method'] !== $req->method) continue;
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                return ['handler'=>$r['handler'],'params'=>$params,'middleware'=>$r['middleware'],'pattern'=>$r['pattern']];
            }
        }
        if ($matchedPath) return ['error'=>405];
        return ['error'=>404];
    }

    // For tests: check if would be 404/405
    public function match(string $method, string $path): ?array
    {
        $req = new class($method,$path) extends Request {
            public function __construct($m,$p){ $this->method=strtoupper($m); $this->path=$p; $this->uri=$p; $this->query=[]; $this->headers=[]; $this->body=null; }
        };
        return $this->dispatch($req);
    }
}
