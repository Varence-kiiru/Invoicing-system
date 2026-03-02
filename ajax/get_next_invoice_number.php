<?php
require_once '../config/config.php';

$type = $_POST['type'] ?? 'standard';
$year = date('Y');
$prefix = ($type === 'standard') ? 'INV' : 'PRO';

$result = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) as last_number 
                       FROM invoices 
                       WHERE invoice_number LIKE '$prefix-$year-%'");
$row = $result->fetch_assoc();
$next_number = str_pad(($row['last_number'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

echo json_encode(['next_number' => $next_number]);
