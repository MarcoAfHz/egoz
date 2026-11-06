<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aprobar_id'])) {
    $db->prepare("UPDATE resenas SET aprobada = 1 WHERE id = ?")->execute([(int)$_POST['aprobar_id']]);
    header('Location: resenas.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $db->prepare("DELETE FROM resenas WHERE id = ?")->execute([(int)$_POST['eliminar_id']]);
    header('Location: resenas.php'); exit;
}

$filtro = isset($_GET['filtro']) ? sanitize($_GET['filtro']) : 'pendientes';
$where = "WHERE comentario != '' AND puntuacion > 0";
if ($filtro === 'pendientes') $where .= " AND aprobada = 0";
if ($filtro === 'aprobadas')  $where .= " AND aprobada = 1";
$stmt = $db->prepare("SELECT COUNT(*) FROM resenas $where"); $stmt->execute(); $total = $stmt->fetchColumn();
$stmt = $db->prepare("SELECT * FROM resenas $where ORDER BY creado_en DESC"); $stmt->execute(); $resenas = $stmt->fetchAll();
$pendientes = $db->query("SELECT COUNT(*) FROM resenas WHERE aprobada = 0 AND comentario != ''")->fetchColumn();
?>
<!DOCTYPE html><html lang="es"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseñas — EGOZ Admin</title><meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <style>body{padding-top:0;}.admin-layout{display:flex;min-height:100vh;}.admin-offcanvas-link{display:flex;align-items:center;gap:12px;padding:14px 24px;color:var(--purple);text-decoration:none;font-size:0.95rem;border-left:3px solid transparent;transition:var(--transition);}.admin-offcanvas-link:hover{color:var(--gold);background:rgba(201,169,110,0.05);border-left-color:var(--gold);}</style>
</head><body>
<div class="admin-layout">
<aside class="admin-sidebar d-none d-lg-block">
    <div class="text-center px-4 py-4" style="border-bottom:1px solid var(--border);">
        <svg width="52" height="52" viewBox="0 0 64 64"><circle cx="32" cy="32" r="28" fill="#1a0a2e" stroke="#3a1f6e" stroke-width="1.5"/><circle cx="32" cy="32" r="23" fill="none" stroke="#c9a96e" stroke-width="0.6" stroke-dasharray="3,2"/><text x="32" y="28" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">EG</text><line x1="18" y1="32" x2="46" y2="32" stroke="#c9a96e" stroke-width="0.8"/><text x="32" y="44" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">OZ</text><circle cx="32" cy="32" r="2.5" fill="#c9a96e"/></svg>
        <div style="color:var(--gold);font-size:0.8rem;letter-spacing:2px;margin-top:8px;">PANEL ADMIN</div>
        <div style="color:var(--purple-muted);font-size:0.75rem;margin-top:2px;"><?= sanitize($_SESSION['admin_nombre']) ?></div>
    </div>
    <nav class="mt-3">
        <a href="dashboard.php" class="admin-nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="pedidos.php" class="admin-nav-link"><i class="bi bi-bag-check"></i> Pedidos <span class="notif-badge ms-auto badge-pedidos" style="display:none;">0</span></a>
        <a href="productos.php" class="admin-nav-link"><i class="bi bi-grid"></i> Productos</a>
        <a href="mensajes.php" class="admin-nav-link"><i class="bi bi-envelope"></i> Mensajes <span class="notif-badge ms-auto badge-mensajes" style="display:none;">0</span></a>
        <a href="resenas.php" class="admin-nav-link active"><i class="bi bi-star"></i> Reseñas <span class="notif-badge ms-auto badge-resenas" style="display:none;">0</span></a>
        <a href="categorias.php" class="admin-nav-link"><i class="bi bi-tags"></i> Categorías</a>
        <div style="border-top:1px solid var(--border);margin:16px 24px;"></div>
        <a href="<?= URL_INICIO ?>" target="_blank" class="admin-nav-link"><i class="bi bi-box-arrow-up-right"></i> Ver web</a>
        <a href="logout.php" class="admin-nav-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a>
    </nav>
</aside>
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="adminMenu" style="background:var(--bg-secondary);border-right:1px solid var(--border);width:280px;z-index:1055;">
    <div class="offcanvas-header" style="border-bottom:1px solid var(--border);padding:16px 20px;">
        <div style="color:var(--gold);font-weight:700;letter-spacing:2px;font-size:0.9rem;">EGOZ ADMIN</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0"><nav class="mt-2">
        <a href="dashboard.php" class="admin-offcanvas-link"><i class="bi bi-speedometer2 fs-5"></i> Dashboard</a>
        <a href="pedidos.php" class="admin-offcanvas-link"><i class="bi bi-bag-check fs-5"></i> Pedidos <span class="notif-badge badge-pedidos" style="display:none;">0</span></a>
        <a href="productos.php" class="admin-offcanvas-link"><i class="bi bi-grid fs-5"></i> Productos</a>
        <a href="mensajes.php" class="admin-offcanvas-link"><i class="bi bi-envelope fs-5"></i> Mensajes <span class="notif-badge badge-mensajes" style="display:none;">0</span></a>
        <a href="resenas.php" class="admin-offcanvas-link" style="color:var(--gold);border-left-color:var(--gold);"><i class="bi bi-star fs-5"></i> Reseñas <span class="notif-badge badge-resenas" style="display:none;">0</span></a>
        <a href="categorias.php" class="admin-offcanvas-link"><i class="bi bi-tags fs-5"></i> Categorías</a>
        <div style="border-top:1px solid var(--border);margin:12px 20px;"></div>
        <a href="<?= URL_INICIO ?>" target="_blank" class="admin-offcanvas-link"><i class="bi bi-box-arrow-up-right fs-5"></i> Ver web</a>
        <a href="logout.php" class="admin-offcanvas-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left fs-5"></i> Cerrar sesión</a>
    </nav></div>
</div>
<main class="admin-content" style="flex:1;">
    <div class="d-lg-none d-flex align-items-center justify-content-between p-3 mb-3" style="background:var(--bg-secondary);border-bottom:1px solid var(--border);">
        <button class="border-0" style="background:none;color:var(--gold);font-size:1.3rem;cursor:pointer;" data-bs-toggle="offcanvas" data-bs-target="#adminMenu"><i class="bi bi-list"></i></button>
        <span style="color:var(--gold);font-weight:600;font-size:0.95rem;">Reseñas</span>
        <span style="width:24px;"></span>
    </div>
    <div class="px-3 px-lg-0">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 style="font-size:1.4rem;color:var(--gold-light);margin:0;">Reseñas</h1>
            <span style="color:var(--purple);font-size:0.85rem;"><?= $total ?> reseñas</span>
        </div>
        <div class="d-flex gap-2 mb-4">
            <a href="?filtro=pendientes" class="filter-btn <?= $filtro === 'pendientes' ? 'active' : '' ?>" style="font-size:0.8rem;padding:5px 14px;">Pendientes <?php if ($pendientes > 0): ?><span style="background:var(--gold);color:var(--bg-primary);border-radius:10px;padding:1px 6px;font-size:0.7rem;margin-left:4px;"><?= $pendientes ?></span><?php endif; ?></a>
            <a href="?filtro=aprobadas" class="filter-btn <?= $filtro === 'aprobadas' ? 'active' : '' ?>" style="font-size:0.8rem;padding:5px 14px;">Aprobadas</a>
            <a href="?filtro=todas" class="filter-btn <?= $filtro === 'todas' ? 'active' : '' ?>" style="font-size:0.8rem;padding:5px 14px;">Todas</a>
        </div>
        <?php if (empty($resenas)): ?>
        <div class="text-center py-5"><i class="bi bi-star" style="font-size:3rem;color:var(--border);display:block;margin-bottom:16px;"></i><p style="color:var(--purple);">No hay reseñas</p></div>
        <?php else: ?>
        <div>
            <?php foreach ($resenas as $r): ?>
            <div style="background:var(--bg-secondary);border:1px solid <?= !$r['aprobada'] ? 'var(--gold)' : 'var(--border)' ?>;border-radius:var(--radius-lg);padding:18px;margin-bottom:10px;">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div style="flex:1;min-width:0;">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <?php if (!$r['aprobada']): ?><span style="background:var(--gold);color:var(--bg-primary);font-size:0.68rem;font-weight:700;padding:2px 7px;border-radius:10px;">NUEVA</span><?php endif; ?>
                            <span style="color:var(--gold-light);font-weight:600;font-size:0.9rem;"><?= sanitize($r['cliente_nombre']) ?></span>
                            <span style="color:var(--text-muted);font-size:0.75rem;"><?= date('d/m/Y H:i', strtotime($r['creado_en'])) ?></span>
                        </div>
                        <div style="color:var(--gold);font-size:1rem;margin-bottom:8px;">
                            <?php for ($i=1;$i<=5;$i++): ?><span style="color:<?= $i<=$r['puntuacion']?'#c9a96e':'var(--border)' ?>;">★</span><?php endfor; ?>
                            <span style="color:var(--purple);font-size:0.8rem;margin-left:6px;"><?= $r['puntuacion'] ?>/5</span>
                        </div>
                        <p style="color:var(--purple);font-size:0.88rem;line-height:1.6;margin:0;"><?= sanitize($r['comentario']) ?></p>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <?php if (!$r['aprobada']): ?>
                        <form method="POST"><input type="hidden" name="aprobar_id" value="<?= $r['id'] ?>"><button type="submit" class="btn-gold" style="font-size:0.78rem;padding:6px 14px;white-space:nowrap;"><i class="bi bi-check-lg me-1"></i>Aprobar</button></form>
                        <?php endif; ?>
                        <form method="POST" onsubmit="return confirm('¿Eliminar reseña?')"><input type="hidden" name="eliminar_id" value="<?= $r['id'] ?>"><button type="submit" style="background:rgba(107,39,55,0.3);border:1px solid #6b2737;color:#f87171;border-radius:var(--radius-md);padding:6px 12px;font-size:0.78rem;cursor:pointer;"><i class="bi bi-trash3"></i></button></form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>
</div>
<script>
function actualizarBadges(){fetch('<?= SITE_URL ?>/admin/api_notificaciones.php').then(r=>r.json()).then(data=>{document.querySelectorAll('.badge-pedidos').forEach(b=>{b.textContent=data.pedidos;b.style.display=data.pedidos>0?'inline-flex':'none';});document.querySelectorAll('.badge-mensajes').forEach(b=>{b.textContent=data.mensajes;b.style.display=data.mensajes>0?'inline-flex':'none';});document.querySelectorAll('.badge-resenas').forEach(b=>{b.textContent=data.resenas;b.style.display=data.resenas>0?'inline-flex':'none';});}).catch(()=>{});}
actualizarBadges();setInterval(actualizarBadges,15000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>