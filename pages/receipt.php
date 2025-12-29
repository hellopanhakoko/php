<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$md5 = $_GET['md5'] ?? '';

if (empty($md5)) {
    http_response_code(404);
    echo "Transaction not found";
    exit;
}

$transaction = getTransaction($md5);

if (!$transaction) {
    http_response_code(404);
    echo "Transaction not found";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt - Order #<?php echo htmlspecialchars($transaction['order_id']); ?></title>
<style>
* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.receipt-container {
    background: white;
    max-width: 500px;
    width: 100%;
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    overflow: hidden;
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.receipt-header {
    background: <?php echo $transaction['status'] === 'SUCCESS' ? '#4CAF50' : '#f44336'; ?>;
    color: white;
    padding: 30px;
    text-align: center;
}

.receipt-header .icon {
    font-size: 4rem;
    margin-bottom: 10px;
    animation: bounce 1s ease;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-20px);
    }
    60% {
        transform: translateY(-10px);
    }
}

.receipt-header h1 {
    font-size: 1.8rem;
    margin-bottom: 5px;
}

.receipt-header p {
    opacity: 0.9;
    font-size: 0.9rem;
}

.receipt-body {
    padding: 30px;
}

.receipt-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.receipt-row:last-child {
    border-bottom: none;
}

.receipt-label {
    color: #666;
    font-size: 0.9rem;
}

.receipt-value {
    color: #333;
    font-weight: 600;
    text-align: right;
}

.receipt-total {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #eee;
}

.receipt-total .receipt-row {
    border-bottom: none;
}

.receipt-total .receipt-value {
    font-size: 1.5rem;
    color: #4CAF50;
}

.receipt-footer {
    background: #f5f5f5;
    padding: 20px;
    text-align: center;
}

.receipt-footer p {
    color: #666;
    margin-bottom: 10px;
}

.btn-home {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 12px 30px;
    border-radius: 25px;
    text-decoration: none;
    margin-top: 10px;
    transition: all 0.3s ease;
    font-weight: 600;
}

.btn-home:hover {
    background: #5568d3;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-home:active {
    transform: translateY(0);
}

.timestamp {
    color: #999;
    font-size: 0.85rem;
    margin-top: 15px;
    text-align: center;
}

.status-badge {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 10px;
}

.status-success {
    background: #e8f5e9;
    color: #4CAF50;
}

.status-expired {
    background: #ffebee;
    color: #f44336;
}

.status-pending {
    background: #fff3e0;
    color: #ff9800;
}

@media (max-width: 480px) {
    .receipt-header h1 {
        font-size: 1.5rem;
    }
    
    .receipt-header .icon {
        font-size: 3rem;
    }
    
    .receipt-body {
        padding: 20px;
    }
    
    .receipt-total .receipt-value {
        font-size: 1.3rem;
    }
}
</style>
</head>
<body>

<div class="receipt-container">
    <!-- HEADER -->
    <div class="receipt-header">
        <div class="icon">
            <?php 
            if ($transaction['status'] === 'SUCCESS') {
                echo '✅';
            } elseif ($transaction['status'] === 'PENDING') {
                echo '⏳';
            } else {
                echo '❌';
            }
            ?>
        </div>
        <h1>
            <?php 
            if ($transaction['status'] === 'SUCCESS') {
                echo 'Payment Successful';
            } elseif ($transaction['status'] === 'PENDING') {
                echo 'Payment Pending';
            } else {
                echo 'Payment ' . htmlspecialchars($transaction['status']);
            }
            ?>
        </h1>
        <p>Order #<?php echo htmlspecialchars($transaction['order_id']); ?></p>
        <span class="status-badge status-<?php echo strtolower($transaction['status']) === 'success' ? 'success' : (strtolower($transaction['status']) === 'pending' ? 'pending' : 'expired'); ?>">
            <?php echo strtoupper($transaction['status']); ?>
        </span>
    </div>

    <!-- BODY -->
    <div class="receipt-body">
        <div class="receipt-row">
            <span class="receipt-label">Game</span>
            <span class="receipt-value"><?php echo htmlspecialchars($transaction['game']); ?></span>
        </div>

        <?php if (!empty($transaction['player_id'])): ?>
        <div class="receipt-row">
            <span class="receipt-label">Player ID</span>
            <span class="receipt-value"><?php echo htmlspecialchars($transaction['player_id']); ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($transaction['server_id'])): ?>
        <div class="receipt-row">
            <span class="receipt-label">Server ID</span>
            <span class="receipt-value"><?php echo htmlspecialchars($transaction['server_id']); ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($transaction['player_uid'])): ?>
        <div class="receipt-row">
            <span class="receipt-label">Player UID</span>
            <span class="receipt-value"><?php echo htmlspecialchars($transaction['player_uid']); ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($transaction['username'])): ?>
        <div class="receipt-row">
            <span class="receipt-label">Username</span>
            <span class="receipt-value"><?php echo htmlspecialchars($transaction['username']); ?></span>
        </div>
        <?php endif; ?>

        <div class="receipt-row">
            <span class="receipt-label">Item</span>
            <span class="receipt-value"><?php echo htmlspecialchars($transaction['item']); ?></span>
        </div>

        <div class="receipt-row">
            <span class="receipt-label">Payment Method</span>
            <span class="receipt-value"><?php echo htmlspecialchars($transaction['payment_method']); ?></span>
        </div>

        <div class="receipt-total">
            <div class="receipt-row">
                <span class="receipt-label">Total Amount</span>
                <span class="receipt-value">$<?php echo number_format($transaction['amount'], 2); ?></span>
            </div>
        </div>

        <div class="timestamp">
            <?php echo date('F j, Y g:i A', strtotime($transaction['created_at'])); ?>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="receipt-footer">
        <?php if ($transaction['status'] === 'SUCCESS'): ?>
            <p style="color:#666;">✨ Thank you for your purchase!</p>
            <p style="color:#999; font-size:0.85rem;">Your diamonds will be delivered shortly.</p>
        <?php elseif ($transaction['status'] === 'PENDING'): ?>
            <p style="color:#ff9800;">⏳ Payment is still pending...</p>
            <p style="color:#999; font-size:0.85rem;">Please complete your payment.</p>
        <?php else: ?>
            <p style="color:#f44336;">❌ Payment expired or failed</p>
            <p style="color:#999; font-size:0.85rem;">Please try again or contact support.</p>
        <?php endif; ?>
        
        <a href="/" class="btn-home">
            <?php echo $transaction['status'] === 'SUCCESS' ? 'Back to Home' : 'Try Again'; ?>
        </a>
    </div>
</div>

<script>
// Print receipt function
function printReceipt() {
    window.print();
}

// Add print button if successful
<?php if ($transaction['status'] === 'SUCCESS'): ?>
document.addEventListener('DOMContentLoaded', function() {
    const footer = document.querySelector('.receipt-footer');
    const printBtn = document.createElement('button');
    printBtn.textContent = '🖨️ Print Receipt';
    printBtn.className = 'btn-home';
    printBtn.style.marginLeft = '10px';
    printBtn.onclick = printReceipt;
    footer.appendChild(printBtn);
});
<?php endif; ?>

// Auto-refresh if pending
<?php if ($transaction['status'] === 'PENDING'): ?>
setTimeout(function() {
    location.reload();
}, 5000); // Refresh every 5 seconds
<?php endif; ?>
</script>

</body>
</html>