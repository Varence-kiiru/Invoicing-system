<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? 0;
$response = ['success' => false];

$conn->begin_transaction();

try {
    // Delete quotation items first
    $conn->query("DELETE FROM quotation_items WHERE quotation_id = $id");
    
    // Delete quotation
    if ($conn->query("DELETE FROM quotations WHERE id = $id")) {
        $conn->commit();
        $response['success'] = true;
    } else {
        $conn->rollback();
    }
} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
