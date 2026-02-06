<?php
session_start();
require 'db.php';
/** @var PDO $pdo */

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] != 'cliente') { header("Location: index.php"); exit; }

$nome = $_SESSION['nome'];
$rest_id = $_GET['id'] ?? null;
$msg = ""; $tipo_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rest_id = $_POST['restaurante_id']; $dia = $_POST['dia']; $hora_sel = $_POST['hora'];

    $pessoas = $_POST['pessoas'];

    $data_final = $dia . ' ' . $hora_sel . ':00';
    $ts = strtotime($data_final);

    if ($ts < time()) { $msg = "Data passada."; $tipo_msg = "aviso"; }
    else {
        $h = (int)substr($hora_sel, 0, 2); $is_almoco = ($h < 16);
        $sqlCheck = "SELECT data_hora FROM reservas WHERE utilizador_id = :uid AND data_hora::date = :dia";
        $stmtCheck = $pdo->prepare($sqlCheck); $stmtCheck->execute(['uid' => $_SESSION['user_id'], 'dia' => $dia]);
        $conflito = false;
        while ($res = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
            $h_ex = (int)date('H', strtotime($res['data_hora']));
            if (($is_almoco && $h_ex < 16) || (!$is_almoco && $h_ex >= 16)) { $conflito = true; break; }
        }
        if ($conflito) { $msg = "Já tem reserva para esta refeição."; $tipo_msg = "aviso"; }
        else {
            $pdo->prepare("INSERT INTO reservas (utilizador_id, restaurante_id, data_hora, num_pessoas) VALUES (?,?,?,?)")->execute([$_SESSION['user_id'], $rest_id, $data_final, $pessoas]);
            header("Location: dashboard.php?p=reservas"); exit;
        }
    }
}

$rest = $pdo->query("SELECT * FROM restaurantes WHERE id=".(int)$rest_id)->fetch();
if (!$rest) { header("Location: dashboard.php"); exit; }

$slots = []; for ($h=12; $h<=14; $h++) { $slots[] = sprintf("%02d:00", $h); $slots[] = sprintf("%02d:30", $h); }
for ($h=19; $h<=22; $h++) { $slots[] = sprintf("%02d:00", $h); $slots[] = sprintf("%02d:30", $h); }
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <title>Reservar</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        <div class="logo">eatEasy</div>
        <div class="user-info"><p>Olá, <strong><?php echo htmlspecialchars($nome); ?></strong></p><small>Cliente Gastronómico</small></div>
        <nav class="menu-links">
            <a href="dashboard.php?p=home">Início</a>
            <a href="dashboard.php?p=reservas">Minhas Reservas</a>
            <a href="logout.php" class="btn-logout">Terminar Sessão</a>
        </nav>
    </aside>

    <main class="main-content">
        <?php
        $keywords = "restaurant,interior";
        $mapa = [
                'Portuguesa'    => 'portuguese food table',
                'Italiana'      => 'italian restaurant interior',
                'Japonesa'      => 'sushi bar',
                'Americana'     => 'burger bar',
                'Chinesa'       => 'chinese restaurant',
                'Indiana'       => 'indian food table',
                'Mexicana'      => 'mexican food table',
                'Brasileira'    => 'brazilian bbq',
                'Vegetariana'   => 'fresh salad vegetables',
                'Mediterrânica' => 'mediterranean meal',
                'Fast Food'     => 'burger joint',
                'Marisco'       => 'seafood platter'
        ];
        if (isset($mapa[$rest['cozinha']])) {
            $keywords = $mapa[$rest['cozinha']];
        }

        $imgUrl = "https://loremflickr.com/1000/400/" . urlencode($keywords) . "?lock=" . $rest['id'];
        ?>
        <img src="<?php echo $imgUrl; ?>" class="restaurant-banner" alt="Banner">

        <div class="restaurant-header">
            <h1><?php echo htmlspecialchars($rest['nome']); ?></h1>
            <div class="restaurant-tags">
                <span class="tag-item highlight">🍽️ <?php echo htmlspecialchars($rest['cozinha']); ?></span>
                <span class="tag-item">📍 <?php echo htmlspecialchars($rest['localizacao']); ?></span>
                <span class="tag-item">💶 Preço Médio: <?php echo htmlspecialchars($rest['preco_medio']); ?> €</span>
            </div>
            <div class="info-hours"><strong>🕘 Horários:</strong> Almoço: 12:00-15:00 | Jantar: 19:00-23:00</div>
        </div>

        <div class="reservation-section">
            <h3>Fazer uma Reserva</h3>
            <?php if($msg) echo "<div class='$tipo_msg'>$msg</div>"; ?>
            <form method="POST">
                <input type="hidden" name="restaurante_id" value="<?php echo $rest_id; ?>">
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Dia</label>
                        <input type="date" name="dia" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Hora</label>
                        <select name="hora" required>
                            <option value="" disabled selected>Selecionar Hora</option>
                            <optgroup label="Almoço"><?php foreach($slots as $s) if($s<"18:00") echo "<option value='$s'>$s</option>"; ?></optgroup>
                            <optgroup label="Jantar"><?php foreach($slots as $s) if($s>"18:00") echo "<option value='$s'>$s</option>"; ?></optgroup>
                        </select>
                    </div>

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Pessoas</label>
                        <select id="select_pessoas" onchange="togglePessoas(this)">
                            <option value="1">1 Pessoa</option>
                            <option value="2" selected>2 Pessoas</option>
                            <option value="3">3 Pessoas</option>
                            <option value="4">4 Pessoas</option>
                            <option value="5">5 Pessoas</option>
                            <option value="6">6 Pessoas</option>
                            <option value="custom" style="font-weight:bold; color:var(--primary-color);">Outro (até 20)...</option>
                        </select>
                        <input
                                type="number"
                                name="pessoas"
                                id="input_pessoas"
                                min="1"
                                max="20"
                                value="2"
                                style="display:none;"
                                placeholder="Insira o nº de pessoas"
                                oninvalid="this.setCustomValidity('Por favor, selecione um valor entre 1 e 20.')"
                                oninput="this.setCustomValidity('')"
                        >
                    </div>
                </div>
                <div style="display: flex; gap: 15px; align-items: center; margin-top: 10px;">
                    <button type="submit" style="min-width: 200px;">Confirmar Reserva</button>
                    <a href="dashboard.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>