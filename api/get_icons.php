<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

$type = isset($_GET['type']) ? cleanInput($_GET['type']) : 'account';

$sql = "SELECT id, name, icon_class FROM icons WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();

$icons = [];
while ($row = $result->fetch_assoc()) {
    $icons[] = $row;
}

echo json_encode(['icons' => $icons]);
