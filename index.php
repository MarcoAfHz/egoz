<?php
/** @var array $t */
/** @var string $lang */
require_once 'config.php';

$page_title = $lang === 'de'
    ? "EGOZ Boutique Esotérica — Handgefertigte Armbänder und dominikanische Spiritualität in der Schweiz"
    : "EGOZ Boutique Esotérica — Pulseras artesanales y espiritualidad dominicana en Suiza";
$meta_desc = $lang === 'de'
    ? "EGOZ Boutique Esotérica in der Schweiz. Handgefertigte Armbänder, Erzengel, Tierkreis, Ritualkerzen und dominikanische Mystik. Abholung in Zürich. Sichere Zahlung."
    : "EGOZ Boutique Esotérica en Suiza. Pulseras artesanales, arcángeles, zodíaco, velas rituales y misticismo dominicano. Recogida en Zúrich. Pagos seguros.";
$meta_keywords = $lang === 'de'
    ? "esoterische Armbänder Schweiz, Boutique Esoterik Schweiz, dominikanische Spiritualität Schweiz, Erzengel Armbänder, Numerologie Schweiz, Mystik Schweiz, Ritualkerzen, EGOZ"
    : "pulseras esotéricas Suiza, boutique esotérica Suiza, espiritualidad dominicana Suiza, arcángeles pulseras, numerología Suiza, misticismo Suiza, velas rituales, EGOZ";
$canonical = SITE_URL . '/';

require_once 'includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.destacado = 1 AND p.activo = 1 ORDER BY p.creado_en DESC LIMIT 4");
$stmt->execute();
$productos_destacados = $stmt->fetchAll();

$stmt_cats = $db->query("SELECT * FROM categorias ORDER BY nombre ASC");
$categorias = $stmt_cats->fetchAll();

$jsonld = [
    "@context" => "https://schema.org", "@type" => "Store",
    "name" => "EGOZ Boutique Esotérica",
    "description" => $lang === 'de' ? "Handgefertigte Armbänder, dominikanische Spiritualität und Mystik. Abholung in Zürich." : "Pulseras artesanales, espiritualidad dominicana y misticismo. Recogida en Zúrich.",
    "url" => SITE_URL, "telephone" => "+41767133341", "email" => "info@egoz.ch",
    "address" => ["@type" => "PostalAddress", "addressCountry" => "CH"],
    "sameAs" => ["https://www.instagram.com/egoz1984"],
    "priceRange" => "CHF", "currenciesAccepted" => "CHF", "paymentAccepted" => "Credit Card, Stripe", "areaServed" => "CH"
];
?>

<script type="application/ld+json"><?= json_encode($jsonld, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?></script>
<meta name="keywords" content="<?= sanitize($meta_keywords) ?>">
<link rel="canonical" href="<?= $canonical ?>">

<!-- HERO -->
<section class="hero" aria-label="<?= $t['hero_titulo'] ?>">
    <div class="hero-grid" aria-hidden="true"></div>
    <div class="container position-relative">
        <svg width="88" height="88" viewBox="0 0 64 64" class="mx-auto d-block mb-4 hero-logo" role="img" aria-label="Logo EGOZ Boutique Esotérica">
            <circle cx="32" cy="32" r="28" fill="#1a0a2e" stroke="#3a1f6e" stroke-width="1.5"/>
            <circle cx="32" cy="32" r="23" fill="none" stroke="#c9a96e" stroke-width="0.6" stroke-dasharray="3,2"/>
            <text x="32" y="28" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">EG</text>
            <line x1="18" y1="32" x2="46" y2="32" stroke="#c9a96e" stroke-width="0.8"/>
            <text x="32" y="44" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">OZ</text>
            <circle cx="32" cy="32" r="2.5" fill="#c9a96e"/>
        </svg>
        <span class="hero-eyebrow"><i class="bi bi-stars"></i> <?= $lang === 'de' ? 'Handgefertigt · Zürich' : 'Hecho a mano · Zúrich' ?></span>
        <h1><?= $t['hero_titulo'] ?></h1>
        <p><?= $t['hero_subtitulo'] ?></p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= URL_TIENDA ?>" class="btn-gold">
                <i class="bi bi-bag me-2"></i><?= $t['ver_tienda'] ?>
            </a>
            <a href="<?= URL_SOBRE_MI ?>" class="btn-outline-gold">
                <?= $t['conoce_edwin'] ?> <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- PRODUCTOS DESTACADOS -->
<section class="section">
    <div class="container">
        <p class="section-title reveal"><?= $t['destacados_titulo'] ?></p>
        <div class="section-divider"></div>
        <?php if (empty($productos_destacados)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bag-heart" style="font-size:3rem;color:var(--purple-deep);display:block;margin-bottom:16px;"></i>
            <p style="color:var(--purple);"><?= $t['proxim_prods'] ?></p>
            <a href="https://wa.me/41767133341" target="_blank" class="btn-outline-gold mt-3 d-inline-block">
                <i class="bi bi-whatsapp me-2"></i><?= $t['consultar_wp'] ?>
            </a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($productos_destacados as $prod): ?>
            <div class="col-6 col-lg-3 reveal">
                <article class="product-card h-100" itemscope itemtype="https://schema.org/Product">
                    <a href="<?= urlProducto($prod['id'], $prod['slug']) ?>" class="text-decoration-none">
                        <div class="product-card-img">
                            <?php if ($prod['imagen']): ?>
                                <img src="<?= SITE_URL ?>/uploads/productos/<?= sanitize($prod['imagen']) ?>"
                                     alt="<?= sanitize($prod['nombre']) ?> — EGOZ Boutique Esotérica"
                                     loading="lazy" width="400" height="300" itemprop="image">
                            <?php else: ?>
                                <i class="bi bi-bag-heart-fill" style="font-size:3rem;color:var(--gold);opacity:0.5;"></i>
                            <?php endif; ?>
                            <?php if ($prod['stock'] == 0): ?>
                                <span class="product-badge agotado"><?= $t['agotado'] ?></span>
                            <?php else: ?>
                                <span class="product-badge"><?= $t['nuevo'] ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-card-body">
                        <div class="product-category" itemprop="category"><?= sanitize($prod['categoria_nombre'] ?? '') ?></div>
                        <h3 class="product-name" itemprop="name">
                            <a href="<?= urlProducto($prod['id'], $prod['slug']) ?>">
                                <?= sanitize($lang === 'de' && !empty($prod['nombre_de']) ? $prod['nombre_de'] : $prod['nombre']) ?>
                            </a>
                        </h3>
                        <p class="product-desc" itemprop="description">
                            <?= sanitize(substr($lang === 'de' && !empty($prod['descripcion_de']) ? $prod['descripcion_de'] : ($prod['descripcion'] ?? ''), 0, 80)) ?>...
                        </p>
                        <div class="product-footer">
                            <span class="product-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                <span itemprop="price" content="<?= $prod['precio'] ?>"><?= number_format($prod['precio'], 2) ?></span>
                                <span itemprop="priceCurrency" content="CHF"> CHF</span>
                                <meta itemprop="availability" content="<?= $prod['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>">
                            </span>
                            <?php if ($prod['stock'] > 0): ?>
                            <button class="btn-purple btn-carrito"
                                    data-id="<?= (int)$prod['id'] ?>"
                                    data-nombre="<?= sanitize($prod['nombre']) ?>"
                                    data-precio="<?= (float)$prod['precio'] ?>">
                                <i class="bi bi-plus me-1"></i><?= $t['añadir_carrito'] ?>
                            </button>
                            <?php else: ?>
                            <button class="btn-purple" disabled style="opacity:0.4;"><?= $t['agotado'] ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="text-center mt-5">
            <a href="<?= URL_TIENDA ?>" class="btn-outline-gold">
                <?= $t['ver_todos'] ?> <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- CATEGORÍAS -->
<section class="section section-alt">
    <div class="container">
        <p class="section-title reveal"><?= $t['categorias_titulo'] ?></p>
        <div class="section-divider"></div>
        <div class="row g-3 justify-content-center">
            <?php
            $cat_icons = ['pulseras'=>'bi-circle-fill','velas'=>'bi-brightness-high','zodiaco'=>'bi-stars','arcangeles'=>'bi-feather','banos-esotericos'=>'bi-droplet','lectura-cartas'=>'bi-card-text'];
            foreach ($categorias as $cat):
                $icon = (!empty($cat['descripcion']) && str_starts_with($cat['descripcion'], 'bi-'))
                    ? $cat['descripcion']
                    : ($cat_icons[$cat['slug']] ?? 'bi-gem');
            ?>
            <div class="col-6 col-md-4 col-lg-2 reveal">
                <a href="<?= URL_TIENDA ?>?categoria=<?= urlencode($cat['slug']) ?>" class="cat-tile">
                    <span class="cat-tile-icon"><i class="bi <?= $icon ?>"></i></span>
                    <span class="cat-tile-name"><?= sanitize($cat['nombre']) ?></span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SOBRE EDWIN -->
<section class="section">
    <div class="container">
        <div class="about-card reveal">
            <div class="row align-items-center g-5 position-relative">
                <div class="col-md-4 text-center">
                    <div class="avatar-ring"><div><i class="bi bi-person-fill"></i></div></div>
                    <h2 style="margin-bottom:2px;font-size:1.8rem;">Edwin</h2>
                    <p style="color:var(--purple-muted);font-size:0.74rem;font-weight:600;letter-spacing:2px;text-transform:uppercase;"><?= $t['artesano'] ?></p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <a href="https://www.instagram.com/egoz1984" target="_blank" rel="noopener noreferrer" class="social-pill" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="https://wa.me/41767133341" target="_blank" rel="noopener noreferrer" class="social-pill" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-md-8">
                    <p class="section-title" style="text-align:left !important;"><?= $t['historia_titulo'] ?></p>
                    <div style="height:20px;"></div>
                    <p class="about-text"><?= $t['historia_p1'] ?></p>
                    <p class="about-text" style="margin-bottom:28px;"><?= $t['historia_p2'] ?></p>
                    <a href="<?= URL_SOBRE_MI ?>" class="btn-outline-gold"><?= $t['conoce_mas'] ?> <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- RESEÑAS -->
<section class="section section-alt">
    <div class="container">
        <p class="section-title reveal"><?= $t['resenas_titulo'] ?></p>
        <div class="section-divider"></div>
        <?php
        $stmt_res = $db->query("SELECT * FROM resenas WHERE aprobada = 1 AND comentario != '' ORDER BY creado_en DESC LIMIT 3");
        $resenas_home = $stmt_res->fetchAll();
        ?>
        <?php if (!empty($resenas_home)): ?>
        <div class="row g-4">
            <?php foreach ($resenas_home as $r): ?>
            <div class="col-md-4 reveal">
                <div class="review-card">
                    <div class="review-stars" aria-label="<?= (int)$r['puntuacion'] ?>/5">
                        <?php for ($i=1;$i<=5;$i++): ?><span class="<?= $i<=$r['puntuacion'] ? '' : 'off' ?>">★</span><?php endfor; ?>
                    </div>
                    <p class="review-text">"<?= sanitize($r['comentario']) ?>"</p>
                    <div class="review-author">
                        <div class="review-avatar"><?= strtoupper(substr($r['cliente_nombre'],0,1)) ?></div>
                        <span><?= sanitize($r['cliente_nombre']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= SITE_URL ?>/resenas" class="btn-outline-gold"><?= $t['ver_resenas'] ?> <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
        <?php else: ?>
        <p style="color:var(--purple);text-align:center;"><?= $lang === 'de' ? 'Noch keine Bewertungen. Sei der Erste!' : 'Aún no hay reseñas. ¡Sé el primero en dejar tu opinión!' ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="section pt-0">
    <div class="container">
        <div class="cta-box reveal">
            <h2 class="mb-3"><?= $t['cta_titulo'] ?></h2>
            <p><?= $t['cta_subtitulo'] ?></p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="<?= URL_TIENDA ?>" class="btn-gold"><i class="bi bi-bag me-2"></i><?= $t['ver_tienda'] ?></a>
                <a href="https://wa.me/41767133341" target="_blank" rel="noopener noreferrer" class="btn-outline-gold"><i class="bi bi-whatsapp me-2"></i><?= $t['escribeme'] ?></a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>