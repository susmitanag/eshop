<?php
// Database connection settings.
// These match the XAMPP defaults (user root, no password).
$DB_HOST = 'localhost';
$DB_NAME = 'eshop';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cart_count(): int {
    if (empty($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

const FREE_SHIPPING_FROM = 75.00;
const SHIPPING_FEE = 5.00;

function money(float $amount): string {
    return '$' . number_format($amount, 2);
}

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function build_query(array $overrides = []): string {
    $params = array_merge($_GET, $overrides);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return http_build_query($params);
}

function shipping_for(float $subtotal): float {
    return ($subtotal <= 0 || $subtotal >= FREE_SHIPPING_FROM) ? 0.0 : SHIPPING_FEE;
}

/* Cart lines with product details. Quantities are capped at the stock level. */
function cart_lines(PDO $pdo): array {
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return [];
    }
    $ids = array_map('intval', array_keys($cart));
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($marks)");
    $stmt->execute($ids);
    $lines = [];
    foreach ($stmt->fetchAll() as $p) {
        $qty = min((int) $cart[$p['id']], max(0, (int) $p['stock']));
        if ($qty < 1) {
            continue;
        }
        $lines[] = ['product' => $p, 'qty' => $qty, 'line_total' => (float) $p['price'] * $qty];
    }
    return $lines;
}

function cart_summary(array $lines): array {
    $subtotal = array_sum(array_column($lines, 'line_total'));
    $shipping = shipping_for((float) $subtotal);
    return [
        'subtotal' => (float) $subtotal,
        'shipping' => $shipping,
        'total'    => (float) $subtotal + $shipping,
        'count'    => array_sum(array_column($lines, 'qty')),
        'to_free'  => max(0.0, FREE_SHIPPING_FROM - (float) $subtotal),
    ];
}

/* ---------------- Auth helpers ---------------- */

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function is_admin(): bool {
    $user = current_user();
    return $user !== null && !empty($user['is_admin']);
}

function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

require_once __DIR__ . '/includes/art.php';
