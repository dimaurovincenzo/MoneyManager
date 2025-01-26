<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? intval($data['id']) : 0;

    if (!$id) {
        throw new Exception('ID movimento non valido');
    }

    // Inizia la transazione
    $conn->begin_transaction();

    try {
        // Ottieni i dettagli del movimento prima di eliminarlo
        $stmt = $conn->prepare("SELECT amount, type, account_id FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_assoc();

        if (!$transaction) {
            throw new Exception('Movimento non trovato');
        }

        // Aggiorna il saldo del conto
        $amount = $transaction['amount'];
        if ($transaction['type'] === 'expense') {
            $sql = "UPDATE accounts SET current_balance = current_balance + ? WHERE id = ?";
        } else {
            $sql = "UPDATE accounts SET current_balance = current_balance - ? WHERE id = ?";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $amount, $transaction['account_id']);
        $stmt->execute();

        // Elimina il movimento
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Commit della transazione
        $conn->commit();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback in caso di errore
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 