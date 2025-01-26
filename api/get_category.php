<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID non specificato']);
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT c.*, i.icon_class 
        FROM categories c 
        LEFT JOIN icons i ON c.icon_id = i.id 
        WHERE c.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Categoria non trovata']);
}
