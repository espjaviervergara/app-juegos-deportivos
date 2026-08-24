<?php
namespace App\Core;

class Response
{
    public static function json($data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        foreach ($headers as $k => $v) header("$k: $v");
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success($data, int $status = 200, array $meta = []): void
    {
        $payload = ['data' => $data];
        if (!empty($meta)) $payload['meta'] = $meta;
        self::json($payload, $status);
    }

    public static function paginated(array $data, int $page, int $limit, int $total, int $status = 200): void
    {
        self::json([
            'data' => $data,
            'meta' => ['page'=>$page,'limit'=>$limit,'total'=>$total,'pages'=> (int)ceil($total / max(1,$limit))]
        ], $status);
    }

    public static function error(string $code, string $message, int $status, $details = null): void
    {
        $payload = ['error' => ['code'=>$code,'message'=>$message]];
        if ($details !== null) $payload['error']['details'] = $details;
        self::json($payload, $status);
    }

    public static function cors(array $config): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowed = $config['allowed_origins'] ?? ['*'];
        if (in_array('*', $allowed)) {
            header('Access-Control-Allow-Origin: *');
        } elseif ($origin && in_array($origin, $allowed)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Vary: Origin');
        }
        header('Access-Control-Allow-Methods: ' . implode(', ', $config['allowed_methods'] ?? ['GET','POST','PUT','DELETE','OPTIONS']));
        header('Access-Control-Allow-Headers: ' . implode(', ', $config['allowed_headers'] ?? ['Content-Type','Authorization']));
        header('Access-Control-Max-Age: ' . ($config['max_age'] ?? 86400));
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }
    }
}
