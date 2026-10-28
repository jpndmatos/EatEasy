<?php
// PostgreSQL remains the default. SQLite is an explicit local demo option.
$dsn = getenv('EATEASY_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=eateasy_db';
try {
    $pdo = new PDO($dsn, getenv('EATEASY_DB_USER') ?: 'postgres', getenv('EATEASY_DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    error_log('EatEasy database connection failed.');
    http_response_code(503);
    exit('Não foi possível ligar à base de dados. Verifique a configuração do servidor.');
}
