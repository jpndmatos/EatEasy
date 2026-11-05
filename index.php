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
        session_regenerate_id(true);
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg">
    <title>Login - eatEasy</title>
    <link rel="stylesheet" href="css/style.css?v=20261028-theme-icons">
    <script src="js/script.js?v=20261028-theme-icons" defer></script>
    <link rel="stylesheet" href="css/dashboard.css?v=20261103-viewport">
</head>
<body>
<main class="auth-container">
    <div class="auth-card">
        <h1 class="auth-logo">eatEasy</h1>
        <p class="auth-subtitle">Entre para consultar restaurantes e gerir reservas.</p>

        <?php if(isset($erro)) echo "<div class='aviso'>$erro</div>"; ?>

        <form method="POST">
                <?php echo csrf_field(); ?>
            <div class="form-group" style="text-align: left;">
                <label for="email">Email</label>
                        <input id="email" type="email" name="email" placeholder="exemplo@email.com" required>
            </div>

            <div class="form-group" style="text-align: left;">
                <label for="password">Password</label>
                        <input id="password" type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" style="margin-top: 20px;">Entrar</button>
        </form>

        <div style="margin-top: 30px; font-size: 0.9rem; color:var(--text-muted);">
            Ainda não tem conta? <br>
            <a href="registar.php" style="color: var(--primary-color); font-weight: 700; text-decoration: none;">Criar conta gratuita</a>
        </div>
    </div>
</main>
</body>
</html>