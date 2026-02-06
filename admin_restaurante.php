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
    <title>Novo Restaurante</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>

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
                        <label>Nome do Restaurante</label>
                        <input type="text" name="nome" placeholder="Ex: O Tasco do Zé" required>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Localização</label>
                        <input type="text" name="local" placeholder="Ex: Lisboa, Baixa" required>
                    </div>
                </div>

                <div style="display: flex; gap: 30px; flex-wrap: wrap;">

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Tipo de Cozinha</label>
                        <select name="cozinha" required>
                            <option value="" disabled selected>Selecionar...</option>
                            <?php foreach ($tipos_cozinha as $tipo): ?><option value="<?php echo $tipo; ?>"><?php echo $tipo; ?></option><?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="flex: 1; min-width: 150px;">
                        <label>Preço Médio (€)</label>
                        <input type="number" step="0.01" name="preco" placeholder="0.00" required>
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