<?php
// =============================================
// EGOZ — Boutique Esotérica
// admin/api_notificaciones.php
// =============================================
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['nuevas' => 0, 'pedidos' => 0, 'mensajes' => 0]);
    exit;
}

$db = getDB();

$nuevas   = $db->query("SELECT COUNT(*) FROM notificaciones WHERE leida = 0")->fetchColumn();
$pedidos = $db->query("SELECT COUNT(*) FROM pedidos WHERE estado IN ('pagado', 'pendiente')")->fetchColumn();
$mensajes = $db->query("SELECT COUNT(*) FROM contacto_mensajes WHERE leido = 0")->fetchColumn();
$resenas  = $db->query("SELECT COUNT(*) FROM resenas WHERE aprobada = 0 AND comentario != ''")->fetchColumn();

echo json_encode([
    'nuevas'  => (int)$nuevas,
    'pedidos' => (int)$pedidos,
    'mensajes'=> (int)$mensajes,
    'resenas' => (int)$resenas,
]);