<?php
// =============================================
// EGOZ — Boutique Esotérica
// admin/pedidos.php
// =============================================
require_once __DIR__ . '/../config.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $pid   = (int)$_POST['pedido_id'];
    $nuevo = sanitize($_POST['nuevo_estado']);
    $estados_validos = ['pendiente','pagado','enviado','entregado','cancelado'];

    if (in_array($nuevo, $estados_validos)) {
        $stmt = $db->prepare("SELECT p.*, c.nombre as cliente_nombre, c.email as cliente_email FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id WHERE p.id = ?");
        $stmt->execute([$pid]);
        $pedido_data = $stmt->fetch();

        if ($pedido_data && $pedido_data['estado'] !== $nuevo) {
            $db->prepare("UPDATE pedidos SET estado = ? WHERE id = ?")->execute([$nuevo, $pid]);

            // Si se cancela y antes NO estaba cancelado, devolver stock
            if ($nuevo === 'cancelado' && $pedido_data['estado'] !== 'cancelado') {
                devolverStockPedido($pid);
            }

            if ($pedido_data['cliente_email']) {
                require_once __DIR__ . '/../includes/email_helper.php';
                $cliente_email = ['nombre' => $pedido_data['cliente_nombre'], 'email' => $pedido_data['cliente_email']];
                $codigo_ref = $pedido_data['codigo_referencia'] ?? '';
                if ($nuevo === 'enviado')   emailPedidoEnviado($pid, $cliente_email, $codigo_ref);
                if ($nuevo === 'entregado') emailPedidoEntregado($pid, $cliente_email, $codigo_ref);
                if ($nuevo === 'cancelado') emailPedidoCancelado($pid, $cliente_email, $codigo_ref);
            }
        }
    }
    header('Location: pedidos.php'); exit;
}

$estado     = isset($_GET['estado']) ? sanitize($_GET['estado']) : '';
$pagina     = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina = 15;
$offset     = ($pagina - 1) * $por_pagina;
$where      = "WHERE 1=1"; $params = [];
if ($estado) { $where .= " AND p.estado = ?"; $params[] = $estado; }

$stmt = $db->prepare("SELECT COUNT(*) FROM pedidos p $where");
$stmt->execute($params); $total = $stmt->fetchColumn();
$total_paginas = ceil($total / $por_pagina);

$params_pag = array_merge($params, [$por_pagina, $offset]);
$stmt = $db->prepare("SELECT p.*, c.nombre as cliente_nombre, c.email as cliente_email FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id $where ORDER BY p.creado_en DESC LIMIT ? OFFSET ?");
$stmt->execute($params_pag); $pedidos = $stmt->fetchAll();

// Helper: detecta si el pedido es pago en tienda
function esPagoTienda($pedido) {
    return empty($pedido['stripe_payment_id']) && str_contains($pedido['notas'] ?? '', '[PAGO EN TIENDA]');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos — EGOZ Admin</title><meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <style>
        body{padding-top:0;} .admin-layout{display:flex;min-height:100vh;}
        .admin-offcanvas-link{display:flex;align-items:center;gap:12px;padding:14px 24px;color:var(--purple);text-decoration:none;font-size:0.95rem;border-left:3px solid transparent;transition:var(--transition);}
        .admin-offcanvas-link:hover{color:var(--gold);background:rgba(201,169,110,0.05);border-left-color:var(--gold);}
        .badge-tienda{display:inline-flex;align-items:center;gap:4px;background:rgba(201,169,110,0.15);border:1px solid var(--gold);color:var(--gold);font-size:0.68rem;padding:2px 8px;border-radius:10px;font-weight:600;white-space:nowrap;}
    </style>
</head>
<body>
<div class="admin-layout">

<!-- SIDEBAR DESKTOP -->
<aside class="admin-sidebar d-none d-lg-block">
    <div class="text-center px-4 py-4" style="border-bottom:1px solid var(--border);">
        <svg width="52" height="52" viewBox="0 0 64 64"><circle cx="32" cy="32" r="28" fill="#1a0a2e" stroke="#3a1f6e" stroke-width="1.5"/><circle cx="32" cy="32" r="23" fill="none" stroke="#c9a96e" stroke-width="0.6" stroke-dasharray="3,2"/><text x="32" y="28" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">EG</text><line x1="18" y1="32" x2="46" y2="32" stroke="#c9a96e" stroke-width="0.8"/><text x="32" y="44" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">OZ</text><circle cx="32" cy="32" r="2.5" fill="#c9a96e"/></svg>
        <div style="color:var(--gold);font-size:0.8rem;letter-spacing:2px;margin-top:8px;">PANEL ADMIN</div>
        <div style="color:var(--purple-muted); font-size:0.75rem; margin-top:2px;"><?= sanitize($_SESSION['admin_nombre']) ?></div>
    </div>
    <nav class="mt-3">
        <a href="dashboard.php" class="admin-nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="pedidos.php" class="admin-nav-link active"><i class="bi bi-bag-check"></i> Pedidos <span class="notif-badge ms-auto badge-pedidos" style="display:none;">0</span></a>
        <a href="productos.php" class="admin-nav-link"><i class="bi bi-grid"></i> Productos</a>
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
        <a href="pedidos.php" class="admin-offcanvas-link" style="color:var(--gold);border-left-color:var(--gold);"><i class="bi bi-bag-check fs-5"></i> Pedidos <span class="notif-badge badge-pedidos" style="display:none;">0</span></a>
        <a href="productos.php" class="admin-offcanvas-link"><i class="bi bi-grid fs-5"></i> Productos</a>
        <a href="mensajes.php" class="admin-offcanvas-link"><i class="bi bi-envelope fs-5"></i> Mensajes <span class="notif-badge badge-mensajes" style="display:none;">0</span></a>
        <a href="resenas.php" class="admin-offcanvas-link"><i class="bi bi-star fs-5"></i> Reseñas <span class="notif-badge badge-resenas" style="display:none;">0</span></a>
        <a href="categorias.php" class="admin-offcanvas-link"><i class="bi bi-tags fs-5"></i> Categorías</a>
        <div style="border-top:1px solid var(--border);margin:12px 20px;"></div>
        <a href="<?= URL_INICIO ?>" target="_blank" class="admin-offcanvas-link"><i class="bi bi-box-arrow-up-right fs-5"></i> Ver web</a>
        <a href="logout.php" class="admin-offcanvas-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left fs-5"></i> Cerrar sesión</a>
    </nav></div>
</div>

<!-- CONTENIDO -->
<main class="admin-content" style="flex:1;">
    <div class="d-lg-none d-flex align-items-center justify-content-between p-3 mb-3" style="background:var(--bg-secondary);border-bottom:1px solid var(--border);">
        <button class="border-0" style="background:none;color:var(--gold);font-size:1.3rem;cursor:pointer;" data-bs-toggle="offcanvas" data-bs-target="#adminMenu"><i class="bi bi-list"></i></button>
        <span style="color:var(--gold);font-weight:600;font-size:0.95rem;">Pedidos</span>
        <span style="width:24px;"></span>
    </div>

    <div class="px-3 px-lg-0">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 style="font-size:1.4rem;color:var(--gold-light);margin:0;">Pedidos</h1>
            <span style="color:var(--purple);font-size:0.85rem;"><?= $total ?> pedidos</span>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <?php $estados = [''=>'Todos','pendiente'=>'Pendientes','pagado'=>'Pagados','enviado'=>'Enviados','entregado'=>'Entregados','cancelado'=>'Cancelados'];
            foreach ($estados as $val => $label): ?>
            <a href="?estado=<?= $val ?>" class="filter-btn <?= $estado === $val ? 'active' : '' ?>" style="font-size:0.8rem;padding:5px 14px;"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($pedidos)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bag-x" style="font-size:3rem;color:var(--border);display:block;margin-bottom:16px;"></i>
            <p style="color:var(--purple);">No hay pedidos</p>
        </div>
        <?php else: ?>

        <!-- MÓVIL -->
        <div class="d-lg-none">
            <?php foreach ($pedidos as $p): ?>
            <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:14px;margin-bottom:10px;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span style="color:var(--gold);font-weight:700;">#<?= $p['id'] ?></span>
                        <?php if ($p['codigo_referencia']): ?><span style="color:var(--purple-muted);font-size:0.72rem;margin-left:8px;"><?= sanitize($p['codigo_referencia']) ?></span><?php endif; ?>
                        <?php if (esPagoTienda($p)): ?><span class="badge-tienda ms-2"><i class="bi bi-shop"></i> Pago en tienda</span><?php endif; ?>
                    </div>
                    <span style="color:var(--purple);font-size:0.75rem;"><?= date('d/m/Y', strtotime($p['creado_en'])) ?></span>
                </div>
                <div style="color:var(--gold-light);font-size:0.85rem;margin-bottom:2px;"><?= sanitize($p['cliente_nombre'] ?? '—') ?></div>
                <div style="color:var(--text-muted);font-size:0.75rem;margin-bottom:8px;"><?= sanitize($p['cliente_email'] ?? '') ?></div>
                <div class="d-flex justify-content-between align-items-center">
                    <span style="color:var(--gold);font-weight:700;"><?= number_format($p['total'], 2) ?> CHF</span>
                    <div class="d-flex gap-2 align-items-center">
                        <form method="POST" style="display:inline;" onsubmit="return confirmarCambio(this)">
                            <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="cambiar_estado" value="1">
                            <select name="nuevo_estado" onchange="this.form.requestSubmit()" class="form-select form-select-sm" style="width:auto;font-size:0.75rem;padding:3px 6px;">
                                <?php foreach (['pendiente','pagado','enviado','entregado','cancelado'] as $est): ?>
                                <option value="<?= $est ?>" <?= $p['estado'] === $est ? 'selected' : '' ?>><?= ucfirst($est) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <a href="pedido_detalle.php?id=<?= $p['id'] ?>" class="btn-purple" style="font-size:0.75rem;padding:4px 10px;white-space:nowrap;">Ver →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- DESKTOP -->
        <div class="d-none d-lg-block table-responsive admin-card p-0">
            <table class="table table-egoz mb-0">
                <thead><tr><th>#</th><th>Referencia</th><th>Cliente</th><th>Pago</th><th>Total</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td style="color:var(--gold);">#<?= $p['id'] ?></td>
                        <td style="color:var(--purple-muted);font-size:0.78rem;letter-spacing:1px;"><?= sanitize($p['codigo_referencia'] ?? '—') ?></td>
                        <td>
                            <div style="color:var(--gold-light);font-size:0.85rem;"><?= sanitize($p['cliente_nombre'] ?? '—') ?></div>
                            <div style="color:var(--text-muted);font-size:0.75rem;"><?= sanitize($p['cliente_email'] ?? '') ?></div>
                        </td>
                        <td>
                            <?php if (esPagoTienda($p)): ?>
                            <span class="badge-tienda"><i class="bi bi-shop"></i> En tienda</span>
                            <?php else: ?>
                            <span style="color:var(--purple-muted);font-size:0.75rem;"><i class="bi bi-credit-card me-1"></i>Online</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--gold);font-weight:600;"><?= number_format($p['total'], 2) ?> CHF</td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirmarCambio(this)">
                                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="cambiar_estado" value="1">
                                <select name="nuevo_estado" onchange="this.form.requestSubmit()" class="form-select form-select-sm" style="width:auto;padding:3px 8px;font-size:0.78rem;">
                                    <?php foreach (['pendiente','pagado','enviado','entregado','cancelado'] as $est): ?>
                                    <option value="<?= $est ?>" <?= $p['estado'] === $est ? 'selected' : '' ?>><?= ucfirst($est) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td style="color:var(--purple);font-size:0.8rem;"><?= date('d/m/Y', strtotime($p['creado_en'])) ?></td>
                        <td><a href="pedido_detalle.php?id=<?= $p['id'] ?>" class="btn-purple" style="font-size:0.78rem;padding:4px 10px;">Ver →</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_paginas > 1): ?>
        <nav class="mt-4 d-flex justify-content-center"><ul class="pagination">
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?= $i === $pagina ? 'active' : '' ?>"><a class="page-link" href="?estado=<?= $estado ?>&pagina=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul></nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
</div>

<script>
function confirmarCambio(form) {
    const select = form.querySelector('select[name="nuevo_estado"]');
    if (select.value === 'cancelado') {
        return confirm('¿Cancelar este pedido? El stock de los productos se devolverá automáticamente.');
    }
    return true;
}

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
</body>
</html>