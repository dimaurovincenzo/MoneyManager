<?php
require_once 'includes/config.php';

// Rimuovi il token di autenticazione persistente
removeAuthToken();

// Distruggi la sessione
session_destroy();

// Reindirizza alla pagina di login
header('Location: login.php');
exit;
?> 