<?php
// migrate.php — ejecuta migraciones SQL versionadas con PDO
// uso: php migrate.php up | down | fresh

$config = require __DIR__ . '/config/app.php';
$db = $config['db'];
$dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}";

try {
    $pdo = new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    // intenta crear BD si no existe
    $pdoNoDb = new PDO("mysql:host={$db['host']};port={$db['port']};charset={$db['charset']}", $db['username'], $db['password']);
    $pdoNoDb->exec("CREATE DATABASE IF NOT EXISTS `{$db['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo = new PDO($dsn, $db['username'], $db['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    echo "BD creada: {$db['database']}\n";
}

$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (id INT AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(255) UNIQUE, executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

$dir = __DIR__ . '/migrations';
$files = glob($dir . '/*.sql');
sort($files);
$action = $argv[1] ?? 'up';

if ($action === 'down') {
    $executed = $pdo->query("SELECT filename FROM migrations ORDER BY id DESC LIMIT 1")->fetchColumn();
    if (!$executed) { echo "Nada para revertir\n"; exit; }
    echo "Down no implementado por FKs — usar fresh\n"; exit;
}
if ($action === 'fresh') {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) if ($t !== 'migrations') $pdo->exec("DROP TABLE IF EXISTS `$t`");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    $pdo->exec("TRUNCATE migrations");
    echo "Fresh: tablas borradas\n";
}

foreach ($files as $f) {
    $name = basename($f);
    $done = $pdo->prepare("SELECT 1 FROM migrations WHERE filename=?");
    $done->execute([$name]);
    if ($done->fetch()) continue;
    $sql = file_get_contents($f);
    echo "Ejecutando $name ... ";
    $pdo->exec($sql);
    $pdo->prepare("INSERT INTO migrations (filename) VALUES (?)")->execute([$name]);
    echo "OK\n";
}
echo "Migraciones completas\n";

// purga oportunista
$pdo->exec("DELETE FROM refresh_tokens WHERE expires_at < NOW() LIMIT 100");
$pdo->exec("DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
echo "Purga oportunista OK\n";
