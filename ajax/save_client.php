<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? null;
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    try {
        if ($client_id) {
            $stmt = $conn->prepare("UPDATE clients SET 
                name = ?, email = ?, phone = ?, address = ? 
                WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $address, $client_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO clients 
                (name, email, phone, address) 
                VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $address);
        }
        
        $stmt->execute();
        $response['success'] = true;
        $response['message'] = 'Client saved successfully';
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
