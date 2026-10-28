<?php
session_start();
require 'db.php';
/** @var PDO $pdo */

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] != 'admin') { header("Location: index.php"); exit; }

$nome_user = $_SESSION['nome'];
$msg = ""; $tipo_msg = "";

$tipos_cozinha = ["Portuguesa", "Italiana", "Japonesa", "Americana", "Chinesa", "Indiana", "Mexicana", "Brasileira", "Vegetariana", "Mediterrânica", "Fast Food", "Marisco"];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $local = $_POST['local'];
    $preco = $_POST['preco'];
    $cozinha = $_POST['cozinha'];

    $sql = "INSERT INTO restaurantes (nome, localizacao, preco_medio, cozinha) VALUES (:n, :l, :p, :c)";
    try {
        $pdo->prepare($sql)->execute(['n' => $nome, 'l' => $local, 'p' => $preco, 'c' => $cozinha]);
        echo "<script>alert('Restaurante criado com sucesso!'); window.location='dashboard.php';</script>";
    } catch (Exception $e) { $msg = "Erro ao criar."; $tipo_msg = "aviso"; }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg">
    <script src="js/theme.js"></script>
    <title>Novo Restaurante</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
<button type="button" class="theme-toggle" aria-label="Alterar tema" aria-pressed="false"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/></svg></button>

<div class="app-layout">
    <aside class="sidebar">
        <div class="logo">eatEasy</div>
        <div class="user-info">
            <p>Olá, <strong><?php echo htmlspecialchars($nome_user); ?></strong></p>
            <small>Administrador</small>
        </div>
        <nav class="menu-links">
            <a href="dashboard.php">Início</a>
            <a href="admin_reservas.php">Todas as Reservas</a>
            <a href="admin_restaurante.php" class="active">Novo Restaurante</a>
            <a href="logout.php" class="btn-logout">Terminar Sessão</a>
        </nav>
    </aside>

    <main class="main-content">

        <div class="restaurant-header" style="margin-top: 20px; margin-bottom: 30px;">
            <h1>Adicionar Novo Restaurante</h1>
        </div>

        <div class="reservation-section" style="max-width: 800px;">
            <h3>Dados do Estabelecimento</h3>

            <?php if($msg) echo "<div class='$tipo_msg'>$msg</div>"; ?>

            <form method="POST">

                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 2; min-width: 300px;">
                        <label for="nome">Nome do Restaurante</label>
                        <input id="nome" type="text" name="nome" placeholder="Ex: O Tasco do Zé" required>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label for="local">Localização</label>
                        <input id="local" type="text" name="local" placeholder="Ex: Lisboa, Baixa" required>
                    </div>
                </div>

                <div style="display: flex; gap: 30px; flex-wrap: wrap;">

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label for="cozinha">Tipo de Cozinha</label>
                        <select id="cozinha" name="cozinha" required>
                            <option value="" disabled selected>Selecionar...</option>
                            <?php foreach ($tipos_cozinha as $tipo): ?><option value="<?php echo $tipo; ?>"><?php echo $tipo; ?></option><?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="flex: 1; min-width: 150px;">
                        <label for="preco">Preço Médio (€)</label>
                        <input id="preco" type="number" step="0.01" name="preco" placeholder="0.00" required>
                    </div>

                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" style="max-width: 250px;">Criar Restaurante</button>
                    <a href="dashboard.php" class="btn-secondary" style="max-width: 200px;">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>