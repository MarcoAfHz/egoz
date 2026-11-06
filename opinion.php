<?php
/** @var array $t */
/** @var string $lang */
require_once 'config.php';

$token = isset($_GET['token']) ? sanitize($_GET['token']) : '';
if (!$token) { redirect(URL_INICIO); }

$db = getDB();
$stmt = $db->prepare("SELECT r.*, p.id as pedido_id FROM resenas r LEFT JOIN pedidos p ON r.pedido_id = p.id WHERE r.token = ?");
$stmt->execute([$token]);
$resena = $stmt->fetch();
if (!$resena) { redirect(URL_INICIO); }

$ya_opinó = !empty($resena['comentario']) && $resena['puntuacion'] > 0;
$exito = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$ya_opinó) {
    $puntuacion = (int)($_POST['puntuacion'] ?? 0);
    $comentario = sanitize($_POST['comentario'] ?? '');
    $nombre     = sanitize($_POST['nombre'] ?? $resena['cliente_nombre']);

    if ($puntuacion < 1 || $puntuacion > 5) {
        $error = $t['error_puntuacion'];
    } elseif (strlen($comentario) < 10) {
        $error = $t['error_comentario'];
    } else {
        $db->prepare("UPDATE resenas SET puntuacion = ?, comentario = ?, cliente_nombre = ? WHERE token = ?")
           ->execute([$puntuacion, $comentario, $nombre, $token]);
        crearNotificacion('resena', '¡Nueva reseña recibida!', $nombre . ' · ' . $puntuacion . ' estrellas', $resena['pedido_id']);
        $exito = true;
    }
}

$page_title = $lang === 'de' ? "Deine Meinung — EGOZ Boutique Esotérica" : "Deja tu opinión — EGOZ Boutique Esotérica";
$meta_desc  = $lang === 'de' ? "Erzähl uns von deiner Erfahrung mit EGOZ Boutique Esotérica." : "Cuéntanos tu experiencia con EGOZ Boutique Esotérica.";

require_once 'includes/header.php';
?>

<section style="background:var(--bg-secondary);padding:28px 0;border-bottom:1px solid var(--border);text-align:center;">
    <div class="container">
        <h1 style="font-size:1.6rem;letter-spacing:3px;margin-bottom:4px;"><?= $t['opinion_titulo'] ?></h1>
        <p style="color:var(--purple);font-size:0.9rem;"><?= $t['opinion_sub'] ?></p>
    </div>
</section>

<section class="py-5" style="background:var(--bg-primary);min-height:60vh;display:flex;align-items:center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <?php if ($exito): ?>
                <div style="background:var(--bg-secondary);border:1px solid #2d6a4f;border-radius:var(--radius-lg);padding:48px 32px;text-align:center;">
                    <div style="font-size:3rem;margin-bottom:16px;">✨</div>
                    <h2 style="color:var(--gold-light);font-size:1.4rem;margin-bottom:12px;"><?= $t['gracias_opinion'] ?></h2>
                    <p style="color:var(--purple);margin-bottom:24px;"><?= $t['opinion_revisada'] ?></p>
                    <a href="<?= URL_TIENDA ?>" class="btn-gold d-inline-block"><?= $t['seguir_comprando'] ?></a>
                </div>

                <?php elseif ($ya_opinó): ?>
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:48px 32px;text-align:center;">
                    <div style="font-size:3rem;margin-bottom:16px;">⭐</div>
                    <h2 style="color:var(--gold-light);font-size:1.4rem;margin-bottom:12px;"><?= $t['ya_opinaste'] ?></h2>
                    <p style="color:var(--purple);margin-bottom:24px;"><?= $t['gracias_compartir'] ?></p>
                    <a href="<?= URL_INICIO ?>" class="btn-outline-gold d-inline-block"><?= $t['volver_inicio'] ?></a>
                </div>

                <?php else: ?>
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;">
                    <div style="text-align:center;margin-bottom:28px;">
                        <div style="color:var(--gold);font-size:0.8rem;letter-spacing:2px;margin-bottom:8px;"><?= sprintf($t['hola_cliente'], strtoupper(sanitize($resena['cliente_nombre']))) ?></div>
                        <h2 style="color:var(--gold-light);font-size:1.3rem;"><?= $t['como_experiencia'] ?></h2>
                    </div>

                    <?php if ($error): ?>
                    <div style="background:rgba(248,113,113,0.1);border:1px solid #6b2737;border-radius:var(--radius-sm);padding:12px;margin-bottom:20px;color:#f87171;font-size:0.85rem;"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4" style="text-align:center;">
                            <label class="form-label d-block mb-3"><?= $t['puntuacion'] ?> *</label>
                            <div class="d-flex justify-content-center gap-2" id="estrellas">
                                <?php for ($i=1;$i<=5;$i++): ?>
                                <label style="cursor:pointer;font-size:2.2rem;color:var(--border);transition:color 0.2s;" class="estrella" data-val="<?= $i ?>">
                                    ★<input type="radio" name="puntuacion" value="<?= $i ?>" style="display:none;" required>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= $t['tu_nombre'] ?></label>
                            <input type="text" name="nombre" class="form-control" value="<?= sanitize($resena['cliente_nombre']) ?>" placeholder="<?= $t['como_aparecer'] ?>">
                        </div>
                        <div class="mb-4">
                            <label class="form-label"><?= $t['tu_comentario'] ?> *</label>
                            <textarea name="comentario" class="form-control" rows="5" placeholder="<?= $t['comentario_placeholder'] ?>" required></textarea>
                        </div>
                        <button type="submit" class="btn-gold d-block text-center" style="width:100%;padding:14px;font-size:1rem;">
                            <i class="bi bi-star-fill me-2"></i><?= $t['enviar_opinion'] ?>
                        </button>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<script>
const estrellas = document.querySelectorAll('.estrella');
estrellas.forEach((el, i) => {
    el.addEventListener('mouseover', () => { estrellas.forEach((e, j) => e.style.color = j <= i ? '#c9a96e' : 'var(--border)'); });
    el.addEventListener('mouseout', () => {
        const sel = document.querySelector('input[name="puntuacion"]:checked');
        const val = sel ? parseInt(sel.value) : 0;
        estrellas.forEach((e, j) => e.style.color = j < val ? '#c9a96e' : 'var(--border)');
    });
    el.addEventListener('click', () => {
        el.querySelector('input').checked = true;
        const val = i + 1;
        estrellas.forEach((e, j) => e.style.color = j < val ? '#c9a96e' : 'var(--border)');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>