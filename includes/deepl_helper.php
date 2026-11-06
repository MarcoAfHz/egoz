<?php
// =============================================
// EGOZ — Boutique Esotérica
// includes/deepl_helper.php
// =============================================

function traducirDeepL($texto, $idioma_destino = 'DE') {
    if (!defined('DEEPL_API_KEY') || empty($texto)) return null;

    try {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api-free.deepl.com/v2/translate',
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'text'        => $texto,
                'source_lang' => 'ES',
                'target_lang' => $idioma_destino,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: DeepL-Auth-Key ' . DEEPL_API_KEY,
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $http_code !== 200) {
            error_log('DeepL error HTTP: ' . $http_code);
            return null;
        }

        $json = json_decode($response, true);
        return $json['translations'][0]['text'] ?? null;

    } catch (\Exception $e) {
        error_log('DeepL error: ' . $e->getMessage());
        return null;
    }
}

function traducirProducto($nombre_es, $descripcion_es) {
    $nombre_de      = traducirDeepL($nombre_es, 'DE');
    $descripcion_de = traducirDeepL($descripcion_es, 'DE');
    return [$nombre_de, $descripcion_de];
}