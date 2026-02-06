<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['tipo'] = $user['tipo'];
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Email ou password incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <title>Login - eatEasy</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">eatEasy</div>
        <p class="auth-subtitle">As melhores experiências gastronómicas.</p>

        <?php if(isset($erro)) echo "<div class='aviso'>$erro</div>"; ?>

        <form method="POST">
            <div class="form-group" style="text-align: left;">
                <label>Email</label>
                <input type="email" name="email" placeholder="exemplo@email.com" required>
            </div>

            <div class="form-group" style="text-align: left;">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" style="margin-top: 20px;">Entrar</button>
        </form>

        <div style="margin-top: 30px; font-size: 0.9rem; color: #666;">
            Ainda não tem conta? <br>
            <a href="registar.php" style="color: var(--primary-color); font-weight: 700; text-decoration: none;">Criar conta gratuita</a>
        </div>
    </div>
</div>
</body>
</html>