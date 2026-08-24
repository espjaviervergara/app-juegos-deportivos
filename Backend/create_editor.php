<?php
$pdo=new PDO('mysql:host=localhost;dbname=app_juegos_deportivos;charset=utf8mb4','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$hash=password_hash('Editor123!', PASSWORD_BCRYPT);
$pdo->prepare("INSERT IGNORE INTO usuarios (id,nombre,email,password_hash,rol) VALUES (2,'Editor Uno','editor@juegos.local',?, 'editor')")->execute([$hash]);
$pdo->prepare("INSERT IGNORE INTO usuario_torneo (usuario_id, torneo_id) VALUES (2,1)")->execute();
echo "editor seeded\n";
foreach($pdo->query("SELECT id,email,rol FROM usuarios") as $r) echo $r['id']." ".$r['email']." ".$r['rol']."\n";
