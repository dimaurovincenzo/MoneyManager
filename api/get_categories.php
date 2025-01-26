<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                c.id,
                c.name,
                c.type,
                c.color,
                i.icon_class
            FROM categories c
            LEFT JOIN icons i ON c.icon_id = i.id
            ORDER BY c.type, c.name";

    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'type' => $row['type'],
            'color' => $row['color'],
            'icon_class' => $row['icon_class'] ?? 'bi-tag-fill'
        ];
    }

    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    error_log("Error in get_categories.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
