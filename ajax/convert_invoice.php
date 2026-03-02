<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$id = $_POST['id'];
$year = date('Y');

// Get next standard invoice number
$result = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) as last_number 
                       FROM invoices 
                       WHERE invoice_type = 'standard' 
                       AND invoice_number LIKE '%-$year-%'");
$row = $result->fetch_assoc();
$next_number = str_pad(($row['last_number'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);
$new_invoice_number = "INV-$year-$next_number";

$conn->begin_transaction();

try {
    // Update invoice type and number
    $stmt = $conn->prepare("UPDATE invoices SET 
        invoice_type = 'standard', 
        invoice_number = ? 
        WHERE id = ?");
    $stmt->bind_param("si", $new_invoice_number, $id);
    $stmt->execute();
    
    $conn->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
