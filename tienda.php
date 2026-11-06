<?php
require_once 'config.php';
/** @var array $t */
/** @var string $lang */

$page_title = $lang === 'de'
    ? "Shop — Handgefertigte Armbänder und Esoterik-Produkte in der Schweiz"
    : "Tienda — Pulseras artesanales y productos esotéricos en Suiza";
$meta_desc = $lang === 'de'
    ? "Kaufe handgefertigte Armbänder, Ritualkerzen, esoterische Bäder und mehr. Abholung in Zürich. Sichere Zahlung mit Stripe."
    : "Compra pulseras artesanales, velas rituales, baños esotéricos y más. Recogida en Zúrich. Pagos seguros con Stripe.";
$meta_keywords = $lang === 'de'
    ? "Esoterik Shop Schweiz, Erzengel Armbänder kaufen, Ritualkerzen Schweiz, Tierkreis Armbänder"
    : "tienda esotérica Suiza, comprar pulseras arcángeles, velas rituales Suiza, zodíaco pulseras";
$canonical = URL_TIENDA;

require_once 'includes/header.php';

$db = getDB();
$stmt_cats = $db->query("SELECT * FROM categorias ORDER BY nombre ASC");
$categorias = $stmt_cats->fetchAll();

$categoria_slug = isset($_GET['categoria']) ? sanitize($_GET['categoria']) : '';
$orden          = isset($_GET['orden'])     ? sanitize($_GET['orden'])     : 'destacados';
$pagina         = isset($_GET['pagina'])    ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina     = 12;
$offset         = ($pagina - 1) * $por_pagina;

$where  = "WHERE p.activo = 1"; $params = [];
if ($categoria_slug) { $where .= " AND c.slug = ?"; $params[] = $categoria_slug; }

$order_by = match($orden) {
    'precio_asc'  => "p.precio ASC",
    'precio_desc' => "p.precio DESC",
    'nuevo'       => "p.creado_en DESC",
    default       => "p.destacado DESC, p.creado_en DESC"
};

$stmt_total = $db->prepare("SELECT COUNT(*) FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id $where");
$stmt_total->execute($params); $total_productos = $stmt_total->fetchColumn();
$total_paginas = ceil($total_productos / $por_pagina);

$params_pag = array_merge($params, [$por_pagina, $offset]);
$stmt = $db->prepare("SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id $where ORDER BY $order_by LIMIT ? OFFSET ?");
$stmt->execute($params_pag); $productos = $stmt->fetchAll();

$categoria_nombre = '';
if ($categoria_slug) {
    foreach ($categorias as $cat) {
        if ($cat['slug'] === $categoria_slug) { $categoria_nombre = $cat['nombre']; break; }
    }
}

$opciones_orden = $lang === 'de' ? [
    'destacados'  => 'Empfohlen',
    'nuevo'       => 'Neueste',
    'precio_asc'  => 'Preis: aufsteigend',
    'precio_desc' => 'Preis: absteigend',
] : [
    'destacados'  => 'Destacados',
    'nuevo'       => 'Más recientes',
    'precio_asc'  => 'Precio: menor a mayor',
    'precio_desc' => 'Precio: mayor a menor',
];

// Helper para nombre/descripción según idioma
function prodNombre($prod, $lang) {
    return $lang === 'de' && !empty($prod['nombre_de']) ? $prod['nombre_de'] : $prod['nombre'];
}
function prodDesc($prod, $lang, $len = 75) {
    $desc = $lang === 'de' && !empty($prod['descripcion_de']) ? $prod['descripcion_de'] : ($prod['descripcion'] ?? '');
    return sanitize(substr($desc, 0, $len));
}

$jsonld = ["@context" => "https://schema.org", "@type" => "CollectionPage", "name" => "Shop EGOZ Boutique Esotérica", "url" => URL_TIENDA];
?>

<script type="application/ld+json"><?= json_encode($jsonld, JSON_UNESCAPED_UNICODE) ?></script>
<meta name="keywords" content="<?= sanitize($meta_keywords) ?>">
<link rel="canonical" href="<?= $canonical ?>">

<section class="page-header">
    <div class="container">
        <h1>
            <?= $categoria_nombre ? sanitize($categoria_nombre) : $t['tienda_titulo'] ?>
        </h1>
        <p>
            <?= $total_productos ?> <?= $lang === 'de' ? 'Produkt' . ($total_productos !== 1 ? 'e' : '') . ' verfügbar' : 'producto' . ($total_productos !== 1 ? 's' : '') . ' disponible' . ($total_productos !== 1 ? 's' : '') ?>
        </p>
    </div>
</section>

<section class="filters-bar">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex flex-wrap gap-1">
                <a href="<?= URL_TIENDA ?>" class="filter-btn <?= !$categoria_slug ? 'active' : '' ?>"><?= $t['todas_categorias'] ?></a>
                <?php foreach ($categorias as $cat): ?>
                <a href="<?= URL_TIENDA ?>?categoria=<?= urlencode($cat['slug']) ?><?= $orden !== 'destacados' ? '&orden='.$orden : '' ?>"
                   class="filter-btn <?= $categoria_slug === $cat['slug'] ? 'active' : '' ?>">
                    <?= sanitize($cat['nombre']) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <div>
                <select class="form-select form-select-sm" style="width:auto;display:inline-block;"
                        onchange="window.location='<?= URL_TIENDA ?>?<?= $categoria_slug ? 'categoria='.$categoria_slug.'&' : '' ?>orden='+this.value">
                    <?php foreach ($opciones_orden as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $orden === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if (empty($productos)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bag-x" style="font-size:3rem;color:var(--border);display:block;margin-bottom:16px;"></i>
            <h3 style="color:var(--purple);"><?= $t['sin_productos'] ?></h3>
            <a href="<?= URL_TIENDA ?>" class="btn-outline-gold mt-3 d-inline-block"><?= $t['todas_categorias'] ?></a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($productos as $prod): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <article class="product-card h-100" itemscope itemtype="https://schema.org/Product">
                    <a href="<?= urlProducto($prod['id'], $prod['slug']) ?>" class="text-decoration-none">
                        <div class="product-card-img">
                            <?php if ($prod['imagen']): ?>
                                <img src="<?= SITE_URL ?>/uploads/productos/<?= sanitize($prod['imagen']) ?>"
                                     alt="<?= sanitize(prodNombre($prod, $lang)) ?> — EGOZ"
                                     loading="lazy" itemprop="image">
                            <?php else: ?>
                                <i class="bi bi-bag-heart-fill" style="font-size:3rem;color:var(--gold);opacity:0.4;"></i>
                            <?php endif; ?>
                            <?php if ($prod['stock'] == 0): ?>
                                <span class="product-badge agotado"><?= $t['agotado'] ?></span>
                            <?php elseif ($prod['destacado']): ?>
                                <span class="product-badge"><?= $t['nuevo'] ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-card-body">
                        <div class="product-category" itemprop="category"><?= sanitize($prod['categoria_nombre'] ?? '') ?></div>
                        <h2 class="product-name" itemprop="name">
                            <a href="<?= urlProducto($prod['id'], $prod['slug']) ?>" style="color:var(--gold-light);text-decoration:none;">
                                <?= sanitize(prodNombre($prod, $lang)) ?>
                            </a>
                        </h2>
                        <p class="product-desc" itemprop="description">
                            <?= prodDesc($prod, $lang, 75) ?>...
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

        <?php if ($total_paginas > 1): ?>
        <nav class="mt-5 d-flex justify-content-center">
            <ul class="pagination">
                <?php if ($pagina > 1): ?>
                <li class="page-item"><a class="page-link" href="<?= URL_TIENDA ?>?<?= $categoria_slug ? 'categoria='.$categoria_slug.'&' : '' ?>orden=<?= $orden ?>&pagina=<?= $pagina-1 ?>"><i class="bi bi-chevron-left"></i></a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?= $i === $pagina ? 'active' : '' ?>"><a class="page-link" href="<?= URL_TIENDA ?>?<?= $categoria_slug ? 'categoria='.$categoria_slug.'&' : '' ?>orden=<?= $orden ?>&pagina=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($pagina < $total_paginas): ?>
                <li class="page-item"><a class="page-link" href="<?= URL_TIENDA ?>?<?= $categoria_slug ? 'categoria='.$categoria_slug.'&' : '' ?>orden=<?= $orden ?>&pagina=<?= $pagina+1 ?>"><i class="bi bi-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>