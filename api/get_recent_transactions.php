<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                t.id,
                t.amount,
                t.type,
                t.date,
                c.name as category_name,
                c.color as category_color,
                i.icon_class as category_icon,
                a.name as account_name
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            LEFT JOIN icons i ON c.icon_id = i.id
            JOIN accounts a ON t.account_id = a.id
            ORDER BY t.date DESC, t.id DESC
            LIMIT 10";

    $result = $conn->query($sql);
    $transactions = [];

    while ($row = $result->fetch_assoc()) {
        $transactions[] = [
            'id' => $row['id'],
            'amount' => floatval($row['amount']),
            'type' => $row['type'],
            'date' => $row['date'],
            'category_name' => $row['category_name'],
            'category_color' => $row['category_color'] ?? '#6c757d',
            'category_icon' => $row['category_icon'] ?? 'bi-tag-fill',
            'account_name' => $row['account_name']
        ];
    }

    echo json_encode([
        'success' => true,
        'transactions' => $transactions
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
