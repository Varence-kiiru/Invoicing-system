<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $logo_path = null;
        
        // Handle logo upload
        if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['logo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $upload_path = '../uploads/';
                if(!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $new_filename = 'logo_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path . $new_filename);
                $logo_path = 'uploads/' . $new_filename;
            }
        }
        
        // Check if settings exist
        $result = $conn->query("SELECT COUNT(*) as count FROM settings");
        $row = $result->fetch_assoc();
        
        if($row['count'] > 0) {
            $sql = "UPDATE settings SET 
                company_name = ?, 
                company_address = ?,
                company_phone = ?,
                company_email = ?,
                tax_rate = ?,
                invoice_terms = ?,
                quotation_terms = ?,
                currency_symbol = ?";
            
            if($logo_path) {
                $sql .= ", logo_path = ?";
            }
            
            $sql .= " WHERE id = 1";
            
            $stmt = $conn->prepare($sql);
            
            if($logo_path) {
                $stmt->bind_param("ssssdssss", 
                    $_POST['company_name'],
                    $_POST['company_address'],
                    $_POST['company_phone'],
                    $_POST['company_email'],
                    $_POST['tax_rate'],
                    $_POST['invoice_terms'],
                    $_POST['quotation_terms'],
                    $_POST['currency_symbol'],
                    $logo_path
                );
            } else {
                $stmt->bind_param("ssssdsss", 
                    $_POST['company_name'],
                    $_POST['company_address'],
                    $_POST['company_phone'],
                    $_POST['company_email'],
                    $_POST['tax_rate'],
                    $_POST['invoice_terms'],
                    $_POST['quotation_terms'],
                    $_POST['currency_symbol']
                );
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO settings 
                (company_name, company_address, company_phone, company_email, 
                tax_rate, invoice_terms, quotation_terms, currency_symbol, logo_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
            $stmt->bind_param("ssssdssss", 
                $_POST['company_name'],
                $_POST['company_address'],
                $_POST['company_phone'],
                $_POST['company_email'],
                $_POST['tax_rate'],
                $_POST['invoice_terms'],
                $_POST['quotation_terms'],
                $_POST['currency_symbol'],
                $logo_path
            );
        }
        
        $stmt->execute();
        $response['success'] = true;
        $response['message'] = 'Settings saved successfully';
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
