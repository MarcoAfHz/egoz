<?php
// =============================================
// EGOZ — Boutique Esotérica
// includes/pedido_tienda.php
// Guarda un pedido con pago en tienda (sin Stripe)
// =============================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/email_helper.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !validateCSRF($input['csrf'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Token inválido.']);
    exit;
}

$nombre        = sanitize($input['nombre'] ?? '');
$email         = sanitize($input['email'] ?? '');
$telefono      = sanitize($input['telefono'] ?? '');
$direccion     = sanitize($input['direccion'] ?? '');
$ciudad        = sanitize($input['ciudad'] ?? 'Zürich');
$codigo_postal = sanitize($input['codigo_postal'] ?? '');
$notas         = sanitize($input['notas'] ?? '');

if (!$nombre || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$direccion || !$codigo_postal) {
    echo json_encode(['success' => false, 'error' => 'Por favor completa todos los campos obligatorios.']);
    exit;
}

$carrito = $_SESSION['carrito'] ?? [];
if (empty($carrito)) {
    echo json_encode(['success' => false, 'error' => 'El carrito está vacío.']);
    exit;
}

$db = getDB();

try {
    $total = 0;
    foreach ($carrito as $item) { $total += $item['precio'] * $item['cantidad']; }
    $total = round($total, 2);

    // Guardar cliente
    $stmt = $db->prepare("INSERT INTO clientes (nombre, email, telefono, direccion, ciudad, codigo_postal) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $telefono, $direccion, $ciudad, $codigo_postal]);
    $cliente_id = $db->lastInsertId();

    // Código de referencia
    $codigo_ref = 'EGOZ-' . date('Y') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 5));

    // Guardar pedido — estado 'pendiente' porque se paga en tienda
    $stmt = $db->prepare("INSERT INTO pedidos (cliente_id, total, estado, stripe_payment_id, codigo_referencia, tipo_entrega, notas) VALUES (?, ?, 'pendiente', NULL, ?, 'recogida', ?)");
    $stmt->execute([$cliente_id, $total, $codigo_ref, $notas . ' [PAGO EN TIENDA]']);
    $pedido_id = $db->lastInsertId();

    // Guardar items y descontar stock
    $stmt = $db->prepare("INSERT INTO pedido_items (pedido_id, producto_id, nombre_producto, cantidad, precio) VALUES (?, ?, ?, ?, ?)");
    foreach ($carrito as $prod_id => $item) {
        $stmt->execute([$pedido_id, $prod_id, $item['nombre'], $item['cantidad'], $item['precio']]);
        $db->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?")->execute([$item['cantidad'], $prod_id]);
    }

    // Notificación para Edwin
    crearNotificacion('pedido', '¡Nuevo pedido #' . $pedido_id . ' (PAGO EN TIENDA)!', $nombre . ' · ' . number_format($total, 2) . ' CHF', $pedido_id);

    // Emails
    $cliente_data = ['nombre' => $nombre, 'email' => $email, 'telefono' => $telefono, 'direccion' => $direccion, 'ciudad' => $ciudad, 'codigo_postal' => $codigo_postal, 'notas' => $notas];
    $stmt_items = $db->prepare("SELECT * FROM pedido_items WHERE pedido_id = ?");
    $stmt_items->execute([$pedido_id]);
    $items_email = $stmt_items->fetchAll();

    emailNuevoPedido($pedido_id, $cliente_data, $items_email, $total, $codigo_ref);
    emailConfirmacionCliente($pedido_id, $cliente_data, $items_email, $total, $codigo_ref);

    // Vaciar carrito
    $_SESSION['carrito'] = [];
    $_SESSION['cliente_checkout'] = $cliente_data;

    echo json_encode(['success' => true, 'pedido_id' => $pedido_id, 'codigo_referencia' => $codigo_ref]);

} catch (\Exception $e) {
    error_log('EGOZ pedido_tienda error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al procesar el pedido. Inténtalo de nuevo.']);
}