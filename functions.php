<?php
require_once 'config.php';

// =============================
// KHQR GENERATOR
// =============================
class KHQR {
    private $apiToken;
    
    public function __construct($apiToken) {
        $this->apiToken = $apiToken;
    }
    
    public function createQR($params) {
        $qrString = sprintf(
            "%s|%s|%s|%s|%s|%s",
            $params['bank_account'],
            $params['merchant_name'],
            $params['amount'],
            $params['currency'],
            $params['bill_number'],
            $params['phone_number']
        );
        return $qrString;
    }
    
    public function generateMD5($qrString) {
        return md5($qrString . time());
    }
}

// =============================
// HELPER FUNCTIONS
// =============================
function generateShortTransactionId($length = 8) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $id = '';
    for ($i = 0; $i < $length; $i++) {
        $id .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $id;
}

function sendTelegram($message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

function generateQRCode($data) {
    $size = '300x300';
    $url = "https://chart.googleapis.com/chart?chs={$size}&cht=qr&chl=" . urlencode($data);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $imageData = curl_exec($ch);
    curl_close($ch);
    
    return base64_encode($imageData);
}

function setPaymentStatus($md5, $status) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        INSERT INTO payment_status (md5, status) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE status = ?, updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$md5, $status, $status]);
}

function getPaymentStatus($md5) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT status FROM payment_status WHERE md5 = ?");
    $stmt->execute([$md5]);
    $result = $stmt->fetch();
    return $result ? $result['status'] : 'PENDING';
}

function saveTransaction($md5, $data) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        INSERT INTO transactions (
            md5, order_id, status, game, player_id, server_id, 
            player_uid, username, item, amount, payment_method
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            status = VALUES(status),
            updated_at = CURRENT_TIMESTAMP
    ");
    
    $stmt->execute([
        $md5,
        $data['order_id'],
        $data['status'],
        $data['game'],
        $data['player_id'] ?? null,
        $data['server_id'] ?? null,
        $data['player_uid'] ?? null,
        $data['username'] ?? null,
        $data['item'],
        $data['amount'],
        $data['payment_method'] ?? 'KHQR'
    ]);
}

function getTransaction($md5) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE md5 = ?");
    $stmt->execute([$md5]);
    return $stmt->fetch();
}

function checkPaymentAPI($md5) {
    $url = "https://panha-dev.vercel.app/check_payment/{$md5}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);
        return $data;
    }
    
    return null;
}

function fetchItems($game) {
    $url = "https://panhakoko999.pythonanywhere.com/api/items?game={$game}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        return $data['items'] ?? [];
    }
    
    return [];
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function checkMLBBNickname($playerId, $serverId) {
    $url = "https://panha-mlbb-check-v2.vercel.app/api/ml/check?id={$playerId}&serverid={$serverId}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && $response) {
        return json_decode($response, true);
    }
    
    return null;
}

function verifyRobloxUsername($username) {
    $url = "https://panha-roblox-check-profile.vercel.app/api/roblox/avatar-headshot?username=" . urlencode($username);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && $response) {
        return json_decode($response, true);
    }
    
    return null;
}
?>