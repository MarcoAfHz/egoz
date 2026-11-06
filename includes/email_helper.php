<?php
// =============================================
// EGOZ — Boutique Esotérica
// includes/email_helper.php
// =============================================
if (!defined('SITE_URL')) require_once __DIR__ . '/../config.php';
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── FUNCIÓN BASE ─────────────────────────────
function enviarEmail($para, $para_nombre, $asunto, $html) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(MAIL_FROM, MAIL_NAME);
        $mail->addAddress($para, $para_nombre);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $html;
        $mail->AltBody = strip_tags($html);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email error: ' . $mail->ErrorInfo);
        return false;
    }
}

// ── CÓDIGO REFERENCIA ─────────────────────────
function htmlCodigoRef($codigo_ref) {
    if (!$codigo_ref) return '';
    return "
    <div style='background:#0f0720; border:1px solid #c9a96e; border-radius:10px; padding:20px; text-align:center; margin-bottom:20px;'>
        <div style='color:#5a4a8f; font-size:0.75rem; letter-spacing:2px; margin-bottom:8px;'>TU CÓDIGO DE REFERENCIA</div>
        <div style='color:#c9a96e; font-size:1.6rem; font-weight:700; letter-spacing:6px;'>$codigo_ref</div>
        <div style='color:#5a4a8f; font-size:0.75rem; margin-top:8px;'>Guárdalo · Edwin te lo pedirá para verificar tu pedido</div>
    </div>";
}

// ── CREAR TOKEN RESEÑA ────────────────────────
function crearTokenResena($pedido_id, $cliente_nombre, $cliente_email) {
    if (!defined('SITE_URL')) return null;
    try {
        $db    = getDB();
        $token = bin2hex(random_bytes(32));
        // Verificar si ya existe reseña para este pedido
        $stmt  = $db->prepare("SELECT id FROM resenas WHERE pedido_id = ?");
        $stmt->execute([$pedido_id]);
        if (!$stmt->fetch()) {
            $db->prepare("INSERT INTO resenas (pedido_id, cliente_nombre, cliente_email, puntuacion, comentario, token) VALUES (?, ?, ?, 0, '', ?)")
               ->execute([$pedido_id, $cliente_nombre, $cliente_email, $token]);
        } else {
            $stmt = $db->prepare("SELECT token FROM resenas WHERE pedido_id = ?");
            $stmt->execute([$pedido_id]);
            $row = $stmt->fetch();
            $token = $row['token'];
        }
        return $token;
    } catch (\Exception $e) {
        return null;
    }
}

// ── EMAIL A EDWIN — NUEVO PEDIDO ─────────────
function emailNuevoPedido($pedido_id, $cliente, $items, $total, $codigo_ref = '') {
    $items_html = '';
    foreach ($items as $item) {
        $subtotal = number_format($item['precio'] * $item['cantidad'], 2);
        $items_html .= "<tr><td style='padding:10px;border-bottom:1px solid #3a1f6e;color:#e8d5a3;'>{$item['nombre_producto']}</td><td style='padding:10px;border-bottom:1px solid #3a1f6e;color:#9a8abf;text-align:center;'>{$item['cantidad']}</td><td style='padding:10px;border-bottom:1px solid #3a1f6e;color:#c9a96e;text-align:right;'>{$subtotal} CHF</td></tr>";
    }
    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#0a0418;font-family:Arial,sans-serif;'>
    <div style='max-width:600px;margin:0 auto;padding:20px;'>
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:28px;text-align:center;margin-bottom:20px;'>
            <div style='color:#c9a96e;font-size:1.4rem;font-weight:700;letter-spacing:3px;'>EGOZ</div>
            <div style='color:#5a4a8f;font-size:0.8rem;letter-spacing:2px;'>BOUTIQUE ESOTÉRICA</div>
        </div>
        <div style='background:#2d6a4f;border-radius:10px;padding:16px;text-align:center;margin-bottom:20px;'>
            <div style='color:#4ade80;font-size:1.1rem;font-weight:700;'>🛍️ ¡NUEVO PEDIDO #$pedido_id!</div>
        </div>
        " . htmlCodigoRef($codigo_ref) . "
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:24px;margin-bottom:20px;'>
            <div style='color:#c9a96e;font-size:0.8rem;letter-spacing:2px;margin-bottom:14px;'>✦ DATOS DEL CLIENTE</div>
            <table style='width:100%;border-collapse:collapse;'>
                <tr><td style='color:#5a4a8f;padding:4px 0;font-size:0.85rem;width:120px;'>Nombre:</td><td style='color:#e8d5a3;font-size:0.85rem;'>{$cliente['nombre']}</td></tr>
                <tr><td style='color:#5a4a8f;padding:4px 0;font-size:0.85rem;'>Email:</td><td style='color:#e8d5a3;font-size:0.85rem;'>{$cliente['email']}</td></tr>
                " . (!empty($cliente['telefono']) ? "<tr><td style='color:#5a4a8f;padding:4px 0;font-size:0.85rem;'>Teléfono:</td><td style='color:#e8d5a3;font-size:0.85rem;'>{$cliente['telefono']}</td></tr>" : "") . "
            </table>
        </div>
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:24px;margin-bottom:20px;'>
            <div style='color:#c9a96e;font-size:0.8rem;letter-spacing:2px;margin-bottom:14px;'>✦ PRODUCTOS</div>
            <table style='width:100%;border-collapse:collapse;'>
                <thead><tr>
                    <th style='color:#5a4a8f;font-size:0.75rem;text-align:left;padding:8px;border-bottom:1px solid #3a1f6e;'>PRODUCTO</th>
                    <th style='color:#5a4a8f;font-size:0.75rem;text-align:center;padding:8px;border-bottom:1px solid #3a1f6e;'>CANT.</th>
                    <th style='color:#5a4a8f;font-size:0.75rem;text-align:right;padding:8px;border-bottom:1px solid #3a1f6e;'>TOTAL</th>
                </tr></thead>
                <tbody>$items_html</tbody>
            </table>
            <div style='text-align:right;padding-top:14px;border-top:1px solid #3a1f6e;margin-top:4px;'>
                <span style='color:#c9a96e;font-size:1.1rem;font-weight:700;'>TOTAL: " . number_format($total, 2) . " CHF</span>
            </div>
        </div>
        <div style='text-align:center;margin-bottom:20px;'>
            <a href='" . URL_ADMIN . "/pedido_detalle.php?id=$pedido_id' style='background:#c9a96e;color:#0f0720;padding:12px 28px;border-radius:20px;text-decoration:none;font-weight:600;'>Ver pedido en el admin →</a>
        </div>
        <div style='text-align:center;color:#4a3a6f;font-size:0.75rem;'>EGOZ Boutique Esotérica · Suiza</div>
    </div></body></html>";
    return enviarEmail(MAIL_FROM, 'Edwin — EGOZ', "¡Nuevo pedido #$pedido_id — EGOZ!", $html);
}

// ── EMAIL AL CLIENTE — CONFIRMACIÓN ──────────
function emailConfirmacionCliente($pedido_id, $cliente, $items, $total, $codigo_ref = '') {
    $items_html = '';
    foreach ($items as $item) {
        $subtotal = number_format($item['precio'] * $item['cantidad'], 2);
        $items_html .= "<tr><td style='padding:10px;border-bottom:1px solid #3a1f6e;color:#e8d5a3;'>{$item['nombre_producto']}</td><td style='padding:10px;border-bottom:1px solid #3a1f6e;color:#9a8abf;text-align:center;'>{$item['cantidad']}</td><td style='padding:10px;border-bottom:1px solid #3a1f6e;color:#c9a96e;text-align:right;'>{$subtotal} CHF</td></tr>";
    }
    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#0a0418;font-family:Arial,sans-serif;'>
    <div style='max-width:600px;margin:0 auto;padding:20px;'>
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:28px;text-align:center;margin-bottom:20px;'>
            <div style='color:#c9a96e;font-size:1.4rem;font-weight:700;letter-spacing:3px;'>EGOZ</div>
            <div style='color:#5a4a8f;font-size:0.8rem;letter-spacing:2px;'>BOUTIQUE ESOTÉRICA</div>
        </div>
        <div style='background:#1a0a2e;border:1px solid #2d6a4f;border-radius:14px;padding:24px;text-align:center;margin-bottom:20px;'>
            <div style='font-size:2rem;margin-bottom:8px;'>✅</div>
            <div style='color:#e8d5a3;font-size:1.1rem;font-weight:700;margin-bottom:6px;'>¡Pedido confirmado!</div>
            <div style='color:#9a8abf;font-size:0.9rem;'>Hola {$cliente['nombre']}, hemos recibido tu pedido #$pedido_id.</div>
        </div>
        " . htmlCodigoRef($codigo_ref) . "
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:24px;margin-bottom:20px;'>
            <div style='color:#c9a96e;font-size:0.8rem;letter-spacing:2px;margin-bottom:14px;'>✦ TU PEDIDO</div>
            <table style='width:100%;border-collapse:collapse;'>
                <thead><tr>
                    <th style='color:#5a4a8f;font-size:0.75rem;text-align:left;padding:8px;border-bottom:1px solid #3a1f6e;'>PRODUCTO</th>
                    <th style='color:#5a4a8f;font-size:0.75rem;text-align:center;padding:8px;border-bottom:1px solid #3a1f6e;'>CANT.</th>
                    <th style='color:#5a4a8f;font-size:0.75rem;text-align:right;padding:8px;border-bottom:1px solid #3a1f6e;'>TOTAL</th>
                </tr></thead>
                <tbody>$items_html</tbody>
            </table>
            <div style='text-align:right;padding-top:14px;border-top:1px solid #3a1f6e;margin-top:4px;'>
                <span style='color:#c9a96e;font-size:1.1rem;font-weight:700;'>TOTAL: " . number_format($total, 2) . " CHF</span>
            </div>
        </div>
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:24px;margin-bottom:20px;'>
            <div style='color:#c9a96e;font-size:0.8rem;letter-spacing:2px;margin-bottom:12px;'>✦ RECOGIDA EN TIENDA</div>
            <p style='color:#9a8abf;font-size:0.9rem;line-height:1.7;margin:0;'>
                Edwin se pondrá en contacto contigo para coordinar la recogida.<br><br>
                📱 WhatsApp: <strong style='color:#e8d5a3;'>+41 76 713 33 41</strong><br>
                📧 Email: <strong style='color:#e8d5a3;'>info@egoz.ch</strong>
            </p>
        </div>
        <div style='text-align:center;color:#4a3a6f;font-size:0.75rem;'>
            EGOZ Boutique Esotérica · Suiza<br>
            <a href='https://www.instagram.com/egoz1984' style='color:#4a3a6f;'>@egoz1984</a>
        </div>
    </div></body></html>";
    return enviarEmail($cliente['email'], $cliente['nombre'], "Pedido #$pedido_id confirmado — EGOZ Boutique Esotérica", $html);
}

// ── EMAIL AL CLIENTE — ENVIADO ────────────────
function emailPedidoEnviado($pedido_id, $cliente, $codigo_ref = '') {
    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#0a0418;font-family:Arial,sans-serif;'>
    <div style='max-width:600px;margin:0 auto;padding:20px;'>
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:28px;text-align:center;margin-bottom:20px;'>
            <div style='color:#c9a96e;font-size:1.4rem;font-weight:700;letter-spacing:3px;'>EGOZ</div>
            <div style='color:#5a4a8f;font-size:0.8rem;letter-spacing:2px;'>BOUTIQUE ESOTÉRICA</div>
        </div>
        <div style='background:#1a0a2e;border:1px solid #1d4e89;border-radius:14px;padding:24px;text-align:center;margin-bottom:20px;'>
            <div style='font-size:2rem;margin-bottom:8px;'>🛍️</div>
            <div style='color:#e8d5a3;font-size:1.1rem;font-weight:700;margin-bottom:6px;'>¡Tu pedido está listo!</div>
            <div style='color:#9a8abf;font-size:0.9rem;'>Hola {$cliente['nombre']}, tu pedido #$pedido_id está listo para recoger.</div>
        </div>
        " . htmlCodigoRef($codigo_ref) . "
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:24px;margin-bottom:20px;'>
            <p style='color:#9a8abf;font-size:0.9rem;line-height:1.7;margin:0;'>
                Contacta con Edwin para coordinar la recogida:<br><br>
                📱 WhatsApp: <strong style='color:#e8d5a3;'>+41 76 713 33 41</strong><br>
                📧 Email: <strong style='color:#e8d5a3;'>info@egoz.ch</strong>
            </p>
        </div>
        <div style='text-align:center;color:#4a3a6f;font-size:0.75rem;'>EGOZ Boutique Esotérica · Suiza</div>
    </div></body></html>";
    return enviarEmail($cliente['email'], $cliente['nombre'], "Tu pedido #$pedido_id está listo — EGOZ", $html);
}

// ── EMAIL AL CLIENTE — ENTREGADO ──────────────
function emailPedidoEntregado($pedido_id, $cliente, $codigo_ref = '') {
    // Crear token para reseña
    $token = crearTokenResena($pedido_id, $cliente['nombre'], $cliente['email']);
    $url_resena = $token ? SITE_URL . '/opinion.php?token=' . $token : '';

    $btn_resena = $url_resena ? "
        <div style='text-align:center;margin-bottom:20px;'>
            <a href='$url_resena' style='background:#c9a96e;color:#0f0720;padding:14px 32px;border-radius:20px;text-decoration:none;font-weight:700;font-size:0.95rem;display:inline-block;'>
                ⭐ Dejar mi opinión
            </a>
        </div>" : '';

    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#0a0418;font-family:Arial,sans-serif;'>
    <div style='max-width:600px;margin:0 auto;padding:20px;'>
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:28px;text-align:center;margin-bottom:20px;'>
            <div style='color:#c9a96e;font-size:1.4rem;font-weight:700;letter-spacing:3px;'>EGOZ</div>
            <div style='color:#5a4a8f;font-size:0.8rem;letter-spacing:2px;'>BOUTIQUE ESOTÉRICA</div>
        </div>
        <div style='background:#1a0a2e;border:1px solid #2d6a4f;border-radius:14px;padding:24px;text-align:center;margin-bottom:20px;'>
            <div style='font-size:2rem;margin-bottom:8px;'>🎉</div>
            <div style='color:#e8d5a3;font-size:1.1rem;font-weight:700;margin-bottom:6px;'>¡Pedido entregado!</div>
            <div style='color:#9a8abf;font-size:0.9rem;'>Hola {$cliente['nombre']}, gracias por confiar en EGOZ.</div>
        </div>
        " . htmlCodigoRef($codigo_ref) . "
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:24px;margin-bottom:20px;text-align:center;'>
            <div style='color:#c9a96e;font-size:0.8rem;letter-spacing:2px;margin-bottom:12px;'>✦ ¿TE GUSTÓ TU PEDIDO?</div>
            <p style='color:#9a8abf;font-size:0.9rem;line-height:1.7;margin-bottom:16px;'>
                Tu opinión nos ayuda a mejorar y a otros clientes a conocernos. ¡Solo tarda un minuto!
            </p>
            $btn_resena
        </div>
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:24px;margin-bottom:20px;'>
            <p style='color:#9a8abf;font-size:0.9rem;line-height:1.7;margin:0;'>
                Si tienes algún problema contacta con Edwin:<br><br>
                📱 WhatsApp: <strong style='color:#e8d5a3;'>+41 76 713 33 41</strong>
            </p>
        </div>
        <div style='text-align:center;margin-bottom:20px;'>
            <a href='" . SITE_URL . "/tienda' style='background:#c9a96e;color:#0f0720;padding:12px 28px;border-radius:20px;text-decoration:none;font-weight:600;'>Volver a la tienda →</a>
        </div>
        <div style='text-align:center;color:#4a3a6f;font-size:0.75rem;'>EGOZ Boutique Esotérica · Suiza</div>
    </div></body></html>";
    return enviarEmail($cliente['email'], $cliente['nombre'], "Tu pedido #$pedido_id entregado — EGOZ", $html);
}

// ── EMAIL AL CLIENTE — CANCELADO ─────────────
function emailPedidoCancelado($pedido_id, $cliente, $codigo_ref = '') {
    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#0a0418;font-family:Arial,sans-serif;'>
    <div style='max-width:600px;margin:0 auto;padding:20px;'>
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:28px;text-align:center;margin-bottom:20px;'>
            <div style='color:#c9a96e;font-size:1.4rem;font-weight:700;letter-spacing:3px;'>EGOZ</div>
            <div style='color:#5a4a8f;font-size:0.8rem;letter-spacing:2px;'>BOUTIQUE ESOTÉRICA</div>
        </div>
        <div style='background:#1a0a2e;border:1px solid #6b2737;border-radius:14px;padding:24px;text-align:center;margin-bottom:20px;'>
            <div style='font-size:2rem;margin-bottom:8px;'>❌</div>
            <div style='color:#f87171;font-size:1.1rem;font-weight:700;margin-bottom:6px;'>Pedido cancelado</div>
            <div style='color:#9a8abf;font-size:0.9rem;'>Hola {$cliente['nombre']}, tu pedido #$pedido_id ha sido cancelado.</div>
        </div>
        " . htmlCodigoRef($codigo_ref) . "
        <div style='background:#1a0a2e;border:1px solid #3a1f6e;border-radius:14px;padding:24px;margin-bottom:20px;'>
            <p style='color:#9a8abf;font-size:0.9rem;line-height:1.7;margin:0;'>
                Si crees que es un error contacta con Edwin:<br><br>
                📱 WhatsApp: <strong style='color:#e8d5a3;'>+41 76 713 33 41</strong><br>
                📧 Email: <strong style='color:#e8d5a3;'>info@egoz.ch</strong><br><br>
                El reembolso si procede se realizará en 5-10 días hábiles.
            </p>
        </div>
        <div style='text-align:center;color:#4a3a6f;font-size:0.75rem;'>EGOZ Boutique Esotérica · Suiza</div>
    </div></body></html>";
    return enviarEmail($cliente['email'], $cliente['nombre'], "Pedido #$pedido_id cancelado — EGOZ", $html);
}