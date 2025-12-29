<?php
/**
 * Background Payment Checker Script
 * Run via cron: * * * * * php /path/to/payment_checker.php
 */

require_once 'config.php';
require_once 'functions.php';

echo "[" . date('Y-m-d H:i:s') . "] Payment checker started\n";

$pdo = getDBConnection();

// Get all pending payments
$stmt = $pdo->query("
    SELECT t.*, p.created_at 
    FROM transactions t
    JOIN payment_status p ON t.md5 = p.md5
    WHERE p.status = 'PENDING'
    AND TIMESTAMPDIFF(SECOND, p.created_at, NOW()) < " . PAYMENT_TIMEOUT . "
    ORDER BY p.created_at DESC
    LIMIT 50
");

$pendingPayments = $stmt->fetchAll();

echo "Found " . count($pendingPayments) . " pending payments\n";

foreach ($pendingPayments as $payment) {
    $md5 = $payment['md5'];
    
    // Check if payment is made
    $apiData = checkPaymentAPI($md5);
    
    if ($apiData && $apiData['success'] && $apiData['status'] === 'PAID') {
        echo "Payment PAID: {$md5}\n";
        
        setPaymentStatus($md5, 'PAID');
        
        $updateStmt = $pdo->prepare("UPDATE transactions SET status = 'SUCCESS' WHERE md5 = ?");
        $updateStmt->execute([$md5]);
        
        // Send Telegram notification
        $game = $payment['game'];
        $orderId = $payment['order_id'];
        $amount = $payment['amount'];
        
        if ($game === 'Mobile Legends') {
            $message = "✅ MLBB Order #{$orderId}\n";
            $message .= "{$payment['player_id']} ({$payment['server_id']})\n";
            $message .= "{$payment['item']}\n";
            $message .= "\${$amount}";
            sendTelegram($message);
        } elseif ($game === 'Free Fire') {
            $message = "✅ FF Order #{$orderId}\n";
            $message .= "{$payment['player_uid']}\n";
            $message .= "{$payment['item']}\n";
            $message .= "\${$amount}";
            sendTelegram($message);
        } elseif ($game === 'Roblox') {
            $message = "✅ Roblox Order #{$orderId}\n";
            $message .= "Username: {$payment['username']}\n";
            $message .= "Item: {$payment['item']}\n";
            $message .= "\${$amount}";
            sendTelegram($message);
        }
    }
    
    // Check if payment expired
    $createdAt = strtotime($payment['created_at']);
    $elapsed = time() - $createdAt;
    
    if ($elapsed >= PAYMENT_TIMEOUT) {
        echo "Payment EXPIRED: {$md5}\n";
        setPaymentStatus($md5, 'EXPIRED');
        
        $updateStmt = $pdo->prepare("UPDATE transactions SET status = 'EXPIRED' WHERE md5 = ?");
        $updateStmt->execute([$md5]);
    }
    
    usleep(500000);
}

echo "[" . date('Y-m-d H:i:s') . "] Payment checker finished\n";
?>