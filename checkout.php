<?php
/** @var array $t */
/** @var string $lang */
require_once 'config.php';
require_once 'vendor/autoload.php';

$carrito = $_SESSION['carrito'] ?? [];
if (empty($carrito)) { redirect(URL_CARRITO); }

$total = 0;
foreach ($carrito as $item) { $total += $item['precio'] * $item['cantidad']; }
$total = round($total, 2);

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
try {
    $intent = \Stripe\PaymentIntent::create([
        'amount'   => round($total * 100),
        'currency' => STRIPE_CURRENCY,
        'automatic_payment_methods' => ['enabled' => true],
        'metadata' => ['productos' => implode(', ', array_column($carrito, 'nombre'))]
    ]);
    $client_secret = $intent->client_secret;
} catch (\Exception $e) {
    $client_secret = null;
    $stripe_error  = $e->getMessage();
}

$page_title = $lang === 'de' ? "Zur Kasse — EGOZ Boutique Esotérica" : "Checkout — EGOZ Boutique Esotérica";
$meta_desc  = $lang === 'de' ? "Schließe deinen Kauf sicher mit Stripe ab." : "Finaliza tu compra de forma segura con Stripe.";

require_once 'includes/header.php';
?>

<link rel="canonical" href="<?= URL_CHECKOUT ?>">

<section style="background:var(--bg-secondary);padding:28px 0;border-bottom:1px solid var(--border);text-align:center;">
    <div class="container">
        <h1 style="font-size:1.6rem;letter-spacing:3px;margin-bottom:4px;"><?= $t['checkout_titulo'] ?></h1>
        <p style="color:var(--purple);font-size:0.9rem;">
            <i class="bi bi-lock-fill me-1" style="color:var(--gold);"></i><?= $t['pago_seguro'] ?>
        </p>
    </div>
</section>

<section class="py-5" style="background:var(--bg-primary);">
    <div class="container">

        <?php if (!$client_secret): ?>
        <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle" style="font-size:3rem;color:#f87171;display:block;margin-bottom:16px;"></i>
            <h3 style="color:#f87171;"><?= $lang === 'de' ? 'Fehler beim Starten der Zahlung' : 'Error al iniciar el pago' ?></h3>
            <p style="color:var(--purple);"><?= sanitize($stripe_error ?? 'Error desconocido') ?></p>
            <a href="<?= URL_CARRITO ?>" class="btn-outline-gold mt-3 d-inline-block"><?= $t['volver_tienda'] ?></a>
        </div>

        <?php else: ?>
        <div class="row g-4">

            <!-- Formulario -->
            <div class="col-lg-7">
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:2px;margin-bottom:24px;"><?= $t['datos_contacto'] ?></h3>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label"><?= $t['nombre_completo'] ?> *</label>
                            <input type="text" id="nombre" class="form-control" placeholder="<?= $lang === 'de' ? 'Dein vollständiger Name' : 'Tu nombre completo' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $t['email'] ?> *</label>
                            <input type="email" id="email" class="form-control" placeholder="tu@email.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?= $t['telefono'] ?></label>
                            <input type="tel" id="telefono" class="form-control" placeholder="+41 76 000 00 00">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?= $t['direccion'] ?> *</label>
                            <input type="text" id="direccion" class="form-control" placeholder="<?= $lang === 'de' ? 'Strasse, Hausnummer, Stockwerk' : 'Calle, número, piso' ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?= $t['codigo_postal'] ?> *</label>
                            <input type="text" id="codigo_postal" class="form-control" placeholder="8001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?= $t['ciudad'] ?></label>
                            <input type="text" class="form-control" value="Zürich" readonly style="opacity:0.6;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?= $t['pais'] ?></label>
                            <input type="text" class="form-control" value="<?= $lang === 'de' ? 'Schweiz' : 'Suiza' ?>" readonly style="opacity:0.6;">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?= $t['notas'] ?> (<?= $t['opcional'] ?>)</label>
                            <textarea id="notas" class="form-control" rows="2" placeholder="<?= $t['notas_placeholder'] ?>"></textarea>
                        </div>
                    </div>

                    <!-- Recogida -->
                    <div style="background:var(--bg-card);border:1px solid var(--gold);border-radius:var(--radius-md);padding:16px 20px;margin-bottom:24px;">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-shop" style="color:var(--gold);font-size:1.4rem;"></i>
                            <div>
                                <div style="color:var(--gold-light);font-weight:600;font-size:0.95rem;"><?= $t['recogida'] ?></div>
                                <div style="color:var(--purple);font-size:0.82rem;margin-top:2px;"><?= $t['recogida_info'] ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SELECTOR MÉTODO DE PAGO ═══ -->
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:2px;margin-bottom:16px;">
                        <?= $lang === 'de' ? '✦ ZAHLUNGSMETHODE' : '✦ MÉTODO DE PAGO' ?>
                    </h3>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <button type="button" id="btn-metodo-online" onclick="seleccionarMetodo('online')"
                                    class="metodo-pago-btn selected" style="width:100%;padding:16px 12px;border-radius:var(--radius-md);border:2px solid var(--gold);background:rgba(201,169,110,0.1);color:var(--gold-light);cursor:pointer;text-align:center;transition:var(--transition);">
                                <i class="bi bi-credit-card-fill d-block mb-2" style="font-size:1.4rem;"></i>
                                <span style="font-size:0.85rem;font-weight:600;"><?= $lang === 'de' ? 'Online bezahlen' : 'Pagar online' ?></span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" id="btn-metodo-tienda" onclick="seleccionarMetodo('tienda')"
                                    class="metodo-pago-btn" style="width:100%;padding:16px 12px;border-radius:var(--radius-md);border:2px solid var(--border);background:var(--bg-card);color:var(--purple);cursor:pointer;text-align:center;transition:var(--transition);">
                                <i class="bi bi-shop d-block mb-2" style="font-size:1.4rem;"></i>
                                <span style="font-size:0.85rem;font-weight:600;"><?= $lang === 'de' ? 'In der Boutique bezahlen' : 'Pagar en tienda' ?></span>
                            </button>
                        </div>
                    </div>

                    <!-- Bloque pago online (Stripe) -->
                    <div id="bloque-pago-online">
                        <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:2px;margin-bottom:20px;"><?= $t['datos_pago'] ?></h3>
                        <div id="payment-element" style="background:var(--bg-primary);border:1px solid var(--border);border-radius:var(--radius-sm);padding:16px;margin-bottom:20px;"></div>
                    </div>

                    <!-- Bloque pago en tienda -->
                    <div id="bloque-pago-tienda" style="display:none;">
                        <div style="background:rgba(201,169,110,0.08);border:1px solid var(--gold);border-radius:var(--radius-md);padding:18px 20px;margin-bottom:20px;">
                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-info-circle-fill" style="color:var(--gold);font-size:1.2rem;margin-top:2px;"></i>
                                <div>
                                    <div style="color:var(--gold-light);font-weight:600;font-size:0.9rem;margin-bottom:4px;">
                                        <?= $lang === 'de' ? 'Zahlung bei Abholung' : 'Pago al recoger' ?>
                                    </div>
                                    <div style="color:var(--purple);font-size:0.85rem;line-height:1.6;">
                                        <?= $lang === 'de'
                                            ? 'Du bezahlst direkt in der Boutique in Zürich, wenn du deine Bestellung abholst. Edwin wird sich mit dir per WhatsApp in Verbindung setzen, um die Abholung zu koordinieren.'
                                            : 'Pagarás directamente en la boutique de Zúrich cuando recojas tu pedido. Edwin se pondrá en contacto contigo por WhatsApp para coordinar la recogida.' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="payment-message" style="display:none;color:#f87171;font-size:0.85rem;margin-bottom:16px;padding:10px;background:rgba(248,113,113,0.1);border-radius:var(--radius-sm);"></div>

                    <button id="btn-pagar" class="btn-gold" style="width:100%;font-size:1rem;padding:14px;">
                        <i class="bi bi-lock-fill me-2"></i><span id="texto-btn-pagar"><?= sprintf($t['pagar_btn'], number_format($total, 2)) ?></span>
                    </button>
                    <p id="ssl-info-text" style="color:var(--text-muted);font-size:0.75rem;text-align:center;margin-top:12px;">
                        <i class="bi bi-shield-check me-1"></i><?= $t['ssl_info'] ?>
                    </p>
                </div>
            </div>

            <!-- Resumen -->
            <div class="col-lg-5">
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;position:sticky;top:100px;">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:2px;margin-bottom:20px;"><?= $t['resumen'] ?></h3>
                    <?php foreach ($carrito as $item): ?>
                    <div class="d-flex justify-content-between align-items-start mb-3 pb-3" style="border-bottom:1px solid var(--border);">
                        <div style="flex:1;">
                            <div style="color:var(--gold-light);font-size:0.9rem;font-weight:500;"><?= sanitize($item['nombre']) ?></div>
                            <div style="color:var(--purple);font-size:0.8rem;"><?= $t['cantidad'] ?>: <?= $item['cantidad'] ?></div>
                        </div>
                        <div style="color:var(--gold);font-weight:600;font-size:0.9rem;white-space:nowrap;margin-left:12px;"><?= number_format($item['precio'] * $item['cantidad'], 2) ?> CHF</div>
                    </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between mb-2 mt-3">
                        <span style="color:var(--purple);"><?= $t['subtotal'] ?></span>
                        <span style="color:var(--gold-light);"><?= number_format($total, 2) ?> CHF</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span style="color:var(--purple);"><?= $lang === 'de' ? 'Abholung' : 'Recogida' ?></span>
                        <span style="color:#4ade80;font-size:0.85rem;"><?= $t['gratis'] ?> · Zürich</span>
                    </div>
                    <div style="border-top:1px solid var(--border);padding-top:16px;">
                        <div class="d-flex justify-content-between">
                            <span style="color:var(--gold-light);font-weight:600;font-size:1.1rem;"><?= $t['total'] ?></span>
                            <span style="color:var(--gold);font-weight:700;font-size:1.3rem;"><?= number_format($total, 2) ?> CHF</span>
                        </div>
                    </div>
                    <div style="border-top:1px solid var(--border);margin-top:20px;padding-top:16px;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-credit-card" style="color:var(--gold);font-size:0.85rem;"></i>
                            <span style="color:var(--purple-muted);font-size:0.8rem;">Visa, Mastercard, Apple Pay, Google Pay</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shop" style="color:var(--gold);font-size:0.85rem;"></i>
                            <span style="color:var(--purple-muted);font-size:0.8rem;"><?= $t['recogida'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe       = Stripe('<?= STRIPE_PUBLIC_KEY ?>');
const clientSecret = '<?= $client_secret ?? '' ?>';
const TOTAL_TXT     = '<?= sprintf($t['pagar_btn'], number_format($total, 2)) ?>';
const TOTAL_NUM     = '<?= number_format($total, 2) ?>';
let metodoActual    = 'online';
let elements;

if (clientSecret) {
    elements = stripe.elements({ clientSecret });
    const paymentEl = elements.create('payment', {
        layout: 'tabs',
        appearance: { theme:'night', variables:{ colorPrimary:'#c9a96e', colorBackground:'#0f0720', colorText:'#e8d5a3', colorDanger:'#f87171', borderRadius:'6px' } }
    });
    paymentEl.mount('#payment-element');
}

function seleccionarMetodo(metodo) {
    metodoActual = metodo;
    const btnOnline = document.getElementById('btn-metodo-online');
    const btnTienda = document.getElementById('btn-metodo-tienda');
    const bloqueOnline = document.getElementById('bloque-pago-online');
    const bloqueTienda = document.getElementById('bloque-pago-tienda');
    const textoBtnPagar = document.getElementById('texto-btn-pagar');
    const sslInfo = document.getElementById('ssl-info-text');

    if (metodo === 'online') {
        btnOnline.style.borderColor = 'var(--gold)';
        btnOnline.style.background = 'rgba(201,169,110,0.1)';
        btnOnline.style.color = 'var(--gold-light)';
        btnTienda.style.borderColor = 'var(--border)';
        btnTienda.style.background = 'var(--bg-card)';
        btnTienda.style.color = 'var(--purple)';
        bloqueOnline.style.display = 'block';
        bloqueTienda.style.display = 'none';
        textoBtnPagar.textContent = TOTAL_TXT;
        sslInfo.style.display = 'block';
    } else {
        btnTienda.style.borderColor = 'var(--gold)';
        btnTienda.style.background = 'rgba(201,169,110,0.1)';
        btnTienda.style.color = 'var(--gold-light)';
        btnOnline.style.borderColor = 'var(--border)';
        btnOnline.style.background = 'var(--bg-card)';
        btnOnline.style.color = 'var(--purple)';
        bloqueOnline.style.display = 'none';
        bloqueTienda.style.display = 'block';
        textoBtnPagar.textContent = '<?= $lang === "de" ? "Bestellung aufgeben" : "Confirmar pedido" ?>';
        sslInfo.style.display = 'none';
    }
}

document.getElementById('btn-pagar').addEventListener('click', async () => {
    const btn = document.getElementById('btn-pagar');
    const msg = document.getElementById('payment-message');
    const nombre        = document.getElementById('nombre').value.trim();
    const email         = document.getElementById('email').value.trim();
    const telefono      = document.getElementById('telefono').value.trim();
    const direccion     = document.getElementById('direccion').value.trim();
    const codigo_postal = document.getElementById('codigo_postal').value.trim();
    const notas         = document.getElementById('notas').value.trim();

    if (!nombre || !email || !direccion || !codigo_postal) {
        msg.textContent = '<?= $t['error_campos'] ?>';
        msg.style.display = 'block';
        return;
    }

    btn.disabled = true;
    msg.style.display = 'none';

    if (metodoActual === 'tienda') {
        // ── PAGO EN TIENDA: no usa Stripe ──
        btn.querySelector('span') ? btn.querySelector('span').textContent = '<?= $lang === "de" ? "Wird gesendet..." : "Enviando..." ?>' : null;

        try {
            const resp = await fetch('<?= SITE_URL ?>/includes/pedido_tienda.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre, email, telefono, ciudad:'Zürich', direccion, codigo_postal, notas, csrf:'<?= generateCSRF() ?>' })
            });
            const data = await resp.json();
            if (data.success) {
                window.location.href = '<?= URL_GRACIAS ?>?pedido_tienda=1&ref=' + encodeURIComponent(data.codigo_referencia);
            } else {
                msg.textContent = data.error || '<?= $lang === "de" ? "Fehler beim Senden der Bestellung." : "Error al enviar el pedido." ?>';
                msg.style.display = 'block';
                btn.disabled = false;
                document.getElementById('texto-btn-pagar').textContent = '<?= $lang === "de" ? "Bestellung aufgeben" : "Confirmar pedido" ?>';
            }
        } catch (e) {
            msg.textContent = '<?= $lang === "de" ? "Verbindungsfehler." : "Error de conexión." ?>';
            msg.style.display = 'block';
            btn.disabled = false;
        }
        return;
    }

    // ── PAGO ONLINE: Stripe ──
    document.getElementById('texto-btn-pagar').textContent = '<?= $t['procesando'] ?>';

    await fetch('<?= SITE_URL ?>/includes/guardar_cliente.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre, email, telefono, ciudad:'Zürich', direccion, codigo_postal, notas, csrf:'<?= generateCSRF() ?>' })
    });

    const { error } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: '<?= URL_GRACIAS ?>',
            payment_method_data: { billing_details: { name: nombre, email: email } }
        }
    });

    if (error) {
        msg.textContent = error.message;
        msg.style.display = 'block';
        btn.disabled = false;
        document.getElementById('texto-btn-pagar').textContent = TOTAL_TXT;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>