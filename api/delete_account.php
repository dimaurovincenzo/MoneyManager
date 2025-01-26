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
    $forceDelete = isset($data['forceDelete']) ? (bool)$data['forceDelete'] : false;

    if (!$id) {
        throw new Exception('ID conto non valido');
    }

    // Verifica se ci sono movimenti associati
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transactions WHERE account_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    if ($count > 0 && !$forceDelete) {
        echo json_encode([
            'success' => false,
            'error' => 'Ci sono ' . $count . ' transazioni associate a questo conto.',
            'hasTransactions' => true,
            'transactionCount' => $count
        ]);
        exit;
    }

    // Inizia la transazione
    $conn->begin_transaction();

    try {
        // Se richiesto, elimina prima tutte le transazioni
        if ($count > 0) {
            $stmt = $conn->prepare("DELETE FROM transactions WHERE account_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }

        // Elimina il conto
        $stmt = $conn->prepare("DELETE FROM accounts WHERE id = ?");
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