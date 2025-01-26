<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

try {
    $key = cleanInput($_GET['key']);
    
    // Log per debug
    error_log("Executing recurring payment with key: " . $key);
    error_log("Current date: " . date('Y-m-d'));
    
    // Recupera il pagamento ricorrente
    $sql = "SELECT * FROM recurring_payments 
            WHERE secret_key = ? 
            AND is_active = 1 
            AND next_execution = CURRENT_DATE()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();

    // Log per debug
    if (!$payment) {
        error_log("Payment not found or not due. SQL: " . $sql);
        error_log("Parameters: key=" . $key);
    } else {
        error_log("Payment found: " . json_encode($payment));
    }

    if (!$payment) {
        // Verifica se il pagamento esiste ma non è ancora previsto
        $sql = "SELECT next_execution, is_active FROM recurring_payments WHERE secret_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $checkPayment = $result->fetch_assoc();
        
        if ($checkPayment) {
            if (!$checkPayment['is_active']) {
                throw new Exception('Il pagamento è disattivato');
            } else {
                throw new Exception('Il pagamento è previsto per il ' . date('d/m/Y', strtotime($checkPayment['next_execution'])));
            }
        } else {
            throw new Exception('Pagamento non trovato o non ancora previsto');
        }
    }

    // Inizia la transazione
    $conn->begin_transaction();

    try {
        // Inserisci la transazione
        $sql = "INSERT INTO transactions (
                    account_id, category_id, amount, type, date, description
                ) VALUES (?, ?, ?, ?, CURRENT_DATE(), ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iidss",
            $payment['account_id'],
            $payment['category_id'],
            $payment['amount'],
            $payment['type'],
            $payment['description']
        );
        $stmt->execute();

        // Aggiorna il saldo del conto
        $amount = $payment['type'] === 'income' ? $payment['amount'] : -$payment['amount'];
        $sql = "UPDATE accounts 
                SET current_balance = current_balance + ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $amount, $payment['account_id']);
        $stmt->execute();

        // Aggiorna la data di esecuzione del pagamento ricorrente
        $sql = "UPDATE recurring_payments 
                SET last_execution = CURRENT_DATE(),
                    next_execution = DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH)
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $payment['id']);
        $stmt->execute();

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pagamento ricorrente eseguito con successo'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 