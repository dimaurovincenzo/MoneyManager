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
    $type = isset($data['type']) ? trim($data['type']) : '';

    if (!$id || empty($name) || empty($type)) {
        throw new Exception('Dati mancanti o non validi');
    }

    if (!in_array($type, ['income', 'expense'])) {
        throw new Exception('Tipo categoria non valido');
    }

    // Verifica se esiste già una categoria con lo stesso nome (escludendo quella corrente)
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Esiste già una categoria con questo nome');
    }

    // Aggiorna la categoria
    $stmt = $conn->prepare("UPDATE categories SET name = ?, icon = ?, color = ?, type = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $icon, $color, $type, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Errore durante l\'aggiornamento della categoria');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 