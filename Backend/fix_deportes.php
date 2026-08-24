<?php
$pdo=new PDO('mysql:host=localhost;dbname=app_juegos_deportivos;charset=utf8mb4','root','', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->exec("UPDATE deportes SET nombre='Fútbol' WHERE id=1");
$pdo->exec("UPDATE deportes SET nombre='Básquet' WHERE id=2");
$pdo->exec("UPDATE deportes SET nombre='Vóley' WHERE id=3");
foreach($pdo->query('SELECT id,nombre FROM deportes') as $r) echo $r['id'].' '.$r['nombre']."\n";
