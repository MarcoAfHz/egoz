
<?php
$t    = $GLOBALS['t']    ?? [];
$lang = $GLOBALS['lang'] ?? 'es';
?>
<!-- WHATSAPP FLOTANTE -->
<a href="https://wa.me/41767133341" target="_blank" class="whatsapp-float" title="Escríbeme por WhatsApp" aria-label="Contactar por WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5 text-center text-lg-start">
                <div class="footer-brand">EGOZ</div>
                <p class="footer-tagline mx-auto mx-lg-0"><?= $lang === 'de' ? 'Handgefertigte Armbänder, dominikanische Spiritualität und Mystik. Abholung in Zürich.' : 'Pulseras artesanales, espiritualidad dominicana y misticismo. Recogida en Zúrich.' ?></p>
                <div class="d-flex justify-content-center justify-content-lg-start gap-2 mt-3">
                    <a href="https://www.instagram.com/egoz1984" target="_blank" rel="noopener noreferrer" class="social-pill" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://wa.me/41767133341" target="_blank" rel="noopener noreferrer" class="social-pill" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-3 offset-lg-1">
                <div class="footer-heading"><?= $lang === 'de' ? 'Entdecken' : 'Explorar' ?></div>
                <ul class="footer-links">
                    <li><a href="<?= URL_INICIO ?>"><?= $t['nav_inicio'] ?></a></li>
                    <li><a href="<?= URL_TIENDA ?>"><?= $t['nav_tienda'] ?></a></li>
                    <li><a href="<?= URL_SOBRE_MI ?>"><?= $t['nav_sobre_mi'] ?></a></li>
                    <li><a href="<?= URL_CONTACTO ?>"><?= $t['nav_contacto'] ?></a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <div class="footer-heading"><?= $lang === 'de' ? 'Kontakt' : 'Contacto' ?></div>
                <ul class="footer-links">
                    <li><a href="https://wa.me/41767133341" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp me-2"></i>+41 76 713 33 41</a></li>
                    <li><a href="mailto:info@egoz.ch"><i class="bi bi-envelope me-2"></i>info@egoz.ch</a></li>
                    <li><a href="https://www.instagram.com/egoz1984" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram me-2"></i>@egoz1984</a></li>
                    <li><span style="color:var(--purple-muted);font-size:0.88rem;"><i class="bi bi-geo-alt me-2"></i><?= $lang === 'de' ? 'Zürich, Schweiz' : 'Zúrich, Suiza' ?></span></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
            <span class="footer-text"><?= sprintf($t['footer_copy'], date('Y')) ?></span>
            <span class="footer-stripe">
                <i class="bi bi-lock-fill"></i>
                <?= $t['footer_pagos'] ?>
            </span>
        </div>
    </div>
</footer>
<!-- FIN FOOTER -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- JS EGOZ -->
<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/carrito.js"></script>

<!-- PWA Service Worker -->
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= SITE_URL ?>/service-worker.js')
        .then(reg => console.log('PWA registrada'))
        .catch(err => console.log('PWA error:', err));
}
</script>

<!-- JS Carrito global -->
<script>
const CSRF_TOKEN = '<?= generateCSRF() ?>';
const CARRITO_URL = '<?= SITE_URL ?>/includes/carrito_helper.php';

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-carrito').forEach(btn => {
        btn.addEventListener('click', function() {
            const id     = parseInt(this.dataset.id);
            const nombre = this.dataset.nombre;
            const precio = parseFloat(this.dataset.precio);

            fetch(CARRITO_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    accion:   'añadir',
                    id:       id,
                    nombre:   nombre,
                    precio:   precio,
                    cantidad: 1,
                    csrf:     CSRF_TOKEN
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Por esto:
document.querySelectorAll('.cart-count').forEach(b => {
    b.textContent = data.total_items;
});
if (document.querySelectorAll('.cart-count').length === 0) {
    document.querySelectorAll('.cart-btn').forEach(cart => {
        cart.insertAdjacentHTML('beforeend',
            `<span class="cart-count">${data.total_items}</span>`);
    });
}
                    const toast = document.createElement('div');
                    toast.className = 'egoz-toast';
                    toast.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                    toast.append(nombre + ' <?= $lang === "de" ? "zum Warenkorb hinzugefügt" : "añadido al carrito" ?>');
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                } else {
                    alert(data.error);
                }
            })
            .catch(() => alert('Error al añadir al carrito'));
        });
    });
});
</script>

<!-- UI: navbar al hacer scroll + animaciones de entrada -->
<script>
(function () {
    const nav = document.querySelector('.site-header');
    const onScroll = () => nav && nav.classList.toggle('scrolled', window.scrollY > 10);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    const items = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) { items.forEach(el => el.classList.add('in')); return; }
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    items.forEach(el => io.observe(el));
})();
</script>

<?= isset($extra_js) ? $extra_js : '' ?>

</div>
</body>
</html>