<?php
// =============================================
// EGOZ — Boutique Esotérica
// admin/configuracion.php
// =============================================
require_once __DIR__ . '/../config.php';
requireAdmin();

$db = getDB();

$exito = '';
$error = '';

// Guardar configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = ['telefono','email_contacto','instagram','whatsapp','sobre_mi_texto1','sobre_mi_texto2','horario','envios_info','hero_titulo','hero_subtitulo'];
    
    foreach ($campos as $campo) {
        $valor = sanitize($_POST[$campo] ?? '');
        $db->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?")->execute([$valor, $campo]);
    }
    $exito = 'Configuración guardada correctamente.';
}

// Obtener configuración actual
$stmt = $db->query("SELECT clave, valor FROM configuracion");
$config = [];
while ($row = $stmt->fetch()) {
    $config[$row['clave']] = $row['valor'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración — EGOZ Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <style>
        body{padding-top:0;} .admin-layout{display:flex; min-height:100vh;}
        .admin-offcanvas-link{display:flex;align-items:center;gap:12px;padding:14px 24px;color:var(--purple);text-decoration:none;font-size:0.95rem;border-left:3px solid transparent;transition:var(--transition);}
        .admin-offcanvas-link:hover{color:var(--gold);background:rgba(201,169,110,0.05);border-left-color:var(--gold);}
    </style>
</head>
<body>
<div class="admin-layout">

<!-- SIDEBAR DESKTOP -->
<aside class="admin-sidebar d-none d-lg-block">
    <div class="text-center px-4 py-4" style="border-bottom:1px solid var(--border);">
        <svg width="52" height="52" viewBox="0 0 64 64"><circle cx="32" cy="32" r="28" fill="#1a0a2e" stroke="#3a1f6e" stroke-width="1.5"/><circle cx="32" cy="32" r="23" fill="none" stroke="#c9a96e" stroke-width="0.6" stroke-dasharray="3,2"/><text x="32" y="28" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">EG</text><line x1="18" y1="32" x2="46" y2="32" stroke="#c9a96e" stroke-width="0.8"/><text x="32" y="44" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">OZ</text><circle cx="32" cy="32" r="2.5" fill="#c9a96e"/></svg>
        <div style="color:var(--gold); font-size:0.8rem; letter-spacing:2px; margin-top:8px;">PANEL ADMIN</div>
        <div style="color:var(--purple-muted); font-size:0.75rem; margin-top:2px;"><?= sanitize($_SESSION['admin_nombre']) ?></div>
    </div>
    <nav class="mt-3">
        <a href="dashboard.php" class="admin-nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="pedidos.php" class="admin-nav-link"><i class="bi bi-bag-check"></i> Pedidos</a>
        <a href="productos.php" class="admin-nav-link"><i class="bi bi-grid"></i> Productos</a>
        <a href="mensajes.php" class="admin-nav-link"><i class="bi bi-envelope"></i> Mensajes</a>
        <a href="resenas.php" class="admin-nav-link"><i class="bi bi-star"></i> Reseñas</a>
        <a href="categorias.php" class="admin-nav-link"><i class="bi bi-tags"></i> Categorías</a>
        <a href="configuracion.php" class="admin-nav-link active"><i class="bi bi-gear"></i> Configuración</a>
        <div style="border-top:1px solid var(--border); margin:16px 24px;"></div>
        <a href="<?= URL_INICIO ?>" target="_blank" class="admin-nav-link"><i class="bi bi-box-arrow-up-right"></i> Ver web</a>
        <a href="logout.php" class="admin-nav-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a>
    </nav>
</aside>

<!-- OFFCANVAS MÓVIL -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="adminMenu" style="background:var(--bg-secondary);border-right:1px solid var(--border);width:280px;z-index:1055;">
    <div class="offcanvas-header" style="border-bottom:1px solid var(--border);padding:16px 20px;">
        <div style="color:var(--gold);font-weight:700;letter-spacing:2px;font-size:0.9rem;">EGOZ ADMIN</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="mt-2">
            <a href="dashboard.php" class="admin-offcanvas-link"><i class="bi bi-speedometer2 fs-5"></i> Dashboard</a>
            <a href="pedidos.php" class="admin-offcanvas-link"><i class="bi bi-bag-check fs-5"></i> Pedidos</a>
            <a href="productos.php" class="admin-offcanvas-link"><i class="bi bi-grid fs-5"></i> Productos</a>
            <a href="mensajes.php" class="admin-offcanvas-link"><i class="bi bi-envelope fs-5"></i> Mensajes</a>
            <a href="resenas.php" class="admin-offcanvas-link"><i class="bi bi-star fs-5"></i> Reseñas</a>
            <a href="categorias.php" class="admin-offcanvas-link"><i class="bi bi-tags fs-5"></i> Categorías</a>
            <a href="configuracion.php" class="admin-offcanvas-link" style="color:var(--gold);border-left-color:var(--gold);"><i class="bi bi-gear fs-5"></i> Configuración</a>
            <div style="border-top:1px solid var(--border);margin:12px 20px;"></div>
            <a href="<?= URL_INICIO ?>" target="_blank" class="admin-offcanvas-link"><i class="bi bi-box-arrow-up-right fs-5"></i> Ver web</a>
            <a href="logout.php" class="admin-offcanvas-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left fs-5"></i> Cerrar sesión</a>
        </nav>
    </div>
</div>

<!-- CONTENIDO -->
<main class="admin-content" style="flex:1;">

    <div class="d-lg-none d-flex align-items-center justify-content-between p-3" style="background:var(--bg-secondary); border-bottom:1px solid var(--border);">
        <button class="border-0" style="background:none;color:var(--gold);font-size:1.3rem;cursor:pointer;" data-bs-toggle="offcanvas" data-bs-target="#adminMenu">
            <i class="bi bi-list"></i>
        </button>
        <span style="color:var(--gold); font-weight:600;">Configuración</span>
        <a href="dashboard.php" style="color:var(--purple);"><i class="bi bi-arrow-left fs-5"></i></a>
    </div>

    <div class="px-3 px-lg-0 py-3 py-lg-0">
        <h1 style="font-size:1.4rem; color:var(--gold-light); margin-bottom:24px;">Configuración de la web</h1>

        <?php if ($exito): ?>
        <div style="background:rgba(45,106,79,0.2); border:1px solid #2d6a4f; border-radius:var(--radius-sm); padding:12px 16px; margin-bottom:20px; color:#4ade80; font-size:0.85rem;">
            <i class="bi bi-check-circle me-2"></i><?= $exito ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-4">

                <!-- Columna izquierda -->
                <div class="col-lg-6">

                    <!-- Contacto -->
                    <div class="admin-card mb-4">
                        <h3 style="color:var(--gold-light); font-size:1rem; letter-spacing:1px; margin-bottom:20px;">✦ CONTACTO</h3>
                        <div class="mb-3">
                            <label class="form-label">TELÉFONO / WHATSAPP</label>
                            <input type="text" name="telefono" class="form-control" value="<?= sanitize($config['telefono'] ?? '') ?>" placeholder="+41 76 713 33 41">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">NÚMERO WHATSAPP (solo números)</label>
                            <input type="text" name="whatsapp" class="form-control" value="<?= sanitize($config['whatsapp'] ?? '') ?>" placeholder="41767133341">
                            <div style="color:var(--text-muted); font-size:0.75rem; margin-top:4px;">Sin + ni espacios. Ejemplo: 41767133341</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">EMAIL DE CONTACTO</label>
                            <input type="email" name="email_contacto" class="form-control" value="<?= sanitize($config['email_contacto'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">INSTAGRAM (URL completa)</label>
                            <input type="text" name="instagram" class="form-control" value="<?= sanitize($config['instagram'] ?? '') ?>" placeholder="https://www.instagram.com/egoz1984">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">HORARIO DE ATENCIÓN</label>
                            <input type="text" name="horario" class="form-control" value="<?= sanitize($config['horario'] ?? '') ?>" placeholder="Lunes a Viernes · 9:00 — 20:00">
                        </div>
                    </div>

                    <!-- Hero -->
                    <div class="admin-card">
                        <h3 style="color:var(--gold-light); font-size:1rem; letter-spacing:1px; margin-bottom:20px;">✦ PÁGINA PRINCIPAL</h3>
                        <div class="mb-3">
                            <label class="form-label">TÍTULO HERO</label>
                            <input type="text" name="hero_titulo" class="form-control" value="<?= sanitize($config['hero_titulo'] ?? '') ?>" placeholder="ENERGÍA · LUZ · TRANSFORMACIÓN">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">SUBTÍTULO HERO</label>
                            <textarea name="hero_subtitulo" class="form-control" rows="3"><?= sanitize($config['hero_subtitulo'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha -->
                <div class="col-lg-6">

                    <!-- Sobre mí -->
                    <div class="admin-card mb-4">
                        <h3 style="color:var(--gold-light); font-size:1rem; letter-spacing:1px; margin-bottom:20px;">✦ SOBRE MÍ</h3>
                        <div class="mb-3">
                            <label class="form-label">PRIMER PÁRRAFO</label>
                            <textarea name="sobre_mi_texto1" class="form-control" rows="5"><?= sanitize($config['sobre_mi_texto1'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">SEGUNDO PÁRRAFO</label>
                            <textarea name="sobre_mi_texto2" class="form-control" rows="5"><?= sanitize($config['sobre_mi_texto2'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Envíos -->
                    <div class="admin-card">
                        <h3 style="color:var(--gold-light); font-size:1rem; letter-spacing:1px; margin-bottom:20px;">✦ ENVÍOS</h3>
                        <div class="mb-0">
                            <label class="form-label">INFO DE ENVÍOS</label>
                            <input type="text" name="envios_info" class="form-control" value="<?= sanitize($config['envios_info'] ?? '') ?>" placeholder="Envíos a toda Suiza">
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" class="btn-gold" style="padding:14px 40px; font-size:1rem;">
                    <i class="bi bi-check-circle me-2"></i>Guardar cambios
                </button>
            </div>
        </form>
    </div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
