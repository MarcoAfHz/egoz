<?php
// =============================================
// EGOZ — Boutique Esotérica
// admin/dashboard.php
// =============================================
require_once __DIR__ . '/../config.php';
requireAdmin();

$db = getDB();

$stmt = $db->query("SELECT COUNT(*) FROM pedidos WHERE estado != 'cancelado'");
$total_pedidos = $stmt->fetchColumn();

$stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado = 'pagado' OR estado = 'enviado' OR estado = 'entregado'");
$total_ventas = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM pedidos WHERE estado IN ('pagado', 'pendiente')");
$pedidos_nuevos = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM productos WHERE activo = 1");
$total_productos = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM contacto_mensajes WHERE leido = 0");
$mensajes_nuevos = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM resenas WHERE aprobada = 0 AND comentario != ''");
$resenas_nuevas = $stmt->fetchColumn();

$notificaciones = contarNotificaciones();

$stmt = $db->query("
    SELECT p.*, c.nombre as cliente_nombre, c.email as cliente_email
    FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id
    ORDER BY p.creado_en DESC LIMIT 5
");
$ultimos_pedidos = $stmt->fetchAll();

$stmt = $db->query("SELECT * FROM notificaciones ORDER BY creado_en DESC LIMIT 10");
$notifs = $stmt->fetchAll();

$db->query("UPDATE notificaciones SET leida = 1 WHERE leida = 0");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — EGOZ Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <style>
        body { padding-top: 0; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-offcanvas-link { display:flex; align-items:center; gap:12px; padding:14px 24px; color:var(--purple); text-decoration:none; font-size:0.95rem; border-left:3px solid transparent; transition:var(--transition); }
        .admin-offcanvas-link:hover, .admin-offcanvas-link.active { color:var(--gold); background:rgba(201,169,110,0.05); border-left-color:var(--gold); }
        .admin-offcanvas-link .notif-badge { margin-left: auto; }
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
        <a href="dashboard.php" class="admin-nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="pedidos.php" class="admin-nav-link">
            <i class="bi bi-bag-check"></i> Pedidos
            <?php if ($pedidos_nuevos > 0): ?><span class="notif-badge ms-auto badge-pedidos"><?= $pedidos_nuevos ?></span><?php endif; ?>
        </a>
        <a href="productos.php" class="admin-nav-link"><i class="bi bi-grid"></i> Productos</a>
        <a href="mensajes.php" class="admin-nav-link">
            <i class="bi bi-envelope"></i> Mensajes
            <?php if ($mensajes_nuevos > 0): ?><span class="notif-badge ms-auto badge-mensajes"><?= $mensajes_nuevos ?></span><?php endif; ?>
        </a>
        <a href="resenas.php" class="admin-nav-link">
            <i class="bi bi-star"></i> Reseñas
            <?php if ($resenas_nuevas > 0): ?><span class="notif-badge ms-auto badge-resenas"><?= $resenas_nuevas ?></span><?php endif; ?>
        </a>
        <a href="categorias.php" class="admin-nav-link"><i class="bi bi-tags"></i> Categorías</a>
        <div style="border-top:1px solid var(--border); margin:16px 24px;"></div>
        <a href="<?= URL_INICIO ?>" target="_blank" class="admin-nav-link"><i class="bi bi-box-arrow-up-right"></i> Ver web</a>
        <a href="logout.php" class="admin-nav-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a>
    </nav>
</aside>

<!-- OFFCANVAS MÓVIL -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="adminMenu"
     style="background:var(--bg-secondary); border-right:1px solid var(--border); width:280px; z-index:1055;">
    <div class="offcanvas-header" style="border-bottom:1px solid var(--border); padding:16px 20px;">
        <div class="d-flex align-items-center gap-3">
            <svg width="40" height="40" viewBox="0 0 64 64"><circle cx="32" cy="32" r="28" fill="#1a0a2e" stroke="#3a1f6e" stroke-width="1.5"/><circle cx="32" cy="32" r="23" fill="none" stroke="#c9a96e" stroke-width="0.6" stroke-dasharray="3,2"/><text x="32" y="28" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">EG</text><line x1="18" y1="32" x2="46" y2="32" stroke="#c9a96e" stroke-width="0.8"/><text x="32" y="44" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">OZ</text><circle cx="32" cy="32" r="2.5" fill="#c9a96e"/></svg>
            <div>
                <div style="color:var(--gold); font-size:0.85rem; font-weight:700; letter-spacing:2px;">EGOZ ADMIN</div>
                <div style="color:var(--purple-muted); font-size:0.72rem;"><?= sanitize($_SESSION['admin_nombre']) ?></div>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="mt-2">
            <a href="dashboard.php" class="admin-offcanvas-link active"><i class="bi bi-speedometer2 fs-5"></i> Dashboard</a>
            <a href="pedidos.php" class="admin-offcanvas-link">
                <i class="bi bi-bag-check fs-5"></i> Pedidos
                <?php if ($pedidos_nuevos > 0): ?><span class="notif-badge badge-pedidos"><?= $pedidos_nuevos ?></span><?php endif; ?>
            </a>
            <a href="productos.php" class="admin-offcanvas-link"><i class="bi bi-grid fs-5"></i> Productos</a>
            <a href="mensajes.php" class="admin-offcanvas-link">
                <i class="bi bi-envelope fs-5"></i> Mensajes
                <?php if ($mensajes_nuevos > 0): ?><span class="notif-badge badge-mensajes"><?= $mensajes_nuevos ?></span><?php endif; ?>
            </a>
            <a href="resenas.php" class="admin-offcanvas-link">
                <i class="bi bi-star fs-5"></i> Reseñas
                <?php if ($resenas_nuevas > 0): ?><span class="notif-badge badge-resenas"><?= $resenas_nuevas ?></span><?php endif; ?>
            </a>
            <a href="categorias.php" class="admin-offcanvas-link"><i class="bi bi-tags fs-5"></i> Categorías</a>
            <div style="border-top:1px solid var(--border); margin:12px 20px;"></div>
            <a href="<?= URL_INICIO ?>" target="_blank" class="admin-offcanvas-link"><i class="bi bi-box-arrow-up-right fs-5"></i> Ver web</a>
            <a href="logout.php" class="admin-offcanvas-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left fs-5"></i> Cerrar sesión</a>
        </nav>
    </div>
</div>

<!-- CONTENIDO -->
<main class="admin-content" style="flex:1;">

    <div class="d-lg-none d-flex align-items-center justify-content-between p-3 mb-3"
         style="background:var(--bg-secondary); border-bottom:1px solid var(--border);">
        <button class="border-0" style="background:none; color:var(--gold); font-size:1.3rem; cursor:pointer;"
                data-bs-toggle="offcanvas" data-bs-target="#adminMenu">
            <i class="bi bi-list"></i>
        </button>
        <span style="color:var(--gold); font-weight:600; font-size:0.95rem;">Dashboard</span>
        <span style="width:24px;"></span>
    </div>

    <div class="px-3 px-lg-0">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 style="font-size:1.4rem; color:var(--gold-light); margin-bottom:4px;" class="d-none d-lg-block">Dashboard</h1>
                <p style="color:var(--purple); font-size:0.85rem; margin:0;" id="fecha-actual"><?= date('d/m/Y H:i') ?></p>
            </div>
            <?php if ($notificaciones > 0): ?>
            <div id="badge-notif" style="background:var(--gold); color:var(--bg-primary); padding:6px 14px; border-radius:20px; font-size:0.85rem; font-weight:600;">
                <i class="bi bi-bell-fill me-1"></i><span id="count-notif"><?= $notificaciones ?></span> nuevas
            </div>
            <?php else: ?>
            <div id="badge-notif" style="display:none; background:var(--gold); color:var(--bg-primary); padding:6px 14px; border-radius:20px; font-size:0.85rem; font-weight:600;">
                <i class="bi bi-bell-fill me-1"></i><span id="count-notif">0</span> nuevas
            </div>
            <?php endif; ?>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <i class="bi bi-bag-check" style="font-size:1.5rem; color:var(--gold); display:block; margin-bottom:8px;"></i>
                    <div class="stat-number" id="stat-pedidos"><?= $total_pedidos ?></div>
                    <div class="stat-label">Pedidos</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <i class="bi bi-currency-exchange" style="font-size:1.5rem; color:var(--gold); display:block; margin-bottom:8px;"></i>
                    <div class="stat-number"><?= number_format($total_ventas, 0) ?></div>
                    <div class="stat-label">CHF Vendidos</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <i class="bi bi-grid" style="font-size:1.5rem; color:var(--gold); display:block; margin-bottom:8px;"></i>
                    <div class="stat-number"><?= $total_productos ?></div>
                    <div class="stat-label">Productos</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <i class="bi bi-envelope" style="font-size:1.5rem; color:var(--gold); display:block; margin-bottom:8px;"></i>
                    <div class="stat-number" id="stat-mensajes"><?= $mensajes_nuevos ?></div>
                    <div class="stat-label">Mensajes nuevos</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="admin-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 style="color:var(--gold-light); font-size:1rem; letter-spacing:1px; margin:0;">✦ ÚLTIMOS PEDIDOS</h3>
                        <a href="pedidos.php" style="color:var(--purple); font-size:0.8rem;">Ver todos →</a>
                    </div>
                    <?php if (empty($ultimos_pedidos)): ?>
                    <p style="color:var(--text-muted); font-size:0.9rem; text-align:center; padding:20px 0;">Aún no hay pedidos</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-egoz mb-0">
                            <thead><tr><th>#</th><th>Cliente</th><th>Total</th><th>Estado</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ($ultimos_pedidos as $p): ?>
                                <tr>
                                    <td style="color:var(--gold);">#<?= $p['id'] ?></td>
                                    <td>
                                        <div style="color:var(--gold-light); font-size:0.85rem;"><?= sanitize($p['cliente_nombre'] ?? 'Cliente') ?></div>
                                        <div style="color:var(--text-muted); font-size:0.75rem;"><?= sanitize($p['cliente_email'] ?? '') ?></div>
                                    </td>
                                    <td style="color:var(--gold); font-weight:600;"><?= number_format($p['total'], 2) ?> CHF</td>
                                    <td><span class="badge badge-<?= $p['estado'] ?>" style="padding:4px 10px; border-radius:10px; font-size:0.75rem;"><?= ucfirst($p['estado']) ?></span></td>
                                    <td><a href="pedido_detalle.php?id=<?= $p['id'] ?>" style="color:var(--purple); font-size:0.8rem;">Ver →</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="admin-card">
                    <h3 style="color:var(--gold-light); font-size:1rem; letter-spacing:1px; margin-bottom:16px;">✦ NOTIFICACIONES</h3>
                    <?php if (empty($notifs)): ?>
                    <p style="color:var(--text-muted); font-size:0.9rem; text-align:center; padding:20px 0;">No hay notificaciones</p>
                    <?php else: ?>
                    <div style="max-height:300px; overflow-y:auto;" id="lista-notifs">
                        <?php foreach ($notifs as $n): ?>
                        <div style="padding:10px 0; border-bottom:1px solid var(--border);">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi <?= $n['tipo'] === 'pedido' ? 'bi-bag-check' : ($n['tipo'] === 'resena' ? 'bi-star' : 'bi-envelope') ?>" style="color:var(--gold); font-size:0.9rem; margin-top:2px;"></i>
                                <div>
                                    <div style="color:var(--gold-light); font-size:0.85rem; font-weight:500;"><?= sanitize($n['titulo']) ?></div>
                                    <?php if ($n['descripcion']): ?>
                                    <div style="color:var(--purple); font-size:0.78rem;"><?= sanitize($n['descripcion']) ?></div>
                                    <?php endif; ?>
                                    <div style="color:var(--text-muted); font-size:0.72rem; margin-top:2px;"><?= date('d/m/Y H:i', strtotime($n['creado_en'])) ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="admin-card mt-3">
                    <h3 style="color:var(--gold-light); font-size:1rem; letter-spacing:1px; margin-bottom:14px;">✦ ACCIONES RÁPIDAS</h3>
                    <a href="producto_nuevo.php" class="btn-gold d-block text-center mb-2" style="font-size:0.85rem; padding:10px;">
                        <i class="bi bi-plus-circle me-2"></i>Añadir producto
                    </a>
                    <a href="pedidos.php" class="btn-outline-gold d-block text-center mb-2" style="font-size:0.85rem; padding:10px;">
                        <i class="bi bi-bag-check me-2"></i>Ver pedidos
                    </a>
                    <a href="resenas.php" class="btn-outline-gold d-block text-center" style="font-size:0.85rem; padding:10px;">
                        <i class="bi bi-star me-2"></i>Ver reseñas
                        <?php if ($resenas_nuevas > 0): ?>
                        <span style="background:var(--gold);color:var(--bg-primary);border-radius:10px;padding:1px 7px;font-size:0.75rem;margin-left:4px;"><?= $resenas_nuevas ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>
</div>

<audio id="notif-sound" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2ozHTJ3rN7SoF4uHB1Tn9bWn1ouFyFXmtnZl1EqEipandfamVEoEy1hnt3anVMoFS9joN/an1MqFzBmoeDbn1QrGDJqpuLbn1QsGjNvqOTbn1QsGzVzquXbn1UsHDd3rObbn1YtHTp7runbn1YtHj2AruvbnlgvHz+EsO3bnlgwIEKItO/bnlkxIkWMuPHbgFozJEiRvfPbgFs0JkuVwfXbgFw1KE6ZxffbgF02Kk+dyPnbgF83K1KhzPvbgGE5LVSmz/3bgGI6L1eryP/bgGM7MFivzAHcgGQ8Mluz0APcgGU9M12v1AXcgGY+NV+z2AfcgGc/N2K32gncgGhBOWS83Qvcgmo=" type="audio/wav">
</audio>

<script>
// Guardamos los contadores actuales para detectar cambios
let prevPedidos  = <?= $pedidos_nuevos ?>;
let prevMensajes = <?= $mensajes_nuevos ?>;
let prevResenas  = <?= $resenas_nuevas ?>;
let prevNotifs   = <?= $notificaciones ?>;

function checkNotificaciones() {
    fetch('<?= SITE_URL ?>/admin/api_notificaciones.php')
        .then(r => r.json())
        .then(data => {
            const hayNuevo = data.pedidos > prevPedidos ||
                             data.mensajes > prevMensajes ||
                             data.resenas > prevResenas;

            // Sonar si hay algo nuevo
            if (hayNuevo) {
                const audio = document.getElementById('notif-sound');
                if (audio) audio.play().catch(() => {});
            }

            // Actualizar badges pedidos
            document.querySelectorAll('.badge-pedidos').forEach(b => {
                b.textContent = data.pedidos;
                b.style.display = data.pedidos > 0 ? 'inline-flex' : 'none';
            });

            // Actualizar badges mensajes
            document.querySelectorAll('.badge-mensajes').forEach(b => {
                b.textContent = data.mensajes;
                b.style.display = data.mensajes > 0 ? 'inline-flex' : 'none';
            });

            // Actualizar badges reseñas
            document.querySelectorAll('.badge-resenas').forEach(b => {
                b.textContent = data.resenas;
                b.style.display = data.resenas > 0 ? 'inline-flex' : 'none';
            });

            // Actualizar stats tarjetas
            const statPedidos = document.getElementById('stat-pedidos');
            if (statPedidos) statPedidos.textContent = data.pedidos;

            const statMensajes = document.getElementById('stat-mensajes');
            if (statMensajes) statMensajes.textContent = data.mensajes;

            // Actualizar badge notificaciones general
            const badgeNotif = document.getElementById('badge-notif');
            const countNotif = document.getElementById('count-notif');
            if (badgeNotif && countNotif) {
                countNotif.textContent = data.nuevas;
                badgeNotif.style.display = data.nuevas > 0 ? 'block' : 'none';
            }

            // Si hay cambios recargar para actualizar tabla pedidos y notificaciones
            if (hayNuevo) {
                location.reload();
            }

            prevPedidos  = data.pedidos;
            prevMensajes = data.mensajes;
            prevResenas  = data.resenas;
            prevNotifs   = data.nuevas;
        }).catch(() => {});
}

// Comprobar cada 15 segundos
setInterval(checkNotificaciones, 15000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>