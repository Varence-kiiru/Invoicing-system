<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$id = $_POST['id'];
$response = ['success' => false];

$conn->begin_transaction();

try {
    // Delete related invoice items
    $conn->query("DELETE FROM invoice_items WHERE invoice_id IN 
                 (SELECT id FROM invoices WHERE client_id = $id)");
    
    // Delete related quotation items
    $conn->query("DELETE FROM quotation_items WHERE quotation_id IN 
                 (SELECT id FROM quotations WHERE client_id = $id)");
    
    // Delete invoices
    $conn->query("DELETE FROM invoices WHERE client_id = $id");
    
    // Delete quotations
    $conn->query("DELETE FROM quotations WHERE client_id = $id");
    
    // Delete client
    $conn->query("DELETE FROM clients WHERE id = $id");
    
    $conn->commit();
    $response['success'] = true;
    
} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
