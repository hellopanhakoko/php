<?php
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

$md5 = $_GET['bill_number'] ?? '';

if (empty($md5)) {
    jsonResponse(['error' => 'Missing bill_number'], 400);
}

$status = getPaymentStatus($md5);
jsonResponse(['status' => $status]);
?>