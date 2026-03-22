<?php
// webhook.php

// Cargar configuración
$baseDir = __DIR__;
if (!file_exists($baseDir . '/config.php')) {
    $baseDir = dirname(__DIR__);
}
if (!file_exists($baseDir . '/config.php')) {
    $baseDir = dirname(dirname(__DIR__));
}
$config = require $baseDir . '/config.php';

// Obtener la actualización de Telegram
$update = json_decode(file_get_contents('php://input'), true);

// Verificar si es una callback query
if (isset($update['callback_query'])) {
    $callback_query = $update['callback_query'];
    $callback_data = explode(':', $callback_query['data']);
    $action = $callback_data[0];
    $transaction_id = $callback_data[1];

    // Cargar acciones desde archivo
    $actions_file = __DIR__ . '/actions.json';
    $actions = [];
    if (file_exists($actions_file)) {
        $actions = json_decode(file_get_contents($actions_file), true) ?? [];
    }

    // Almacenar la acción
    $actions[$transaction_id] = $action;

    // Guardar en archivo
    file_put_contents($actions_file, json_encode($actions));

    error_log("[webhook] Action stored: $action for $transaction_id");

    // Responder a Telegram
    $response = ['method' => 'answerCallbackQuery', 'callback_query_id' => $callback_query['id']];
    echo json_encode($response);
}

// Responder con OK
http_response_code(200);
echo "OK";
?>