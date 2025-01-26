<?php
require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conti - My VDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="includes/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Conti</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#accountModal">
                <i class="bi bi-plus-lg"></i> Nuovo Conto
            </button>
        </div>

        <div id="accountsList">
            <!-- I conti verranno caricati qui dinamicamente -->
        </div>
    </div>

    <!-- Modal Nuovo/Modifica Conto -->
    <div class="modal fade" id="accountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuovo Conto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="accountForm">
                        <input type="hidden" id="accountId" name="id">
                        
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descrizione</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Saldo Iniziale</label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" name="initial_balance" 
                                       step="0.01" value="0.00" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Icona</label>
                            <div class="icon-grid" id="iconGrid">
                                <!-- Le icone verranno caricate qui -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="saveAccount">Salva</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Bottom -->
    <?php include 'includes/bottom_navbar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funzione per formattare i numeri come valuta
        function formatCurrency(amount) {
            return (Number(amount) || 0).toFixed(2);
        }

        // Funzione per caricare i conti
        function loadAccounts() {
            fetch('api/get_accounts.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.success || !data.accounts.length) {
                        document.getElementById('accountsList').innerHTML = `
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-wallet2 fs-1 mb-3"></i>
                                <p>Nessun conto presente.<br>Clicca su "Nuovo Conto" per iniziare.</p>
                            </div>`;
                        return;
                    }

                    const accountsList = document.getElementById('accountsList');
                    accountsList.innerHTML = data.accounts.map(account => `
                        <div class="account-card">
                            <div class="account-icon" style="background-color: ${account.color}20">
                                <i class="bi ${account.icon_class}" style="color: ${account.color}"></i>
                            </div>
                            <div class="account-name">${account.name}</div>
                            <div class="account-balance">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Saldo Attuale</span>
                                    <strong>€${formatCurrency(account.current_balance)}</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted">Saldo Iniziale</small>
                                    <small class="text-muted">€${formatCurrency(account.initial_balance)}</small>
                                </div>
                            </div>
                            <div class="account-description text-muted mt-2">
                                ${account.description || 'Nessuna descrizione'}
                            </div>
                            <div class="account-actions mt-3">
                                <button class="btn btn-sm btn-outline-primary me-2" onclick="editAccount(${account.id})">
                                    <i class="bi bi-pencil"></i> Modifica
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteAccount(${account.id})">
                                    <i class="bi bi-trash"></i> Elimina
                                </button>
                            </div>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    document.getElementById('accountsList').innerHTML = `
                        <div class="text-center text-danger py-5">
                            Errore nel caricamento dei conti
                        </div>`;
                });
        }

        // Funzione per caricare le icone
        function loadIcons() {
            fetch('api/get_icons.php?category=account')
                .then(response => response.json())
                .then(data => {
                    const iconGrid = document.getElementById('iconGrid');
                    iconGrid.innerHTML = data.icons.map(icon => `
                        <div class="icon-item" data-icon-id="${icon.id}" onclick="selectIcon(this)">
                            <i class="bi ${icon.icon_class}"></i>
                        </div>
                    `).join('');
                });
        }

        // Funzione per selezionare un'icona
        function selectIcon(element) {
            document.querySelectorAll('.icon-item').forEach(item => 
                item.classList.remove('selected'));
            element.classList.add('selected');
        }

        // Funzione per modificare un conto
        function editAccount(id) {
            fetch(`api/get_account.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('accountId').value = data.id;
                    document.querySelector('input[name="name"]').value = data.name;
                    document.querySelector('textarea[name="description"]').value = data.description || '';
                    document.querySelector('input[name="initial_balance"]').value = 
                        parseFloat(data.initial_balance).toFixed(2);
                    
                    // Seleziona l'icona corretta
                    setTimeout(() => {
                        const iconItem = document.querySelector(`.icon-item[data-icon-id="${data.icon_id}"]`);
                        if (iconItem) {
                            selectIcon(iconItem);
                        }
                    }, 100);

                    document.querySelector('.modal-title').textContent = 'Modifica Conto';
                    const modal = new bootstrap.Modal(document.getElementById('accountModal'));
                    modal.show();
                });
        }

        // Gestione salvataggio
        document.getElementById('saveAccount').addEventListener('click', () => {
            const form = document.getElementById('accountForm');
            const formData = new FormData(form);
            
            const selectedIcon = document.querySelector('.icon-item.selected');
            if (selectedIcon) {
                formData.append('icon_id', selectedIcon.dataset.iconId);
            }

            fetch('api/save_account.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadAccounts();
                    bootstrap.Modal.getInstance(document.getElementById('accountModal')).hide();
                    form.reset();
                    document.querySelectorAll('.icon-item').forEach(item => 
                        item.classList.remove('selected'));
                } else {
                    alert(data.error || 'Errore durante il salvataggio');
                }
            });
        });

        // Reset form quando il modal viene chiuso
        document.getElementById('accountModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('accountForm').reset();
            document.getElementById('accountId').value = '';
            document.querySelectorAll('.icon-item').forEach(item => 
                item.classList.remove('selected'));
            document.querySelector('.modal-title').textContent = 'Nuovo Conto';
        });

        // Modifica l'event listener per il modal quando viene aperto
        document.getElementById('accountModal').addEventListener('show.bs.modal', function(event) {
            // Se il modal viene aperto dal pulsante "Nuovo Conto"
            if (event.relatedTarget && event.relatedTarget.classList.contains('btn-primary')) {
                // Reset completo del form
                document.getElementById('accountForm').reset();
                // Reset esplicito dell'ID nascosto
                document.getElementById('accountId').value = '';
                // Reset del titolo
                document.querySelector('.modal-title').textContent = 'Nuovo Conto';
                // Reset della selezione delle icone
                document.querySelectorAll('.icon-item').forEach(item => 
                    item.classList.remove('selected'));
            }
        });

        // Caricamento iniziale
        loadAccounts();
        loadIcons();

        function deleteAccount(id) {
            fetch('api/delete_account.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.hasTransactions) {
                    if (confirm(`Attenzione: ${data.transactionCount} transazioni verranno eliminate insieme al conto. Vuoi procedere?`)) {
                        // Richiama l'eliminazione con forceDelete
                        fetch('api/delete_account.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: id, forceDelete: true })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                loadAccounts();
                            } else {
                                alert(data.error || 'Errore durante l\'eliminazione del conto');
                            }
                        });
                    }
                } else if (data.success) {
                    loadAccounts();
                } else {
                    alert(data.error || 'Errore durante l\'eliminazione del conto');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore durante l\'eliminazione del conto');
            });
        }

        function editAccount(id) {
            fetch(`api/get_account.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('accountId').value = data.id;
                    document.querySelector('input[name="name"]').value = data.name;
                    document.querySelector('textarea[name="description"]').value = data.description || '';
                    document.querySelector('input[name="initial_balance"]').value = 
                        parseFloat(data.initial_balance).toFixed(2);
                    
                    // Seleziona l'icona corretta
                    setTimeout(() => {
                        const iconItem = document.querySelector(`.icon-item[data-icon-id="${data.icon_id}"]`);
                        if (iconItem) {
                            selectIcon(iconItem);
                        }
                    }, 100);

                    document.querySelector('.modal-title').textContent = 'Modifica Conto';
                    const modal = new bootstrap.Modal(document.getElementById('accountModal'));
                    modal.show();
                });
        }
    </script>
</body>
</html>
