<?php
require_once '../includes/config.php';
require_once '../includes/cronjob_config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('ID mancante');
    }

    $id = intval($data['id']);
    
    // Recupera l'ID del cronjob prima di eliminare il pagamento
    $sql = "SELECT cronjob_id FROM recurring_payments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    
    if ($payment && $payment['cronjob_id']) {
        deleteCronjob($payment['cronjob_id']);
    }
    
    // Elimina il pagamento ricorrente
    $sql = "DELETE FROM recurring_payments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Pagamento non trovato');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Pagamento eliminato con successo'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 