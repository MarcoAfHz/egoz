<?php
// =============================================
// EGOZ — Boutique Esotérica
// includes/stripe_helper.php
// =============================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

// ── CREAR PAYMENT INTENT ─────────────────────
function crearPaymentIntent($total_chf, $metadata = []) {
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

    $intent = \Stripe\PaymentIntent::create([
        'amount'   => round($total_chf * 100),
        'currency' => STRIPE_CURRENCY,
        'metadata' => $metadata,
        'automatic_payment_methods' => ['enabled' => true],
    ]);

    return $intent;
}

// ── VERIFICAR WEBHOOK ─────────────────────────
function verificarWebhook($payload, $sig_header, $webhook_secret) {
    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $webhook_secret);
        return $event;
    } catch (\Exception $e) {
        return null;
    }
}