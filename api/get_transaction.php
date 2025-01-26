<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID non specificato']);
    exit;
}

$id = intval($_GET['id']);

try {
    $sql = "SELECT t.id, t.amount, t.description, t.date, t.type, t.account_id, t.category_id,
                   a.name as account_name, c.name as category_name, i.icon_class, c.color
            FROM transactions t
            JOIN accounts a ON t.account_id = a.id
            JOIN categories c ON t.category_id = c.id
            JOIN icons i ON c.icon_id = i.id
            WHERE t.id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Movimento non trovato');
    }
    
    $transaction = $result->fetch_assoc();
    echo json_encode($transaction);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 