<?php
session_start();
require 'db.php';
/** @var PDO $pdo */

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] != 'admin') { header("Location: index.php"); exit; }

$nome_user = $_SESSION['nome'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg">
    <script src="js/theme.js"></script>
    <title>Gerir Reservas</title>
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
            <a href="admin_reservas.php" class="active">Todas as Reservas</a>
            <a href="admin_restaurante.php">Novo Restaurante</a>
            <a href="logout.php" class="btn-logout">Terminar Sessão</a>
        </nav>
    </aside>

    <main class="main-content">

        <div class="restaurant-header" style="margin-top: 20px; margin-bottom: 30px;">
            <h1>Gestão de Reservas</h1>
        </div>

        <div class="white-box">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                <tr>
                    <th style="width: 25%;">Restaurante</th>
                    <th style="width: 25%;">Cliente</th>
                    <th style="width: 20%;">Data e Hora</th>
                    <th style="width: 15%;">Pessoas</th>
                    <th style="width: 15%;">Estado</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sqlAdmin = "SELECT r.data_hora, r.num_pessoas, u.nome as cliente, rest.nome as restaurante 
                              FROM reservas r 
                              JOIN utilizadores u ON r.utilizador_id = u.id 
                              JOIN restaurantes rest ON r.restaurante_id = rest.id 
                              ORDER BY r.data_hora DESC";
                $stmtAdmin = $pdo->query($sqlAdmin);

                $adminReservations = $stmtAdmin->fetchAll(PDO::FETCH_ASSOC);
                if (count($adminReservations) > 0):
                    foreach ($adminReservations as $adm):
                        $passou = strtotime($adm['data_hora']) < time();
                        ?>
                        <tr>
                            <td style="font-weight: bold; color: var(--primary-color);">
                                <?php echo htmlspecialchars($adm['restaurante']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($adm['cliente']); ?>
                            </td>
                            <td>
                                <?php echo date('d/m/Y - H:i', strtotime($adm['data_hora'])); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($adm['num_pessoas']); ?> pax
                            </td>
                            <td>
                                <?php if($passou): ?>
                                    <span class="badge-past">Concluída</span>
                                <?php else: ?>
                                    <span class="badge-future">Agendada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr><td colspan="5" style="text-align:center; padding: 30px; color:var(--text-muted);">Não existem reservas registadas.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>