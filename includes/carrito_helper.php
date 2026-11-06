<?php
// =============================================
// EGOZ — Boutique Esotérica
// includes/carrito_helper.php
// Maneja el carrito via AJAX + Sesiones PHP
// =============================================
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Leer JSON del body
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Validar CSRF
if (empty($data['csrf']) || !hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $data['csrf'])) {
    echo json_encode(['success' => false, 'error' => 'Token inválido']);
    exit;
}

$accion = sanitize($data['accion'] ?? '');

// ── INICIALIZAR CARRITO ───────────────────────
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// ── FUNCIONES HELPER ─────────────────────────
function contarItems() {
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['cantidad'];
    }
    return $total;
}

function calcularTotal() {
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    return round($total, 2);
}

function responder($extra = []) {
    echo json_encode(array_merge([
        'success'     => true,
        'total_items' => contarItems(),
        'total_precio'=> calcularTotal(),
        'carrito'     => $_SESSION['carrito']
    ], $extra));
    exit;
}

// ── ACCIONES ─────────────────────────────────
switch ($accion) {

    // ── AÑADIR ───────────────────────────────
    case 'añadir':
        $id       = (int)($data['id'] ?? 0);
        $nombre   = sanitize($data['nombre'] ?? '');
        $precio   = (float)($data['precio'] ?? 0);
        $cantidad = max(1, (int)($data['cantidad'] ?? 1));

        if (!$id || !$nombre || $precio <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos del producto inválidos']);
            exit;
        }

        // Verificar stock en BD
        $db   = getDB();
        $stmt = $db->prepare("SELECT stock, nombre, precio FROM productos WHERE id = ? AND activo = 1");
        $stmt->execute([$id]);
        $prod = $stmt->fetch();

        if (!$prod) {
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
            exit;
        }

        // Calcular cantidad total en carrito
        $en_carrito = $_SESSION['carrito'][$id]['cantidad'] ?? 0;
        if ($en_carrito + $cantidad > $prod['stock']) {
            echo json_encode([
                'success' => false,
                'error'   => 'Stock insuficiente. Solo quedan ' . ($prod['stock'] - $en_carrito) . ' unidades.'
            ]);
            exit;
        }

        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$id] = [
                'id'       => $id,
                'nombre'   => $prod['nombre'],
                'precio'   => (float)$prod['precio'],
                'cantidad' => $cantidad,
            ];
        }

        responder(['mensaje' => $prod['nombre'] . ' añadido al carrito']);
        break;

    // ── ACTUALIZAR CANTIDAD ───────────────────
    case 'actualizar':
        $id       = (int)($data['id'] ?? 0);
        $cantidad = max(0, (int)($data['cantidad'] ?? 0));

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            exit;
        }

        if ($cantidad === 0) {
            unset($_SESSION['carrito'][$id]);
        } else {
            // Verificar stock
            $db   = getDB();
            $stmt = $db->prepare("SELECT stock FROM productos WHERE id = ? AND activo = 1");
            $stmt->execute([$id]);
            $prod = $stmt->fetch();

            if ($prod && $cantidad > $prod['stock']) {
                echo json_encode([
                    'success' => false,
                    'error'   => 'Stock insuficiente. Máximo ' . $prod['stock'] . ' unidades.'
                ]);
                exit;
            }

            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id]['cantidad'] = $cantidad;
            }
        }

        responder();
        break;

    // ── ELIMINAR ─────────────────────────────
    case 'eliminar':
        $id = (int)($data['id'] ?? 0);
        if ($id && isset($_SESSION['carrito'][$id])) {
            unset($_SESSION['carrito'][$id]);
        }
        responder();
        break;

    // ── VACIAR ───────────────────────────────
    case 'vaciar':
        $_SESSION['carrito'] = [];
        responder();
        break;

    // ── OBTENER ──────────────────────────────
    case 'obtener':
        responder();
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
        exit;
}