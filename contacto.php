<?php
/** @var array $t */
/** @var string $lang */
require_once 'config.php';

$exito = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = sanitize($_POST['nombre']  ?? '');
    $email   = sanitize($_POST['email']   ?? '');
    $asunto  = sanitize($_POST['asunto']  ?? '');
    $mensaje = sanitize($_POST['mensaje'] ?? '');

    if (!$nombre || !$email || !$mensaje) {
        $error = $t['error_campos_ob'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = $t['error_email_inv'];
    } elseif (strlen($mensaje) < 10) {
        $error = $t['error_comentario'];
    } else {
        $db = getDB();
        $db->prepare("INSERT INTO contacto_mensajes (nombre, email, asunto, mensaje) VALUES (?, ?, ?, ?)")
           ->execute([$nombre, $email, $asunto, $mensaje]);

        crearNotificacion('mensaje', '¡Nuevo mensaje de ' . $nombre . '!', $email, null);

        require_once 'includes/email_helper.php';
        $html_edwin = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:0;background:#0a0418;font-family:Arial,sans-serif;'>
        <div style='max-width:600px;margin:0 auto;padding:20px;'>
            <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:28px;text-align:center;margin-bottom:20px;'>
                <div style='color:#c9a96e;font-size:1.4rem;font-weight:700;letter-spacing:3px;'>EGOZ</div>
                <div style='color:#5a4a8f;font-size:0.8rem;letter-spacing:2px;'>BOUTIQUE ESOTÉRICA</div>
            </div>
            <div style='background:#1d4e89;border-radius:10px;padding:16px;text-align:center;margin-bottom:20px;'>
                <div style='color:#93c5fd;font-size:1.1rem;font-weight:700;'>✉️ NUEVO MENSAJE DE CONTACTO</div>
            </div>
            <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:24px;margin-bottom:20px;'>
                <table style='width:100%;border-collapse:collapse;'>
                    <tr><td style='color:#5a4a8f;padding:6px 0;font-size:0.85rem;width:80px;'>Nombre:</td><td style='color:#e8d5a3;font-size:0.85rem;'>$nombre</td></tr>
                    <tr><td style='color:#5a4a8f;padding:6px 0;font-size:0.85rem;'>Email:</td><td style='color:#e8d5a3;font-size:0.85rem;'>$email</td></tr>
                    <tr><td style='color:#5a4a8f;padding:6px 0;font-size:0.85rem;'>Asunto:</td><td style='color:#e8d5a3;font-size:0.85rem;'>" . ($asunto ?: '—') . "</td></tr>
                </table>
            </div>
            <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:24px;margin-bottom:20px;'>
                <div style='color:#c9a96e;font-size:0.8rem;letter-spacing:2px;margin-bottom:12px;'>✦ MENSAJE</div>
                <p style='color:#9a8abf;font-size:0.9rem;line-height:1.7;margin:0;'>$mensaje</p>
            </div>
            <div style='text-align:center;margin-bottom:20px;'>
                <a href='mailto:$email?subject=Re: $asunto' style='background:#c9a96e;color:#0f0720;padding:12px 28px;border-radius:20px;text-decoration:none;font-weight:600;'>Responder →</a>
            </div>
            <div style='text-align:center;color:#4a3a6f;font-size:0.75rem;'>EGOZ Boutique Esotérica · Suiza</div>
        </div></body></html>";

        enviarEmail(MAIL_FROM, 'Edwin — EGOZ', "Nuevo mensaje de $nombre — EGOZ", $html_edwin);
        $exito = true;
    }
}

$page_title = $lang === 'de' ? "Kontakt — EGOZ Boutique Esotérica" : "Contacto — EGOZ Boutique Esotérica";
$meta_desc  = $lang === 'de' ? "Kontaktiere Edwin von EGOZ Boutique Esotérica. WhatsApp, E-Mail und Instagram. Abholung in Zürich." : "Contacta con Edwin de EGOZ Boutique Esotérica. WhatsApp, email e Instagram. Recogida en Zúrich.";
$canonical  = URL_CONTACTO;

require_once 'includes/header.php';
?>

<link rel="canonical" href="<?= $canonical ?>">

<section style="background:linear-gradient(180deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);padding:60px 0 40px;text-align:center;border-bottom:1px solid var(--border);">
    <div class="container">
        <h1 style="font-size:1.8rem;letter-spacing:3px;margin-bottom:10px;"><?= $t['contacto_titulo'] ?></h1>
        <p style="color:var(--purple);font-size:1rem;max-width:500px;margin:0 auto;"><?= $t['contacto_sub'] ?></p>
    </div>
</section>

<section class="py-5" style="background:var(--bg-primary);">
    <div class="container">
        <div class="row g-4 justify-content-center">

            <!-- WhatsApp -->
            <div class="col-12 col-md-8 col-lg-4">
                <a href="https://wa.me/41767133341" target="_blank" rel="noopener noreferrer"
                   style="display:block;background:var(--green-wa-dark);border:1px solid #2a5a4e;border-radius:var(--radius-lg);padding:24px;text-decoration:none;transition:var(--transition);"
                   onmouseover="this.style.transform='translateY(-4px)';" onmouseout="this.style.transform='translateY(0)';">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-whatsapp" style="font-size:2.2rem;color:var(--green-wa);flex-shrink:0;"></i>
                        <div>
                            <div style="color:var(--green-wa);font-weight:600;margin-bottom:2px;">WhatsApp</div>
                            <div style="color:#a0d8b0;font-size:0.9rem;">+41 76 713 33 41</div>
                            <div style="color:#2a6a4e;font-size:0.8rem;"><?= $t['wp_respuesta'] ?></div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Email -->
            <div class="col-12 col-md-8 col-lg-4">
                <a href="mailto:info@egoz.ch"
                   style="display:block;background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;text-decoration:none;transition:var(--transition);"
                   onmouseover="this.style.borderColor='var(--gold)';this.style.transform='translateY(-4px)';" onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)';">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-envelope-fill" style="font-size:2.2rem;color:var(--gold);flex-shrink:0;"></i>
                        <div>
                            <div style="color:var(--gold-light);font-weight:600;margin-bottom:2px;">Email</div>
                            <div style="color:var(--purple);font-size:0.9rem;">info@egoz.ch</div>
                            <div style="color:var(--text-muted);font-size:0.8rem;"><?= $t['email_respuesta'] ?></div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Instagram -->
            <div class="col-12 col-md-8 col-lg-4">
                <a href="https://www.instagram.com/egoz1984" target="_blank" rel="noopener noreferrer"
                   style="display:block;background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;text-decoration:none;transition:var(--transition);"
                   onmouseover="this.style.borderColor='var(--gold)';this.style.transform='translateY(-4px)';" onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)';">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-instagram" style="font-size:2.2rem;color:var(--gold);flex-shrink:0;"></i>
                        <div>
                            <div style="color:var(--gold-light);font-weight:600;margin-bottom:2px;">Instagram</div>
                            <div style="color:var(--purple);font-size:0.9rem;">@egoz1984</div>
                            <div style="color:var(--text-muted);font-size:0.8rem;"><?= $lang === 'de' ? 'Neuigkeiten und Produkte' : 'Novedades y productos' ?></div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Formulario -->
            <div class="col-12 col-lg-8 mt-2">
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:2px;margin-bottom:24px;"><?= $t['enviar_mensaje'] ?></h3>

                    <?php if ($exito): ?>
                    <div style="background:rgba(45,106,79,0.2);border:1px solid #2d6a4f;border-radius:var(--radius-sm);padding:16px;text-align:center;color:#4ade80;">
                        <i class="bi bi-check-circle me-2"></i><?= $t['mensaje_enviado'] ?>
                    </div>
                    <?php else: ?>
                    <?php if ($error): ?>
                    <div style="background:rgba(248,113,113,0.1);border:1px solid #6b2737;border-radius:var(--radius-sm);padding:12px;margin-bottom:20px;color:#f87171;font-size:0.85rem;"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= $t['nombre_label'] ?> *</label>
                                <input type="text" name="nombre" class="form-control" value="<?= sanitize($_POST['nombre'] ?? '') ?>" placeholder="<?= $lang === 'de' ? 'Dein Name' : 'Tu nombre' ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= $t['email'] ?> *</label>
                                <input type="email" name="email" class="form-control" value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="tu@email.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><?= $t['asunto_label'] ?></label>
                                <input type="text" name="asunto" class="form-control" value="<?= sanitize($_POST['asunto'] ?? '') ?>" placeholder="<?= $t['asunto_placeholder'] ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label"><?= $t['mensaje_label'] ?> *</label>
                                <textarea name="mensaje" class="form-control" rows="5" placeholder="<?= $t['mensaje_placeholder'] ?>" required><?= sanitize($_POST['mensaje'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn-gold" style="width:100%;padding:14px;font-size:1rem;">
                                    <i class="bi bi-send me-2"></i><?= $t['enviar_btn'] ?>
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Horario -->
            <div class="col-12 col-lg-8">
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;text-align:center;">
                    <p class="section-title mb-2"><?= $t['horario_titulo'] ?></p>
                    <p style="color:var(--purple);font-size:0.95rem;line-height:1.8;margin-bottom:0;">
                        <?= $t['horario_texto'] ?><br>
                        <span style="color:var(--text-muted);font-size:0.85rem;"><?= $t['horario_idiomas'] ?></span>
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="py-5 text-center" style="background:var(--bg-secondary);border-top:1px solid var(--border);">
    <div class="container">
        <h2 class="mb-3"><?= $lang === 'de' ? 'Bereit für dein nächstes Armband?' : '¿Listo para tu próxima pulsera?' ?></h2>
        <p style="color:var(--purple);margin-bottom:28px;"><?= $lang === 'de' ? 'Entdecke unseren Shop und finde dein perfektes Stück' : 'Explora nuestra tienda y encuentra tu pieza perfecta' ?></p>
        <a href="<?= URL_TIENDA ?>" class="btn-gold me-3"><i class="bi bi-bag me-2"></i><?= $t['ver_tienda'] ?></a>
        <a href="https://wa.me/41767133341" target="_blank" rel="noopener noreferrer" class="btn-outline-gold"><i class="bi bi-whatsapp me-2"></i><?= $t['escribeme_ahora'] ?></a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>