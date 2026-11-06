<?php
// =============================================
// EGOZ — Boutique Esotérica
// includes/seguridad.php
// =============================================

// ── HEADERS DE SEGURIDAD HTTP ────────────────
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// En producción activar esto:
// header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
// header("Content-Security-Policy: default-src 'self' https://js.stripe.com https://cdn.jsdelivr.net; script-src 'self' 'unsafe-inline' https://js.stripe.com https://cdn.jsdelivr.net https://www.google.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:;");

// ── RATE LIMITING ────────────────────────────
function checkRateLimit($accion, $max_intentos = 10, $ventana = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = 'rate_' . $accion . '_' . md5($ip);

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'time' => time()];
    }

    // Reset si pasó la ventana de tiempo
    if (time() - $_SESSION[$key]['time'] > $ventana) {
        $_SESSION[$key] = ['count' => 0, 'time' => time()];
    }

    $_SESSION[$key]['count']++;

    if ($_SESSION[$key]['count'] > $max_intentos) {
        http_response_code(429);
        die(json_encode(['error' => 'Demasiados intentos. Espera un momento.']));
    }

    return true;
}

// ── HONEYPOT ─────────────────────────────────
function checkHoneypot($field = 'website') {
    if (!empty($_POST[$field])) {
        // Es un bot — fallo silencioso
        http_response_code(200);
        exit;
    }
}

// ── VALIDAR RECAPTCHA ────────────────────────
function validateRecaptcha($token) {
    if (empty($token)) return false;

    $data = [
        'secret'   => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];

    $context  = stream_context_create($options);
    $result   = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $response = json_decode($result, true);

    return isset($response['success']) && $response['success'] === true && ($response['score'] ?? 0) >= 0.5;
}

// ── VALIDAR SOLO ADMIN ───────────────────────
function soloAdmin() {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

// ── VALIDAR IP BLOQUEADA ─────────────────────
function ipBloqueada() {
    $ips_bloqueadas = []; // Añadir IPs manualmente si necesario
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($ip, $ips_bloqueadas);
}

if (ipBloqueada()) {
    http_response_code(403);
    exit('Acceso denegado.');
}

// ── SUBIDA SEGURA DE IMÁGENES ────────────────
function subirImagen($file, $destino = 'uploads/productos/') {
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Error al subir el archivo.'];
    }

    if ($file['size'] > $max_size) {
        return ['error' => 'El archivo es demasiado grande. Máximo 5MB.'];
    }

    // Validar tipo real del archivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipo_real = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($tipo_real, $tipos_permitidos)) {
        return ['error' => 'Tipo de archivo no permitido. Solo JPG, PNG o WEBP.'];
    }

    // Nombre aleatorio para evitar sobreescrituras
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre = bin2hex(random_bytes(16)) . '.' . strtolower($extension);
    $ruta = $destino . $nombre;

    if (!move_uploaded_file($file['tmp_name'], $ruta)) {
        return ['error' => 'No se pudo guardar el archivo.'];
    }

    return ['success' => true, 'nombre' => $nombre, 'ruta' => $ruta];
}