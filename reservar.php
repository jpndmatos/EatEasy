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

    $validSlots = array_merge(['12:00','12:30','13:00','13:30','14:00','14:30'], ['19:00','19:30','20:00','20:30','21:00','21:30','22:00','22:30']);
    $validDate = DateTime::createFromFormat('!Y-m-d', $dia);
    if (!$validDate || $validDate->format('Y-m-d') !== $dia || !in_array($hora_sel, $validSlots, true) || !filter_var($pessoas, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]])) {
        $msg = "Escolha um dia, horário e número de pessoas válidos."; $tipo_msg = "aviso";
    }
    elseif ($ts < time()) { $msg = "Data passada."; $tipo_msg = "aviso"; }
    else {
        $h = (int)substr($hora_sel, 0, 2); $is_almoco = ($h < 16);
        $sqlCheck = "SELECT data_hora FROM reservas WHERE utilizador_id = :uid AND DATE(data_hora) = :dia";
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

$rest = $pdo->query("SELECT * FROM restaurantes WHERE ativo = TRUE AND id=".(int)$rest_id)->fetch();
if (!$rest) { header("Location: dashboard.php"); exit; }

$slots = []; for ($h=12; $h<=14; $h++) { $slots[] = sprintf("%02d:00", $h); $slots[] = sprintf("%02d:30", $h); }
for ($h=19; $h<=22; $h++) { $slots[] = sprintf("%02d:00", $h); $slots[] = sprintf("%02d:30", $h); }
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="assets/icon.svg">
    <script src="js/theme.js"></script>
    <title>Reservar</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
<button type="button" class="theme-toggle" aria-label="Alterar tema" aria-pressed="false"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/></svg></button>

<div class="app-layout">
    <aside class="sidebar">
        <div class="logo">eatEasy</div>
        <div class="user-info"><p>Olá, <strong><?php echo htmlspecialchars($nome); ?></strong></p><small>Cliente</small></div>
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
                            if (getenv("EATEASY_DEMO")) { $imgUrl = "assets/restaurant-" . (((int)($row["id"] ?? $r["id"] ?? $rest["id"] ?? 1) - 1) % 3 + 1) . ".jpg"; }
        ?>
        <img src="<?php echo $imgUrl; ?>" class="restaurant-banner" alt="Banner">

        <div class="restaurant-header">
            <h1><?php echo htmlspecialchars($rest['nome']); ?></h1>
            <div class="restaurant-tags">
                <span class="tag-item highlight"><?php echo htmlspecialchars($rest['cozinha']); ?></span>
                <span class="tag-item"><?php echo htmlspecialchars($rest['localizacao']); ?></span>
                <span class="tag-item">Preço Médio: <?php echo htmlspecialchars($rest['preco_medio']); ?> €</span>
            </div>
            <div class="info-hours"><strong>Horários:</strong> Almoço: 12:00-15:00 | Jantar: 19:00-23:00</div>
        </div>

        <div class="reservation-section">
            <h3>Fazer uma Reserva</h3>
            <?php if($msg) echo "<div class='$tipo_msg'>$msg</div>"; ?>
            <form method="POST">
                <input type="hidden" name="restaurante_id" value="<?php echo $rest_id; ?>">
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label for="dia">Dia</label>
                        <input id="dia" type="date" name="dia" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label for="hora">Hora</label>
                        <select id="hora" name="hora" required>
                            <option value="" disabled selected>Selecionar Hora</option>
                            <optgroup label="Almoço"><?php foreach($slots as $s) if($s<"18:00") echo "<option value='$s'>$s</option>"; ?></optgroup>
                            <optgroup label="Jantar"><?php foreach($slots as $s) if($s>"18:00") echo "<option value='$s'>$s</option>"; ?></optgroup>
                        </select>
                    </div>

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label for="select_pessoas">Pessoas</label>
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
                                aria-label="Número de pessoas"
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