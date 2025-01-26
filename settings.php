<?php
require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impostazioni - My VDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="includes/styles.css" rel="stylesheet">
    <style>
        .settings-item {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
            transition: background-color 0.2s;
        }
        .settings-item:hover {
            background-color: #f8f9fa;
            color: inherit;
        }
        .settings-item i {
            font-size: 1.5rem;
            margin-right: 1rem;
            width: 2rem;
            text-align: center;
        }
        .settings-group {
            margin-bottom: 1.5rem;
        }
        .settings-group-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #6c757d;
            padding: 0.5rem 1rem;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5 pb-5">
        <h2 class="mb-4">Impostazioni</h2>

        <!-- Gestione Finanziaria -->
        <div class="settings-group">
            <h6 class="settings-group-title">Gestione Finanziaria</h6>
            <div class="card">
                <a href="accounts.php" class="settings-item">
                    <i class="bi bi-wallet2 text-primary"></i>
                    <div>
                        <div>Conti</div>
                        <small class="text-muted">Gestisci i tuoi conti</small>
                    </div>
                </a>
                <a href="categories.php" class="settings-item">
                    <i class="bi bi-tags text-success"></i>
                    <div>
                        <div>Categorie</div>
                        <small class="text-muted">Gestisci le categorie</small>
                    </div>
                </a>
                <a href="recurring_payments.php" class="settings-item">
                    <i class="bi bi-calendar-check text-info"></i>
                    <div>
                        <div>Pagamenti Ricorrenti</div>
                        <small class="text-muted">Gestisci i pagamenti automatici</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- Analisi -->
        <div class="settings-group">
            <h6 class="settings-group-title">Analisi</h6>
            <div class="card">
                <a href="reports.php" class="settings-item">
                    <i class="bi bi-graph-up text-warning"></i>
                    <div>
                        <div>Report</div>
                        <small class="text-muted">Visualizza report e statistiche</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- Amministrazione -->
        <div class="settings-group">
            <h6 class="settings-group-title">Amministrazione</h6>
            <div class="card">
                <a href="#" class="settings-item" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="bi bi-key text-warning"></i>
                    <div>
                        <div>Cambia Password</div>
                        <small class="text-muted">Modifica la tua password</small>
                    </div>
                </a>
                <a href="admin/create_user.php" class="settings-item">
                    <i class="bi bi-person-plus text-danger"></i>
                    <div>
                        <div>Gestione Utenti</div>
                        <small class="text-muted">Crea e gestisci gli utenti</small>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Cambio Password -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambia Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <div class="mb-3">
                            <label class="form-label">Password Attuale</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nuova Password</label>
                            <input type="password" class="form-control" name="new_password" required>
                            <div class="form-text">Minimo 8 caratteri</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Conferma Nuova Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" onclick="changePassword()">Salva</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Bottom -->
    <?php include 'includes/bottom_navbar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changePassword() {
            const form = document.getElementById('changePasswordForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            if (data.new_password !== data.confirm_password) {
                alert('Le password non coincidono');
                return;
            }

            if (data.new_password.length < 8) {
                alert('La nuova password deve essere di almeno 8 caratteri');
                return;
            }

            fetch('api/change_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error);
                }
                alert('Password modificata con successo');
                bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                form.reset();
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message);
            });
        }
    </script>
</body>
</html> 