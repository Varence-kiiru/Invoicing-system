<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;
$response = ['success' => false];

$invoice = $conn->query("SELECT * FROM invoices WHERE id = $id")->fetch_assoc();
if ($invoice) {
    $items = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $id")->fetch_all(MYSQLI_ASSOC);
    $invoice['items'] = $items;
    $response['success'] = true;
    $response['data'] = $invoice;
}

echo json_encode($response);
