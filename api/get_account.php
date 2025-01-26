<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID non specificato']);
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT a.*, i.icon_class 
        FROM accounts a 
        LEFT JOIN icons i ON a.icon_id = i.id 
        WHERE a.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Conto non trovato']);
}
