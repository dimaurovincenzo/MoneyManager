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
        throw new Exception('ID articolo mancante');
    }

    // Verifica che l'articolo appartenga a una lista dell'utente
    $sql = "UPDATE shopping_list_items sli 
            JOIN shopping_lists sl ON sli.list_id = sl.id 
            SET sli.is_checked = NOT sli.is_checked 
            WHERE sli.id = ? AND sl.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $data['id'], $_SESSION['user_id']);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Articolo non trovato');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Stato articolo aggiornato'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 