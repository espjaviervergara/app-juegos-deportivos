<?php
$pdo=new PDO('mysql:host=localhost;dbname=app_juegos_deportivos;charset=utf8mb4','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$hash=password_hash('Admin123!', PASSWORD_BCRYPT);
$pdo->prepare('INSERT IGNORE INTO usuarios (id,nombre,email,password_hash,rol) VALUES (1,?, ?, ?, "admin")')->execute(['Administrador Principal','admin@juegos.local',$hash]);
echo "admin seeded: $hash\n";
echo "deportes: ".$pdo->query('SELECT COUNT(*) FROM deportes')->fetchColumn()."\n";
echo "usuarios: ".$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn()."\n";
