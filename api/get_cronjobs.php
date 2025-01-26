<?php
require_once '../includes/config.php';
require_once '../includes/cronjob_config.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Ottieni tutti i cronjob da cron-job.org
    $ch = curl_init(CRONJOB_API_URL . 'jobs');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . CRONJOB_API_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'My VDM App/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    error_log("Request Headers: " . print_r([
        'Authorization: Bearer ' . CRONJOB_API_KEY,
        'Content-Type: application/json',
        'User-Agent: My VDM App/1.0'
    ], true));
    error_log("Curl Info: " . print_r(curl_getinfo($ch), true));
    error_log("Cronjob API Request URL: " . CRONJOB_API_URL . '/jobs');
    error_log("Cronjob API Response Code: " . $httpCode);
    error_log("Cronjob API Response: " . $response);
    
    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Errore nella richiesta a cron-job.org (HTTP ' . $httpCode . ')');
    }

    $cronjobsData = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Errore nel parsing della risposta JSON: ' . json_last_error_msg());
    }
    
    if (!isset($cronjobsData['jobs'])) {
        throw new Exception('Formato risposta non valido: manca il campo jobs');
    }

    // Ottieni i pagamenti ricorrenti dal database
    $sql = "SELECT id, name, cronjob_id FROM recurring_payments WHERE cronjob_id IS NOT NULL";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Errore query database: ' . $conn->error);
    }

    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[$row['cronjob_id']] = $row;
    }

    // Prepara i dati
    $jobs = [];
    $statistics = [
        'total' => 0,
        'active' => 0,
        'paused' => 0,
        'errors' => 0
    ];
    $events = [];

    foreach ($cronjobsData['jobs'] as $job) {
        $jobStatus = determineJobStatus($job);
        $statistics['total']++;
        $statistics[$jobStatus]++;

        $jobs[] = [
            'id' => $job['jobId'],
            'title' => $job['title'],
            'payment_name' => $payments[$job['jobId']]['name'] ?? 'N/A',
            'status' => $jobStatus,
            'enabled' => $job['enabled'],
            'next_execution' => $job['schedule']['nextExecution'] ?? null,
            'last_execution' => $job['lastExecution'] ?? null
        ];

        // Aggiungi gli ultimi eventi
        if (isset($job['lastExecution'])) {
            $events[] = [
                'job_title' => $job['title'],
                'status' => $jobStatus,
                'timestamp' => $job['lastExecution'],
                'message' => $job['lastStatus'] ?? 'Esecuzione completata'
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'jobs' => $jobs,
        'statistics' => $statistics,
        'events' => array_slice($events, 0, 10) // Ultimi 10 eventi
    ]);

} catch (Exception $e) {
    error_log("Error in get_cronjobs.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function determineJobStatus($job) {
    if (!$job['enabled']) return 'paused';
    if (isset($job['lastStatus']) && $job['lastStatus'] !== 200) return 'error';
    return 'active';
} 