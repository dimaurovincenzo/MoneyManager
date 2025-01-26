<?php
require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamenti Ricorrenti - My VDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="includes/styles.css" rel="stylesheet">
    <style>
        .accounts-grid, .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .account-item, .category-item {
            text-align: center;
            padding: 0.75rem;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .account-item:hover, .category-item:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        
        .account-item.selected, .category-item.selected {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        
        .account-item i, .category-item i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .account-item span, .category-item span {
            font-size: 0.8rem;
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Stili aggiuntivi per le griglie nel modal */
        .modal .accounts-grid, .modal .categories-grid {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Pagamenti Ricorrenti</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="bi bi-plus-lg"></i> Nuovo Pagamento
            </button>
        </div>

        <div id="recurringPayments">
            <!-- I pagamenti verranno caricati qui -->
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuovo Pagamento Ricorrente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <input type="hidden" name="id" id="paymentId">
                        <input type="hidden" name="account_id" id="accountId">
                        <input type="hidden" name="category_id" id="categoryId">
                        
                        <!-- Tipo movimento -->
                        <div class="mb-3">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="typeExpense" value="expense" checked>
                                <label class="btn btn-outline-danger" for="typeExpense">
                                    <i class="bi bi-graph-down-arrow"></i> Uscita
                                </label>
                                
                                <input type="radio" class="btn-check" name="type" id="typeIncome" value="income">
                                <label class="btn btn-outline-success" for="typeIncome">
                                    <i class="bi bi-graph-up-arrow"></i> Entrata
                                </label>
                            </div>
                        </div>

                        <!-- Nome e Importo -->
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Importo</label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" name="amount" step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descrizione (opzionale)</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giorno del mese per l'esecuzione</label>
                            <div class="row">
                                <div class="col-sm-6">
                                    <input type="number" class="form-control" name="day_of_month" 
                                           min="1" max="31" required placeholder="es. 1">
                                    <div class="form-text">
                                        Inserisci un numero da 1 a 31
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selezione Conto -->
                        <div class="mb-4">
                            <label class="form-label">Seleziona il conto</label>
                            <div class="accounts-grid" id="accountsGrid">
                                <!-- I conti verranno caricati qui -->
                            </div>
                        </div>

                        <!-- Selezione Categoria -->
                        <div class="mb-4">
                            <label class="form-label">Seleziona la categoria</label>
                            <div class="categories-grid" id="categoriesGrid">
                                <!-- Le categorie verranno caricate qui -->
                            </div>
                        </div>

                        <!-- Info Cronjob -->
                        <div id="cronInfo" class="mb-3">
                            <div class="alert alert-info">
                                <small>
                                    <i class="bi bi-info-circle"></i> 
                                    Il pagamento verrà eseguito automaticamente il giorno selezionato di ogni mese
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="saveButton">Salva</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Bottom -->
    <?php include 'includes/bottom_navbar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dichiarazione variabili globali
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        let editingId = null;
        let categories = [];
        let accounts = [];

        // Funzione per aggiornare la griglia dei conti
        function updateAccountsGrid() {
            const grid = document.getElementById('accountsGrid');
            if (!grid) return;
            
            grid.innerHTML = accounts.map(account => `
                <div class="account-item" 
                     onclick="selectAccount(${account.id}, this)" 
                     data-account-id="${account.id}">
                    <i class="bi ${account.icon_class}" style="color: ${account.color}"></i>
                    <span>${account.name}</span>
                </div>
            `).join('');
        }

        // Funzione per aggiornare la griglia delle categorie
        function updateCategoriesGrid(type) {
            const grid = document.getElementById('categoriesGrid');
            if (!grid) return;
            
            const filteredCategories = categories.filter(cat => cat.type === type);
            grid.innerHTML = filteredCategories.map(category => `
                <div class="category-item" 
                     onclick="selectCategory(${category.id}, this)" 
                     data-category-id="${category.id}">
                    <i class="bi ${category.icon_class}" style="color: ${category.color}"></i>
                    <span>${category.name}</span>
                </div>
            `).join('');
        }

        // Funzione per selezionare un conto
        function selectAccount(id, element) {
            document.querySelectorAll('.account-item').forEach(item => {
                item.classList.remove('selected');
            });
            element.classList.add('selected');
            document.getElementById('accountId').value = id;
        }

        // Funzione per selezionare una categoria
        function selectCategory(id, element) {
            document.querySelectorAll('.category-item').forEach(item => {
                item.classList.remove('selected');
            });
            element.classList.add('selected');
            document.getElementById('categoryId').value = id;
        }

        // Carica i dati iniziali
        async function loadInitialData() {
            try {
                const [accountsResponse, categoriesResponse] = await Promise.all([
                    fetch('api/get_accounts.php'),
                    fetch('api/get_categories.php')
                ]);

                const accountsData = await accountsResponse.json();
                const categoriesData = await categoriesResponse.json();

                if (!accountsData.success || !categoriesData.success) {
                    throw new Error('Errore nel caricamento dei dati');
                }

                accounts = accountsData.accounts;
                categories = categoriesData.categories;

                await loadPayments();
            } catch (error) {
                console.error('Error:', error);
                alert('Errore nel caricamento dei dati: ' + error.message);
            }
        }

        // Carica i pagamenti ricorrenti
        async function loadPayments() {
            try {
                const response = await fetch('api/get_recurring_payments.php');
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Errore nel caricamento dei pagamenti');
                }

                const container = document.getElementById('recurringPayments');
                if (!data.payments || data.payments.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-calendar-check fs-1 mb-3"></i>
                            <p>Nessun pagamento ricorrente.<br>Crea un nuovo pagamento per iniziare.</p>
                        </div>`;
                    return;
                }

                container.innerHTML = data.payments.map(payment => `
                    <div class="card mb-3 ${!payment.is_active ? 'bg-light' : ''}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="category-icon me-3" style="background-color: ${payment.color}20">
                                        <i class="bi ${payment.icon_class}" style="color: ${payment.color}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">${payment.name}</h6>
                                        <small class="text-muted">
                                            ${payment.account_name} • ${payment.category_name}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="${payment.type === 'expense' ? 'text-danger' : 'text-success'}">
                                        ${payment.type === 'expense' ? '-' : '+'}€${payment.amount.toFixed(2)}
                                    </div>
                                    <small class="text-muted">
                                        Giorno ${payment.day_of_month} del mese
                                    </small>
                                </div>
                            </div>
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted" style="font-size: 0.7rem;">
                                        Prossima esecuzione: ${new Date(payment.next_execution).toLocaleDateString()}
                                    </span>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="editPayment(${payment.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-${payment.is_active ? 'danger' : 'success'}" 
                                            onclick="togglePayment(${payment.id})">
                                        <i class="bi bi-${payment.is_active ? 'pause' : 'play'}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deletePayment(${payment.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" 
                                            onclick="copyPaymentUrl('${payment.secret_key}')">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('recurringPayments').innerHTML = `
                    <div class="alert alert-danger">
                        Errore nel caricamento dei pagamenti: ${error.message}
                    </div>`;
            }
        }

        // Funzione per salvare il pagamento
        function savePayment() {
            const form = document.getElementById('paymentForm');
            const formData = new FormData(form);
            const data = {};
            
            // Raccogli tutti i dati del form
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Aggiungi i valori degli input nascosti
            data.account_id = document.getElementById('accountId').value;
            data.category_id = document.getElementById('categoryId').value;
            
            // Validazione
            if (!data.name || !data.amount || !data.day_of_month || !data.account_id || !data.category_id) {
                alert('Compila tutti i campi obbligatori');
                return;
            }

            // Validazione giorno del mese
            const day = parseInt(data.day_of_month);
            if (day < 1 || day > 31) {
                alert('Il giorno del mese deve essere compreso tra 1 e 31');
                return;
            }

            // Mostra indicatore di caricamento
            const saveButton = document.getElementById('saveButton');
            const originalText = saveButton.innerHTML;
            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvataggio...';
            saveButton.disabled = true;

            // Aggiungi l'ID se stiamo modificando
            if (editingId) {
                data.id = editingId;
            }

            fetch('api/save_recurring_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    throw new Error(result.error || 'Errore nel salvataggio');
                }
                
                // Chiudi il modal e resetta il form
                const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                modal.hide();
                form.reset();
                editingId = null;
                
                // Ricarica i pagamenti
                loadPayments();
                
                // Mostra messaggio di successo
                alert(result.message || 'Pagamento salvato con successo');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore nel salvataggio del pagamento: ' + error.message);
            })
            .finally(() => {
                // Ripristina il pulsante
                saveButton.innerHTML = originalText;
                saveButton.disabled = false;
            });
        }

        // Aggiungiamo anche un controllo per debug delle griglie
        function debugGrids() {
            console.log('Accounts Grid:', document.getElementById('accountsGrid')?.innerHTML);
            console.log('Categories Grid:', document.getElementById('categoriesGrid')?.innerHTML);
            console.log('Accounts Data:', accounts);
            console.log('Categories Data:', categories);
        }

        // Modifica un pagamento
        function editPayment(id) {
            editingId = id;
            fetch(`api/get_recurring_payment.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success || !data.payment) {
                        throw new Error(data.error || 'Pagamento non trovato');
                    }
                    const payment = data.payment;

                    const form = document.getElementById('paymentForm');
                    form.querySelector('[name="name"]').value = payment.name;
                    form.querySelector('[name="description"]').value = payment.description || '';
                    form.querySelector('[name="amount"]').value = payment.amount;
                    form.querySelector(`[name="type"][value="${payment.type}"]`).checked = true;
                    document.getElementById('accountId').value = payment.account_id;
                    document.getElementById('categoryId').value = payment.category_id;
                    form.querySelector('[name="day_of_month"]').value = payment.day_of_month;

                    // Aggiorna il titolo del modal
                    document.querySelector('.modal-title').textContent = 'Modifica Pagamento';
                    
                    // Mostra info sul cronjob
                    const cronInfo = document.getElementById('cronInfo');
                    if (cronInfo) {
                        cronInfo.innerHTML = `
                            <div class="alert alert-info">
                                <small>
                                    <i class="bi bi-info-circle"></i> 
                                    Questo pagamento verrà eseguito automaticamente il giorno ${payment.day_of_month} di ogni mese
                                </small>
                            </div>`;
                    }

                    paymentModal.show();
                    
                    // Aggiorna le griglie e seleziona gli elementi corretti
                    updateAccountsGrid();
                    updateCategoriesGrid(payment.type);
                    setTimeout(() => {
                        // Seleziona il conto
                        const accountItem = document.querySelector(`.account-item[data-account-id="${payment.account_id}"]`);
                        if (accountItem) accountItem.classList.add('selected');
                        
                        // Seleziona la categoria
                        const categoryItem = document.querySelector(`.category-item[data-category-id="${payment.category_id}"]`);
                        if (categoryItem) categoryItem.classList.add('selected');
                    }, 100);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Errore nel caricamento del pagamento: ' + error.message);
                });
        }

        // Toggle stato attivo/inattivo
        function togglePayment(id) {
            fetch('api/toggle_recurring_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error);
                }
                loadPayments();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore nell\'aggiornamento dello stato');
            });
        }

        // Copia URL per cron-job.org
        function copyPaymentUrl(key) {
            const url = `${window.location.origin}/api/execute_recurring_payment.php?key=${key}`;
            navigator.clipboard.writeText(url).then(() => {
                alert('URL copiato negli appunti');
            });
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', () => {
            // Gestione modal
            const modalElement = document.getElementById('paymentModal');
            if (modalElement) {
                modalElement.addEventListener('show.bs.modal', () => {
                    const form = document.getElementById('paymentForm');
                    form.reset();
                    document.getElementById('accountId').value = '';
                    document.getElementById('categoryId').value = '';
                    
                    setTimeout(() => {
                        const selectedType = document.querySelector('input[name="type"]:checked').value;
                        updateAccountsGrid();
                        updateCategoriesGrid(selectedType);
                    }, 200);
                });

                modalElement.addEventListener('hidden.bs.modal', () => {
                    editingId = null;
                    document.querySelector('.modal-title').textContent = 'Nuovo Pagamento';
                });
            }

            // Gestione pulsante salvataggio
            const saveButton = document.getElementById('saveButton');
            if (saveButton) {
                saveButton.addEventListener('click', savePayment);
            }

            // Gestione tipo pagamento
            document.querySelectorAll('input[name="type"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    updateCategoriesGrid(e.target.value);
                });
            });

            // Caricamento iniziale
            loadInitialData().catch(error => {
                console.error('Initialization error:', error);
                alert('Errore nell\'inizializzazione dell\'applicazione');
            });
        });

        // Aggiungi questa funzione dopo togglePayment
        function deletePayment(id) {
            if (!confirm('Sei sicuro di voler eliminare questo pagamento ricorrente?')) {
                return;
            }

            fetch('api/delete_recurring_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Errore durante l\'eliminazione');
                }
                loadPayments();
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore nell\'eliminazione del pagamento: ' + error.message);
            });
        }
    </script>
</body>
</html> 