<?php
// Creates an isolated sample database; never changes a configured PostgreSQL database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$path = dirname(__DIR__) . '/var/demo.sqlite';
if (file_exists($path)) { echo "Demo database already exists. No data changed.\n"; exit; }
if (!is_dir(dirname($path))) mkdir(dirname($path), 0700, true);
$pdo = new PDO('sqlite:' . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec(file_get_contents(dirname(__DIR__) . '/database/schema-sqlite.sql'));
$user = $pdo->prepare('INSERT INTO utilizadores (nome,email,password,tipo) VALUES (?,?,?,?)');
$user->execute(['Alex','alex@example.test',password_hash('EatEasy-demo-2026', PASSWORD_DEFAULT),'cliente']);
$user->execute(['Admin','admin@example.test',password_hash('EatEasy-demo-2026', PASSWORD_DEFAULT),'admin']);
$restaurant = $pdo->prepare('INSERT INTO restaurantes (nome,localizacao,preco_medio,cozinha) VALUES (?,?,?,?)');
foreach ([['Mesa da Vila','Lisboa',22,'Portuguesa'],['Forno & Farinha','Porto',18,'Italiana'],['Verde à Mesa','Braga',16,'Vegetariana'],['Casa do Mar','Matosinhos',32,'Marisco'],['Pátio do Sul','Faro',24,'Mediterrânica'],['Trattoria do Bairro','Coimbra',20,'Italiana']] as $row) $restaurant->execute($row);
$booking = $pdo->prepare('INSERT INTO reservas (utilizador_id,restaurante_id,data_hora,num_pessoas) VALUES (1,?,?,?)');
$booking->execute([1,date('Y-m-d',strtotime('+2 days')).' 19:30:00',2]);
$booking->execute([2,date('Y-m-d',strtotime('-5 days')).' 13:00:00',4]);
echo "Sample database created at var/demo.sqlite.\n";
