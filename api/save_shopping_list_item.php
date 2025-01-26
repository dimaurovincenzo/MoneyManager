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
    
    if (empty($data['name'])) {
        throw new Exception('Nome articolo obbligatorio');
    }

    $name = cleanInput($data['name']);
    $quantity = isset($data['quantity']) ? cleanInput($data['quantity']) : '1';

    // Se è presente un ID, modifica l'articolo esistente
    if (isset($data['id'])) {
        // Verifica che l'articolo appartenga a una lista dell'utente
        $sql = "UPDATE shopping_list_items sli 
                JOIN shopping_lists sl ON sli.list_id = sl.id 
                SET sli.name = ?, sli.quantity = ? 
                WHERE sli.id = ? AND sl.user_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $name, $quantity, $data['id'], $_SESSION['user_id']);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception('Articolo non trovato o non autorizzato');
        }

    } else {
        // Nuovo articolo
        if (!isset($data['list_id'])) {
            throw new Exception('ID lista obbligatorio');
        }

        // Verifica che la lista appartenga all'utente
        $stmt = $conn->prepare("SELECT 1 FROM shopping_lists WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $data['list_id'], $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result->fetch_assoc()) {
            throw new Exception('Lista non trovata o non autorizzata');
        }

        $list_id = intval($data['list_id']);
        
        $sql = "INSERT INTO shopping_list_items (list_id, name, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $list_id, $name, $quantity);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    echo json_encode([
        'success' => true,
        'id' => isset($data['id']) ? $data['id'] : $conn->insert_id,
        'message' => isset($data['id']) ? 'Articolo modificato' : 'Articolo aggiunto'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}