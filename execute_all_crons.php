<?php
// Ottieni il percorso della directory corrente
$currentDir = dirname(__FILE__);

// Rimuovi la parte del percorso dopo /membri/vdm
$basePath = preg_replace('/\/membri\/vdm\/.*/', '/membri/vdm', $currentDir);

// Includi il file di configurazione usando il percorso base
require_once $basePath . '/includes/config.php';

// Buffer per memorizzare i messaggi
$messages = [];
$errors = [];
$successCount = 0;
$errorCount = 0;

// Funzione per aggiungere un messaggio
function addMessage($message, $isError = false) {
    global $messages, $errors, $successCount, $errorCount;
    if ($isError) {
        $errors[] = $message;
        $errorCount++;
    } else {
        $messages[] = $message;
        $successCount++;
    }
    error_log($message);
}

// Imposta il timeout a 5 minuti
set_time_limit(300);

addMessage("[Cron Master] Inizio esecuzione cronjob - " . date('Y-m-d H:i:s'));

try {
    // Ottieni tutti i pagamenti ricorrenti attivi che devono essere eseguiti oggi
    $sql = "SELECT rp.*, a.current_balance 
            FROM recurring_payments rp
            JOIN accounts a ON rp.account_id = a.id 
            WHERE rp.is_active = 1 
            AND rp.day_of_month = ?
            AND (rp.last_execution IS NULL OR DATE(rp.last_execution) < CURDATE())";
            
    $stmt = $conn->prepare($sql);
    $currentDay = intval(date('j')); // Giorno corrente del mese
    $stmt->bind_param('i', $currentDay);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($payment = $result->fetch_assoc()) {
            addMessage("[Cron Master] Elaborazione pagamento: {$payment['name']}");
            
            try {
                // Inizia una transazione
                $conn->begin_transaction();
                
                // 1. Aggiorna il saldo del conto
                $newBalance = $payment['type'] === 'income' 
                    ? $payment['current_balance'] + $payment['amount']
                    : $payment['current_balance'] - $payment['amount'];
                
                $updateAccount = $conn->prepare("UPDATE accounts SET current_balance = ? WHERE id = ?");
                $updateAccount->bind_param('di', $newBalance, $payment['account_id']);
                $updateAccount->execute();

                // 2. Inserisci la transazione
                $insertTrans = $conn->prepare("INSERT INTO transactions 
                    (account_id, category_id, amount, type, date, description) 
                    VALUES (?, ?, ?, ?, CURDATE(), ?)");
                $description = "Pagamento automatico: " . $payment['name'];
                $insertTrans->bind_param('iidss', 
                    $payment['account_id'],
                    $payment['category_id'],
                    $payment['amount'],
                    $payment['type'],
                    $description
                );
                $insertTrans->execute();

                // 3. Aggiorna la data di ultima esecuzione e prossima esecuzione
                $nextMonth = date('Y-m-d', strtotime('+1 month'));
                $updatePayment = $conn->prepare("UPDATE recurring_payments 
                    SET last_execution = CURDATE(),
                        next_execution = ?
                    WHERE id = ?");
                $updatePayment->bind_param('si', $nextMonth, $payment['id']);
                $updatePayment->execute();

                // Commit della transazione
                $conn->commit();
                
                addMessage("[Cron Master] Pagamento eseguito con successo: {$payment['name']} - Importo: €{$payment['amount']}");
                
            } catch (Exception $e) {
                // Rollback in caso di errore
                $conn->rollback();
                addMessage("[Cron Master] Errore nell'esecuzione del pagamento {$payment['name']}: " . $e->getMessage(), true);
            }
        }
    } else {
        addMessage("[Cron Master] Nessun pagamento da eseguire oggi");
    }
    
    addMessage("[Cron Master] Esecuzione completata con successo");

} catch (Exception $e) {
    addMessage("[Cron Master] Errore generale: " . $e->getMessage(), true);
}

addMessage("[Cron Master] Fine esecuzione cronjob - " . date('Y-m-d H:i:s'));

// Output HTML
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esecuzione Cron Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Risultati Esecuzione Cron Jobs</h2>
        
        <!-- Riepilogo -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Riepilogo</h5>
                        <p class="mb-1">
                            <i class="bi bi-check-circle text-success"></i> 
                            Operazioni completate: <?php echo $successCount; ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-exclamation-circle text-danger"></i> 
                            Errori: <?php echo $errorCount; ?>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-clock"></i> 
                            Data esecuzione: <?php echo date('d/m/Y H:i:s'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Operazioni -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Log Operazioni</h5>
            </div>
            <div class="card-body">
                <?php foreach ($messages as $message): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Errori -->
        <?php if (!empty($errors)): ?>
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">Errori Riscontrati</h5>
            </div>
            <div class="card-body">
                <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pulsante per tornare alla dashboard -->
        <div class="text-center mb-4">
            <a href="index.php" class="btn btn-primary">
                <i class="bi bi-house"></i> Torna alla Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 