<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_data = [
        'invoice_number' => $_POST['invoice_number'] ?? '',
        'client_id' => $_POST['client_id'],
        'issue_date' => $_POST['issue_date'],
        'due_date' => $_POST['due_date'],
        'tax_rate' => $_POST['tax_rate'] ?? 0,
        'subtotal' => $_POST['subtotal'],
        'tax_amount' => $_POST['tax_amount'],
        'total_amount' => $_POST['total_amount'],
        'type' => $_POST['invoice_type'] ?? 'standard',
        'status' => 'pending'
    ];
    
    // If invoice number is empty, generate it
    if(empty($invoice_data['invoice_number'])) {
        $type = $invoice_data['type'];
        $year = date('Y');
        $prefix = ($type === 'standard') ? 'INV' : 'PRO';
        $result = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) as last_number 
                               FROM invoices 
                               WHERE invoice_number LIKE '$prefix-$year-%'");
        $row = $result->fetch_assoc();
        $next_number = str_pad(($row['last_number'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        $invoice_data['invoice_number'] = "$prefix-$year-$next_number";
    }

    $conn->begin_transaction();

    try {
        // Insert invoice
        $stmt = $conn->prepare("INSERT INTO invoices (invoice_number, client_id, issue_date, due_date, tax_rate, subtotal, tax_amount, total_amount, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissddddss", 
            $invoice_data['invoice_number'],
            $invoice_data['client_id'],
            $invoice_data['issue_date'],
            $invoice_data['due_date'],
            $invoice_data['tax_rate'],
            $invoice_data['subtotal'],
            $invoice_data['tax_amount'],
            $invoice_data['total_amount'],
            $invoice_data['type'],
            $invoice_data['status']
        );
        $stmt->execute();
        $invoice_id = $conn->insert_id;

        // Insert items
        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total, tax_exempt) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($_POST['description'] as $key => $description) {
            $tax_exempt = isset($_POST['tax_exempt'][$key]) ? 1 : 0;
            $stmt->bind_param("isidsi",
                $invoice_id,
                $description,
                $_POST['quantity'][$key],
                $_POST['unit_price'][$key],
                $_POST['total'][$key],
                $tax_exempt
            );
            $stmt->execute();
        }

        $conn->commit();
        $response['success'] = true;
        $response['invoice_id'] = $invoice_id;

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
