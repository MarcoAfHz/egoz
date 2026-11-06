<?php
// =============================================
// EGOZ — Boutique Esotérica
// admin/login.php
// =============================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/seguridad.php';

// Si ya está logueado redirigir al dashboard
if (isAdmin()) { redirect(URL_ADMIN . '/dashboard.php'); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting
    checkRateLimit('admin_login', 5, 300); // 5 intentos cada 5 minutos

    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor introduce email y contraseña.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM admin_usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Login correcto
            session_regenerate_id(true);
            $_SESSION['admin_id']     = $admin['id'];
            $_SESSION['admin_nombre'] = $admin['nombre'];
            $_SESSION['admin_email']  = $admin['email'];
            redirect(URL_ADMIN . '/dashboard.php');
        } else {
            $error = 'Email o contraseña incorrectos.';
            // Pequeño delay para evitar fuerza bruta
            sleep(1);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — EGOZ Boutique Esotérica</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
        .login-box { background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius-lg); padding:40px; width:100%; max-width:420px; }
    </style>
</head>
<body>

<div class="login-box">
    <!-- Logo -->
    <div class="text-center mb-4">
        <svg width="64" height="64" viewBox="0 0 64 64" role="img" aria-label="Logo EGOZ">
            <circle cx="32" cy="32" r="28" fill="#1a0a2e" stroke="#3a1f6e" stroke-width="1.5"/>
            <circle cx="32" cy="32" r="23" fill="none" stroke="#c9a96e" stroke-width="0.6" stroke-dasharray="3,2"/>
            <text x="32" y="28" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">EG</text>
            <line x1="18" y1="32" x2="46" y2="32" stroke="#c9a96e" stroke-width="0.8"/>
            <text x="32" y="44" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">OZ</text>
            <circle cx="32" cy="32" r="2.5" fill="#c9a96e"/>
        </svg>
        <h1 style="color:var(--gold-light); font-size:1.2rem; letter-spacing:2px; margin-top:12px;">PANEL ADMIN</h1>
        <p style="color:var(--purple); font-size:0.85rem;">EGOZ Boutique Esotérica</p>
    </div>

    <?php if ($error): ?>
    <div style="background:rgba(248,113,113,0.1); border:1px solid #6b2737; border-radius:var(--radius-sm); padding:12px 16px; margin-bottom:20px; color:#f87171; font-size:0.85rem;">
        <i class="bi bi-exclamation-triangle me-2"></i><?= sanitize($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">EMAIL</label>
            <input type="email" name="email" class="form-control"
                   value="<?= sanitize($_POST['email'] ?? '') ?>"
                   placeholder="pedidos@egoz.ch" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label">CONTRASEÑA</label>
            <div class="position-relative">
                <input type="password" name="password" id="password" class="form-control"
                       placeholder="••••••••" required>
                <button type="button" onclick="togglePass()"
                        style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--purple); cursor:pointer;">
                    <i class="bi bi-eye" id="eye-icon"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn-gold" style="width:100%; padding:12px;">
            <i class="bi bi-box-arrow-in-right me-2"></i>Entrar al panel
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="<?= URL_INICIO ?>" style="color:var(--purple-muted); font-size:0.8rem;">
            <i class="bi bi-arrow-left me-1"></i>Volver a la web
        </a>
    </div>
</div>

<script>
function togglePass() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type    = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type    = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

</body>
</html>