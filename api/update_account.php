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
    $name = isset($data['name']) ? trim($data['name']) : '';
    $icon = isset($data['icon']) ? trim($data['icon']) : '';
    $color = isset($data['color']) ? trim($data['color']) : '';
    $balance = isset($data['balance']) ? floatval($data['balance']) : 0;

    if (!$id || empty($name)) {
        throw new Exception('Dati mancanti o non validi');
    }

    // Verifica se esiste già un conto con lo stesso nome (escludendo quello corrente)
    $stmt = $conn->prepare("SELECT id FROM accounts WHERE name = ? AND id != ?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Esiste già un conto con questo nome');
    }

    // Aggiorna il conto
    $stmt = $conn->prepare("UPDATE accounts SET name = ?, icon = ?, color = ?, balance = ? WHERE id = ?");
    $stmt->bind_param("sssdi", $name, $icon, $color, $balance, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Errore durante l\'aggiornamento del conto');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 