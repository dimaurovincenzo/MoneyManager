<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$amount = floatval($_POST['amount']);
$description = cleanInput($_POST['description']);
$date = $_POST['date'];
$type = cleanInput($_POST['type']);
$account_id = intval($_POST['account_id']);
$category_id = intval($_POST['category_id']);

if (!$id) {
    echo json_encode(['error' => 'ID movimento non valido']);
    exit;
}

if ($amount <= 0) {
    echo json_encode(['error' => 'Importo non valido']);
    exit;
}

if (!in_array($type, ['income', 'expense'])) {
    echo json_encode(['error' => 'Tipo non valido']);
    exit;
}

if (!$account_id || !$category_id) {
    echo json_encode(['error' => 'Seleziona un conto e una categoria']);
    exit;
}

$conn->begin_transaction();

try {
    // Recupera il vecchio movimento
    $stmt = $conn->prepare("SELECT amount, type, account_id FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $old_transaction = $stmt->get_result()->fetch_assoc();

    if (!$old_transaction) {
        throw new Exception('Movimento non trovato');
    }

    // Annulla l'effetto del vecchio movimento
    if ($old_transaction['type'] === 'income') {
        $sql = "UPDATE accounts SET current_balance = current_balance - ? WHERE id = ?";
    } else {
        $sql = "UPDATE accounts SET current_balance = current_balance + ? WHERE id = ?";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $old_transaction['amount'], $old_transaction['account_id']);
    $stmt->execute();

    // Aggiorna il movimento
    $sql = "UPDATE transactions SET amount = ?, description = ?, date = ?, type = ?, 
            account_id = ?, category_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dsssiii", $amount, $description, $date, $type, 
                     $account_id, $category_id, $id);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    // Applica il nuovo movimento
    if ($type === 'income') {
        $sql = "UPDATE accounts SET current_balance = current_balance + ? WHERE id = ?";
    } else {
        $sql = "UPDATE accounts SET current_balance = current_balance - ? WHERE id = ?";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $amount, $account_id);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true]);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'Errore durante l\'aggiornamento: ' . $e->getMessage()]);
} 