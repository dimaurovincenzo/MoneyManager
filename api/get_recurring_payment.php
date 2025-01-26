<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID mancante']);
    exit;
}

try {
    $id = intval($_GET['id']);
    
    $sql = "SELECT rp.* FROM recurring_payments rp 
            WHERE rp.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Pagamento non trovato');
    }

    $payment = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'payment' => $payment
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 