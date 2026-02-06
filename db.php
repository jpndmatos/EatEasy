<?php
// db.php

$host = 'localhost';
$db   = 'eateasy_db';
$user = 'postgres';
$pass = 'postgres';
$port = '5432';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}