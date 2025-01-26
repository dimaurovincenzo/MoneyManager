<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Ottieni il primo e l'ultimo giorno del mese corrente
    $firstDayOfMonth = date('Y-m-01');
    $lastDayOfMonth = date('Y-m-t');

    // Query per ottenere le top categorie di spesa del mese
    $sql = "SELECT 
                c.id,
                c.name,
                c.color,
                i.icon_class as icon,
                SUM(t.amount) as total_amount,
                COUNT(*) as transaction_count,
                (SELECT SUM(amount) 
                 FROM transactions 
                 WHERE type = 'expense' 
                 AND date BETWEEN ? AND ?) as total_expenses
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            LEFT JOIN icons i ON c.icon_id = i.id
            WHERE t.date BETWEEN ? AND ?
                AND t.type = 'expense'
            GROUP BY c.id, c.name, c.color, i.icon_class
            HAVING total_amount > 0
            ORDER BY total_amount DESC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $firstDayOfMonth, $lastDayOfMonth, $firstDayOfMonth, $lastDayOfMonth);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    $totalExpense = 0;
    $first = true;

    while ($row = $result->fetch_assoc()) {
        if ($first) {
            $totalExpense = $row['total_expenses'];
            $first = false;
        }

        $categories[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'icon' => $row['icon'] ?? 'bi-tag-fill',
            'color' => $row['color'] ?? '#6c757d',
            'amount' => floatval($row['total_amount']),
            'percentage' => $totalExpense > 0 ? (floatval($row['total_amount']) / $totalExpense) * 100 : 0,
            'transaction_count' => $row['transaction_count']
        ];
    }

    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'total_expense' => $totalExpense
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 