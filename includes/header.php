<?php
// =============================================
// EGOZ — Boutique Esotérica
// includes/header.php
// =============================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/seguridad.php';

if (!isset($t)) require_once __DIR__ . '/lang.php';

$pagina_actual = basename($_SERVER['PHP_SELF']);

$carrito_count = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) { $carrito_count += $item['cantidad']; }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($meta_desc) ? sanitize($meta_desc) : ($lang === 'de' ? 'EGOZ Boutique Esotérica — Handgefertigte Armbänder, dominikanische Spiritualität und Mystik. Abholung in Zürich.' : 'EGOZ Boutique Esotérica — Pulseras artesanales, espiritualidad dominicana y misticismo. Recogida en Zúrich.') ?>">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?= isset($page_title) ? sanitize($page_title) : 'EGOZ Boutique Esotérica' ?>">
    <meta property="og:description" content="<?= $lang === 'de' ? 'Handgefertigte Armbänder, dominikanische Spiritualität und Mystik. Abholung in Zürich.' : 'Pulseras artesanales, espiritualidad dominicana y misticismo. Recogida en Zúrich.' ?>">
    <meta property="og:image" content="<?= SITE_URL ?>/img/og-image.png">
    <meta property="og:url" content="<?= SITE_URL ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= $lang === 'de' ? 'de_CH' : 'es_ES' ?>">
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
    <meta name="theme-color" content="#0b0616">
    <link rel="apple-touch-icon" href="<?= SITE_URL ?>/img/icon-192.png">
    <title><?= isset($page_title) ? sanitize($page_title) : 'EGOZ Boutique Esotérica' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <?= isset($extra_css) ? $extra_css : '' ?>
</head>
<body>
<div style="overflow-x:hidden;width:100%;">

<!-- CABECERA: franja + isla flotante -->
<header class="site-header fixed-top">
    <div class="topbar">
        <span><i class="bi bi-geo-alt-fill"></i> <?= $lang === 'de' ? 'Abholung in Zürich' : 'Recogida en Zúrich' ?></span>
        <span class="topbar-dot">·</span>
        <span class="topbar-pay"><i class="bi bi-lock-fill"></i> <?= $lang === 'de' ? 'Sichere Zahlung mit Stripe' : 'Pago seguro con Stripe' ?></span>
    </div>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <div class="nav-island">

                <!-- Logo -->
                <a class="navbar-brand" href="<?= URL_INICIO ?>">
                    <svg width="36" height="36" viewBox="0 0 64 64" role="img" aria-label="Logo EGOZ">
                        <circle cx="32" cy="32" r="28" fill="#1a0a2e" stroke="#3a1f6e" stroke-width="1.5"/>
                        <circle cx="32" cy="32" r="23" fill="none" stroke="#c9a96e" stroke-width="0.6" stroke-dasharray="3,2"/>
                        <text x="32" y="28" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">EG</text>
                        <line x1="18" y1="32" x2="46" y2="32" stroke="#c9a96e" stroke-width="0.8"/>
                        <text x="32" y="44" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">OZ</text>
                        <circle cx="32" cy="32" r="2.5" fill="#c9a96e"/>
                    </svg>
                    <span class="brand-text">EGOZ</span>
                </a>

                <!-- Carrito móvil + Toggle -->
                <div class="d-flex align-items-center gap-1 ms-auto d-lg-none">
                    <a href="<?= URL_CARRITO ?>" class="cart-btn cart-btn-solid" aria-label="<?= $lang === 'de' ? 'Warenkorb' : 'Carrito' ?>">
                        <i class="bi bi-bag"></i>
                        <span class="cart-count"><?= $carrito_count > 0 ? $carrito_count : '' ?></span>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral" aria-label="<?= $lang === 'de' ? 'Menü öffnen' : 'Abrir menú' ?>">
                        <i class="bi bi-list"></i>
                    </button>
                </div>

                <!-- Links desktop -->
                <div class="collapse navbar-collapse" id="navMenu">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= $pagina_actual === 'index.php' ? 'active' : '' ?>" href="<?= URL_INICIO ?>"><?= $t['nav_inicio'] ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $pagina_actual === 'tienda.php' ? 'active' : '' ?>" href="<?= URL_TIENDA ?>"><?= $t['nav_tienda'] ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $pagina_actual === 'sobre-mi.php' ? 'active' : '' ?>" href="<?= URL_SOBRE_MI ?>"><?= $t['nav_sobre_mi'] ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $pagina_actual === 'contacto.php' ? 'active' : '' ?>" href="<?= URL_CONTACTO ?>"><?= $t['nav_contacto'] ?></a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center gap-2">
                        <div class="lang-switcher">
                            <a href="?lang=es" class="<?= $lang === 'es' ? 'active' : '' ?>">ES</a>
                            <a href="?lang=de" class="<?= $lang === 'de' ? 'active' : '' ?>">DE</a>
                        </div>
                        <a href="<?= URL_CARRITO ?>" class="cart-btn cart-cta">
                            <i class="bi bi-bag"></i>
                            <?= $lang === 'de' ? 'Warenkorb' : 'Carrito' ?>
                            <span class="cart-count"><?= $carrito_count > 0 ? $carrito_count : '' ?></span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </nav>
</header>

<!-- MENÚ LATERAL MÓVIL -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="menuLateral">
    <div class="offcanvas-header" style="border-bottom:1px solid var(--border-soft);padding:16px 20px;">
        <svg width="44" height="44" viewBox="0 0 64 64">
            <circle cx="32" cy="32" r="28" fill="#1a0a2e" stroke="#3a1f6e" stroke-width="1.5"/>
            <circle cx="32" cy="32" r="23" fill="none" stroke="#c9a96e" stroke-width="0.6" stroke-dasharray="3,2"/>
            <text x="32" y="28" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">EG</text>
            <line x1="18" y1="32" x2="46" y2="32" stroke="#c9a96e" stroke-width="0.8"/>
            <text x="32" y="44" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">OZ</text>
            <circle cx="32" cy="32" r="2.5" fill="#c9a96e"/>
        </svg>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="<?= $lang === 'de' ? 'Menü schließen' : 'Cerrar menú' ?>"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="list-unstyled mt-2 mb-4">
            <li><a class="offcanvas-link <?= $pagina_actual === 'index.php' ? 'offcanvas-active' : '' ?>" href="<?= URL_INICIO ?>"><i class="bi bi-house me-3"></i><?= $t['nav_inicio'] ?></a></li>
            <li><a class="offcanvas-link <?= $pagina_actual === 'tienda.php' ? 'offcanvas-active' : '' ?>" href="<?= URL_TIENDA ?>"><i class="bi bi-bag me-3"></i><?= $t['nav_tienda'] ?></a></li>
            <li><a class="offcanvas-link <?= $pagina_actual === 'sobre-mi.php' ? 'offcanvas-active' : '' ?>" href="<?= URL_SOBRE_MI ?>"><i class="bi bi-person me-3"></i><?= $t['nav_sobre_mi'] ?></a></li>
            <li><a class="offcanvas-link <?= $pagina_actual === 'contacto.php' ? 'offcanvas-active' : '' ?>" href="<?= URL_CONTACTO ?>"><i class="bi bi-envelope me-3"></i><?= $t['nav_contacto'] ?></a></li>
        </ul>
        <div style="border-top:1px solid var(--border-soft);margin:12px 20px 20px;"></div>
        <div class="px-4 mb-4">
            <div class="lang-switcher">
                <a href="?lang=es" class="<?= $lang === 'es' ? 'active' : '' ?>">ES</a>
                <a href="?lang=de" class="<?= $lang === 'de' ? 'active' : '' ?>">DE</a>
            </div>
        </div>
        <div class="d-flex gap-2 px-4 mt-2">
            <a href="https://www.instagram.com/egoz1984" target="_blank" rel="noopener noreferrer" class="social-pill" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="https://wa.me/41767133341" target="_blank" rel="noopener noreferrer" class="social-pill" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
        </div>
        <div class="px-4 mt-4">
            <p style="color:var(--purple-deep);font-size:0.75rem;line-height:1.6;">
                EGOZ Boutique Esotérica<br><?= $lang === 'de' ? 'Schweiz · Abholung in Zürich' : 'Suiza · Recogida en Zúrich' ?>
            </p>
        </div>
    </div>
</div>
<!-- FIN MENÚ LATERAL -->