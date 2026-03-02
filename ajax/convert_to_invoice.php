<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$id = $_POST['id'];
$response = ['success' => false];

$conn->begin_transaction();

try {
    // Get quotation data
    $quotation = $conn->query("SELECT * FROM quotations WHERE id = $id")->fetch_assoc();
    $items = $conn->query("SELECT * FROM quotation_items WHERE quotation_id = $id");
    
    // Generate new invoice number
    $year = date('Y');
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) as last_number 
                           FROM invoices 
                           WHERE invoice_number LIKE 'INV-$year-%'");
    $row = $result->fetch_assoc();
    $next_number = str_pad(($row['last_number'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);
    $invoice_number = "INV-$year-$next_number";
    
    // Create invoice
    $stmt = $conn->prepare("INSERT INTO invoices 
        (invoice_number, client_id, issue_date, due_date, 
        subtotal, tax_amount, total_amount, status) 
        VALUES (?, ?, ?, DATE_ADD(?, INTERVAL 30 DAY), ?, ?, ?, 'pending')");
    $stmt->bind_param("sissddd", $invoice_number, $quotation['client_id'],
                    $quotation['issue_date'], $quotation['issue_date'],
                    $quotation['subtotal'], $quotation['tax_amount'], 
                    $quotation['total_amount']);
    $stmt->execute();
    $invoice_id = $conn->insert_id;
    
    // Copy items to invoice
    $stmt = $conn->prepare("INSERT INTO invoice_items 
        (invoice_id, description, quantity, unit_price, total) 
        VALUES (?, ?, ?, ?, ?)");
        
    while($item = $items->fetch_assoc()) {
        $stmt->bind_param("isids", $invoice_id, $item['description'],
                        $item['quantity'], $item['unit_price'], $item['total']);
        $stmt->execute();
    }
    
    // Update quotation status
    $conn->query("UPDATE quotations SET status = 'accepted' WHERE id = $id");
    
    $conn->commit();
    $response['success'] = true;
    $response['invoice_id'] = $invoice_id;
    
} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
