<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;
$response = ['success' => false];

$quotation = $conn->query("SELECT * FROM quotations WHERE id = $id")->fetch_assoc();
if ($quotation) {
    $items = $conn->query("SELECT * FROM quotation_items WHERE quotation_id = $id")->fetch_all(MYSQLI_ASSOC);
    $quotation['items'] = $items;
    $response['success'] = true;
    $response['data'] = $quotation;
}

echo json_encode($response);
