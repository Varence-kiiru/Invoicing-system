<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quotation_id = $_POST['quotation_id'] ?? null;
    $quotation_number = $_POST['quotation_number'] ?? '';
    $client_id = $_POST['client_id'];
    $issue_date = $_POST['issue_date'];
    $valid_until = $_POST['valid_until'];
    $tax_rate = $_POST['tax_rate'] ?? 0;
    $subtotal = $_POST['subtotal'];
    $tax_amount = $_POST['tax_amount'];
    $total_amount = $_POST['total_amount'];
    
    // If quotation number is empty, generate it
    if(empty($quotation_number)) {
        $year = date('Y');
        $result = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(quotation_number, '-', -1) AS UNSIGNED)) as last_number 
                               FROM quotations 
                               WHERE quotation_number LIKE 'QUO-$year-%'");
        $row = $result->fetch_assoc();
        $next_number = str_pad(($row['last_number'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        $quotation_number = "QUO-$year-$next_number";
    }
    
    $conn->begin_transaction();
    
    try {
        if ($quotation_id) {
            // Update existing quotation
            $stmt = $conn->prepare("UPDATE quotations SET 
                client_id = ?, issue_date = ?, valid_until = ?, tax_rate = ?,
                subtotal = ?, tax_amount = ?, total_amount = ?
                WHERE id = ?");
            $stmt->bind_param("issddddi", $client_id, $issue_date, $valid_until, $tax_rate,
                            $subtotal, $tax_amount, $total_amount, $quotation_id);
            $stmt->execute();
            
            // Delete existing items
            $conn->query("DELETE FROM quotation_items WHERE quotation_id = $quotation_id");
        } else {
            // Create new quotation
            $stmt = $conn->prepare("INSERT INTO quotations 
                (quotation_number, client_id, issue_date, valid_until, tax_rate,
                subtotal, tax_amount, total_amount, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("sissdddd", $quotation_number, $client_id,
                            $issue_date, $valid_until, $tax_rate, $subtotal, $tax_amount, $total_amount);
            $stmt->execute();
            $quotation_id = $conn->insert_id;
        }
        
        // Insert quotation items
        $stmt = $conn->prepare("INSERT INTO quotation_items 
            (quotation_id, description, quantity, unit_price, total, tax_exempt) 
            VALUES (?, ?, ?, ?, ?, ?)");
            
        foreach ($_POST['description'] as $key => $description) {
            $quantity = $_POST['quantity'][$key];
            $unit_price = $_POST['unit_price'][$key];
            $total = $_POST['total'][$key];
            $tax_exempt = isset($_POST['tax_exempt'][$key]) ? 1 : 0;
            
            $stmt->bind_param("isidsi", $quotation_id, $description, 
                            $quantity, $unit_price, $total, $tax_exempt);
            $stmt->execute();
        }
        
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Quotation saved successfully';
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
