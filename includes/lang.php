<?php
// =============================================
// EGOZ — Boutique Esotérica
// includes/lang.php — Sistema de idiomas
// =============================================

// Cambiar idioma si se pide
if (isset($_GET['lang']) && in_array($_GET['lang'], ['es', 'de'])) {
    setcookie('egoz_lang', $_GET['lang'], time() + (365 * 24 * 60 * 60), '/');
    $_SESSION['lang'] = $_GET['lang'];
    // Redirigir sin el parámetro lang en la URL
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    $params = $_GET;
    unset($params['lang']);
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    header('Location: ' . $url);
    exit;
}

// Detectar idioma actual
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = $_COOKIE['egoz_lang'] ?? 'es';
}

$lang = $_SESSION['lang'];

// Cargar traducciones
$lang_file = __DIR__ . '/../lang/' . $lang . '.php';
if (!file_exists($lang_file)) {
    $lang_file = __DIR__ . '/../lang/es.php';
    $lang = 'es';
}

$GLOBALS['t']    = require $lang_file;
$GLOBALS['lang'] = $lang;
$t    = $GLOBALS['t'];
$lang = $GLOBALS['lang'];

// Función helper para textos con variables
function __t($key, ...$args) {
    global $t;
    $text = $t[$key] ?? $key;
    if (!empty($args)) {
        return vsprintf($text, $args);
    }
    return $text;
}