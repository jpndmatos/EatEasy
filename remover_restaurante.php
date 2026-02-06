<?php
session_start();
require 'db.php';
/** @var PDO $pdo */

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] != 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "UPDATE restaurantes SET ativo = FALSE WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute(['id' => $id]);
        echo "<script>alert('Restaurante removido com sucesso!'); window.location='dashboard.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Erro ao remover restaurante.'); window.location='dashboard.php';</script>";
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>