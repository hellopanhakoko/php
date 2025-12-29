<?php
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');

if (empty($username)) {
    jsonResponse(['error' => 'Username is required'], 400);
}

$apiData = verifyRobloxUsername($username);

if (!$apiData || !isset($apiData['data']) || empty($apiData['data'])) {
    jsonResponse([
        'success' => false,
        'error' => 'Username not found'
    ], 404);
}

$avatarData = $apiData['data'][0];

jsonResponse([
    'success' => true,
    'username' => $username,
    'imageUrl' => $avatarData['imageUrl'] ?? '',
    'state' => $avatarData['state'] ?? '',
    'targetId' => $avatarData['targetId'] ?? ''
]);
?>