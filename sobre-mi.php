<?php
/** @var array $t */
/** @var string $lang */
require_once 'config.php';

$page_title = $lang === 'de' ? "Über mich — Edwin · EGOZ Boutique Esotérica" : "Sobre mí — Edwin · EGOZ Boutique Esotérica";
$meta_desc  = $lang === 'de' ? "Lerne Edwin kennen, spirituellen Handwerker und esoterischen Begleiter in der Schweiz." : "Conoce a Edwin, artesano espiritual y guía esotérico en Suiza.";
$canonical  = URL_SOBRE_MI;

require_once 'includes/header.php';

$db = getDB();
$stmt_res = $db->query("SELECT * FROM resenas WHERE aprobada = 1 AND comentario != '' ORDER BY creado_en DESC LIMIT 6");
$resenas = $stmt_res->fetchAll();
?>

<link rel="canonical" href="<?= $canonical ?>">

<!-- HERO -->
<section style="background:linear-gradient(180deg,var(--bg-secondary) 0%,var(--bg-primary) 100%);padding:60px 0 40px;text-align:center;border-bottom:1px solid var(--border);position:relative;overflow:hidden;">
    <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:radial-gradient(ellipse at center,rgba(58,31,110,0.3) 0%,transparent 70%);pointer-events:none;"></div>
    <div class="container position-relative">
        <div style="width:120px;height:120px;border-radius:50%;background:var(--bg-card);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <i class="bi bi-person-fill" style="font-size:3.5rem;color:var(--gold);"></i>
        </div>
        <h1 style="font-size:1.8rem;letter-spacing:3px;margin-bottom:8px;">EDWIN</h1>
        <p style="color:var(--purple-muted);font-size:0.85rem;letter-spacing:3px;margin-bottom:16px;">
            <?= $lang === 'de' ? 'HANDWERKER · SPIRITUELLER BEGLEITER · SCHWEIZ' : 'ARTESANO · GUÍA ESPIRITUAL · SUIZA' ?>
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="https://www.instagram.com/egoz1984" target="_blank" rel="noopener noreferrer" style="color:var(--purple);font-size:1.4rem;"><i class="bi bi-instagram"></i></a>
            <a href="https://wa.me/41767133341" target="_blank" rel="noopener noreferrer" style="color:var(--purple);font-size:1.4rem;"><i class="bi bi-whatsapp"></i></a>
        </div>
    </div>
</section>

<!-- HISTORIA -->
<section class="py-5" style="background:var(--bg-primary);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <p class="section-title" style="text-align:left !important;">
                    <?= $lang === 'de' ? '✦ MEINE GESCHICHTE' : '✦ MI HISTORIA' ?>
                </p>
                <div style="width:40px;height:1px;background:var(--gold);margin-bottom:28px;opacity:0.5;"></div>

                <?php if ($lang === 'de'): ?>
                <p style="color:var(--purple);font-size:1rem;line-height:1.9;margin-bottom:20px;">
                    Seit über 10 Jahren arbeite ich mit Energien, Kristallen und natürlichen Elementen der dominikanischen spirituellen Tradition. Schon als Kind spürte ich eine tiefe Verbindung zur spirituellen Welt und zur heilenden Kraft der Naturelemente.
                </p>
                <p style="color:var(--purple);font-size:1rem;line-height:1.9;margin-bottom:20px;">
                    Heute lebe ich in der Schweiz, wo ich die reiche spirituelle Tradition meiner Heimat mit dem mystischen Wissen verbinde, das ich im Laufe der Jahre gesammelt habe. Jedes Stück, das ich kreiere, trägt eine spezifische Intention und ist mit Liebe, Zweck und positiver Energie aufgeladen.
                </p>
                <p style="color:var(--purple);font-size:1rem;line-height:1.9;margin-bottom:40px;">
                    Meine Mission ist es, Menschen dabei zu helfen, sich mit ihrer inneren Energie zu verbinden, Gleichgewicht zu finden und das anzuziehen, was sie verdienen — sei es Schutz, Liebe, Fülle oder Heilung.
                </p>
                <?php else: ?>
                <p style="color:var(--purple);font-size:1rem;line-height:1.9;margin-bottom:20px;">
                    Llevo más de 10 años trabajando con energías, cristales y elementos naturales de la tradición espiritual dominicana. Desde pequeño sentí una conexión profunda con el mundo espiritual y con el poder sanador de los elementos de la naturaleza.
                </p>
                <p style="color:var(--purple);font-size:1rem;line-height:1.9;margin-bottom:20px;">
                    Hoy vivo en Suiza, donde combino la rica tradición espiritual de mi tierra natal con el conocimiento místico que he ido adquiriendo a lo largo de los años. Cada pieza que creo lleva una intención específica y está cargada de amor, propósito y energía positiva.
                </p>
                <p style="color:var(--purple);font-size:1rem;line-height:1.9;margin-bottom:40px;">
                    Mi misión es ayudar a las personas a conectar con su energía interior, encontrar equilibrio y atraer lo que merecen a sus vidas — ya sea protección, amor, abundancia o sanación.
                </p>
                <?php endif; ?>

                <!-- Valores -->
                <div class="row g-3 mb-5">
                    <?php
                    $valores = $lang === 'de' ? [
                        ['bi-heart-fill',  'Mit Liebe gemacht',       'Jedes Stück ist einzigartig und handgefertigt, mit Hingabe und Fürsorge.'],
                        ['bi-leaf',        'Natürliche Materialien',  'Steine, Kristalle und Naturelemente sorgfältig ausgewählt.'],
                        ['bi-star-fill',   'Spirituelle Intention',   'Jedes Produkt trägt eine spezifische Energie und einen Zweck.'],
                        ['bi-shield-fill', 'Dominikanische Tradition','Verwurzelt in der karibischen Spiritualität und der Ahnenmystik.'],
                    ] : [
                        ['bi-heart-fill',  'Hecho con amor',       'Cada pieza es única y artesanal, creada con dedicación y cariño.'],
                        ['bi-leaf',        'Materiales naturales',  'Piedras, cristales y elementos seleccionados de la naturaleza.'],
                        ['bi-star-fill',   'Intención espiritual',  'Cada producto lleva una energía y propósito específico.'],
                        ['bi-shield-fill', 'Tradición dominicana',  'Raíces en la espiritualidad caribeña y el misticismo ancestral.'],
                    ];
                    foreach ($valores as $v):
                    ?>
                    <div class="col-6 col-md-6">
                        <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;">
                            <i class="bi <?= $v[0] ?>" style="color:var(--gold);font-size:1.4rem;display:block;margin-bottom:10px;"></i>
                            <div style="color:var(--gold-light);font-family:var(--font-title);font-size:0.95rem;font-weight:600;margin-bottom:6px;"><?= $v[1] ?></div>
                            <div style="color:var(--text-muted);font-size:0.85rem;line-height:1.5;"><?= $v[2] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SERVICIOS -->
<section class="py-5" style="background:var(--bg-dark);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
    <div class="container">
        <p class="section-title"><?= $lang === 'de' ? '✦ MEINE LEISTUNGEN ✦' : '✦ LO QUE OFREZCO ✦' ?></p>
        <div class="section-divider"></div>
        <div class="row g-4 justify-content-center">
            <?php
            $servicios = $lang === 'de' ? [
                ['bi-circle-dotted',   'Handgefertigte Armbänder', 'Personalisierte Armbänder nach Erzengel, Sternzeichen oder Monat. Jedes mit seiner spezifischen Energie.'],
                ['bi-brightness-high', 'Ritualkerzen',             'Kerzen mit Kräutern, Ölen und Kristallen für Liebe, Schutz oder Fülle.'],
                ['bi-droplet-fill',    'Esoterische Bäder',        'Zubereitungen für Gesundheit, Liebe und Glück. Tiefe energetische Reinigungsrituale.'],
                ['bi-card-text',       'Kartenlegung',             'Persönliche Tarot-Beratungen. Online per WhatsApp oder Videoanruf.'],
                ['bi-123',             'Numerologie',              'Analyse deiner Lebenszahl, Mission und Lebenszyklen nach deinem Geburtsdatum.'],
                ['bi-telephone-fill',  'Persönliche Beratung',     'Spirituelle Beratung auf Spanisch und Deutsch. Abholung in Zürich.'],
            ] : [
                ['bi-circle-dotted',   'Pulseras Artesanales',  'Pulseras personalizadas por arcángel, signo zodiacal o mes. Cada una con su energía específica.'],
                ['bi-brightness-high', 'Velas Ritualizadas',    'Velas preparadas con hierbas, aceites y cristales para atraer lo que necesitas.'],
                ['bi-droplet-fill',    'Baños Esotéricos',      'Preparaciones para salud, amor y suerte. Rituales de limpieza energética profunda.'],
                ['bi-card-text',       'Lectura de Cartas',     'Consultas personalizadas de tarot. Online por WhatsApp o videollamada.'],
                ['bi-123',             'Numerología',           'Análisis de tu número de vida, misión y ciclos vitales según tu fecha de nacimiento.'],
                ['bi-telephone-fill',  'Atención Personalizada','Consultas y asesoramiento espiritual. Disponible en español y alemán.'],
            ];
            foreach ($servicios as $s):
            ?>
            <div class="col-6 col-md-4 col-lg-4">
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;text-align:center;height:100%;transition:var(--transition);"
                     onmouseover="this.style.borderColor='var(--gold)';this.style.transform='translateY(-4px)';"
                     onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)';">
                    <i class="bi <?= $s[0] ?>" style="color:var(--gold);font-size:2rem;display:block;margin-bottom:14px;"></i>
                    <div style="color:var(--gold-light);font-family:var(--font-title);font-size:0.95rem;font-weight:600;margin-bottom:8px;"><?= $s[1] ?></div>
                    <div style="color:var(--text-muted);font-size:0.85rem;line-height:1.6;"><?= $s[2] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- RESEÑAS DE LA BD -->
<?php if (!empty($resenas)): ?>
<section class="py-5" style="background:var(--bg-primary);">
    <div class="container">
        <p class="section-title"><?= $lang === 'de' ? '✦ WAS MEINE KUNDEN SAGEN ✦' : '✦ LO QUE DICEN MIS CLIENTES ✦' ?></p>
        <div class="section-divider"></div>
        <div class="row g-4">
            <?php foreach ($resenas as $r): ?>
            <div class="col-md-4">
                <blockquote style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;height:100%;">
                    <div style="color:var(--gold);font-size:1rem;margin-bottom:12px;">
                        <?php for ($i=1;$i<=5;$i++): ?><span style="color:<?= $i<=$r['puntuacion']?'#c9a96e':'var(--border)' ?>;">★</span><?php endfor; ?>
                    </div>
                    <p style="color:var(--purple);font-size:0.95rem;line-height:1.7;margin-bottom:16px;font-style:italic;">"<?= sanitize($r['comentario']) ?>"</p>
                    <footer style="color:var(--purple-muted);font-size:0.85rem;">— <?= sanitize($r['cliente_nombre']) ?></footer>
                </blockquote>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= SITE_URL ?>/resenas" class="btn-outline-gold" style="font-size:0.9rem;"><?= $t['ver_resenas'] ?> <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="py-5 text-center" style="background:var(--bg-secondary);border-top:1px solid var(--border);">
    <div class="container">
        <h2 class="mb-3"><?= $lang === 'de' ? 'Möchtest du ein personalisiertes Stück?' : '¿Quieres una pieza personalizada?' ?></h2>
        <p style="color:var(--purple);margin-bottom:28px;"><?= $lang === 'de' ? 'Schreib mir und erzähl mir, was du brauchst' : 'Contáctame y cuéntame lo que necesitas' ?></p>
        <a href="<?= URL_TIENDA ?>" class="btn-gold me-3"><i class="bi bi-bag me-2"></i><?= $t['ver_tienda'] ?></a>
        <a href="https://wa.me/41767133341" target="_blank" rel="noopener noreferrer" class="btn-outline-gold"><i class="bi bi-whatsapp me-2"></i><?= $t['escribeme'] ?></a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>