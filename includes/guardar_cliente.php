<?php
// =============================================
// EGOZ — Boutique Esotérica
// includes/guardar_cliente.php
// =============================================
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { exit; }

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { exit; }

if (empty($data['csrf']) || !hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $data['csrf'])) {
    echo json_encode(['success' => false]);
    exit;
}

// Guardar en sesión hasta confirmar pago
$_SESSION['cliente_checkout'] = [
    'nombre'        => sanitize($data['nombre']        ?? ''),
    'email'         => sanitize($data['email']         ?? ''),
    'telefono'      => sanitize($data['telefono']      ?? ''),
    'ciudad'        => sanitize($data['ciudad']        ?? ''),
    'direccion'     => sanitize($data['direccion']     ?? ''),
    'codigo_postal' => sanitize($data['codigo_postal'] ?? ''),
    'notas'         => sanitize($data['notas']         ?? ''),
];

echo json_encode(['success' => true]);