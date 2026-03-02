<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$id = $_GET['id'];
$response = ['success' => false];

$result = $conn->query("SELECT * FROM clients WHERE id = $id");
$client = $result->fetch_assoc();

if ($client) {
    $response['success'] = true;
    $response['data'] = $client;
}

echo json_encode($response);
