<?php
// =============================================
// EGOZ — Boutique Esotérica
// admin/pedido_detalle.php
// =============================================
require_once __DIR__ . '/../config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { redirect(URL_ADMIN . '/pedidos.php'); }

$db = getDB();

$stmt = $db->prepare("SELECT p.*, c.nombre as cliente_nombre, c.email as cliente_email, c.telefono, c.direccion, c.ciudad, c.codigo_postal FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id WHERE p.id = ?");
$stmt->execute([$id]); $pedido = $stmt->fetch();
if (!$pedido) { redirect(URL_ADMIN . '/pedidos.php'); }

$stmt = $db->prepare("SELECT pi.*, p.slug as producto_slug FROM pedido_items pi LEFT JOIN productos p ON pi.producto_id = p.id WHERE pi.pedido_id = ?");
$stmt->execute([$id]); $items = $stmt->fetchAll();

$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_estado'])) {
    $nuevo = sanitize($_POST['nuevo_estado']);
    $estados_validos = ['pendiente','pagado','enviado','entregado','cancelado'];
    if (in_array($nuevo, $estados_validos) && $pedido['estado'] !== $nuevo) {
        $estado_anterior = $pedido['estado'];
        $db->prepare("UPDATE pedidos SET estado = ? WHERE id = ?")->execute([$nuevo, $id]);
        $pedido['estado'] = $nuevo;

        // Si se cancela y antes NO estaba cancelado, devolver stock
        if ($nuevo === 'cancelado' && $estado_anterior !== 'cancelado') {
            devolverStockPedido($id);
        }

        if ($pedido['cliente_email']) {
            require_once __DIR__ . '/../includes/email_helper.php';
            $cliente_email = ['nombre' => $pedido['cliente_nombre'], 'email' => $pedido['cliente_email']];
            $codigo_ref = $pedido['codigo_referencia'] ?? '';
            if ($nuevo === 'enviado')   emailPedidoEnviado($id, $cliente_email, $codigo_ref);
            if ($nuevo === 'entregado') emailPedidoEntregado($id, $cliente_email, $codigo_ref);
            if ($nuevo === 'cancelado') emailPedidoCancelado($id, $cliente_email, $codigo_ref);
        }
        $exito = 'Estado actualizado a <strong>' . ucfirst($nuevo) . '</strong>' . ($pedido['cliente_email'] ? ' — email enviado al cliente.' : '.');
        if ($nuevo === 'cancelado' && $estado_anterior !== 'cancelado') {
            $exito .= ' El stock de los productos ha sido devuelto.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #<?= $id ?> — EGOZ Admin</title><meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <style>body{padding-top:0;}.admin-layout{display:flex;min-height:100vh;}.admin-offcanvas-link{display:flex;align-items:center;gap:12px;padding:14px 24px;color:var(--purple);text-decoration:none;font-size:0.95rem;border-left:3px solid transparent;transition:var(--transition);}.admin-offcanvas-link:hover{color:var(--gold);background:rgba(201,169,110,0.05);border-left-color:var(--gold);}</style>
</head>
<body>
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
        <span style="color:var(--gold);font-weight:600;font-size:0.95rem;">Pedido #<?= $id ?></span>
        <a href="pedidos.php" style="color:var(--purple);font-size:1.1rem;"><i class="bi bi-arrow-left"></i></a>
    </div>

    <div class="px-3 px-lg-0">
        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <a href="pedidos.php" style="color:var(--purple);font-size:0.85rem;" class="d-none d-lg-inline"><i class="bi bi-arrow-left me-1"></i>Volver</a>
            <h1 style="font-size:1.4rem;color:var(--gold-light);margin:0;">Pedido #<?= $id ?></h1>
            <span class="badge badge-<?= $pedido['estado'] ?>" style="padding:5px 12px;border-radius:10px;"><?= ucfirst($pedido['estado']) ?></span>
        </div>

        <?php if ($exito): ?>
        <div style="background:rgba(45,106,79,0.2);border:1px solid #2d6a4f;border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:20px;color:#4ade80;font-size:0.85rem;">
            <i class="bi bi-check-circle me-2"></i><?= $exito ?>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="admin-card">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:16px;">✦ PRODUCTOS</h3>
                    <?php foreach ($items as $item): ?>
                    <div class="d-flex align-items-center gap-3 pb-3 mb-3" style="border-bottom:1px solid var(--border);">
                        <div style="width:50px;height:50px;background:var(--bg-card);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-bag-heart-fill" style="color:var(--gold);opacity:0.5;"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="color:var(--gold-light);font-size:0.9rem;font-weight:500;"><?= sanitize($item['nombre_producto']) ?></div>
                            <div style="color:var(--purple);font-size:0.8rem;">Cantidad: <?= $item['cantidad'] ?> × <?= number_format($item['precio'], 2) ?> CHF</div>
                        </div>
                        <div style="color:var(--gold);font-weight:600;font-size:0.95rem;"><?= number_format($item['precio'] * $item['cantidad'], 2) ?> CHF</div>
                    </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between pt-2">
                        <span style="color:var(--gold-light);font-size:1.1rem;font-weight:600;">TOTAL</span>
                        <span style="color:var(--gold);font-size:1.2rem;font-weight:700;"><?= number_format($pedido['total'], 2) ?> CHF</span>
                    </div>
                </div>

                <?php if ($pedido['notas']): ?>
                <div class="admin-card mt-3">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:10px;">✦ NOTAS</h3>
                    <p style="color:var(--purple);font-size:0.9rem;margin:0;"><?= sanitize($pedido['notas']) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($pedido['stripe_payment_id']): ?>
                <div class="admin-card mt-3">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:10px;">✦ PAGO STRIPE</h3>
                    <p style="color:var(--text-muted);font-size:0.8rem;margin:0;word-break:break-all;"><?= sanitize($pedido['stripe_payment_id']) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <div class="admin-card mb-3">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:16px;">✦ CLIENTE</h3>
                    <div class="mb-2"><div style="color:var(--purple-muted);font-size:0.75rem;letter-spacing:1px;">NOMBRE</div><div style="color:var(--gold-light);font-size:0.9rem;"><?= sanitize($pedido['cliente_nombre'] ?? '—') ?></div></div>
                    <div class="mb-2"><div style="color:var(--purple-muted);font-size:0.75rem;letter-spacing:1px;">EMAIL</div><div style="color:var(--gold-light);font-size:0.9rem;"><?= sanitize($pedido['cliente_email'] ?? '—') ?></div></div>
                    <?php if ($pedido['telefono']): ?>
                    <div class="mb-2"><div style="color:var(--purple-muted);font-size:0.75rem;letter-spacing:1px;">TELÉFONO</div><div style="color:var(--gold-light);font-size:0.9rem;"><?= sanitize($pedido['telefono']) ?></div></div>
                    <?php endif; ?>
                    <div class="mb-2">
                        <div style="color:var(--purple-muted);font-size:0.75rem;letter-spacing:1px;">DIRECCIÓN</div>
                        <div style="color:var(--gold-light);font-size:0.9rem;"><?= sanitize($pedido['direccion'] ?? '—') ?><br><?= sanitize($pedido['codigo_postal'] ?? '') ?> <?= sanitize($pedido['ciudad'] ?? '') ?></div>
                    </div>
                    <div class="mb-2"><div style="color:var(--purple-muted);font-size:0.75rem;letter-spacing:1px;">FECHA</div><div style="color:var(--gold-light);font-size:0.9rem;"><?= date('d/m/Y H:i', strtotime($pedido['creado_en'])) ?></div></div>
                    <?php if ($pedido['codigo_referencia']): ?>
                    <div style="background:var(--bg-card);border:1px solid var(--gold);border-radius:var(--radius-md);padding:12px 16px;margin-top:12px;text-align:center;">
                        <div style="color:var(--purple-muted);font-size:0.72rem;letter-spacing:2px;margin-bottom:4px;">CÓDIGO DE REFERENCIA</div>
                        <div style="color:var(--gold);font-size:1.1rem;font-weight:700;letter-spacing:4px;"><?= sanitize($pedido['codigo_referencia']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="admin-card mb-3">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:8px;">✦ CAMBIAR ESTADO</h3>
                    <p style="color:var(--text-muted);font-size:0.78rem;margin-bottom:14px;"><i class="bi bi-info-circle me-1"></i>Al cambiar a Enviado, Entregado o Cancelado se enviará un email automático al cliente. Cancelar devuelve el stock automáticamente.</p>
                    <form method="POST" onsubmit="return confirmarCambioDetalle(this)">
                        <select name="nuevo_estado" class="form-select mb-3">
                            <?php foreach (['pendiente','pagado','enviado','entregado','cancelado'] as $est): ?>
                            <option value="<?= $est ?>" <?= $pedido['estado'] === $est ? 'selected' : '' ?>><?= ucfirst($est) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-gold" style="width:100%;padding:10px;"><i class="bi bi-check-circle me-2"></i>Actualizar estado</button>
                    </form>
                </div>

                <div class="admin-card">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:14px;">✦ CONTACTAR</h3>
                    <?php if ($pedido['cliente_email']): ?>
                    <a href="mailto:<?= sanitize($pedido['cliente_email']) ?>?subject=Pedido%20EGOZ%20%23<?= $id ?>" class="btn-outline-gold d-block text-center mb-2" style="font-size:0.85rem;padding:10px;">
                        <i class="bi bi-envelope me-2"></i>Enviar email
                    </a>
                    <?php endif; ?>
                    <?php if ($pedido['telefono']): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $pedido['telefono']) ?>?text=Hola%2C%20soy%20Edwin%20de%20EGOZ.%20Te%20escribo%20por%20tu%20pedido%20%23<?= $id ?>" target="_blank" class="btn-outline-gold d-block text-center" style="font-size:0.85rem;padding:10px;border-color:#4ade80;color:#4ade80;">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
</div>
<script>
function confirmarCambioDetalle(form) {
    const select = form.querySelector('select[name="nuevo_estado"]');
    if (select.value === 'cancelado') {
        return confirm('¿Cancelar este pedido? El stock de los productos se devolverá automáticamente.');
    }
    return true;
}
function actualizarBadges(){fetch('<?= SITE_URL ?>/admin/api_notificaciones.php').then(r=>r.json()).then(data=>{document.querySelectorAll('.badge-pedidos').forEach(b=>{b.textContent=data.pedidos;b.style.display=data.pedidos>0?'inline-flex':'none';});document.querySelectorAll('.badge-mensajes').forEach(b=>{b.textContent=data.mensajes;b.style.display=data.mensajes>0?'inline-flex':'none';});document.querySelectorAll('.badge-resenas').forEach(b=>{b.textContent=data.resenas;b.style.display=data.resenas>0?'inline-flex':'none';});}).catch(()=>{});}
actualizarBadges();setInterval(actualizarBadges,15000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>