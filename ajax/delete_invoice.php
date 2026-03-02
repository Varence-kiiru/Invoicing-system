<?php
require_once '../config/config.php';

$id = $_POST['id'] ?? 0;
$response = ['success' => false];

if ($conn->query("DELETE FROM invoices WHERE id = $id")) {
    $response['success'] = true;
}

echo json_encode($response);
