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
    if ($id) {
        // Recupera il vecchio movimento
        $stmt = $conn->prepare("SELECT amount, type, account_id FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $old_transaction = $stmt->get_result()->fetch_assoc();

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
    } else {
        // Inserisce nuovo movimento
        $sql = "INSERT INTO transactions (amount, description, date, type, account_id, category_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dsssii", $amount, $description, $date, $type, 
                         $account_id, $category_id);
    }

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    // Aggiorna il saldo del conto
    if ($type === 'income') {
        $sql = "UPDATE accounts SET current_balance = current_balance + ? WHERE id = ?";
    } else {
        $sql = "UPDATE accounts SET current_balance = current_balance - ? WHERE id = ?";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $amount, $account_id);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'id' => $id ?: $conn->insert_id]);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'Errore durante il salvataggio: ' . $e->getMessage()]);
}
