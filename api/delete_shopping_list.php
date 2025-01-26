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
    
    if (!isset($data['id'])) {
        throw new Exception('ID lista mancante');
    }

    // Verifica che la lista appartenga all'utente
    $stmt = $conn->prepare("SELECT user_id FROM shopping_lists WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $list = $result->fetch_assoc();

    if (!$list || $list['user_id'] !== $_SESSION['user_id']) {
        throw new Exception('Lista non trovata');
    }

    // Elimina prima gli elementi della lista
    $stmt = $conn->prepare("DELETE FROM shopping_list_items WHERE list_id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();

    // Elimina la lista
    $stmt = $conn->prepare("DELETE FROM shopping_lists WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Lista eliminata con successo'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 