<?php
/** @var array $t */
/** @var string $lang */
require_once 'config.php';

$page_title = $lang === 'de' ? "Warenkorb — EGOZ Boutique Esotérica" : "Carrito — EGOZ Boutique Esotérica";
$meta_desc  = $lang === 'de' ? "Dein Warenkorb bei EGOZ Boutique Esotérica." : "Tu carrito de compra en EGOZ Boutique Esotérica.";

require_once 'includes/header.php';

$db = getDB();
$carrito = $_SESSION['carrito'] ?? [];
$total = 0;
foreach ($carrito as $item) { $total += $item['precio'] * $item['cantidad']; }
$total = round($total, 2);
$total_items = array_sum(array_column($carrito, 'cantidad'));
?>

<link rel="canonical" href="<?= URL_CARRITO ?>">

<section style="background:var(--bg-secondary);padding:28px 0;border-bottom:1px solid var(--border);text-align:center;">
    <div class="container">
        <h1 style="font-size:1.6rem;letter-spacing:3px;margin-bottom:4px;"><?= $t['carrito_titulo'] ?></h1>
        <p style="color:var(--purple);font-size:0.9rem;">
            <?= $total_items ?> <?= $lang === 'de' ? 'Produkt' . ($total_items !== 1 ? 'e' : '') : 'producto' . ($total_items !== 1 ? 's' : '') ?>
        </p>
    </div>
</section>

<section class="py-5" style="background:var(--bg-primary);">
    <div class="container">

        <?php if (empty($carrito)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bag-x" style="font-size:4rem;color:var(--border);display:block;margin-bottom:20px;"></i>
            <h3 style="color:var(--purple);margin-bottom:12px;"><?= $t['carrito_vacio'] ?></h3>
            <p style="color:var(--text-muted);margin-bottom:28px;"><?= $t['carrito_vacio_sub'] ?></p>
            <a href="<?= URL_TIENDA ?>" class="btn-gold">
                <i class="bi bi-bag me-2"></i><?= $t['ver_tienda'] ?>
            </a>
        </div>

        <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div id="carrito-items">
                    <?php foreach ($carrito as $id => $item):
                        // Buscar nombre en alemán si existe
                        $nombre_mostrar = $item['nombre'];
                        if ($lang === 'de') {
                            $stmt_prod = $db->prepare("SELECT nombre_de FROM productos WHERE id = ?");
                            $stmt_prod->execute([(int)$id]);
                            $prod_de = $stmt_prod->fetch();
                            if (!empty($prod_de['nombre_de'])) $nombre_mostrar = $prod_de['nombre_de'];
                        }
                    ?>
                    <div id="item-<?= (int)$id ?>" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px;">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width:60px;height:60px;background:var(--bg-card);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-bag-heart-fill" style="font-size:1.4rem;color:var(--gold);opacity:0.5;"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="color:var(--gold-light);font-family:var(--font-title);font-size:0.95rem;font-weight:600;margin-bottom:2px;"><?= sanitize($nombre_mostrar) ?></div>
                                <div style="color:var(--purple);font-size:0.85rem;"><?= number_format($item['precio'], 2) ?> CHF / <?= $lang === 'de' ? 'Stk.' : 'ud.' ?></div>
                            </div>
                            <button onclick="eliminarItem(<?= (int)$id ?>)" style="background:none;border:none;color:var(--purple-muted);cursor:pointer;font-size:1.1rem;padding:4px;" aria-label="<?= $t['eliminar_item'] ?>">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center" style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);overflow:hidden;">
                                <button onclick="actualizarCantidad(<?= (int)$id ?>, <?= $item['cantidad'] - 1 ?>)" style="background:none;border:none;color:var(--gold);padding:8px 14px;cursor:pointer;font-size:1rem;">−</button>
                                <span style="color:var(--gold-light);padding:8px 12px;min-width:36px;text-align:center;font-size:0.95rem;"><?= $item['cantidad'] ?></span>
                                <button onclick="actualizarCantidad(<?= (int)$id ?>, <?= $item['cantidad'] + 1 ?>)" style="background:none;border:none;color:var(--gold);padding:8px 14px;cursor:pointer;font-size:1rem;">+</button>
                            </div>
                            <div style="color:var(--gold);font-size:1.1rem;font-weight:700;"><?= number_format($item['precio'] * $item['cantidad'], 2) ?> CHF</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-2">
                    <button onclick="vaciarCarrito()" style="background:none;border:none;color:var(--purple-muted);font-size:0.85rem;cursor:pointer;text-decoration:underline;">
                        <i class="bi bi-trash3 me-1"></i><?= $t['vaciar_carrito'] ?>
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="cart-total-box">
                    <h3 style="color:var(--gold-light);font-size:1.1rem;letter-spacing:2px;margin-bottom:20px;"><?= $t['resumen'] ?></h3>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--purple);"><?= $t['subtotal'] ?></span>
                        <span style="color:var(--gold-light);" id="subtotal"><?= number_format($total, 2) ?> CHF</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--purple);"><?= $lang === 'de' ? 'Versand' : 'Envío' ?></span>
                        <span style="color:#4ade80;font-size:0.85rem;"><?= $lang === 'de' ? 'Abholung in Zürich' : 'Recogida en Zúrich' ?></span>
                    </div>
                    <div style="border-top:1px solid var(--border);margin:16px 0;"></div>
                    <div class="d-flex justify-content-between mb-4">
                        <span style="color:var(--gold-light);font-weight:600;font-size:1.1rem;"><?= $t['total'] ?></span>
                        <span style="color:var(--gold);font-weight:700;font-size:1.3rem;" id="total-precio"><?= number_format($total, 2) ?> CHF</span>
                    </div>
                    <a href="<?= URL_CHECKOUT ?>" class="btn-gold d-block text-center mb-3" style="width:100%;">
                        <i class="bi bi-lock-fill me-2"></i><?= $t['finalizar_compra'] ?>
                    </a>
                    <a href="<?= URL_TIENDA ?>" class="btn-outline-gold d-block text-center" style="width:100%;">
                        <i class="bi bi-arrow-left me-2"></i><?= $t['seguir_comprando'] ?>
                    </a>
                    <div style="border-top:1px solid var(--border);margin-top:20px;padding-top:16px;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-lock-fill" style="color:var(--gold);font-size:0.85rem;"></i>
                            <span style="color:var(--purple-muted);font-size:0.8rem;"><?= $t['pago_seguro'] ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-credit-card" style="color:var(--gold);font-size:0.85rem;"></i>
                            <span style="color:var(--purple-muted);font-size:0.8rem;">Visa, Mastercard, Apple Pay</span>
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

<script>
const CSRF = '<?= generateCSRF() ?>';
const URL_HELPER = '<?= SITE_URL ?>/includes/carrito_helper.php';

function peticion(accion, datos = {}) {
    return fetch(URL_HELPER, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion, csrf: CSRF, ...datos })
    }).then(r => r.json());
}

function actualizarCantidad(id, cantidad) {
    if (cantidad < 0) return;
    peticion('actualizar', { id, cantidad }).then(data => {
        if (data.success) location.reload();
        else alert(data.error);
    });
}

function eliminarItem(id) {
    peticion('eliminar', { id }).then(data => {
        if (data.success) {
            const el = document.getElementById('item-' + id);
            if (el) { el.style.transition = 'opacity 0.3s'; el.style.opacity = '0'; setTimeout(() => location.reload(), 300); }
        }
    });
}

function vaciarCarrito() {
    if (!confirm('<?= $lang === "de" ? "Warenkorb leeren?" : "¿Vaciar el carrito?" ?>')) return;
    peticion('vaciar').then(data => { if (data.success) location.reload(); });
}
</script>

<?php require_once 'includes/footer.php'; ?>