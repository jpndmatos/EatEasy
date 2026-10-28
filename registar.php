<?php
require 'db.php';

$mensagem = "";
$tipo_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO utilizadores (nome, email, password, tipo) VALUES (:nome, :email, :pass, 'cliente')";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute(['nome' => $nome, 'email' => $email, 'pass' => $passHash]);
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $mensagem = "Não foi possível criar a conta. Verifique os dados ou utilize outro email.";
        $tipo_msg = "aviso";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg">
    <script src="js/theme.js"></script>
    <title>Registar - eatEasy</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
<button type="button" class="theme-toggle" aria-label="Alterar tema" aria-pressed="false">Tema escuro</button>
<div class="auth-container">
    <div class="auth-card">
        <h2>Criar Nova Conta</h2>

        <?php if($mensagem) echo "<div class='$tipo_msg'>$mensagem</div>"; ?>

        <form method="POST">
            <label for="nome">Nome Completo:</label>
                        <input id="nome" type="text" name="nome" placeholder="O teu nome" required>

            <label for="email">Email:</label>
                        <input id="email" type="email" name="email" placeholder="exemplo@email.com" required>

            <label for="password">Password:</label>
                        <input id="password" type="password" name="password" placeholder="Uma senha segura" required>

            <button type="submit">Registar</button>
        </form>

        <br>
        <p style="font-size: 0.9rem;">
            Já tem conta? <a href="index.php">Faça Login</a>.
        </p>
    </div>
</div>
</body>
</html>