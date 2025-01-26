<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disabilita l'output degli errori PHP

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

try {
    $id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : null;
    $name = isset($_POST['name']) ? cleanInput($_POST['name']) : '';
    $description = isset($_POST['description']) ? cleanInput($_POST['description']) : '';
    $icon_id = isset($_POST['icon_id']) ? intval($_POST['icon_id']) : 0;
    $initial_balance = isset($_POST['initial_balance']) ? floatval($_POST['initial_balance']) : 0.00;

    // Validazione
    if (empty($name)) {
        throw new Exception('Il nome è obbligatorio');
    }

    if (!$icon_id) {
        throw new Exception('Seleziona un\'icona');
    }

    $conn->begin_transaction();

    try {
        if ($id) {
            // Recupera il vecchio saldo iniziale
            $stmt = $conn->prepare("SELECT initial_balance, current_balance FROM accounts WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $old_account = $result->fetch_assoc();
            
            if (!$old_account) {
                throw new Exception('Conto non trovato');
            }
            
            // Calcola la differenza nel saldo
            $balance_difference = $initial_balance - $old_account['initial_balance'];
            $new_current_balance = $old_account['current_balance'] + $balance_difference;
            
            // Aggiorna il conto
            $sql = "UPDATE accounts SET 
                    name = ?, 
                    description = ?, 
                    icon_id = ?, 
                    initial_balance = ?,
                    current_balance = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiddi", 
                $name, 
                $description, 
                $icon_id, 
                $initial_balance,
                $new_current_balance,
                $id
            );
        } else {
            // Inserisce nuovo conto
            $sql = "INSERT INTO accounts (name, description, icon_id, initial_balance, current_balance) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssidd", 
                $name, 
                $description, 
                $icon_id, 
                $initial_balance,
                $initial_balance
            );
        }
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $conn->commit();
        echo json_encode([
            'success' => true, 
            'id' => $id ?: $conn->insert_id,
            'message' => $id ? 'Conto aggiornato con successo' : 'Conto creato con successo'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
