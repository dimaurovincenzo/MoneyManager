<?php
// Avvia la sessione all'inizio del file
session_start();

// Genera un nuovo token CSRF se non esiste
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Configurazione del database
$db_config = [
    'host' => 'localhost',
    'dbname' => 'my_vdm',
    'username' => 'root',
    'password' => ''
];

// Funzione per la connessione al database
function connectDB($config) {
    try {
        $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname']);
        if ($conn->connect_error) {
            throw new Exception("Connessione al database fallita: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("Errore: " . $e->getMessage());
    }
}

// Funzione per validare i dati dell'utente
function validateUserData($username, $password, $confirm_password) {
    $errors = [];
    
    // Validazione username
    if (empty($username)) {
        $errors[] = "Username obbligatorio";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username deve essere tra 3 e 50 caratteri";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username può contenere solo lettere, numeri e underscore";
    }
    
    // Validazione password
    if (empty($password)) {
        $errors[] = "Password obbligatoria";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password deve essere di almeno 8 caratteri";
    }
    
    // Conferma password
    if ($password !== $confirm_password) {
        $errors[] = "Le password non coincidono";
    }
    
    return $errors;
}

// Funzione per verificare se l'username esiste già
function usernameExists($conn, $username) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Funzione per creare un nuovo utente
function createUser($conn, $username, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);
    return $stmt->execute();
}

$message = '';
$errors = [];

// Gestione del form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica del token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Errore di validazione del token CSRF");
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validazione
    $errors = validateUserData($username, $password, $confirm_password);
    
    if (empty($errors)) {
        $conn = connectDB($db_config);
        
        // Verifica se l'username esiste già
        if (usernameExists($conn, $username)) {
            $errors[] = "Username già in uso";
        } else {
            // Crea il nuovo utente
            if (createUser($conn, $username, $password)) {
                $message = "Utente creato con successo!";
            } else {
                $errors[] = "Errore durante la creazione dell'utente";
            }
        }
        
        $conn->close();
    }
}

// Rigenera il token CSRF dopo ogni invio del form riuscito
if (!empty($message)) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creazione Utente - My VDM Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../includes/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Nuovo Utente</h2>
                    <a href="../settings.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Torna alle Impostazioni
                    </a>
                </div>
                <div class="card shadow">
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                       required>
                                <div class="form-text">Solo lettere, numeri e underscore (_)</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Minimo 8 caratteri</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Conferma Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Crea Utente</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Bottom -->
    <?php include '../includes/bottom_navbar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 