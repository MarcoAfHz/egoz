<?php
/** @var array $t */
/** @var string $lang */
require_once 'config.php';

$id   = isset($_GET['id'])   ? (int)$_GET['id']       : 0;
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
if (!$id && !$slug) { redirect(URL_TIENDA); }

$db = getDB();
if ($slug) {
    $stmt = $db->prepare("SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.slug = ? AND p.activo = 1");
    $stmt->execute([$slug]);
} else {
    $stmt = $db->prepare("SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ? AND p.activo = 1");
    $stmt->execute([$id]);
}
$producto = $stmt->fetch();
if (!$producto) { redirect(URL_TIENDA); }

$stmt_rel = $db->prepare("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.categoria_id = ? AND p.id != ? AND p.activo = 1 LIMIT 4");
$stmt_rel->execute([$producto['categoria_id'], $producto['id']]);
$relacionados = $stmt_rel->fetchAll();

$page_title = sanitize($producto['nombre']) . ' — EGOZ Boutique Esotérica';
$meta_desc  = sanitize(substr($producto['descripcion'] ?? '', 0, 160));
$canonical  = urlProducto($producto['id'], $producto['slug']);

$jsonld = [
    "@context" => "https://schema.org", "@type" => "Product",
    "name" => $producto['nombre'], "description" => $producto['descripcion'], "url" => $canonical,
    "offers" => ["@type" => "Offer", "price" => $producto['precio'], "priceCurrency" => "CHF",
        "availability" => $producto['stock'] > 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
        "seller" => ["@type" => "Organization", "name" => "EGOZ Boutique Esotérica"]]
];
if ($producto['imagen']) { $jsonld['image'] = SITE_URL . '/uploads/productos/' . $producto['imagen']; }

require_once 'includes/header.php';
?>

<script type="application/ld+json"><?= json_encode($jsonld, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?></script>
<link rel="canonical" href="<?= $canonical ?>">

<!-- BREADCRUMB -->
<div class="breadcrumb-bar" style="background:var(--bg-dark);border-bottom:1px solid var(--border);padding:10px 0;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size:0.8rem;">
                <li class="breadcrumb-item"><a href="<?= URL_INICIO ?>" style="color:var(--purple);"><?= $t['nav_inicio'] ?></a></li>
                <li class="breadcrumb-item"><a href="<?= URL_TIENDA ?>" style="color:var(--purple);"><?= $t['nav_tienda'] ?></a></li>
                <?php if ($producto['categoria_nombre']): ?>
                <li class="breadcrumb-item"><a href="<?= URL_TIENDA ?>?categoria=<?= urlencode($producto['categoria_slug']) ?>" style="color:var(--purple);"><?= sanitize($producto['categoria_nombre']) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" style="color:var(--gold);"><?= sanitize($lang === 'de' && !empty($producto['nombre_de']) ? $producto['nombre_de'] : $producto['nombre']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- PRODUCTO -->
<section class="py-5" style="background:var(--bg-primary);">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-md-5">
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;aspect-ratio:1;display:flex;align-items:center;justify-content:center;">
                    <?php if ($producto['imagen']): ?>
                        <img src="<?= SITE_URL ?>/uploads/productos/<?= sanitize($producto['imagen']) ?>" alt="<?= sanitize($producto['nombre']) ?> — EGOZ" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <i class="bi bi-bag-heart-fill" style="font-size:6rem;color:var(--gold);opacity:0.3;"></i>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-7">
                <?php if ($producto['categoria_nombre']): ?>
                <div style="color:var(--purple-muted);font-size:0.8rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:10px;">
                    <a href="<?= URL_TIENDA ?>?categoria=<?= urlencode($producto['categoria_slug']) ?>" style="color:var(--purple-muted);"><?= sanitize($producto['categoria_nombre']) ?></a>
                </div>
                <?php endif; ?>
                <h1 style="font-size:1.8rem;color:var(--gold-light);margin-bottom:16px;letter-spacing:1px;"><?= sanitize($lang === 'de' && !empty($producto['nombre_de']) ? $producto['nombre_de'] : $producto['nombre']) ?></h1>
                <div style="font-size:2rem;font-weight:700;color:var(--gold);margin-bottom:20px;font-family:var(--font-title);"><?= number_format($producto['precio'], 2) ?> CHF</div>

                <?php if ($producto['stock'] > 0): ?>
                <div style="color:#4ade80;font-size:0.85rem;margin-bottom:20px;">
                    <i class="bi bi-check-circle-fill me-1"></i><?= $t['stock_disponible'] ?> (<?= $producto['stock'] ?> <?= $t['disponibles'] ?>)
                </div>
                <?php else: ?>
                <div style="color:#f87171;font-size:0.85rem;margin-bottom:20px;">
                    <i class="bi bi-x-circle-fill me-1"></i><?= $t['stock_agotado'] ?>
                </div>
                <?php endif; ?>

                <div style="color:var(--purple);font-size:0.95rem;line-height:1.9;margin-bottom:28px;"><?= nl2br(sanitize($lang === 'de' && !empty($producto['descripcion_de']) ? $producto['descripcion_de'] : ($producto['descripcion'] ?? ''))) ?></div>

                <?php if ($producto['stock'] > 0): ?>
                <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                    <div class="d-flex align-items-center" style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-md);overflow:hidden;">
                        <button onclick="cambiarCantidad(-1)" style="background:none;border:none;color:var(--gold);padding:8px 14px;font-size:1.1rem;cursor:pointer;">−</button>
                        <input type="number" id="cantidad" value="1" min="1" max="<?= $producto['stock'] ?>" style="width:50px;background:none;border:none;color:var(--gold-light);text-align:center;font-size:1rem;padding:8px 0;">
                        <button onclick="cambiarCantidad(1)" style="background:none;border:none;color:var(--gold);padding:8px 14px;font-size:1.1rem;cursor:pointer;">+</button>
                    </div>
                    <button class="btn-gold" onclick="añadirAlCarrito(<?= (int)$producto['id'] ?>, '<?= sanitize($producto['nombre']) ?>', <?= (float)$producto['precio'] ?>)">
                        <i class="bi bi-bag-plus me-2"></i><?= $t['añadir_carrito'] ?>
                    </button>
                </div>
                <?php else: ?>
                <div class="mb-4"><button class="btn-gold" disabled style="opacity:0.5;cursor:not-allowed;"><i class="bi bi-bag-x me-2"></i><?= $t['agotado'] ?></button></div>
                <?php endif; ?>

                <a href="https://wa.me/41767133341?text=<?= $lang === 'de' ? 'Hallo%20Edwin%2C%20ich%20interessiere%20mich%20für%20' : 'Hola%20Edwin%2C%20me%20interesa%20' ?><?= urlencode($producto['nombre']) ?>" target="_blank" rel="noopener noreferrer" class="btn-outline-gold d-inline-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-whatsapp"></i><?= $t['consultar_wp'] ?>
                </a>

                <div style="border-top:1px solid var(--border);padding-top:20px;">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <i class="bi bi-shop" style="color:var(--gold);"></i>
                        <span style="color:var(--purple);font-size:0.85rem;"><?= $t['envios_info'] ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <i class="bi bi-lock-fill" style="color:var(--gold);"></i>
                        <span style="color:var(--purple);font-size:0.85rem;"><?= $t['pago_info'] ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-arrow-return-left" style="color:var(--gold);"></i>
                        <span style="color:var(--purple);font-size:0.85rem;"><?= $t['problema_info'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- RELACIONADOS -->
<?php if (!empty($relacionados)): ?>
<section class="py-5" style="background:var(--bg-dark);border-top:1px solid var(--border);">
    <div class="container">
        <p class="section-title"><?= $t['relacionados'] ?></p>
        <div class="section-divider"></div>
        <div class="row g-4">
            <?php foreach ($relacionados as $rel): ?>
            <div class="col-12 col-sm-6 col-md-3">
                <article class="product-card h-100">
                    <a href="<?= urlProducto($rel['id'], $rel['slug']) ?>" class="text-decoration-none">
                        <div class="product-card-img">
                            <?php if ($rel['imagen']): ?>
                                <img src="<?= SITE_URL ?>/uploads/productos/<?= sanitize($rel['imagen']) ?>" alt="<?= sanitize($rel['nombre']) ?>" loading="lazy">
                            <?php else: ?>
                                <i class="bi bi-bag-heart-fill" style="font-size:2.5rem;color:var(--gold);opacity:0.4;"></i>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-card-body">
                        <div class="product-category"><?= sanitize($rel['categoria_nombre'] ?? '') ?></div>
                        <h3 class="product-name"><a href="<?= urlProducto($rel['id'], $rel['slug']) ?>" style="color:var(--gold-light);text-decoration:none;"><?= sanitize($lang === 'de' && !empty($rel['nombre_de']) ? $rel['nombre_de'] : $rel['nombre']) ?></a></h3>
                        <div class="product-footer">
                            <span class="product-price"><?= number_format($rel['precio'], 2) ?> CHF</span>
                            <button class="btn-purple btn-carrito" data-id="<?= (int)$rel['id'] ?>" data-nombre="<?= sanitize($rel['nombre']) ?>" data-precio="<?= (float)$rel['precio'] ?>">
                                <i class="bi bi-plus me-1"></i><?= $t['añadir_carrito'] ?>
                            </button>
                        </div>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- RESEÑAS -->
<?php
$stmt_rp = $db->prepare("SELECT r.* FROM resenas r JOIN pedido_items pi ON pi.pedido_id = r.pedido_id WHERE pi.producto_id = ? AND r.aprobada = 1 AND r.comentario != '' ORDER BY r.creado_en DESC LIMIT 5");
$stmt_rp->execute([$producto['id']]);
$resenas_prod = $stmt_rp->fetchAll();
if (!empty($resenas_prod)):
?>
<section class="py-5" style="background:var(--bg-primary);border-top:1px solid var(--border);">
    <div class="container">
        <p class="section-title">✦ <?= $t['tu_comentario'] ?> ✦</p>
        <div class="section-divider"></div>
        <div class="row g-3">
            <?php foreach ($resenas_prod as $r): ?>
            <div class="col-md-6">
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;">
                    <div style="color:var(--gold);margin-bottom:8px;">
                        <?php for ($i=1;$i<=5;$i++): ?><span style="color:<?= $i<=$r['puntuacion']?'#c9a96e':'var(--border)' ?>;">★</span><?php endfor; ?>
                    </div>
                    <p style="color:var(--purple);font-size:0.9rem;line-height:1.7;margin-bottom:12px;font-style:italic;">"<?= sanitize($r['comentario']) ?>"</p>
                    <span style="color:var(--purple-muted);font-size:0.8rem;">— <?= sanitize($r['cliente_nombre']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
function cambiarCantidad(delta){const input=document.getElementById('cantidad');const max=parseInt(input.max);let val=parseInt(input.value)+delta;if(val<1)val=1;if(val>max)val=max;input.value=val;}
function añadirAlCarrito(id,nombre,precio){
    const cantidad=parseInt(document.getElementById('cantidad').value);
    fetch('<?= SITE_URL ?>/includes/carrito_helper.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({accion:'añadir',id,nombre,precio,cantidad,csrf:'<?= generateCSRF() ?>'})})
    .then(r=>r.json()).then(data=>{
        if(data.success){
            document.querySelectorAll('.cart-count').forEach(b=>{b.textContent=data.total_items;});
            if(document.querySelectorAll('.cart-count').length===0){document.querySelectorAll('.cart-btn').forEach(cart=>{cart.insertAdjacentHTML('beforeend',`<span class="cart-count">${data.total_items}</span>`);});}
            mostrarToast('✦ '+nombre+' <?= $lang === "de" ? "zum Warenkorb hinzugefügt" : "añadido al carrito" ?>');
        }
    }).catch(()=>mostrarToast('<?= $lang === "de" ? "Fehler beim Hinzufügen" : "Error al añadir al carrito" ?>'));
}
function mostrarToast(msg){const toast=document.createElement('div');toast.textContent=msg;toast.style.cssText='position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:var(--gold);color:var(--bg-primary);padding:12px 24px;border-radius:20px;font-size:0.85rem;font-weight:600;z-index:9999;animation:fadeInOut 3s forwards;white-space:nowrap;';document.body.appendChild(toast);setTimeout(()=>toast.remove(),3000);}
</script>
<style>
@keyframes fadeInOut{0%{opacity:0;transform:translateX(-50%) translateY(10px);}15%{opacity:1;transform:translateX(-50%) translateY(0);}85%{opacity:1;}100%{opacity:0;}}
.breadcrumb-item+.breadcrumb-item::before{color:var(--border);}
</style>

<?php require_once 'includes/footer.php'; ?>