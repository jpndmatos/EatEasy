<?php
session_start();
require 'db.php';
/** @var PDO $pdo */

if (!isset($_SESSION['user_id'])) {
    die("Erro: Tem de estar logado para cancelar reservas.");
}

if (isset($_GET['id'])) {
    $reserva_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        $sql = "DELETE FROM reservas WHERE id = :rid AND utilizador_id = :uid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['rid' => $reserva_id, 'uid' => $user_id]);

        if ($stmt->rowCount() > 0) {
            header("Location: dashboard.php?p=reservas&msg=cancelado");
            exit;
        } else {
            echo "<h1>Erro ao Cancelar</h1>";
            echo "<p>Não foi possível cancelar a reserva #$reserva_id.</p>";
            echo "<p>Causas possíveis: A reserva já foi cancelada ou não lhe pertence.</p>";
            echo "<br><a href='dashboard.php?p=reservas'>Voltar</a>";
        }

    } catch (Exception $e) {
        echo "<h1>Erro de Sistema</h1>";
        echo "<p>" . $e->getMessage() . "</p>";
    }

} else {
    echo "<h1>Erro</h1><p>ID da reserva não fornecido.</p>";
}
?>