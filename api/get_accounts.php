<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                a.id,
                a.name,
                a.description,
                COALESCE(a.current_balance, 0.00) as current_balance,
                COALESCE(a.initial_balance, 0.00) as initial_balance,
                i.icon_class,
                CASE 
                    WHEN a.id % 5 = 0 THEN '#3498db' -- blu
                    WHEN a.id % 5 = 1 THEN '#2ecc71' -- verde
                    WHEN a.id % 5 = 2 THEN '#e74c3c' -- rosso
                    WHEN a.id % 5 = 3 THEN '#f1c40f' -- giallo
                    ELSE '#9b59b6' -- viola
                END as color
            FROM accounts a
            LEFT JOIN icons i ON a.icon_id = i.id
            ORDER BY a.name";

    $result = $conn->query($sql);
    $accounts = [];

    while ($row = $result->fetch_assoc()) {
        $accounts[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'current_balance' => floatval($row['current_balance']),
            'initial_balance' => floatval($row['initial_balance']),
            'icon_class' => $row['icon_class'] ?? 'bi-wallet2',
            'color' => $row['color']
        ];
    }

    echo json_encode([
        'success' => true,
        'accounts' => $accounts
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
