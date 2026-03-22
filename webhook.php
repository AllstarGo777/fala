<?php
// webhook.php

// Cargar configuración
$config = require __DIR__ . '/../config.php';

// Obtener la actualización de Telegram
$update = json_decode(file_get_contents('php://input'), true);

// Verificar si es una callback query
if (isset($update['callback_query'])) {
    $callback_query = $update['callback_query'];
    $callback_data = explode(':', $callback_query['data']);
    $action = $callback_data[0];
    $transaction_id = $callback_data[1];

    // Iniciar sesión para almacenar el estado
    session_start();

    // Almacenar la acción en el array de acciones
    if (!isset($_SESSION['actions'])) {
        $_SESSION['actions'] = [];
    }
    $_SESSION['actions'][$transaction_id] = $action;

    // Responder a Telegram
    $response = ['method' => 'answerCallbackQuery', 'callback_query_id' => $callback_query['id']];
    echo json_encode($response);
}

// Responder con OK
http_response_code(200);
echo "OK";
?>