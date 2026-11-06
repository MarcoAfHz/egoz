<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $db->prepare("UPDATE productos SET activo = 0 WHERE id = ?")->execute([(int)$_POST['eliminar_id']]);
    header('Location: productos.php'); exit;
}

$categoria_slug = isset($_GET['categoria']) ? sanitize($_GET['categoria']) : '';
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina = 10; $offset = ($pagina - 1) * $por_pagina;
$where = "WHERE p.activo = 1"; $params = [];
if ($categoria_slug) { $where .= " AND c.slug = ?"; $params[] = $categoria_slug; }
$stmt = $db->prepare("SELECT COUNT(*) FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id $where");
$stmt->execute($params); $total = $stmt->fetchColumn(); $total_paginas = ceil($total / $por_pagina);
$params_pag = array_merge($params, [$por_pagina, $offset]);
$stmt = $db->prepare("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id $where ORDER BY p.creado_en DESC LIMIT ? OFFSET ?");
$stmt->execute($params_pag); $productos = $stmt->fetchAll();
$categorias = $db->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html><html lang="es"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos — EGOZ Admin</title><meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <style>
        body{padding-top:0;} .admin-layout{display:flex;min-height:100vh;}
        .admin-offcanvas-link{display:flex;align-items:center;gap:12px;padding:14px 24px;color:var(--purple);text-decoration:none;font-size:0.95rem;border-left:3px solid transparent;transition:var(--transition);}
        .admin-offcanvas-link:hover{color:var(--gold);background:rgba(201,169,110,0.05);border-left-color:var(--gold);}
    </style>
</head><body>
<div class="admin-layout">

<!-- SIDEBAR DESKTOP -->
<aside class="admin-sidebar d-none d-lg-block">
    <div class="text-center px-4 py-4" style="border-bottom:1px solid var(--border);">
        <svg width="52" height="52" viewBox="0 0 64 64"><circle cx="32" cy="32" r="28" fill="#1a0a2e" stroke="#3a1f6e" stroke-width="1.5"/><circle cx="32" cy="32" r="23" fill="none" stroke="#c9a96e" stroke-width="0.6" stroke-dasharray="3,2"/><text x="32" y="28" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">EG</text><line x1="18" y1="32" x2="46" y2="32" stroke="#c9a96e" stroke-width="0.8"/><text x="32" y="44" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">OZ</text><circle cx="32" cy="32" r="2.5" fill="#c9a96e"/></svg>
        <div style="color:var(--gold);font-size:0.8rem;letter-spacing:2px;margin-top:8px;">PANEL ADMIN</div>
        <div style="color:var(--purple-muted);font-size:0.75rem;margin-top:2px;"><?= sanitize($_SESSION['admin_nombre']) ?></div>
    </div>
    <nav class="mt-3">
        <a href="dashboard.php" class="admin-nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="pedidos.php" class="admin-nav-link"><i class="bi bi-bag-check"></i> Pedidos <span class="notif-badge ms-auto badge-pedidos" style="display:none;">0</span></a>
        <a href="productos.php" class="admin-nav-link active"><i class="bi bi-grid"></i> Productos</a>
        <a href="mensajes.php" class="admin-nav-link"><i class="bi bi-envelope"></i> Mensajes <span class="notif-badge ms-auto badge-mensajes" style="display:none;">0</span></a>
        <a href="resenas.php" class="admin-nav-link"><i class="bi bi-star"></i> Reseñas <span class="notif-badge ms-auto badge-resenas" style="display:none;">0</span></a>
        <a href="categorias.php" class="admin-nav-link"><i class="bi bi-tags"></i> Categorías</a>
        <div style="border-top:1px solid var(--border);margin:16px 24px;"></div>
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
    <div class="offcanvas-body p-0"><nav class="mt-2">
        <a href="dashboard.php" class="admin-offcanvas-link"><i class="bi bi-speedometer2 fs-5"></i> Dashboard</a>
        <a href="pedidos.php" class="admin-offcanvas-link"><i class="bi bi-bag-check fs-5"></i> Pedidos <span class="notif-badge badge-pedidos" style="display:none;">0</span></a>
        <a href="productos.php" class="admin-offcanvas-link" style="color:var(--gold);border-left-color:var(--gold);"><i class="bi bi-grid fs-5"></i> Productos</a>
        <a href="mensajes.php" class="admin-offcanvas-link"><i class="bi bi-envelope fs-5"></i> Mensajes <span class="notif-badge badge-mensajes" style="display:none;">0</span></a>
        <a href="resenas.php" class="admin-offcanvas-link"><i class="bi bi-star fs-5"></i> Reseñas <span class="notif-badge badge-resenas" style="display:none;">0</span></a>
        <a href="categorias.php" class="admin-offcanvas-link"><i class="bi bi-tags fs-5"></i> Categorías</a>
        <div style="border-top:1px solid var(--border);margin:12px 20px;"></div>
        <a href="<?= URL_INICIO ?>" target="_blank" class="admin-offcanvas-link"><i class="bi bi-box-arrow-up-right fs-5"></i> Ver web</a>
        <a href="logout.php" class="admin-offcanvas-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left fs-5"></i> Cerrar sesión</a>
    </nav></div>
</div>

<main class="admin-content" style="flex:1;">
    <div class="d-lg-none d-flex align-items-center justify-content-between p-3" style="background:var(--bg-secondary);border-bottom:1px solid var(--border);">
        <button class="border-0" style="background:none;color:var(--gold);font-size:1.3rem;cursor:pointer;" data-bs-toggle="offcanvas" data-bs-target="#adminMenu"><i class="bi bi-list"></i></button>
        <span style="color:var(--gold);font-weight:600;font-size:0.95rem;">Productos</span>
        <a href="producto_nuevo.php" style="color:var(--gold);font-size:1.2rem;"><i class="bi bi-plus-circle"></i></a>
    </div>
    <div class="px-3 px-lg-0 py-3 py-lg-0">
        <div class="d-none d-lg-flex align-items-center justify-content-between mb-4">
            <h1 style="font-size:1.4rem;color:var(--gold-light);margin:0;">Productos <span style="color:var(--purple);font-size:0.9rem;">(<?= $total ?>)</span></h1>
            <a href="producto_nuevo.php" class="btn-gold" style="font-size:0.85rem;padding:8px 20px;"><i class="bi bi-plus-circle me-2"></i>Añadir producto</a>
        </div>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="productos.php" class="filter-btn <?= !$categoria_slug ? 'active' : '' ?>" style="font-size:0.78rem;padding:5px 12px;">Todos</a>
            <?php foreach ($categorias as $cat): ?>
            <a href="?categoria=<?= $cat['slug'] ?>" class="filter-btn <?= $categoria_slug === $cat['slug'] ? 'active' : '' ?>" style="font-size:0.78rem;padding:5px 12px;"><?= sanitize($cat['nombre']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php if (empty($productos)): ?>
        <div class="text-center py-5"><i class="bi bi-grid-x" style="font-size:3rem;color:var(--border);display:block;margin-bottom:16px;"></i><p style="color:var(--purple);">No hay productos</p><a href="producto_nuevo.php" class="btn-gold mt-2 d-inline-block">Añadir primer producto</a></div>
        <?php else: ?>
        <div class="d-lg-none">
            <?php foreach ($productos as $p): ?>
            <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:14px;margin-bottom:10px;">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div style="flex:1;min-width:0;">
                        <div style="color:var(--gold-light);font-size:0.9rem;font-weight:600;margin-bottom:2px;"><?= sanitize($p['nombre']) ?><?php if ($p['destacado']): ?><i class="bi bi-star-fill ms-1" style="color:var(--gold);font-size:0.75rem;"></i><?php endif; ?></div>
                        <div style="color:var(--purple-muted);font-size:0.75rem;margin-bottom:6px;"><?= sanitize($p['categoria_nombre'] ?? '—') ?></div>
                        <div class="d-flex align-items-center gap-3">
                            <span style="color:var(--gold);font-weight:700;font-size:0.95rem;"><?= number_format($p['precio'], 2) ?> CHF</span>
                            <span style="color:<?= $p['stock'] > 0 ? '#4ade80' : '#f87171' ?>;font-size:0.8rem;"><i class="bi bi-box-seam me-1"></i><?= $p['stock'] ?> uds</span>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <a href="producto_editar.php?id=<?= $p['id'] ?>" class="btn-purple" style="font-size:0.75rem;padding:5px 10px;text-align:center;"><i class="bi bi-pencil"></i></a>
                        <a href="<?= urlProducto($p['id'], $p['slug']) ?>" target="_blank" style="background:var(--bg-card);border:1px solid var(--border);color:var(--purple);border-radius:var(--radius-md);padding:5px 10px;font-size:0.75rem;text-align:center;text-decoration:none;"><i class="bi bi-eye"></i></a>
                        <form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="eliminar_id" value="<?= $p['id'] ?>"><button type="submit" style="background:rgba(107,39,55,0.3);border:1px solid #6b2737;color:#f87171;border-radius:var(--radius-md);padding:5px 10px;font-size:0.75rem;width:100%;cursor:pointer;"><i class="bi bi-trash3"></i></button></form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="d-none d-lg-block admin-card p-0">
            <table class="table table-egoz mb-0">
                <thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Dest.</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($productos as $p): ?>
                <tr>
                    <td><div style="color:var(--gold-light);font-size:0.85rem;font-weight:500;"><?= sanitize($p['nombre']) ?></div><div style="color:var(--text-muted);font-size:0.72rem;"><?= sanitize($p['slug']) ?></div></td>
                    <td style="color:var(--purple);font-size:0.85rem;"><?= sanitize($p['categoria_nombre'] ?? '—') ?></td>
                    <td style="color:var(--gold);font-weight:600;font-size:0.85rem;"><?= number_format($p['precio'], 2) ?> CHF</td>
                    <td><span style="color:<?= $p['stock'] > 0 ? '#4ade80' : '#f87171' ?>;font-size:0.85rem;"><?= $p['stock'] ?></span></td>
                    <td><?php if ($p['destacado']): ?><i class="bi bi-star-fill" style="color:var(--gold);"></i><?php else: ?><i class="bi bi-star" style="color:var(--border);"></i><?php endif; ?></td>
                    <td><div class="d-flex gap-2">
                        <a href="producto_editar.php?id=<?= $p['id'] ?>" class="btn-purple" style="font-size:0.75rem;padding:4px 10px;"><i class="bi bi-pencil"></i></a>
                        <form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="eliminar_id" value="<?= $p['id'] ?>"><button type="submit" style="background:rgba(107,39,55,0.3);border:1px solid #6b2737;color:#f87171;border-radius:var(--radius-md);padding:4px 10px;font-size:0.75rem;cursor:pointer;"><i class="bi bi-trash3"></i></button></form>
                        <a href="<?= urlProducto($p['id'], $p['slug']) ?>" target="_blank" style="color:var(--purple);padding:4px 6px;font-size:0.85rem;"><i class="bi bi-box-arrow-up-right"></i></a>
                    </div></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_paginas > 1): ?><nav class="mt-4 d-flex justify-content-center"><ul class="pagination"><?php for ($i=1;$i<=$total_paginas;$i++): ?><li class="page-item <?= $i===$pagina?'active':'' ?>"><a class="page-link" href="?categoria=<?= $categoria_slug ?>&pagina=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
        <div class="d-lg-none mt-3"><a href="producto_nuevo.php" class="btn-gold d-block text-center" style="padding:12px;"><i class="bi bi-plus-circle me-2"></i>Añadir producto</a></div>
        <?php endif; ?>
    </div>
</main>
</div>
<script>
function actualizarBadges() {
    fetch('<?= SITE_URL ?>/admin/api_notificaciones.php')
        .then(r => r.json())
        .then(data => {
            document.querySelectorAll('.badge-pedidos').forEach(b => { b.textContent = data.pedidos; b.style.display = data.pedidos > 0 ? 'inline-flex' : 'none'; });
            document.querySelectorAll('.badge-mensajes').forEach(b => { b.textContent = data.mensajes; b.style.display = data.mensajes > 0 ? 'inline-flex' : 'none'; });
            document.querySelectorAll('.badge-resenas').forEach(b => { b.textContent = data.resenas; b.style.display = data.resenas > 0 ? 'inline-flex' : 'none'; });
        }).catch(() => {});
}
actualizarBadges();
setInterval(actualizarBadges, 15000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>