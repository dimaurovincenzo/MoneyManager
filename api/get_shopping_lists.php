<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                sl.id,
                sl.name,
                sl.created_at,
                sl.is_archived,
                COUNT(sli.id) as total_items,
                SUM(sli.is_checked) as checked_items
            FROM shopping_lists sl
            LEFT JOIN shopping_list_items sli ON sl.id = sli.list_id
            GROUP BY sl.id
            ORDER BY sl.is_archived ASC, sl.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $lists = [];
    while ($row = $result->fetch_assoc()) {
        $lists[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'created_at' => $row['created_at'],
            'is_archived' => (bool)$row['is_archived'],
            'total_items' => (int)$row['total_items'],
            'checked_items' => (int)$row['checked_items']
        ];
    }

    echo json_encode([
        'success' => true,
        'lists' => $lists
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}