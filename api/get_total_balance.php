<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Ottieni il saldo totale attuale
    $sql = "SELECT SUM(current_balance) as total_balance FROM accounts";
    $result = $conn->query($sql);
    $currentBalance = $result->fetch_assoc()['total_balance'] ?? 0;

    // Calcola il saldo del mese precedente
    $firstDayLastMonth = date('Y-m-01', strtotime('first day of last month'));
    $lastDayLastMonth = date('Y-m-t', strtotime('first day of last month'));
    
    $sql = "SELECT 
                (SELECT SUM(current_balance) FROM accounts) - 
                (
                    SELECT COALESCE(SUM(
                        CASE 
                            WHEN type = 'expense' THEN -amount 
                            ELSE amount 
                        END
                    ), 0)
                    FROM transactions 
                    WHERE date > ?
                ) as last_month_balance";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $lastDayLastMonth);
    $stmt->execute();
    $lastMonthBalance = $stmt->get_result()->fetch_assoc()['last_month_balance'] ?? 0;

    // Calcola il trend (differenza rispetto al mese precedente)
    $trend = $currentBalance - $lastMonthBalance;

    echo json_encode([
        'success' => true,
        'balance' => $currentBalance,
        'trend' => $trend
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
