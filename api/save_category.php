<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$name = cleanInput($_POST['name']);
$type = cleanInput($_POST['type']);
$icon_id = intval($_POST['icon_id']);
$color = cleanInput($_POST['color']);

if (empty($name) || empty($type)) {
    echo json_encode(['error' => 'Nome e tipo sono obbligatori']);
    exit;
}

if (!in_array($type, ['income', 'expense'])) {
    echo json_encode(['error' => 'Tipo non valido']);
    exit;
}

try {
    if ($id) {
        // Aggiorna la categoria
        $sql = "UPDATE categories SET name = ?, type = ?, icon_id = ?, color = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $name, $type, $icon_id, $color, $id);
    } else {
        // Inserisce nuova categoria
        $sql = "INSERT INTO categories (name, type, icon_id, color) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $name, $type, $icon_id, $color);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $id ?: $conn->insert_id]);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Errore durante il salvataggio: ' . $e->getMessage()]);
}
