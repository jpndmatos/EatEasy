<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); header('Allow: POST');
    exit('Use o botão Cancelar na lista de reservas.');
}
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Reserva inválida.'); }
$stmt = $pdo->prepare('DELETE FROM reservas WHERE id = :rid AND utilizador_id = :uid AND data_hora > :now');
$stmt->execute(['rid' => $id, 'uid' => $_SESSION['user_id'], 'now' => date('Y-m-d H:i:s')]);
header('Location: dashboard.php?p=reservas&msg=' . ($stmt->rowCount() ? 'cancelado' : 'indisponivel'), true, 303);
exit;
