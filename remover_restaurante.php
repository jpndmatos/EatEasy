<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'admin') { header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); header('Allow: POST'); exit; }
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Restaurante inválido.'); }
$stmt = $pdo->prepare('UPDATE restaurantes SET ativo = FALSE WHERE id = :id');
$stmt->execute(['id' => $id]);
header('Location: dashboard.php?msg=removido', true, 303);
exit;
