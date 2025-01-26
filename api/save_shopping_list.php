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
        throw new Exception('Il nome della lista è obbligatorio');
    }

    $name = cleanInput($data['name']);
    $id = isset($data['id']) ? intval($data['id']) : null;

    if ($id) {
        // Verifica che la lista appartenga all'utente
        $stmt = $conn->prepare("SELECT user_id FROM shopping_lists WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $list = $result->fetch_assoc();

        if (!$list || $list['user_id'] !== $_SESSION['user_id']) {
            throw new Exception('Lista non trovata');
        }

        $sql = "UPDATE shopping_lists SET name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);
    } else {
        $sql = "INSERT INTO shopping_lists (name, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $_SESSION['user_id']);
    }

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    echo json_encode([
        'success' => true,
        'id' => $id ?: $conn->insert_id,
        'message' => $id ? 'Lista aggiornata' : 'Lista creata'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 