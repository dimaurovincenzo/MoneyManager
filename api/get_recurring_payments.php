<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

function getNextExecutionDate($dayOfMonth) {
    $today = new DateTime();
    $nextExecution = new DateTime();
    $nextExecution->setDate(
        $today->format('Y'),  // anno corrente
        $today->format('m'),  // mese corrente
        $dayOfMonth          // giorno specificato
    );

    // Se la data calcolata è già passata, aggiungiamo un mese
    if ($nextExecution < $today) {
        $nextExecution->modify('+1 month');
    }

    return $nextExecution->format('Y-m-d');
}

try {
    // Verifica che la tabella esista
    $check = $conn->query("SHOW TABLES LIKE 'recurring_payments'");
    if ($check->num_rows === 0) {
        throw new Exception('Tabella recurring_payments non trovata');
    }

    $sql = "SELECT 
                rp.*,
                a.name as account_name,
                c.name as category_name,
                i.icon_class,
                c.color
            FROM recurring_payments rp
            LEFT JOIN accounts a ON rp.account_id = a.id
            LEFT JOIN categories c ON rp.category_id = c.id
            LEFT JOIN icons i ON c.icon_id = i.id
            WHERE 1
            ORDER BY rp.is_active DESC, rp.name ASC";

    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'amount' => floatval($row['amount']),
            'type' => $row['type'],
            'account_id' => $row['account_id'],
            'account_name' => $row['account_name'],
            'category_id' => $row['category_id'],
            'category_name' => $row['category_name'],
            'icon_class' => $row['icon_class'] ?? 'bi-calendar-check',
            'color' => $row['color'] ?? '#6c757d',
            'day_of_month' => $row['day_of_month'],
            'next_execution' => getNextExecutionDate($row['day_of_month']),
            'last_execution' => $row['last_execution'],
            'is_active' => (bool)$row['is_active'],
            'secret_key' => $row['secret_key']
        ];
    }

    echo json_encode([
        'success' => true,
        'payments' => $payments
    ]);

} catch (Exception $e) {
    error_log("Error in get_recurring_payments.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 