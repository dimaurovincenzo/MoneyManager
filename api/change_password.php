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
    
    if (empty($data['current_password']) || empty($data['new_password'])) {
        throw new Exception('Dati mancanti');
    }

    // Verifica la password attuale
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!password_verify($data['current_password'], $user['password'])) {
        throw new Exception('Password attuale non corretta');
    }

    // Aggiorna la password
    $new_password = password_hash($data['new_password'], PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_password, $_SESSION['user_id']);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Password aggiornata con successo'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 