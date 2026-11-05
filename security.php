<?php
// Shared by the server-rendered forms, including sign-in and registration.
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') . '">';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('A sessão do formulário expirou. Volte à página anterior, atualize e tente novamente.');
    }
}

function restaurant_image(int $id): string {
    // Bundled editorial photography; not a claimed photograph of the restaurant.
    return 'assets/restaurant-' . ((max(1, $id) - 1) % 3 + 1) . '.webp';
}
