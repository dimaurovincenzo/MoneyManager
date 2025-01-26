<?php
require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimenti - My VDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="includes/styles.css" rel="stylesheet">
    <style>
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .category-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
            background-color: white;
        }

        .category-item i {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .category-item .name {
            font-size: 0.8rem;
            margin-top: 5px;
            word-break: break-word;
        }

        .category-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .category-item.selected {
            border-width: 2px;
        }

        .account-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .account-select-card {
            display: flex;
            flex-direction: column;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            background-color: white;
            transition: all 0.2s ease;
        }

        .account-select-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .account-select-card.selected {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .account-select-card .name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .account-select-card .balance {
            font-size: 0.9rem;
            color: #6c757d;
        }

        @media (max-width: 576px) {
            .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 8px;
            }

            .account-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 8px;
            }

            .category-item {
                padding: 8px;
            }

            .category-item i {
                font-size: 1.2rem;
            }

            .category-item .name {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Movimenti</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transactionModal">
                <i class="bi bi-plus-lg"></i> Nuovo
            </button>
        </div>

        <div class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" 
                       placeholder="Cerca movimento...">
                <button class="btn btn-outline-secondary" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
            
            <div class="collapse mt-2" id="filterCollapse">
                <div class="card card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Data da</label>
                            <input type="date" class="form-control" id="dateFrom">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Data a</label>
                            <input type="date" class="form-control" id="dateTo">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Conto</label>
                            <select class="form-select" id="accountFilter">
                                <option value="">Tutti i conti</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Categoria</label>
                            <select class="form-select" id="categoryFilter">
                                <option value="">Tutte le categorie</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="btn-group w-100">
                                <input type="radio" class="btn-check" name="typeFilter" 
                                       id="typeFilterAll" value="" checked>
                                <label class="btn btn-outline-secondary" for="typeFilterAll">Tutti</label>
                                
                                <input type="radio" class="btn-check" name="typeFilter" 
                                       id="typeFilterExpense" value="expense">
                                <label class="btn btn-outline-danger" for="typeFilterExpense">Uscite</label>
                                
                                <input type="radio" class="btn-check" name="typeFilter" 
                                       id="typeFilterIncome" value="income">
                                <label class="btn btn-outline-success" for="typeFilterIncome">Entrate</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="transactionsList">
            <!-- I movimenti verranno caricati qui -->
        </div>

        <div class="text-center mt-4">
            <button class="btn btn-outline-primary" id="loadMore">Carica altri</button>
        </div>
    </div>

    <!-- Modal Nuovo/Modifica Movimento -->
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionModalLabel">Nuovo Movimento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="transactionForm" autocomplete="off">
                        <input type="hidden" id="transactionId" name="id">
                        
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

                        <!-- Importo -->
                        <div class="mb-3">
                            <label for="amount" class="form-label">Importo</label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="amount" 
                                       name="amount" 
                                       step="0.01" 
                                       min="0.01" 
                                       required 
                                       inputmode="decimal"
                                       pattern="[0-9]*[.,]?[0-9]*">
                            </div>
                        </div>

                        <!-- Data -->
                        <div class="mb-3">
                            <label for="date" class="form-label">Data</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>

                        <!-- Descrizione -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Descrizione</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>

                        <!-- Selezione Conto -->
                        <div class="mb-3">
                            <label class="form-label">Conto</label>
                            <div class="account-grid" id="accountGrid">
                                <!-- I conti verranno caricati dinamicamente -->
                            </div>
                        </div>

                        <!-- Selezione Categoria -->
                        <div class="mb-3">
                            <label class="form-label">Categoria</label>
                            <div class="category-grid" id="categoryGrid">
                                <!-- Le categorie verranno caricate dinamicamente -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="saveTransaction">Salva</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Bottom -->
    <?php include 'includes/bottom_navbar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPage = 1;
        const pageSize = 20;
        let hasMoreTransactions = true;

        // Imposta la data di oggi nel form
        document.querySelector('input[name="date"]').value = new Date().toISOString().split('T')[0];

        // Carica i conti per la griglia
        function loadAccountGrid() {
            fetch('api/get_accounts.php')
                .then(response => response.json())
                .then(data => {
                    const accountGrid = document.getElementById('accountGrid');
                    accountGrid.innerHTML = data.accounts.map(account => `
                        <div class="account-select-card" data-account-id="${account.id}">
                            <div class="account-select-icon" style="color: ${account.color}">
                                <i class="bi ${account.icon_class}"></i>
                            </div>
                            <div class="account-select-name">${account.name}</div>
                        </div>
                    `).join('');

                    // Event listener per la selezione dei conti
                    document.querySelectorAll('.account-select-card').forEach(item => {
                        item.addEventListener('click', () => {
                            document.querySelectorAll('.account-select-card').forEach(i => 
                                i.classList.remove('selected'));
                            item.classList.add('selected');
                        });
                    });

                    // Popola anche il filtro conti
                    const accountFilter = document.getElementById('accountFilter');
                    accountFilter.innerHTML = '<option value="">Tutti i conti</option>' + 
                        data.accounts.map(account => `
                            <option value="${account.id}">${account.name}</option>
                        `).join('');
                });
        }

        // Carica le categorie per la griglia
        function loadCategoryGrid() {
            return new Promise((resolve, reject) => {
                const categoryGrid = document.getElementById('categoryGrid');
                if (!categoryGrid) {
                    reject('Grid delle categorie non trovato');
                    return;
                }

                const typeRadio = document.querySelector('input[name="type"]:checked');
                const currentType = typeRadio ? typeRadio.value : 'expense';

                fetch('api/get_categories.php')
                    .then(response => response.json())
                    .then(data => {
                        const filteredCategories = data.categories.filter(cat => cat.type === currentType);
                        
                        categoryGrid.innerHTML = filteredCategories.map(category => `
                            <div class="category-item" 
                                 data-category-id="${category.id}"
                                 data-category-color="${category.color}">
                                <i class="bi ${category.icon_class}" style="color: ${category.color}"></i>
                                <div class="name">${category.name}</div>
                            </div>
                        `).join('');

                        // Event listener per la selezione delle categorie
                        document.querySelectorAll('.category-item').forEach(item => {
                            item.addEventListener('click', () => {
                                document.querySelectorAll('.category-item').forEach(i => {
                                    i.classList.remove('selected');
                                    i.style.borderColor = '#dee2e6';
                                    i.style.backgroundColor = 'white';
                                });
                                item.classList.add('selected');
                                const color = item.dataset.categoryColor;
                                item.style.borderColor = color;
                                item.style.backgroundColor = `${color}20`;
                            });
                        });

                        resolve();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        categoryGrid.innerHTML = '<div class="alert alert-danger">Errore nel caricamento delle categorie</div>';
                        reject(error);
                    });
            });
        }

        // Funzione per caricare i movimenti
        function loadTransactions(page = 1, append = false) {
            const accountFilter = document.getElementById('accountFilter');
            const categoryFilter = document.getElementById('categoryFilter');
            const typeFilter = document.querySelector('input[name="typeFilter"]:checked');
            const searchInput = document.getElementById('searchInput');
            const dateFromFilter = document.getElementById('dateFrom');
            const dateToFilter = document.getElementById('dateTo');

            const params = new URLSearchParams({
                page: page,
                account_id: accountFilter ? accountFilter.value : '',
                category_id: categoryFilter ? categoryFilter.value : '',
                type: typeFilter ? typeFilter.value : '',
                search: searchInput ? searchInput.value : '',
                date_from: dateFromFilter ? dateFromFilter.value : '',
                date_to: dateToFilter ? dateToFilter.value : ''
            });

            fetch(`api/get_transactions.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    const transactionsList = document.getElementById('transactionsList');
                    if (!transactionsList) return;

                    if (data.transactions.length === 0 && !append) {
                        transactionsList.innerHTML = `
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <p class="mt-3 text-muted">Nessun movimento trovato</p>
                            </div>`;
                        document.getElementById('loadMore').style.display = 'none';
                        return;
                    }

                    const transactionsHtml = data.transactions.map(transaction => `
                        <div class="transaction-item">
                            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="category-icon me-3" style="background-color: ${transaction.color}20">
                                        <i class="bi ${transaction.icon_class}" style="color: ${transaction.color}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">${transaction.category_name}</div>
                                        <div class="text-muted small">
                                            ${transaction.date} - ${transaction.account_name}
                                        </div>
                                        ${transaction.description ? `<div class="text-muted small">${transaction.description}</div>` : ''}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold ${transaction.type === 'income' ? 'text-success' : 'text-danger'}">
                                        ${transaction.type === 'income' ? '+' : '-'}€${parseFloat(transaction.amount).toFixed(2)}
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-link btn-sm text-muted" onclick="editTransaction(${transaction.id}, event)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-link btn-sm text-muted" onclick="deleteTransaction(${transaction.id}, event)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    if (append) {
                        transactionsList.insertAdjacentHTML('beforeend', transactionsHtml);
                    } else {
                        transactionsList.innerHTML = transactionsHtml;
                    }

                    // Gestione pulsante "Carica altri"
                    const loadMoreButton = document.getElementById('loadMore');
                    if (loadMoreButton) {
                        loadMoreButton.style.display = data.has_more ? 'inline-block' : 'none';
                    }

                    // Aggiorna la pagina corrente
                    currentPage = page;
                })
                .catch(error => {
                    console.error('Error:', error);
                    const transactionsList = document.getElementById('transactionsList');
                    if (transactionsList && !append) {
                        transactionsList.innerHTML = `
                            <div class="alert alert-danger m-3">
                                Errore durante il caricamento dei movimenti: ${error.message}
                            </div>`;
                    }
                });
        }

        // Carica più movimenti
        document.getElementById('loadMore').addEventListener('click', () => {
            loadTransactions(currentPage + 1, true);
        });

        // Funzione per aggiornare la paginazione
        function updatePagination(currentPage, totalPages) {
            const pagination = document.getElementById('transactionsPagination');
            if (!pagination) return;

            let paginationHtml = '<ul class="pagination justify-content-center">';
            
            // Pulsante precedente
            paginationHtml += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="loadTransactions(${currentPage - 1}); return false;">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>`;

            // Pagine
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    paginationHtml += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="loadTransactions(${i}); return false;">${i}</a>
                        </li>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            // Pulsante successivo
            paginationHtml += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="loadTransactions(${currentPage + 1}); return false;">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>`;

            paginationHtml += '</ul>';
            pagination.innerHTML = paginationHtml;
        }

        // Inizializza i filtri
        function initializeFilters() {
            // Carica i conti per il filtro
            fetch('api/get_accounts.php')
                .then(response => response.json())
                .then(data => {
                    const accountFilter = document.getElementById('accountFilter');
                    if (accountFilter) {
                        accountFilter.innerHTML = '<option value="">Tutti i conti</option>' + 
                            data.accounts.map(account => `
                                <option value="${account.id}">${account.name}</option>
                            `).join('');
                    }
                });

            // Carica le categorie per il filtro
            fetch('api/get_categories.php')
                .then(response => response.json())
                .then(data => {
                    const categoryFilter = document.getElementById('categoryFilter');
                    if (categoryFilter) {
                        categoryFilter.innerHTML = '<option value="">Tutte le categorie</option>' + 
                            data.categories.map(category => `
                                <option value="${category.id}">${category.name}</option>
                            `).join('');
                    }
                });

            // Imposta gli event listener per i filtri
            const filters = ['accountFilter', 'categoryFilter', 'typeFilter', 'searchInput', 
                           'dateFromFilter', 'dateToFilter'];
            
            filters.forEach(filterId => {
                const element = document.getElementById(filterId);
                if (element) {
                    if (element.tagName === 'INPUT' && element.type === 'text') {
                        let timeout;
                        element.addEventListener('input', () => {
                            clearTimeout(timeout);
                            timeout = setTimeout(() => loadTransactions(1), 500);
                        });
                    } else {
                        element.addEventListener('change', () => loadTransactions(1));
                    }
                }
            });
        }

        // Inizializza la pagina
        document.addEventListener('DOMContentLoaded', () => {
            initializeFilters();
            loadTransactions(1);
        });

        // Salva movimento
        function saveTransaction() {
            const form = document.getElementById('transactionForm');
            if (!form) {
                alert('Form non trovato');
                return;
            }

            const selectedAccount = document.querySelector('.account-select-card.selected');
            const selectedCategory = document.querySelector('.category-item.selected');
            
            if (!selectedAccount) {
                alert('Seleziona un conto');
                return;
            }

            if (!selectedCategory) {
                alert('Seleziona una categoria');
                return;
            }

            const formData = new FormData(form);
            formData.append('account_id', selectedAccount.dataset.accountId);
            formData.append('category_id', selectedCategory.dataset.categoryId);

            const transactionId = form.querySelector('#transactionId').value;
            const url = transactionId ? 'api/update_transaction.php' : 'api/save_transaction.php';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTransactions();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('transactionModal'));
                    if (modal) {
                        modal.hide();
                    }
                    resetForm();
                } else {
                    throw new Error(data.error || 'Errore durante il salvataggio');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Errore durante il salvataggio');
            });
        }

        // Modifica movimento
        function editTransaction(id, event) {
            if (event) {
                event.stopPropagation();
            }

            fetch(`api/get_transaction.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    const form = document.getElementById('transactionForm');
                    if (form) {
                        // Imposta l'ID del movimento
                        const transactionIdInput = form.querySelector('#transactionId');
                        if (transactionIdInput) {
                            transactionIdInput.value = data.id;
                        }

                        // Imposta l'importo
                        const amountInput = form.querySelector('input[name="amount"]');
                        if (amountInput) {
                            amountInput.value = parseFloat(data.amount).toFixed(2);
                        }

                        // Imposta la data
                        const dateInput = form.querySelector('input[name="date"]');
                        if (dateInput) {
                            dateInput.value = data.date;
                        }

                        // Imposta la descrizione
                        const descriptionInput = form.querySelector('textarea[name="description"]');
                        if (descriptionInput) {
                            descriptionInput.value = data.description || '';
                        }

                        // Imposta il tipo e carica le categorie appropriate
                        const typeRadio = form.querySelector(`input[name="type"][value="${data.type}"]`);
                        if (typeRadio) {
                            typeRadio.checked = true;
                        }

                        // Carica e seleziona la categoria corretta
                        loadCategoryGrid().then(() => {
                            const categoryCard = document.querySelector(`.category-item[data-category-id="${data.category_id}"]`);
                            if (categoryCard) {
                                document.querySelectorAll('.category-item').forEach(i => {
                                    i.classList.remove('selected');
                                    i.style.borderColor = '#dee2e6';
                                    i.style.backgroundColor = 'white';
                                });
                                categoryCard.classList.add('selected');
                                const color = categoryCard.dataset.categoryColor;
                                categoryCard.style.borderColor = color;
                                categoryCard.style.backgroundColor = `${color}20`;
                            }
                        });

                        // Seleziona il conto
                        const accountCard = document.querySelector(`.account-select-card[data-account-id="${data.account_id}"]`);
                        if (accountCard) {
                            document.querySelectorAll('.account-select-card').forEach(i => 
                                i.classList.remove('selected'));
                            accountCard.classList.add('selected');
                        }

                        // Aggiorna il titolo del modal
                        const modalTitle = document.querySelector('#transactionModal .modal-title');
                        if (modalTitle) {
                            modalTitle.textContent = 'Modifica Movimento';
                        }

                        // Mostra il modal
                        const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
                        modal.show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Errore durante il caricamento del movimento: ' + error.message);
                });
        }

        // Elimina movimento
        function deleteTransaction(id, event) {
            event.stopPropagation(); // Previene l'apertura del modal di modifica

            if (!confirm('Sei sicuro di voler eliminare questo movimento?')) {
                return;
            }

            fetch('api/delete_transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTransactions();
                } else {
                    alert(data.error || 'Errore durante l\'eliminazione');
                }
            });
        }

        // Reset form quando il modal viene chiuso
        document.getElementById('transactionModal').addEventListener('hidden.bs.modal', () => {
            const form = document.getElementById('transactionForm');
            form.reset();
            document.querySelector('input[name="date"]').value = 
                new Date().toISOString().split('T')[0];
            document.querySelectorAll('.account-select-card, .category-item').forEach(i => {
                i.classList.remove('selected');
                i.style.backgroundColor = 'white';
                if (i.classList.contains('category-item')) {
                    i.style.borderColor = '#dee2e6';
                }
            });
            document.querySelector('input[name="type"][value="expense"]').checked = true;
            document.querySelector('.modal-title').textContent = 'Nuovo Movimento';
            loadCategoryGrid();
        });

        // Event listeners per i filtri
        document.getElementById('searchInput').addEventListener('input', debounce(() => {
            currentPage = 1;
            loadTransactions();
        }, 300));

        document.querySelectorAll('#dateFrom, #dateTo, #accountFilter, #categoryFilter, input[name="typeFilter"]')
            .forEach(element => {
                element.addEventListener('change', () => {
                    currentPage = 1;
                    loadTransactions();
                });
            });

        // Funzione debounce per la ricerca
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Caricamento iniziale
        document.addEventListener('DOMContentLoaded', () => {
            // Inizializza le funzioni base
            loadAccountGrid();
            loadCategoryGrid();
            loadTransactions();

            // Imposta la data di oggi nel form
            const dateInput = document.querySelector('input[name="date"]');
            if (dateInput) {
                dateInput.value = new Date().toISOString().split('T')[0];
            }

            // Event listener per il salvataggio
            const saveButton = document.getElementById('saveTransaction');
            if (saveButton) {
                saveButton.addEventListener('click', saveTransaction);
            }

            // Event listener per il modal
            const transactionModal = document.getElementById('transactionModal');
            if (transactionModal) {
                transactionModal.addEventListener('shown.bs.modal', () => {
                    const amountInput = document.getElementById('amount');
                    if (amountInput) {
                        amountInput.focus();
                    }
                });
                transactionModal.addEventListener('hidden.bs.modal', resetForm);
            }

            // Event listener per il cambio tipo
            const typeRadios = document.querySelectorAll('input[name="type"]');
            typeRadios.forEach(radio => {
                radio.addEventListener('change', () => {
                    loadCategoryGrid();
                });
            });

            // Event listener per l'input dell'importo
            const amountInput = document.getElementById('amount');
            if (amountInput) {
                // Gestisci l'input per accettare solo numeri e virgola/punto
                amountInput.addEventListener('keypress', (e) => {
                    const char = String.fromCharCode(e.which);
                    const pattern = /[0-9.,]/;
                    
                    if (!pattern.test(char)) {
                        e.preventDefault();
                    }

                    // Permetti solo una virgola o un punto
                    if ((char === '.' || char === ',') && 
                        (amountInput.value.includes('.') || amountInput.value.includes(','))) {
                        e.preventDefault();
                    }
                });

                // Converti la virgola in punto al blur
                amountInput.addEventListener('blur', () => {
                    if (amountInput.value) {
                        amountInput.value = amountInput.value.replace(',', '.');
                    }
                });

                // Previeni input non validi
                amountInput.addEventListener('input', () => {
                    let value = amountInput.value.replace(',', '.');
                    if (value && !isNaN(value)) {
                        const numValue = parseFloat(value);
                        if (numValue < 0) {
                            amountInput.value = Math.abs(numValue);
                        }
                    }
                });
            }
        });

        // Funzione per resettare il form
        function resetForm() {
            const form = document.getElementById('transactionForm');
            if (form) {
                form.reset();
                const transactionIdInput = form.querySelector('#transactionId');
                if (transactionIdInput) {
                    transactionIdInput.value = '';
                }

                const dateInput = form.querySelector('input[name="date"]');
                if (dateInput) {
                    dateInput.value = new Date().toISOString().split('T')[0];
                }

                document.querySelectorAll('.account-select-card, .category-item').forEach(i => {
                    i.classList.remove('selected');
                    i.style.backgroundColor = 'white';
                    if (i.classList.contains('category-item')) {
                        i.style.borderColor = '#dee2e6';
                    }
                });

                const expenseRadio = form.querySelector('input[name="type"][value="expense"]');
                if (expenseRadio) {
                    expenseRadio.checked = true;
                    loadCategoryGrid();
                }

                const modalTitle = document.querySelector('.modal-title');
                if (modalTitle) {
                    modalTitle.textContent = 'Nuovo Movimento';
                }
            }
        }

        // Aggiornamento periodico dei movimenti
        setInterval(() => {
            if (currentPage === 1) {
                loadTransactions();
            }
        }, 30000);
    </script>
</body>
</html>
