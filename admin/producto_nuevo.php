<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
$db = getDB();
$categorias = $db->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$error = ''; $exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre'] ?? ''); $descripcion = sanitize($_POST['descripcion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0); $stock = (int)($_POST['stock'] ?? 0);
    $categoria = (int)($_POST['categoria_id'] ?? 0); $destacado = isset($_POST['destacado']) ? 1 : 0;
    $slug = trim(preg_replace('/[^a-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', strtolower(trim($nombre)))), '-');
    if (!$nombre || !$precio || !$categoria) { $error = 'Nombre, precio y categoría son obligatorios.'; }
    else {
        $stmt = $db->prepare("SELECT id FROM productos WHERE slug = ?"); $stmt->execute([$slug]);
        if ($stmt->fetch()) { $slug .= '-' . time(); }
        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) { $error = 'Formato no permitido. Usa JPG, PNG o WEBP.'; }
            elseif ($_FILES['imagen']['size'] > 2*1024*1024) { $error = 'La imagen no puede superar 2MB.'; }
            else { $imagen = $slug.'.'.$ext; if (!move_uploaded_file($_FILES['imagen']['tmp_name'], __DIR__.'/../uploads/productos/'.$imagen)) { $error = 'Error al subir la imagen.'; $imagen = null; } }
        }
        if (!$error) {
            // Traducir con DeepL
            require_once __DIR__ . '/../includes/deepl_helper.php';
            [$nombre_de, $descripcion_de] = traducirProducto($nombre, $descripcion);

            $db->prepare("INSERT INTO productos (nombre,slug,descripcion,nombre_de,descripcion_de,precio,stock,categoria_id,imagen,destacado,activo) VALUES (?,?,?,?,?,?,?,?,?,?,1)")
               ->execute([$nombre,$slug,$descripcion,$nombre_de,$descripcion_de,$precio,$stock,$categoria,$imagen,$destacado]);
            header('Location: producto_editar.php?id='.$db->lastInsertId().'&creado=1'); exit;
        }
    }
}
?>
<!DOCTYPE html><html lang="es"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto — EGOZ Admin</title><meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <style>body{padding-top:0;}.admin-layout{display:flex;min-height:100vh;}.admin-offcanvas-link{display:flex;align-items:center;gap:12px;padding:14px 24px;color:var(--purple);text-decoration:none;font-size:0.95rem;border-left:3px solid transparent;transition:var(--transition);}.admin-offcanvas-link:hover{color:var(--gold);background:rgba(201,169,110,0.05);border-left-color:var(--gold);}</style>
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
        <a href="productos.php" class="admin-nav-link active"><i class="bi bi-grid"></i> Productos</a>
        <a href="mensajes.php" class="admin-nav-link"><i class="bi bi-envelope"></i> Mensajes <span class="notif-badge ms-auto badge-mensajes" style="display:none;">0</span></a>
        <a href="resenas.php" class="admin-nav-link"><i class="bi bi-star"></i> Reseñas <span class="notif-badge ms-auto badge-resenas" style="display:none;">0</span></a>
        <a href="categorias.php" class="admin-nav-link"><i class="bi bi-tags"></i> Categorías</a>
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
        <a href="productos.php" class="admin-offcanvas-link" style="color:var(--gold);border-left-color:var(--gold);"><i class="bi bi-grid fs-5"></i> Productos</a>
        <a href="mensajes.php" class="admin-offcanvas-link"><i class="bi bi-envelope fs-5"></i> Mensajes <span class="notif-badge badge-mensajes" style="display:none;">0</span></a>
        <a href="resenas.php" class="admin-offcanvas-link"><i class="bi bi-star fs-5"></i> Reseñas <span class="notif-badge badge-resenas" style="display:none;">0</span></a>
        <a href="categorias.php" class="admin-offcanvas-link"><i class="bi bi-tags fs-5"></i> Categorías</a>
        <div style="border-top:1px solid var(--border);margin:12px 20px;"></div>
        <a href="<?= URL_INICIO ?>" target="_blank" class="admin-offcanvas-link"><i class="bi bi-box-arrow-up-right fs-5"></i> Ver web</a>
        <a href="logout.php" class="admin-offcanvas-link" style="color:#f87171 !important;"><i class="bi bi-box-arrow-left fs-5"></i> Cerrar sesión</a>
    </nav></div>
</div>
<main class="admin-content" style="flex:1;">
    <div class="d-lg-none d-flex align-items-center justify-content-between p-3" style="background:var(--bg-secondary);border-bottom:1px solid var(--border);">
        <button class="border-0" style="background:none;color:var(--gold);font-size:1.3rem;cursor:pointer;" data-bs-toggle="offcanvas" data-bs-target="#adminMenu"><i class="bi bi-list"></i></button>
        <span style="color:var(--gold);font-weight:600;font-size:0.95rem;">Nuevo Producto</span>
        <a href="productos.php" style="color:var(--purple);font-size:1.1rem;"><i class="bi bi-arrow-left"></i></a>
    </div>
    <div class="px-3 px-lg-0 py-3 py-lg-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="productos.php" style="color:var(--purple);font-size:0.85rem;" class="d-none d-lg-inline"><i class="bi bi-arrow-left me-1"></i>Volver</a>
            <h1 style="font-size:1.4rem;color:var(--gold-light);margin:0;">Nuevo Producto</h1>
        </div>
        <?php if ($error): ?><div style="background:rgba(248,113,113,0.1);border:1px solid #6b2737;border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:20px;color:#f87171;font-size:0.85rem;"><i class="bi bi-exclamation-triangle me-2"></i><?= $error ?></div><?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="admin-card">
                        <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:20px;">✦ INFORMACIÓN</h3>
                        <div class="mb-3"><label class="form-label">NOMBRE *</label><input type="text" name="nombre" class="form-control" value="<?= sanitize($_POST['nombre'] ?? '') ?>" placeholder="Ej: Pulsera Arcángel Miguel" required></div>
                        <div class="mb-3"><label class="form-label">DESCRIPCIÓN</label><textarea name="descripcion" class="form-control" rows="5" placeholder="Describe el producto, sus propiedades y beneficios..."><?= sanitize($_POST['descripcion'] ?? '') ?></textarea></div>
                        <div class="row g-3">
                            <div class="col-6"><label class="form-label">PRECIO (CHF) *</label><input type="number" name="precio" class="form-control" value="<?= $_POST['precio'] ?? '' ?>" step="0.01" min="0" placeholder="28.00" required></div>
                            <div class="col-6"><label class="form-label">STOCK *</label><input type="number" name="stock" class="form-control" value="<?= $_POST['stock'] ?? '0' ?>" min="0" placeholder="10" required></div>
                        </div>
                    </div>
                    <div class="admin-card mt-3">
                        <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:16px;">✦ IMAGEN</h3>
                        <div id="drop-zone" style="border:2px dashed var(--border);border-radius:var(--radius-md);padding:30px;text-align:center;cursor:pointer;" onclick="document.getElementById('imagen').click()" ondragover="event.preventDefault();this.style.borderColor='var(--gold)';" ondragleave="this.style.borderColor='var(--border)';" ondrop="handleDrop(event)">
                            <i class="bi bi-cloud-upload" style="font-size:2rem;color:var(--gold);display:block;margin-bottom:8px;"></i>
                            <div id="drop-text" style="color:var(--purple);font-size:0.85rem;">Arrastra una imagen aquí o haz clic<br><span style="color:var(--text-muted);font-size:0.75rem;">JPG, PNG, WEBP · Máx 2MB</span></div>
                            <img id="preview" src="" style="max-width:200px;max-height:200px;display:none;margin:10px auto 0;border-radius:var(--radius-sm);">
                        </div>
                        <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewImagen(this)">
                    </div>
                    <!-- Info DeepL -->
                    <div class="admin-card mt-3" style="border-color:rgba(201,169,110,0.3);">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-translate" style="color:var(--gold);font-size:1.1rem;"></i>
                            <div>
                                <div style="color:var(--gold-light);font-size:0.85rem;font-weight:600;">Traducción automática al alemán</div>
                                <div style="color:var(--purple);font-size:0.78rem;">Al guardar, DeepL traducirá el nombre y descripción automáticamente.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="admin-card">
                        <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:16px;">✦ CATEGORÍA *</h3>
                        <select name="categoria_id" class="form-select" required>
                            <option value="">Selecciona categoría</option>
                            <?php foreach ($categorias as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($_POST['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['nombre']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-card mt-3">
                        <h3 style="color:var(--gold-light);font-size:1rem;letter-spacing:1px;margin-bottom:16px;">✦ OPCIONES</h3>
                        <div class="d-flex align-items-center gap-3">
                            <input type="checkbox" name="destacado" id="destacado" <?= isset($_POST['destacado']) ? 'checked' : '' ?> style="width:18px;height:18px;cursor:pointer;accent-color:var(--gold);">
                            <label for="destacado" style="color:var(--purple);font-size:0.9rem;cursor:pointer;"><i class="bi bi-star-fill me-1" style="color:var(--gold);"></i>Producto destacado</label>
                        </div>
                        <p style="color:var(--text-muted);font-size:0.78rem;margin-top:8px;margin-bottom:0;">Los destacados aparecen en la página principal.</p>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn-gold d-block text-center" style="width:100%;padding:14px;font-size:1rem;"><i class="bi bi-plus-circle me-2"></i>Crear producto</button>
                        <a href="productos.php" class="btn-outline-gold d-block text-center mt-2" style="width:100%;padding:12px;">Cancelar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
</div>
<script>
function previewImagen(input){if(input.files&&input.files[0]){const r=new FileReader();r.onload=e=>{document.getElementById('preview').src=e.target.result;document.getElementById('preview').style.display='block';document.getElementById('drop-text').style.display='none';};r.readAsDataURL(input.files[0]);}}
function handleDrop(e){e.preventDefault();document.getElementById('drop-zone').style.borderColor='var(--border)';const f=e.dataTransfer.files[0];if(f){const i=document.getElementById('imagen');const dt=new DataTransfer();dt.items.add(f);i.files=dt.files;previewImagen(i);}}
function actualizarBadges(){fetch('<?= SITE_URL ?>/admin/api_notificaciones.php').then(r=>r.json()).then(data=>{document.querySelectorAll('.badge-pedidos').forEach(b=>{b.textContent=data.pedidos;b.style.display=data.pedidos>0?'inline-flex':'none';});document.querySelectorAll('.badge-mensajes').forEach(b=>{b.textContent=data.mensajes;b.style.display=data.mensajes>0?'inline-flex':'none';});document.querySelectorAll('.badge-resenas').forEach(b=>{b.textContent=data.resenas;b.style.display=data.resenas>0?'inline-flex':'none';});}).catch(()=>{});}
actualizarBadges();setInterval(actualizarBadges,15000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>