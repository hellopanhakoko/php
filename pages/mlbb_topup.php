<?php
require_once 'config.php';
require_once 'functions.php';

$items = fetchItems('mlbb');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $playerId = $_POST['player_id'] ?? '';
    $serverId = $_POST['server_id'] ?? '';
    $itemId = intval($_POST['item_id'] ?? 0);
    
    $checkData = checkMLBBNickname($playerId, $serverId);
    
    if (!$checkData || $checkData['status'] !== 'success') {
        jsonResponse(['success' => false, 'error' => 'Unable to verify account'], 400);
    }
    
    $playerInfo = $checkData['player'] ?? [];
    $country = $playerInfo['country'] ?? '';
    
    if ($country !== 'Cambodia') {
        jsonResponse([
            'success' => false,
            'error' => "Sorry, this service is only available for Cambodia accounts. Your account is from: {$country}"
        ], 403);
    }
    
    $selectedItem = null;
    foreach ($items as $item) {
        if ($item['id'] == $itemId) {
            $selectedItem = $item;
            break;
        }
    }
    
    if (!$selectedItem) {
        jsonResponse(['success' => false, 'error' => 'Invalid item'], 400);
    }
    
    $amount = $selectedItem['price'];
    $orderId = generateShortTransactionId(10);
    $billNumber = generateShortTransactionId();
    
    $khqr = new KHQR(API_TOKEN_BAKONG);
    $qrString = $khqr->createQR([
        'bank_account' => BANK_ACCOUNT,
        'merchant_name' => 'MLBB TOPUP',
        'merchant_city' => 'Phnom Penh',
        'amount' => $amount,
        'currency' => 'USD',
        'store_label' => 'MLBB',
        'phone_number' => PHONE_NUMBER,
        'bill_number' => $billNumber,
        'terminal_label' => 'MLBB-01'
    ]);
    
    $md5 = $khqr->generateMD5($qrString);
    $qrBase64 = generateQRCode($qrString);
    
    setPaymentStatus($md5, 'PENDING');
    
    saveTransaction($md5, [
        'order_id' => $orderId,
        'status' => 'PENDING',
        'game' => 'Mobile Legends',
        'player_id' => $playerId,
        'server_id' => $serverId,
        'item' => $selectedItem['name'],
        'amount' => $amount
    ]);
    
    include __DIR__ . '/../templates/mlbb_qrcode.php';
    exit;
}

include __DIR__ . '/../templates/mlbb_topup.html';
?>