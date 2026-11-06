<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
$db = getDB();
$exito = ''; $error = '';

// Iconos disponibles para categorías esotéricas
$iconos_disponibles = [
    'bi-circle-fill'     => 'Círculo (Pulseras)',
    'bi-brightness-high' => 'Sol (Velas)',
    'bi-stars'           => 'Estrellas (Zodíaco)',
    'bi-feather'         => 'Pluma (Arcángeles)',
    'bi-droplet'         => 'Gota (Baños)',
    'bi-card-text'       => 'Cartas (Lectura)',
    'bi-mortarboard'     => 'Birrete (Cursos)',
    'bi-gem'             => 'Gema (Cristales)',
    'bi-moon-fill'       => 'Luna',
    'bi-sun'             => 'Sol',
    'bi-eye'             => 'Ojo',
    'bi-heart-fill'      => 'Corazón',
    'bi-shield-fill'     => 'Escudo',
    'bi-flower1'         => 'Flor',
    'bi-tree-fill'       => 'Árbol',
    'bi-fire'            => 'Fuego',
    'bi-water'           => 'Agua',
    'bi-wind'            => 'Viento',
    'bi-lightning-fill'  => 'Rayo',
    'bi-infinity'        => 'Infinito',
    'bi-yin-yang'        => 'Yin Yang',
    'bi-puzzle-fill'     => 'Puzzle',
    'bi-bookmark-star'   => 'Estrella favorita',
    'bi-magic'           => 'Magia',
    'bi-peace'           => 'Paz',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $nombre = sanitize($_POST['nombre'] ?? '');
    $icono  = sanitize($_POST['icono'] ?? 'bi-gem');
    if (!$nombre) { $error = 'El nombre es obligatorio.'; }
    else {
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', strtolower(trim($nombre)))), '-');
        $stmt = $db->prepare("SELECT id FROM categorias WHERE slug = ?"); $stmt->execute([$slug]);
        if ($stmt->fetch()) { $slug .= '-' . time(); }
        try {
            $db->prepare("INSERT INTO categorias (nombre, slug, descripcion) VALUES (?, ?, ?)")->execute([$nombre, $slug, $icono]);
            $exito = "Categoría '$nombre' creada correctamente.";
        } catch (\Exception $e) { $error = 'Error al crear la categoría.'; }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id     = (int)$_POST['cat_id'];
    $nombre = sanitize($_POST['nombre_edit'] ?? '');
    $icono  = sanitize($_POST['icono_edit'] ?? 'bi-gem');
    if ($id && $nombre) {
        $db->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?")->execute([$nombre, $icono, $id]);
        $exito = 'Categoría actualizada.';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $id = (int)$_POST['eliminar_id'];
    $stmt = $db->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = ? AND activo = 1"); $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) { $error = 'No puedes eliminar una categoría con productos activos.'; }
    else { $db->prepare("DELETE FROM categorias WHERE id = ?")->execute([$id]); $exito = 'Categoría eliminada.'; }
}

$categorias = $db->query("SELECT c.*, COUNT(p.id) as total_productos FROM categorias c LEFT JOIN productos p ON p.categoria_id = c.id AND p.activo = 1 GROUP BY c.id ORDER BY c.nombre ASC")->fetchAll();
?>
<!DOCTYPE html><html lang="es"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías — EGOZ Admin</title><meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <style>
    body{padding-top:0;}.admin-layout{display:flex;min-height:100vh;}
    .admin-offcanvas-link{display:flex;align-items:center;gap:12px;padding:14px 24px;color:var(--purple);text-decoration:none;font-size:0.95rem;border-left:3px solid transparent;transition:var(--transition);}.admin-offcanvas-link:hover{color:var(--gold);background:rgba(201,169,110,0.05);border-left-color:var(--gold);}
    .icon-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(60px,1fr));gap:8px;margin-top:8px;}
    .icon-option{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:10px 6px;border:2px solid var(--border);border-radius:var(--radius-md);cursor:pointer;transition:var(--transition);font-size:0.6rem;color:var(--purple);gap:4px;}
    .icon-option:hover{border-color:var(--gold);color:var(--gold);}
    .icon-option.selected{border-color:var(--gold);background:rgba(201,169,110,0.1);color:var(--gold);}
    .icon-option i{font-size:1.3rem;}
    </style>
</head><body>
<div class="admin-layout">
<aside class="admin-sidebar d-none d-lg-block">
    <div class="text-center px-4 py-4" style="border-bottom:1px solid var(--border);">
        <svg width="52" height="52" viewBox="0 0 64 64"><circle cx="32" cy="32" r="28" fill="#1a0a2e" stroke="#3a1f6e" stroke-width="1.5"/><circle cx="32" cy="32" r="23" fill="none" stroke="#c9a96e" stroke-width="0.6" stroke-dasharray="3,2"/><text x="32" y="28" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">EG</text><line x1="18" y1="32" x2="46" y2="32" stroke="#c9a96e" stroke-width="0.8"/><text x="32" y="44" text-anchor="middle" font-size="11" font-weight="700" fill="#c9a96e" font-family="Georgia,serif">OZ</text><circle cx="32" cy="32" r="2.5" fill="#c9a96e"/></svg>
        <div style="color:var(--gold);font-size:0.8rem;letter-spacing:2px;margin-top:8px;">PANEL ADMIN</div>
        <div style="color:var(--purple-muted);font-size:0.75rem;margin-top:2px;"><?= sanitize($_SESSION['admin_nombre']) ?></div>
    </div>
    <nav class="mt-3">
        <a href="dashboard.php" class="admin-nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="pedidos.php" class="admin-nav-link"><i class="bi bi-bag-check"></i> Pedidos <span class="notif-badge ms-auto badge-pedidos" style="display:none;">0</span></a>
        <a href="productos.php" class="admin-nav-link"><i class="bi bi-grid"></i> Productos</a>
        <a href="mensajes.php" class="admin-nav-link"><i class="bi bi-envelope"></i> Mensajes <span class="notif-badge ms-auto badge-mensajes" style="display:none;">0</span></a>
        <a href="resenas.php" class="admin-nav-link"><i class="bi bi-star"></i> Reseñas <span class="notif-badge ms-auto badge-resenas" style="display:none;">0</span></a>
        <a href="categorias.php" class="admin-nav-link active"><i class="bi bi-tags"></i> Categorías</a>
        <div style="border-top:1px solid var(--border);margin:16px 24px;"></div>
        <a href="<?= URL_INICIO ?>" target="_blank" class="admin-nav-link"><i class="bi bi-box-arrow-up-right"></i> Ver web</a>
        <a href="logout.php" class="admin-nav-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a>
    </nav>
</aside>
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="adminMenu" style="background:var(--bg-secondary);border-right:1px solid var(--border);width:280px;z-index:1055;">
    <div class="offcanvas-header" style="border-bottom:1px solid var(--border);padding:16px 20px;">
        <div style="color:var(--gold);font-weight:700;letter-spacing:2px;font-size:0.9rem;">EGOZ ADMIN</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0"><nav class="mt-2">
        <a href="dashboard.php" class="admin-offcanvas-link"><i class="bi bi-speedometer2 fs-5"></i> Dashboard</a>
        <a href="pedidos.php" class="admin-offcanvas-link"><i class="bi bi-bag-check fs-5"></i> Pedidos <span class="notif-badge badge-pedidos" style="display:none;">0</span></a>
        <a href="productos.php" class="admin-offcanvas-link"><i class="bi bi-grid fs-5"></i> Productos</a>
        <a href="mensajes.php" class="admin-offcanvas-link"><i class="bi bi-envelope fs-5"></i> Mensajes <span class="notif-badge badge-mensajes" style="display:none;">0</span></a>
        <a href="resenas.php" class="admin-offcanvas-link"><i class="bi bi-star fs-5"></i> Reseñas <span class="notif-badge badge-resenas" style="display:none;">0</span></a>
        <a href="categorias.php" class="admin-offcanvas-link" style="color:var(--gold);border-left-color:var(--gold);"><i class="bi bi-tags fs-5"></i> Categorías</a>
        <div style="border-top:1px solid var(--border);margin:12px 20px;"></div>
        <a href="<?= URL_INICIO ?>" target="_blank" class="admin-offcanvas-link"><i class="bi bi-box-arrow-up-right fs-5"></i> Ver web</a>
        <a href="logout.php" class="admin-offcanvas-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left fs-5"></i> Cerrar sesión</a>
    </nav></div>
</div>
<main class="admin-content" style="flex:1;">
    <div class="d-lg-none d-flex align-items-center justify-content-between p-3" style="background:var(--bg-secondary);border-bottom:1px solid var(--border);">
        <button class="border-0" style="background:none;color:var(--gold);font-size:1.3rem;cursor:pointer;" data-bs-toggle="offcanvas" data-bs-target="#adminMenu"><i class="bi bi-list"></i></button>
        <span style="color:var(--gold);font-weight:600;">Categorías</span>
        <span style="width:28px;"></span>
    </div>
    <div class="px-3 px-lg-0 py-3 py-lg-0">
        <h1 style="font-size:1.4rem;color:var(--gold-light);margin-bottom:24px;">Categorías</h1>
        <?php if ($exito): ?><div style="background:rgba(45,106,79,0.2);border:1px solid #2d6a4f;border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:20px;color:#4ade80;font-size:0.85rem;"><i class="bi bi-check-circle me-2"></i><?= $exito ?></div><?php endif; ?>
        <?php if ($error): ?><div style="background:rgba(248,113,113,0.1);border:1px solid #6b2737;border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:20px;color:#f87171;font-size:0.85rem;"><i class="bi bi-exclamation-triangle me-2"></i><?= $error ?></div><?php endif; ?>
        <div class="row g-4">
            <!-- Nueva categoría -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:16px;">✦ NUEVA CATEGORÍA</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">NOMBRE</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Cristales" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ICONO</label>
                            <input type="hidden" name="icono" id="icono_nuevo" value="bi-gem">
                            <div class="icon-grid" id="grid-nuevo">
                                <?php foreach ($iconos_disponibles as $icon => $label): ?>
                                <div class="icon-option <?= $icon === 'bi-gem' ? 'selected' : '' ?>" onclick="seleccionarIcono('nuevo', '<?= $icon ?>')" title="<?= $label ?>">
                                    <i class="bi <?= $icon ?>"></i>
                                    <span><?= explode(' ', $label)[0] ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div style="color:var(--text-muted);font-size:0.78rem;margin-bottom:14px;">El slug se genera automáticamente del nombre.</div>
                        <button type="submit" name="crear" class="btn-gold" style="width:100%;padding:10px;"><i class="bi bi-plus-circle me-2"></i>Crear categoría</button>
                    </form>
                </div>
            </div>

            <!-- Lista categorías -->
            <div class="col-lg-8">
                <div class="admin-card">
                    <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:16px;">✦ CATEGORÍAS <span style="color:var(--purple);font-size:0.85rem;">(<?= count($categorias) ?>)</span></h3>
                    <?php foreach ($categorias as $cat):
                        $icono_actual = !empty($cat['descripcion']) && str_starts_with($cat['descripcion'], 'bi-') ? $cat['descripcion'] : 'bi-gem';
                    ?>
                    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:14px;margin-bottom:10px;">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div style="width:36px;height:36px;background:var(--bg-secondary);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="bi <?= $icono_actual ?>" style="color:var(--gold);font-size:1.1rem;"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="color:var(--gold-light);font-size:0.9rem;font-weight:600;"><?= sanitize($cat['nombre']) ?></div>
                                <div style="color:var(--text-muted);font-size:0.75rem;"><?= sanitize($cat['slug']) ?> · <?= $cat['total_productos'] ?> producto<?= $cat['total_productos'] != 1 ? 's' : '' ?></div>
                            </div>
                            <button onclick="toggleEdit(<?= $cat['id'] ?>)" class="btn-purple" style="font-size:0.78rem;padding:5px 12px;"><i class="bi bi-pencil"></i></button>
                            <?php if ($cat['total_productos'] == 0): ?>
                            <form method="POST" onsubmit="return confirm('¿Eliminar categoría?')" style="display:inline;">
                                <input type="hidden" name="eliminar_id" value="<?= $cat['id'] ?>">
                                <button type="submit" style="background:rgba(107,39,55,0.3);border:1px solid #6b2737;color:#f87171;border-radius:var(--radius-md);padding:5px 12px;font-size:0.78rem;cursor:pointer;"><i class="bi bi-trash3"></i></button>
                            </form>
                            <?php else: ?>
                            <button disabled style="background:var(--bg-dark);border:1px solid var(--border);color:var(--border);border-radius:var(--radius-md);padding:5px 12px;font-size:0.78rem;cursor:not-allowed;" title="Tiene productos activos"><i class="bi bi-trash3"></i></button>
                            <?php endif; ?>
                        </div>
                        <div id="edit-<?= $cat['id'] ?>" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
                            <form method="POST">
                                <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                                <input type="hidden" name="icono_edit" id="icono_edit_<?= $cat['id'] ?>" value="<?= $icono_actual ?>">
                                <div class="d-flex gap-2 mb-3">
                                    <input type="text" name="nombre_edit" class="form-control form-control-sm" value="<?= sanitize($cat['nombre']) ?>" required style="flex:1;">
                                    <button type="submit" name="editar" class="btn-gold" style="padding:6px 16px;font-size:0.82rem;white-space:nowrap;">Guardar</button>
                                </div>
                                <label class="form-label" style="font-size:0.78rem;">CAMBIAR ICONO</label>
                                <div class="icon-grid" id="grid-<?= $cat['id'] ?>">
                                    <?php foreach ($iconos_disponibles as $icon => $label): ?>
                                    <div class="icon-option <?= $icon === $icono_actual ? 'selected' : '' ?>" onclick="seleccionarIcono('edit_<?= $cat['id'] ?>', '<?= $icon ?>')" title="<?= $label ?>">
                                        <i class="bi <?= $icon ?>"></i>
                                        <span><?= explode(' ', $label)[0] ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>
</div>
<script>
function toggleEdit(id){const el=document.getElementById('edit-'+id);el.style.display=el.style.display==='none'?'block':'none';}

function seleccionarIcono(prefijo, icono) {
    document.getElementById('icono_' + prefijo).value = icono;
    document.querySelectorAll('#grid-' + (prefijo === 'nuevo' ? 'nuevo' : prefijo.replace('edit_','')) + ' .icon-option').forEach(el => el.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

function actualizarBadges(){fetch('<?= SITE_URL ?>/admin/api_notificaciones.php').then(r=>r.json()).then(data=>{document.querySelectorAll('.badge-pedidos').forEach(b=>{b.textContent=data.pedidos;b.style.display=data.pedidos>0?'inline-flex':'none';});document.querySelectorAll('.badge-mensajes').forEach(b=>{b.textContent=data.mensajes;b.style.display=data.mensajes>0?'inline-flex':'none';});document.querySelectorAll('.badge-resenas').forEach(b=>{b.textContent=data.resenas;b.style.display=data.resenas>0?'inline-flex':'none';});}).catch(()=>{});}
actualizarBadges();setInterval(actualizarBadges,15000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>