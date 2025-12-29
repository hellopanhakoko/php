<?php
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$playerId = trim($input['player_id'] ?? '');
$serverId = trim($input['server_id'] ?? '');

if (empty($playerId) || empty($serverId)) {
    jsonResponse([
        'success' => false,
        'error' => 'Player ID and Server ID are required'
    ], 400);
}

$apiData = checkMLBBNickname($playerId, $serverId);

if (!$apiData || $apiData['status'] !== 'success') {
    jsonResponse([
        'success' => false,
        'error' => 'Invalid Player ID or Server ID'
    ], 404);
}

$playerInfo = $apiData['player'] ?? [];
$country = $playerInfo['country'] ?? '';
$nickname = $playerInfo['nickname'] ?? '';

if ($country !== 'Cambodia') {
    jsonResponse([
        'success' => false,
        'error' => "Only Cambodia accounts are supported. Your account is from: {$country}",
        'country' => $country
    ], 403);
}

jsonResponse([
    'success' => true,
    'nickname' => $nickname,
    'country' => $country,
    'player_id' => $playerId,
    'server_id' => $serverId
]);
?>