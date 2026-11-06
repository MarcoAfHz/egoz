<?php
/** @var array $t */
/** @var string $lang */
require_once 'config.php';

$db = getDB();

$filtro = isset($_GET['estrellas']) ? (int)$_GET['estrellas'] : 0;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina = 12;
$offset = ($pagina - 1) * $por_pagina;

$where = "WHERE aprobada = 1 AND comentario != ''";
$params = [];
if ($filtro >= 1 && $filtro <= 5) { $where .= " AND puntuacion = ?"; $params[] = $filtro; }

$stmt = $db->prepare("SELECT COUNT(*) FROM resenas $where");
$stmt->execute($params); $total = $stmt->fetchColumn();
$total_paginas = ceil($total / $por_pagina);

$params_pag = array_merge($params, [$por_pagina, $offset]);
$stmt = $db->prepare("SELECT * FROM resenas $where ORDER BY creado_en DESC LIMIT ? OFFSET ?");
$stmt->execute($params_pag); $resenas = $stmt->fetchAll();

$stats = $db->query("SELECT AVG(puntuacion) as media, COUNT(*) as total FROM resenas WHERE aprobada = 1 AND comentario != ''")->fetch();
$media = round($stats['media'] ?? 0, 1);
$total_resenas = $stats['total'] ?? 0;

$dist = [];
for ($i = 5; $i >= 1; $i--) {
    $s = $db->prepare("SELECT COUNT(*) FROM resenas WHERE aprobada = 1 AND puntuacion = ?");
    $s->execute([$i]); $dist[$i] = $s->fetchColumn();
}

$page_title = $lang === 'de' ? "Bewertungen — EGOZ Boutique Esotérica" : "Reseñas — EGOZ Boutique Esotérica";
$meta_desc  = $lang === 'de' ? "Lese die Meinungen unserer Kunden über EGOZ Boutique Esotérica." : "Lee las opiniones de nuestros clientes sobre EGOZ Boutique Esotérica.";

require_once 'includes/header.php';
?>

<section style="background:var(--bg-secondary);padding:40px 0;border-bottom:1px solid var(--border);text-align:center;">
    <div class="container">
        <h1 style="font-size:1.6rem;letter-spacing:3px;margin-bottom:8px;"><?= $t['resenas_page_titulo'] ?></h1>
        <p style="color:var(--purple);font-size:0.9rem;"><?= $t['resenas_page_sub'] ?></p>
    </div>
</section>

<section class="py-5" style="background:var(--bg-primary);">
    <div class="container">

        <?php if ($total_resenas > 0): ?>
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;">
                    <div class="row align-items-center g-4">
                        <div class="col-md-4 text-center">
                            <div style="color:var(--gold);font-size:4rem;font-weight:700;line-height:1;"><?= $media ?></div>
                            <div style="color:var(--gold);font-size:1.4rem;margin:8px 0;">
                                <?php for ($i=1;$i<=5;$i++): ?><span style="color:<?= $i<=round($media)?'var(--gold)':'var(--border)' ?>;">★</span><?php endfor; ?>
                            </div>
                            <div style="color:var(--purple);font-size:0.85rem;"><?= $total_resenas ?> <?= $lang === 'de' ? 'Bewertungen' : 'reseñas' ?></div>
                        </div>
                        <div class="col-md-8">
                            <?php for ($i=5;$i>=1;$i--): ?>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <span style="color:var(--gold);font-size:0.85rem;width:60px;"><?= $i ?> ★</span>
                                <div style="flex:1;background:var(--bg-card);border-radius:4px;height:8px;overflow:hidden;">
                                    <div style="background:var(--gold);height:100%;width:<?= $total_resenas>0?round(($dist[$i]/$total_resenas)*100):0 ?>%;transition:width 0.6s;"></div>
                                </div>
                                <span style="color:var(--purple-muted);font-size:0.8rem;width:20px;"><?= $dist[$i] ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
            <a href="resenas.php" class="filter-btn <?= !$filtro?'active':'' ?>" style="font-size:0.8rem;padding:5px 14px;"><?= $t['todas_estrellas'] ?></a>
            <?php for ($i=5;$i>=1;$i--): ?>
            <a href="?estrellas=<?= $i ?>" class="filter-btn <?= $filtro===$i?'active':'' ?>" style="font-size:0.8rem;padding:5px 14px;"><?= $i ?> ★</a>
            <?php endfor; ?>
        </div>

        <?php if (empty($resenas)): ?>
        <div class="text-center py-5">
            <i class="bi bi-star" style="font-size:3rem;color:var(--border);display:block;margin-bottom:16px;"></i>
            <p style="color:var(--purple);"><?= $t['sin_resenas'] ?></p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($resenas as $r): ?>
            <div class="col-md-6 col-lg-4">
                <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;height:100%;">
                    <div style="color:var(--gold);font-size:1.1rem;margin-bottom:12px;">
                        <?php for ($i=1;$i<=5;$i++): ?><span style="color:<?= $i<=$r['puntuacion']?'#c9a96e':'var(--border)' ?>;">★</span><?php endfor; ?>
                    </div>
                    <p style="color:var(--purple);font-size:0.9rem;line-height:1.7;margin-bottom:16px;flex:1;">"<?= sanitize($r['comentario']) ?>"</p>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:36px;height:36px;background:var(--bg-card);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.9rem;color:var(--gold);font-weight:700;flex-shrink:0;"><?= strtoupper(substr($r['cliente_nombre'],0,1)) ?></div>
                        <div>
                            <div style="color:var(--gold-light);font-size:0.85rem;font-weight:600;"><?= sanitize($r['cliente_nombre']) ?></div>
                            <div style="color:var(--text-muted);font-size:0.75rem;"><?= date('d/m/Y', strtotime($r['creado_en'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($total_paginas > 1): ?>
        <nav class="mt-5 d-flex justify-content-center"><ul class="pagination">
            <?php for ($i=1;$i<=$total_paginas;$i++): ?>
            <li class="page-item <?= $i===$pagina?'active':'' ?>"><a class="page-link" href="?estrellas=<?= $filtro ?>&pagina=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul></nav>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>