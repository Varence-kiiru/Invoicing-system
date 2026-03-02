<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$type = $_POST['type'];
$year = date('Y');

$result = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) as last_number 
                       FROM invoices 
                       WHERE invoice_type = '$type' 
                       AND invoice_number LIKE '%-$year-%'");
                       
$row = $result->fetch_assoc();
$next_number = str_pad(($row['last_number'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

echo json_encode(['next_number' => $next_number]);
