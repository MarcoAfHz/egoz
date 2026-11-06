<?php
// =============================================
// EGOZ — Boutique Esotérica
// config.php — Configuración general
// =============================================

// ── BASE DE DATOS ─────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'egoz');
define('DB_USER', 'root');       // Cambiar en producción
define('DB_PASS', '');           // Cambiar en producción
define('DB_CHARSET', 'utf8mb4');

// ── STRIPE ────────────────────────────────
define('STRIPE_PUBLIC_KEY', '');
define('STRIPE_SECRET_KEY', '');
define('STRIPE_CURRENCY', 'chf');

// ── EMAIL ─────────────────────────────────
define('MAIL_HOST', 'smtp.hostinger.com');
define('MAIL_PORT', 587);
define('MAIL_USER', '');
define('MAIL_PASS', '');
define('MAIL_FROM', 'info@egoz.ch');
define('MAIL_NAME', 'EGOZ Boutique Esotérica');
// ── WEB ───────────────────────────────────
define('SITE_NAME', 'EGOZ Boutique Esotérica');
define('SITE_URL',  'http://egoz.test');  // Cambiar a https://egoz.ch en producción
define('SITE_LANG', 'es');

// ── URLs LIMPIAS ──────────────────────────
define('URL_INICIO',   SITE_URL . '/');
define('URL_TIENDA',   SITE_URL . '/tienda');
define('URL_CARRITO',  SITE_URL . '/carrito');
define('URL_CHECKOUT', SITE_URL . '/checkout');
define('URL_GRACIAS',  SITE_URL . '/gracias');
define('URL_SOBRE_MI', SITE_URL . '/sobre-mi');
define('URL_CONTACTO', SITE_URL . '/contacto');
define('URL_ADMIN',    SITE_URL . '/admin');

// ── SEGURIDAD ─────────────────────────────
define('RECAPTCHA_SITE_KEY',    '');
define('RECAPTCHA_SECRET_KEY',  '');
define('SESSION_NAME',          'egoz_session');
define('CSRF_TOKEN_NAME',       'egoz_csrf');

// ── CONEXIÓN BD ───────────────────────────
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    return $pdo;
}

// ── SESIÓN ────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── CSRF ──────────────────────────────────
function generateCSRF() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCSRF($token) {
    if (empty($_SESSION[CSRF_TOKEN_NAME]) || !hash_equals($_SESSION[CSRF_TOKEN_NAME], $token)) {
        die("Token CSRF inválido.");
    }
    return true;
}

// ── HELPERS ───────────────────────────────
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function isAdmin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect(URL_ADMIN . '/login');
    }
}

// ── URL PRODUCTO ──────────────────────────
function urlProducto($id, $slug = '') {
    if ($slug) {
        return SITE_URL . '/producto/' . $slug;
    }
    return SITE_URL . '/producto.php?id=' . (int)$id;
}

// ── NOTIFICACIONES ────────────────────────
function crearNotificacion($tipo, $titulo, $descripcion = '', $referencia_id = null) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO notificaciones (tipo, titulo, descripcion, referencia_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$tipo, $titulo, $descripcion, $referencia_id]);
}

function contarNotificaciones() {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) FROM notificaciones WHERE leida = 0");
    return $stmt->fetchColumn();
}

// ── DEVOLVER STOCK AL CANCELAR ────────────
function devolverStockPedido($pedido_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT producto_id, cantidad FROM pedido_items WHERE pedido_id = ?");
    $stmt->execute([$pedido_id]);
    $items = $stmt->fetchAll();
    foreach ($items as $item) {
        $db->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?")
           ->execute([$item['cantidad'], $item['producto_id']]);
    }
}


// ── DEEPL ─────────────────────────────────
define('DEEPL_API_KEY', '');

// ── IDIOMA ────────────────────────────────
require_once __DIR__ . '/includes/lang.php';