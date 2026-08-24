<?php
return [
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'app_juegos_deportivos',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
    ],
    'jwt' => [
        'secret' => require __DIR__ . '/secret.php',
        'algo' => 'HS256',
        'access_ttl' => 900, // 15m
        'refresh_ttl' => 604800, // 7d
        'issuer' => 'juegos-api',
    ],
    'cors' => [
        'allowed_origins' => ['*'], // en prod: ['https://tu-front.com']
        'allowed_methods' => ['GET','POST','PUT','DELETE','OPTIONS','PATCH'],
        'allowed_headers' => ['Content-Type','Authorization','X-HTTP-Method-Override','If-Match'],
        'max_age' => 86400,
    ],
    'rate_limit' => [
        'write_per_min' => 60,
        'read_per_min' => 100,
        'window_seconds' => 60,
    ],
    'overlap_buffer_minutes' => 120,
    'pagination' => [
        'default_limit' => 20,
        'max_limit' => 100,
    ],
];
