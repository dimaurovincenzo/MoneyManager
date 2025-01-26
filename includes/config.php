<?php
session_start();

// Configurazione del database
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'moneymanager';

// Connessione al database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Funzione per verificare se l'utente è loggato
function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        return true;
    }

    // Verifica il token di autenticazione persistente
    $token = isset($_COOKIE['remember_token']) ? $_COOKIE['remember_token'] : null;
    if ($token) {
        $user_id = validateAuthToken($token);
        if ($user_id) {
            $_SESSION['user_id'] = $user_id;
            return true;
        }
    }

    return false;
}

// Funzione per richiedere il login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

// Funzione per generare un token casuale
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Funzione per creare un nuovo token di autenticazione
function createAuthToken($user_id) {
    global $conn;
    
    // Genera un nuovo token
    $token = generateToken();
    
    // Imposta la scadenza a 1 anno
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 year'));
    
    // Elimina eventuali token esistenti per l'utente
    $stmt = $conn->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Inserisce il nuovo token
    $stmt = $conn->prepare("INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $token, $expires_at);
    
    if ($stmt->execute()) {
        // Imposta il cookie per 1 anno
        setcookie('remember_token', $token, time() + (86400 * 365), '/', '', true, true);
        return true;
    }
    
    return false;
}

// Funzione per validare un token di autenticazione
function validateAuthToken($token) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT user_id FROM auth_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        return $row['user_id'];
    }
    
    return false;
}

// Funzione per rimuovere il token di autenticazione
function removeAuthToken() {
    global $conn;
    
    $token = isset($_COOKIE['remember_token']) ? $_COOKIE['remember_token'] : null;
    if ($token) {
        // Rimuove il token dal database
        $stmt = $conn->prepare("DELETE FROM auth_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        // Rimuove il cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
}

// Funzione per pulire l'input
function cleanInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}
