<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$year = date('Y');
$result = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(quotation_number, '-', -1) AS UNSIGNED)) as last_number 
                       FROM quotations 
                       WHERE quotation_number LIKE 'QUO-$year-%'");
                       
$row = $result->fetch_assoc();
$next_number = str_pad(($row['last_number'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);
$quotation_number = "QUO-$year-$next_number";

echo json_encode(['number' => $quotation_number]);
