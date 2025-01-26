<?php
require_once '../includes/config.php';
require_once '../includes/cronjob_config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validazione dati
    if (empty($data['name']) || !isset($data['amount']) || !isset($data['day_of_month'])) {
        throw new Exception('Dati mancanti');
    }

    $name = cleanInput($data['name']);
    $description = cleanInput($data['description'] ?? '');
    $amount = floatval($data['amount']);
    $type = in_array($data['type'], ['income', 'expense']) ? $data['type'] : 'expense';
    $account_id = intval($data['account_id']);
    $category_id = intval($data['category_id']);
    $day_of_month = min(31, max(1, intval($data['day_of_month'])));
    $id = isset($data['id']) ? intval($data['id']) : null;

    // Calcola prossima esecuzione
    $today = new DateTime();
    $execution_date = new DateTime($today->format('Y-m-') . str_pad($day_of_month, 2, '0', STR_PAD_LEFT));
    if (intval($today->format('d')) > $day_of_month) {
        $execution_date->modify('+1 month');
    }
    $next_execution = $execution_date->format('Y-m-d');

    // Genera chiave segreta per l'URL
    $secret_key = bin2hex(random_bytes(32));
    $executeUrl = "https://" . $_SERVER['HTTP_HOST'] . "/api/execute_recurring_payment.php?key=" . $secret_key;

    if ($id) {
        // Aggiornamento
        $stmt = $conn->prepare("SELECT cronjob_id FROM recurring_payments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $old_cronjob = $result->fetch_assoc();

        if ($old_cronjob && $old_cronjob['cronjob_id']) {
            deleteCronjob($old_cronjob['cronjob_id']);
        }
    }

    // Crea nuovo cronjob
    $cronjob_id = createCronjob($executeUrl, $name, $day_of_month);

    if ($id) {
        $sql = "UPDATE recurring_payments SET 
                name = ?, description = ?, amount = ?, type = ?,
                account_id = ?, category_id = ?, day_of_month = ?,
                next_execution = ?, cronjob_id = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsiiissi", 
            $name, $description, $amount, $type,
            $account_id, $category_id, $day_of_month,
            $next_execution, $cronjob_id, $id
        );
    } else {
        $sql = "INSERT INTO recurring_payments (
                    name, description, amount, type, account_id, 
                    category_id, day_of_month, next_execution, secret_key,
                    cronjob_id, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsiiisss", 
            $name, $description, $amount, $type,
            $account_id, $category_id, $day_of_month,
            $next_execution, $secret_key, $cronjob_id
        );
    }

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    echo json_encode([
        'success' => true,
        'id' => $id ?: $conn->insert_id,
        'message' => $id ? 'Pagamento aggiornato' : 'Pagamento creato'
    ]);

} catch (Exception $e) {
    error_log("Errore nel salvataggio del pagamento ricorrente: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 