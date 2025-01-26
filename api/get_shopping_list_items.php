<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_GET['list_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID lista mancante']);
    exit;
}

try {
    $sql = "SELECT sli.* 
            FROM shopping_list_items sli
            JOIN shopping_lists sl ON sli.list_id = sl.id
            WHERE sli.list_id = ?
            ORDER BY sli.position ASC, sli.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['list_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'is_checked' => (bool)$row['is_checked'],
            'position' => $row['position']
        ];
    }

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}