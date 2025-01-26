<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Ottieni il primo e l'ultimo giorno del mese corrente
    $firstDayOfMonth = date('Y-m-01');
    $lastDayOfMonth = date('Y-m-t');

    // Query per ottenere totale entrate e uscite del mese
    $sql = "SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
            FROM transactions 
            WHERE date BETWEEN ? AND ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $firstDayOfMonth, $lastDayOfMonth);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    echo json_encode([
        'success' => true,
        'income' => $result['total_income'] ?? 0,
        'expense' => $result['total_expense'] ?? 0
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 