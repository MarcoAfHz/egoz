<?php
/** @var array $t */
/** @var string $lang */
require_once 'config.php';
require_once 'vendor/autoload.php';

// ── PEDIDO EN TIENDA (sin Stripe) ──
$pedido_tienda = isset($_GET['pedido_tienda']) && $_GET['pedido_tienda'] === '1';
$ref_tienda    = isset($_GET['ref']) ? sanitize($_GET['ref']) : '';

if ($pedido_tienda && $ref_tienda) {
    $page_title = $lang === 'de' ? "Bestellung aufgegeben! — EGOZ Boutique Esotérica" : "¡Pedido recibido! — EGOZ Boutique Esotérica";
    $meta_desc  = $lang === 'de' ? "Deine Bestellung wurde aufgegeben." : "Tu pedido ha sido recibido.";
    require_once 'includes/header.php';
    ?>
    <link rel="canonical" href="<?= URL_GRACIAS ?>">
    <section class="py-5" style="background:var(--bg-primary);min-height:70vh;display:flex;align-items:center;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7 text-center">
                    <div style="background:var(--bg-secondary);border:1px solid var(--gold);border-radius:var(--radius-lg);padding:48px 32px;">
                        <div style="width:80px;height:80px;background:rgba(201,169,110,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
                            <i class="bi bi-shop" style="font-size:2.5rem;color:var(--gold);"></i>
                        </div>
                        <h1 style="font-size:1.6rem;color:var(--gold-light);margin-bottom:12px;letter-spacing:2px;">
                            <?= $lang === 'de' ? 'BESTELLUNG AUFGEGEBEN!' : '¡PEDIDO RECIBIDO!' ?>
                        </h1>

                        <div style="background:var(--bg-card);border:1px solid var(--gold);border-radius:var(--radius-md);padding:16px 24px;margin-bottom:20px;display:inline-block;">
                            <div style="color:var(--purple-muted);font-size:0.75rem;letter-spacing:2px;margin-bottom:6px;"><?= $t['cod_referencia'] ?></div>
                            <div style="color:var(--gold);font-size:1.4rem;font-weight:700;letter-spacing:4px;"><?= $ref_tienda ?></div>
                            <div style="color:var(--text-muted);font-size:0.75rem;margin-top:6px;"><?= $t['cod_guardalo'] ?></div>
                        </div>

                        <p style="color:var(--purple);font-size:0.95rem;line-height:1.7;margin-bottom:28px;">
                            <?= $lang === 'de'
                                ? 'Deine Bestellung wurde registriert. Du bezahlst direkt in der Boutique in Zürich, wenn du sie abholst. Edwin wird sich bald per WhatsApp mit dir in Verbindung setzen.'
                                : 'Tu pedido ha sido registrado. Pagarás directamente en la boutique de Zúrich al recogerlo. Edwin se pondrá en contacto contigo pronto por WhatsApp.' ?>
                        </p>

                        <div style="background:var(--bg-card);border-radius:var(--radius-md);padding:20px;margin-bottom:28px;text-align:left;">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <i class="bi bi-whatsapp" style="color:#4ade80;font-size:1.1rem;"></i>
                                <span style="color:var(--purple);font-size:0.9rem;"><?= $lang === 'de' ? 'Edwin kontaktiert dich per WhatsApp' : 'Edwin te contactará por WhatsApp' ?>: <strong style="color:var(--gold-light);">+41 76 713 33 41</strong></span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-cash-coin" style="color:var(--gold);font-size:1.1rem;"></i>
                                <span style="color:var(--purple);font-size:0.9rem;"><?= $lang === 'de' ? 'Zahlung bei Abholung in der Boutique' : 'Pago al recoger en la boutique' ?></span>
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <a href="<?= URL_INICIO ?>" class="btn-gold"><i class="bi bi-house me-2"></i><?= $t['ir_inicio'] ?></a>
                            <a href="<?= URL_TIENDA ?>" class="btn-outline-gold"><i class="bi bi-bag me-2"></i><?= $t['seguir_comprando'] ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    require_once 'includes/footer.php';
    exit;
}

// ── PEDIDO ONLINE (Stripe) — flujo original ──
$payment_intent_id     = isset($_GET['payment_intent'])               ? sanitize($_GET['payment_intent'])               : '';
$payment_intent_secret = isset($_GET['payment_intent_client_secret']) ? sanitize($_GET['payment_intent_client_secret']) : '';
$redirect_status       = isset($_GET['redirect_status'])              ? sanitize($_GET['redirect_status'])              : '';

if (!$payment_intent_id) { redirect(URL_INICIO); }

$pedido_guardado = false;
$pedido_id       = null;
$codigo_ref      = '';

try {
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);

    if ($intent->status === 'succeeded') {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, codigo_referencia FROM pedidos WHERE stripe_payment_id = ?");
        $stmt->execute([$payment_intent_id]);
        $pedido_existente = $stmt->fetch();

        error_log('EGOZ GRACIAS - CARRITO: ' . (empty($_SESSION['carrito'] ?? []) ? 'VACIO' : count($_SESSION['carrito'] ?? []) . ' items'));
        error_log('EGOZ GRACIAS - CLIENTE: ' . (empty($_SESSION['cliente_checkout'] ?? []) ? 'VACIO' : 'OK - ' . ($_SESSION['cliente_checkout']['email'] ?? 'sin email')));

        if (!$pedido_existente) {
            $carrito = $_SESSION['carrito'] ?? [];
            $cliente = $_SESSION['cliente_checkout'] ?? [];

            if (!empty($carrito) && !empty($cliente)) {
                $total = 0;
                foreach ($carrito as $item) { $total += $item['precio'] * $item['cantidad']; }

                $stmt = $db->prepare("INSERT INTO clientes (nombre, email, telefono, direccion, ciudad, codigo_postal) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$cliente['nombre'], $cliente['email'], $cliente['telefono'] ?? '', $cliente['direccion'] ?? '', $cliente['ciudad'] ?? '', $cliente['codigo_postal'] ?? '']);
                $cliente_id = $db->lastInsertId();

                $codigo_ref = 'EGOZ-' . date('Y') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 5));

                $stmt = $db->prepare("INSERT INTO pedidos (cliente_id, total, estado, stripe_payment_id, codigo_referencia, notas) VALUES (?, ?, 'pagado', ?, ?, ?)");
                $stmt->execute([$cliente_id, round($total, 2), $payment_intent_id, $codigo_ref, $cliente['notas'] ?? '']);
                $pedido_id = $db->lastInsertId();

                $stmt = $db->prepare("INSERT INTO pedido_items (pedido_id, producto_id, nombre_producto, cantidad, precio) VALUES (?, ?, ?, ?, ?)");
                foreach ($carrito as $prod_id => $item) {
                    $stmt->execute([$pedido_id, $prod_id, $item['nombre'], $item['cantidad'], $item['precio']]);
                    $db->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?")->execute([$item['cantidad'], $prod_id]);
                }

                crearNotificacion('pedido', '¡Nuevo pedido #' . $pedido_id . '!', $cliente['nombre'] . ' · ' . number_format($total, 2) . ' CHF', $pedido_id);

                require_once 'includes/email_helper.php';
                $stmt_items = $db->prepare("SELECT * FROM pedido_items WHERE pedido_id = ?");
                $stmt_items->execute([$pedido_id]);
                $items_email = $stmt_items->fetchAll();
                $r1 = emailNuevoPedido($pedido_id, $cliente, $items_email, round($total, 2), $codigo_ref);
                $r2 = emailConfirmacionCliente($pedido_id, $cliente, $items_email, round($total, 2), $codigo_ref);
                error_log('EGOZ EMAIL ADMIN: ' . ($r1 ? 'OK' : 'FALLO'));
                error_log('EGOZ EMAIL CLIENTE: ' . ($r2 ? 'OK' : 'FALLO'));

                $_SESSION['carrito'] = [];
                $_SESSION['cliente_checkout'] = [];
                $pedido_guardado = true;
            } else {
                error_log('EGOZ GRACIAS - No se guardó pedido: carrito o cliente vacíos');
            }
        } else {
            $pedido_id  = $pedido_existente['id'];
            $codigo_ref = $pedido_existente['codigo_referencia'];
            $pedido_guardado = true;
            $_SESSION['carrito'] = [];
            $_SESSION['cliente_checkout'] = [];
        }
    }
} catch (\Exception $e) {
    error_log('EGOZ GRACIAS ERROR: ' . $e->getMessage());
}

$page_title = $lang === 'de' ? "Bestellung bestätigt! — EGOZ Boutique Esotérica" : "¡Pedido confirmado! — EGOZ Boutique Esotérica";
$meta_desc  = $lang === 'de' ? "Deine Bestellung wurde bestätigt. Danke für deinen Einkauf bei EGOZ!" : "Tu pedido ha sido confirmado. ¡Gracias por tu compra en EGOZ!";

require_once 'includes/header.php';
?>

<link rel="canonical" href="<?= URL_GRACIAS ?>">

<section class="py-5" style="background:var(--bg-primary);min-height:70vh;display:flex;align-items:center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">

                <?php if ($redirect_status === 'succeeded' || $pedido_guardado): ?>
                <div style="background:var(--bg-secondary);border:1px solid #2d6a4f;border-radius:var(--radius-lg);padding:48px 32px;">
                    <div style="width:80px;height:80px;background:rgba(45,106,79,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
                        <i class="bi bi-check-lg" style="font-size:2.5rem;color:#4ade80;"></i>
                    </div>
                    <h1 style="font-size:1.6rem;color:var(--gold-light);margin-bottom:12px;letter-spacing:2px;"><?= $t['gracias_titulo'] ?></h1>

                    <?php if ($pedido_id): ?>
                    <p style="color:var(--purple);font-size:0.9rem;margin-bottom:8px;">
                        <?= $lang === 'de' ? 'Bestellnummer' : 'Número de pedido' ?>: <strong style="color:var(--gold);">#<?= $pedido_id ?></strong>
                    </p>
                    <?php endif; ?>

                    <?php if ($codigo_ref): ?>
                    <div style="background:var(--bg-card);border:1px solid var(--gold);border-radius:var(--radius-md);padding:16px 24px;margin-bottom:20px;display:inline-block;">
                        <div style="color:var(--purple-muted);font-size:0.75rem;letter-spacing:2px;margin-bottom:6px;"><?= $t['cod_referencia'] ?></div>
                        <div style="color:var(--gold);font-size:1.4rem;font-weight:700;letter-spacing:4px;"><?= $codigo_ref ?></div>
                        <div style="color:var(--text-muted);font-size:0.75rem;margin-top:6px;"><?= $t['cod_guardalo'] ?></div>
                    </div>
                    <?php endif; ?>

                    <p style="color:var(--purple);font-size:0.95rem;line-height:1.7;margin-bottom:28px;">
                        <?= $t['gracias_subtitulo'] ?>
                    </p>

                    <div style="background:var(--bg-card);border-radius:var(--radius-md);padding:20px;margin-bottom:28px;text-align:left;">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <i class="bi bi-whatsapp" style="color:#4ade80;font-size:1.1rem;"></i>
                            <span style="color:var(--purple);font-size:0.9rem;"><?= $lang === 'de' ? 'Edwin kontaktiert dich per WhatsApp' : 'Edwin te contactará por WhatsApp' ?>: <strong style="color:var(--gold-light);">+41 76 713 33 41</strong></span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-envelope" style="color:var(--gold);font-size:1.1rem;"></i>
                            <span style="color:var(--purple);font-size:0.9rem;"><?= $lang === 'de' ? 'Oder per E-Mail' : 'O por email' ?>: <strong style="color:var(--gold-light);">info@egoz.ch</strong></span>
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="<?= URL_INICIO ?>" class="btn-gold"><i class="bi bi-house me-2"></i><?= $t['ir_inicio'] ?></a>
                        <a href="<?= URL_TIENDA ?>" class="btn-outline-gold"><i class="bi bi-bag me-2"></i><?= $t['seguir_comprando'] ?></a>
                    </div>
                </div>

                <?php else: ?>
                <div style="background:var(--bg-secondary);border:1px solid #6b2737;border-radius:var(--radius-lg);padding:48px 32px;">
                    <div style="width:80px;height:80px;background:rgba(107,39,55,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
                        <i class="bi bi-x-lg" style="font-size:2.5rem;color:#f87171;"></i>
                    </div>
                    <h1 style="font-size:1.6rem;color:#f87171;margin-bottom:12px;letter-spacing:2px;"><?= $t['pago_fallido'] ?></h1>
                    <p style="color:var(--purple);font-size:0.95rem;line-height:1.7;margin-bottom:28px;"><?= $t['pago_fallido_sub'] ?></p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="<?= URL_CHECKOUT ?>" class="btn-gold"><i class="bi bi-arrow-left me-2"></i><?= $t['intentar_nuevo'] ?></a>
                        <a href="https://wa.me/41767133341" target="_blank" class="btn-outline-gold"><i class="bi bi-whatsapp me-2"></i><?= $t['contactar_edwin'] ?></a>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>