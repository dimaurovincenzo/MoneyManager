<?php
require_once 'includes/config.php';

// Password predefinita per l'utente family
$username = 'admin';
$password = 'admin123';

// Genera l'hash della password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifica se l'utente esiste già
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Aggiorna la password dell'utente esistente
    $sql = "UPDATE users SET password = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashed_password, $username);
    
    if ($stmt->execute()) {
        echo "Password aggiornata con successo per l'utente '$username'";
    } else {
        echo "Errore nell'aggiornamento della password: " . $conn->error;
    }
} else {
    // Crea un nuovo utente
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hashed_password);
    
    if ($stmt->execute()) {
        echo "Utente '$username' creato con successo";
    } else {
        echo "Errore nella creazione dell'utente: " . $conn->error;
    }
}

$stmt->close();
$conn->close();
?>
