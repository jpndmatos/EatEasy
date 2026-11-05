<?php
session_start();
require 'db.php';
/** @var PDO $pdo */

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$tipo = $_SESSION['tipo'];
$nome = $_SESSION['nome'];
$pagina = $_GET['p'] ?? 'home';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg">
    <title>eatEasy</title>
    <link rel="stylesheet" href="css/style.css?v=20261028-theme-icons">
    <script src="js/script.js?v=20261028-theme-icons" defer></script>
    <link rel="stylesheet" href="css/dashboard.css?v=20261103-viewport">
</head>
<body class="dashboard-page">

<div class="app-layout">

    <aside class="sidebar">
        <div class="logo">eatEasy</div>
        <div class="user-info">
            <p>Olá, <strong><?php echo htmlspecialchars($nome); ?></strong></p>
            <small><?php echo ($tipo == 'admin') ? 'Administrador' : 'Cliente'; ?></small>
        </div>
        <nav class="menu-links">
            <a href="dashboard.php?p=home" class="<?php echo $pagina=='home'?'active':''; ?>">Início</a>
            <?php if ($tipo == 'cliente'): ?>
                <a href="dashboard.php?p=reservas" class="<?php echo $pagina=='reservas'?'active':''; ?>">Reservas</a>
            <?php endif; ?>
            <?php if ($tipo == 'admin'): ?>
                <a href="admin_reservas.php">Reservas</a>
                <a href="admin_restaurante.php">Adicionar Restaurante</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-logout">Terminar Sessão</a>
        </nav>
    </aside>

    <main class="main-content">
        <?php if (getenv('EATEASY_DEMO')): ?><p class="demo-notice">Demonstração · restaurantes e reservas fictícios.</p><?php endif; ?>

        <!-- Area cliente -->
        <?php if ($tipo == 'cliente'): ?>
            <?php if ($pagina == 'home'): ?>

                <div class="search-box">
                    <form method="GET" style="display:flex; width:100%; gap:10px; margin:0;">
                        <input type="hidden" name="p" value="home">
                        <input aria-label="Pesquisar restaurantes" type="search" name="busca" placeholder="Pesquisar restaurante..." value="<?php echo htmlspecialchars($_GET['busca'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit">Pesquisar</button>
                    </form>
                </div>
                <div class="filter-bar">
                    <a href="dashboard.php?p=home" class="tag-pill <?php echo (!isset($_GET['busca']) || $_GET['busca'] == '') ? 'active' : ''; ?>">
                        Todos
                    </a>
                    <?php
                    $sqlCats = "SELECT DISTINCT cozinha FROM restaurantes WHERE ativo = TRUE ORDER BY cozinha";
                    $stmtCats = $pdo->query($sqlCats);
                    $buscaAtual = $_GET['busca'] ?? '';
                    while ($cat = $stmtCats->fetch(PDO::FETCH_ASSOC)):
                        $isActive = ($buscaAtual == $cat['cozinha']) ? 'active' : '';
                        ?>
                        <a href="dashboard.php?p=home&busca=<?php echo urlencode($cat['cozinha']); ?>" class="tag-pill <?php echo $isActive; ?>">
                            <?php echo htmlspecialchars($cat['cozinha']); ?>
                        </a>
                    <?php endwhile; ?>
                </div>
                <h1>Restaurantes</h1><p class="editorial-note">Imagens de ambiente ilustrativas.</p>
                <div class="restaurant-grid" tabindex="0" role="region" aria-label="Lista de restaurantes">
                    <?php
                    $termo = isset($_GET['busca']) ? "%".$_GET['busca']."%" : "%";
                    $sql = "SELECT * FROM restaurantes WHERE ativo = TRUE AND (LOWER(nome) LIKE LOWER(:t) OR LOWER(cozinha) LIKE LOWER(:t) OR LOWER(localizacao) LIKE LOWER(:t)) ORDER BY nome, id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['t' => $termo]);
                    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($restaurants) > 0) {
                        foreach ($restaurants as $row) {
                            $imgUrl = restaurant_image((int)$row['id']);
                            echo '<div class="card">';
                            echo '  <div class="card-img"><img src="'.$imgUrl.'" alt="" loading="lazy" decoding="async" width="640" height="400"></div>';
                            echo '  <div class="card-info">';
                            echo '      <div style="margin-bottom:10px; width:100%;">';
                            echo '          <span class="card-pill">'.htmlspecialchars($row['cozinha']).'</span>';
                            echo '      </div>';
                            echo '      <h2 class="card-title">'.htmlspecialchars($row['nome']).'</h2>';
                            echo '      <small style="color:var(--text-muted); margin-bottom:15px; display:block;">'.htmlspecialchars($row['localizacao']).'</small>';
                            echo '      <div class="card-meta">';
                            echo '          <span>Preço Médio</span>';
                            echo '          <span class="price-badge">'.htmlspecialchars($row['preco_medio']).' €</span>';
                            echo '      </div>';
                            echo '      <a href="reservar.php?id='.$row['id'].'" class="btn-card">Reservar</a>';
                            echo '  </div>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>Nenhum restaurante encontrado.</p>";
                    }
                    ?>
                </div>

            <?php elseif ($pagina == 'reservas'): ?>
                <!-- Reservas cliente -->
                <h1>As Minhas Reservas</h1>
                <?php if(isset($_GET['msg']) && $_GET['msg']=='indisponivel') echo "<div class='aviso' role='alert'>Esta reserva já não pode ser cancelada.</div>";
                if(isset($_GET['msg']) && $_GET['msg']=='criado') echo "<div class='sucesso' role='status'>Reserva confirmada.</div>";
                if(isset($_GET['msg']) && $_GET['msg']=='cancelado') echo "<div class='sucesso' role='status'>Reserva cancelada com sucesso.</div>"; ?>
                <div class="white-box" tabindex="0" role="region" aria-label="Lista de reservas">
                    <?php
                    $sqlRes = "SELECT r.id, r.data_hora, r.num_pessoas, rest.nome, rest.localizacao FROM reservas r 
                        JOIN restaurantes rest ON r.restaurante_id = rest.id 
                        WHERE r.utilizador_id = :uid ORDER BY r.data_hora DESC";
                    $stmtRes = $pdo->prepare($sqlRes);
                    $stmtRes->execute(['uid' => $_SESSION['user_id']]);
                    $reservations = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
                    if (count($reservations) > 0): ?>
                        <table>
                            <thead><tr><th>Restaurante</th><th>Data e Hora</th><th>Pessoas</th><th>Estado</th><th>Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($reservations as $res):
                                $passou = strtotime($res['data_hora']) < time();
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($res['nome']); ?></strong><br><small style="color:var(--text-muted)"><?php echo htmlspecialchars($res['localizacao']); ?></small></td>
                                    <td><?php echo date('d/m/Y \à\s H:i', strtotime($res['data_hora'])); ?></td>
                                    <td><?php echo htmlspecialchars($res['num_pessoas']); ?> pax</td>
                                    <td><?php if($passou): ?><span class="badge-past">Concluída</span><?php else: ?><span class="badge-future">Confirmada</span><?php endif; ?></td>
                                    <td>
                                        <?php if(!$passou): ?>
                                            <form class="inline-action" method="POST" action="cancelar_reserva.php"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int)$res['id']; ?>"><button class="cancel-booking" type="submit">Cancelar</button></form>
                                        <?php else: ?> <span style="color:var(--text-muted);">-</span> <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:var(--text-muted); text-align:center;">Ainda não tem reservas efetuadas.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Area admin -->
        <?php elseif ($tipo == 'admin'): ?>
            <?php
            $messages = ['criado' => 'Restaurante criado.', 'atualizado' => 'Restaurante atualizado.', 'removido' => 'Restaurante removido da lista.'];
            if (isset($messages[$_GET['msg'] ?? ''])) echo '<p class="sucesso" role="status">' . $messages[$_GET['msg']] . '</p>';
            ?>
            <div class="restaurant-header" style="margin-top: 20px; margin-bottom: 30px;">
                <h1>Painel de Controlo</h1>
            </div>
            <div class="restaurant-grid" tabindex="0" role="region" aria-label="Lista de restaurantes">
                <?php
                $sqlRest = "SELECT * FROM restaurantes WHERE ativo = TRUE ORDER BY id DESC";
                $stmtRest = $pdo->query($sqlRest);
                while ($r = $stmtRest->fetch(PDO::FETCH_ASSOC)):
                    $imgUrl = restaurant_image((int)$r['id']);
                    ?>
                    <div class="card">
                        <div class="card-img" style="height: 140px;"><img src="<?php echo $imgUrl; ?>" alt="" loading="lazy" decoding="async" width="640" height="400"></div>
                        <div class="card-info">
                            <div style="margin-bottom:10px;">
                                <span class="card-pill"><?php echo htmlspecialchars($r['cozinha']); ?></span>
                            </div>
                            <h2 class="card-title"><?php echo htmlspecialchars($r['nome']); ?></h2>
                            <div class="card-meta">
                                <span>Preço Médio</span>
                                <span class="price-badge"><?php echo htmlspecialchars($r['preco_medio']); ?> €</span>
                            </div>
                            <div style="display:flex; gap: 10px; margin-top: 15px; width: 100%;">
                                <a href="editar_restaurante.php?id=<?php echo $r['id']; ?>" class="btn-card" style="flex:1;">Editar</a>
                                <form class="inline-action" method="POST" action="remover_restaurante.php"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>"><button type="submit" class="btn-card btn-remover">Remover</button></form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
