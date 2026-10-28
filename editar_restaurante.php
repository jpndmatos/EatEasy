<?php
session_start();
require 'db.php';
/** @var PDO $pdo */

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] != 'admin') { header("Location: index.php"); exit; }

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: dashboard.php"); exit; }

$nome_user = $_SESSION['nome'];
$msg = ""; $tipo_msg = "";
$tipos_cozinha = ["Portuguesa", "Italiana", "Japonesa", "Americana", "Chinesa", "Indiana", "Mexicana", "Brasileira", "Vegetariana", "Mediterrânica", "Fast Food", "Marisco"];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novo_nome = $_POST['nome']; $novo_local = $_POST['local']; $novo_preco = $_POST['preco']; $nova_cozinha = $_POST['cozinha'];
    try {
        $pdo->beginTransaction();
        $stmtAntigo = $pdo->prepare("SELECT * FROM restaurantes WHERE id = :id"); $stmtAntigo->execute(['id' => $id]); $antigo = $stmtAntigo->fetch();
        $sqlHist = "INSERT INTO historico_restaurantes (restaurante_id, nome_antigo, preco_antigo) VALUES (:rid, :na, :pa)";
        $pdo->prepare($sqlHist)->execute(['rid' => $id, 'na' => $antigo['nome'], 'pa' => $antigo['preco_medio']]);
        $sqlUpd = "UPDATE restaurantes SET nome = :n, localizacao = :l, preco_medio = :p, cozinha = :c WHERE id = :id";
        $pdo->prepare($sqlUpd)->execute(['n' => $novo_nome, 'l' => $novo_local, 'p' => $novo_preco, 'c' => $nova_cozinha, 'id' => $id]);
        $pdo->commit();
        echo "<script>alert('Restaurante atualizado!'); window.location='dashboard.php';</script>"; exit;
    } catch (Exception $e) { $pdo->rollBack(); $msg = "Erro ao atualizar."; $tipo_msg = "aviso"; }
}

$stmt = $pdo->prepare("SELECT * FROM restaurantes WHERE id = :id"); $stmt->execute(['id' => $id]); $rest = $stmt->fetch();
if (!$rest) { echo "Restaurante não encontrado."; exit; }
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg">
    <script src="js/theme.js"></script>
    <title>Editar Restaurante</title>
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
            <a href="admin_restaurante.php">Novo Restaurante</a>
            <a href="logout.php" class="btn-logout">Terminar Sessão</a>
        </nav>
    </aside>

    <main class="main-content" style="display:flex; justify-content:center; align-items:start;">
        <div class="white-box" style="width: 100%; max-width: 600px; margin-top: 20px;">
            <div style="margin-bottom:30px;">
                <h2 style="margin:0;">Editar: <?php echo htmlspecialchars($rest['nome']); ?></h2>
            </div>
            <?php if($msg) echo "<div class='$tipo_msg'>$msg</div>"; ?>
            <form method="POST">
                <div class="form-group"><label for="nome">Nome do Restaurante</label>
                        <input id="nome" type="text" name="nome" value="<?php echo htmlspecialchars($rest['nome']); ?>" required></div>
                <div class="form-group"><label for="local">Localização</label>
                        <input id="local" type="text" name="local" value="<?php echo htmlspecialchars($rest['localizacao']); ?>" required></div>
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="cozinha">Tipo de Cozinha</label>
                        <select id="cozinha" name="cozinha" required>
                            <?php foreach ($tipos_cozinha as $tipo): ?>
                                <option value="<?php echo $tipo; ?>" <?php echo ($rest['cozinha'] == $tipo) ? 'selected' : ''; ?>><?php echo $tipo; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;"><label for="preco">Preço Médio (€)</label>
                        <input id="preco" type="number" step="0.01" name="preco" value="<?php echo htmlspecialchars($rest['preco_medio']); ?>" required></div>
                </div>
                <div class="form-actions">
                    <button type="submit">Guardar Alterações</button>
                    <a href="dashboard.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>