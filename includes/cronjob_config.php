<?php
function createCronjob($url, $name, $dayOfMonth) {
    try {
        // Genera un ID univoco per il cronjob
        return uniqid('cron_', true);
    } catch (Exception $e) {
        error_log("Errore nella creazione del cronjob: " . $e->getMessage());
        throw new Exception("Errore nella creazione del cronjob: " . $e->getMessage());
    }
}

function deleteCronjob($jobId) {
    return true; // Non c'è più bisogno di eliminare file
}
