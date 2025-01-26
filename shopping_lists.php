<?php
require_once 'includes/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste della Spesa - My VDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="includes/styles.css" rel="stylesheet">
    <style>
        .shopping-list-item {
            transition: all 0.2s ease;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            background-color: white;
            margin-bottom: 0.5rem;
        }
        .shopping-list-item:hover {
            background-color: #f8f9fa;
        }
        .shopping-list-item.checked {
            opacity: 0.6;
            text-decoration: line-through;
            background-color: #f8f9fa;
        }
        .shopping-list-item.editing {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .shopping-list-item .btn-link {
            visibility: hidden;
            opacity: 0;
            transition: all 0.2s ease;
        }
        .shopping-list-item:hover .btn-link {
            visibility: visible;
            opacity: 1;
        }
        .shopping-list-item .item-name {
            flex: 1;
            margin-right: 0.5rem;
        }
        .shopping-list-item .badge {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
            font-weight: 500;
            background-color: #6c757d !important;
        }
        .shopping-list-item.checked .badge {
            opacity: 0.7;
        }
        .list-items {
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-top: 1rem;
            overflow: hidden;
        }

        /* Stili per la modifica inline */
        .shopping-list-item .item-edit {
            display: none;
            width: 100%;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
            margin-top: 1rem;
        }
        .shopping-list-item .item-edit.active {
            display: block !important;
        }
        .shopping-list-item .item-content.hidden {
            display: none !important;
        }
        .shopping-list-item .edit-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .shopping-list-item .edit-field {
            display: flex;
            flex-direction: column;
        }
        .shopping-list-item .edit-field label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        .shopping-list-item .edit-name {
            width: 100%;
            padding: 0.5rem;
            font-size: 1rem;
        }
        .shopping-list-item .edit-quantity {
            width: 120px;
            padding: 0.5rem;
            font-size: 1rem;
        }
        .shopping-list-item .edit-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            margin-top: 0.5rem;
        }
        .shopping-list-item .edit-actions .btn {
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }

        .quick-add {
            position: fixed;
            bottom: 100px;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 1.25rem;
            z-index: 1000;
            box-shadow: 0 -4px 15px rgba(0,0,0,0.1);
        }
        .quick-add .input-group {
            max-width: 600px;
            margin: 0 1rem;
            width: auto;
        }
        @media (min-width: 768px) {
            .quick-add .input-group {
                margin: 0 auto;
                max-width: 600px;
            }
        }
        @media (max-width: 768px) {
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            .list-card {
                border-radius: 10px;
            }
            .quick-add {
                padding: 1rem 0.75rem;
            }
        }
        .list-card {
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
        }
        .list-card.active {
            border: 2px solid #0d6efd;
            background-color: #f8f9ff;
        }
        .list-card .card-body {
            padding: 1rem;
        }
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .list-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }
        .list-actions {
            display: flex;
            gap: 0.5rem;
        }
        .progress {
            height: 4px;
            margin: 0.5rem 0;
        }
        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-right: 0.75rem;
        }
        .container {
            padding-bottom: 160px;
        }
    </style>
</head>
<body>
    <div class="container mt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Liste della Spesa</h5>
            <button class="btn btn-sm btn-primary" onclick="createNewList()">
                <i class="bi bi-plus-lg"></i> Nuova Lista
            </button>
        </div>

        <div id="shoppingLists">
            <!-- Le liste verranno caricate qui -->
        </div>

        <!-- Form Aggiunta Rapida -->
        <div class="quick-add">
            <div class="input-group">
                <input type="text" class="form-control" id="quickAddInput" 
                       placeholder="Aggiungi articolo...">
                <input type="number" class="form-control" id="quickAddQuantity" 
                       placeholder="Qtà" style="max-width: 80px;" value="1" min="1">
                <button class="btn btn-primary" type="button" id="quickAddButton">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
            <div id="noListSelected" class="text-center text-muted mt-2" style="display: none;">
                <small>Seleziona una lista per aggiungere articoli</small>
            </div>
        </div>
    </div>

    <!-- Modal Nuova Lista -->
    <div class="modal fade" id="listModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuova Lista</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome Lista</label>
                        <input type="text" class="form-control" id="listName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" onclick="saveList()">Salva</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Bottom -->
    <?php include 'includes/bottom_navbar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentListId = null;
        const listModal = new bootstrap.Modal(document.getElementById('listModal'));

        // Ensure DOM is loaded before adding event listeners
        document.addEventListener('DOMContentLoaded', function() {
            loadLists();
            
            // Add event listener to the form submit
            const addItemForm = document.getElementById('addItemForm');
            if (addItemForm) {
                addItemForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    addItem();
                });
            }
        });

        function addItem() {
            const nameInput = document.getElementById('itemName');
            const quantityInput = document.getElementById('itemQuantity');
            
            if (!nameInput || !currentListId) return;
            
            const name = nameInput.value.trim();
            const quantity = quantityInput ? quantityInput.value.trim() : '1';
            
            if (!name) return;

            fetch('api/save_shopping_list_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    list_id: currentListId,
                    name: name,
                    quantity: quantity
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    nameInput.value = '';
                    if (quantityInput) quantityInput.value = '1';
                    loadListItems(currentListId);
                } else {
                    console.error('Error adding item:', data.error);
                    alert('Errore: ' + (data.error || 'Impossibile aggiungere l\'articolo'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore: Impossibile aggiungere l\'articolo');
            });
        }

        // Carica tutte le liste
        function loadLists() {
            fetch('api/get_shopping_lists.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.error);
                    }

                    const container = document.getElementById('shoppingLists');
                    if (data.lists.length === 0) {
                        container.innerHTML = `
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-cart3 fs-1 mb-2"></i>
                                <p class="mb-0">Nessuna lista presente</p>
                                <small>Crea una nuova lista per iniziare</small>
                            </div>`;
                        return;
                    }

                    container.innerHTML = data.lists.map(list => `
                        <div class="list-card card ${currentListId === list.id ? 'active' : ''} ${list.is_archived ? 'bg-light' : ''}">
                            <div class="card-body">
                                <div class="list-header">
                                    <h6 class="list-title">${list.name}</h6>
                                    <div class="list-actions">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="toggleListItems(${list.id}, this)">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteList(${list.id})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: ${list.total_items ? (list.checked_items / list.total_items * 100) : 0}%">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    ${list.checked_items}/${list.total_items} completati
                                </small>
                                <div class="list-items mt-3" id="items-${list.id}" style="display: none;">
                                    <!-- Gli articoli verranno caricati qui -->
                                </div>
                            </div>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Errore nel caricamento delle liste');
                });
        }

        // Carica gli articoli di una lista
        function loadListItems(listId) {
            fetch(`api/get_shopping_list_items.php?list_id=${listId}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.error);
                    }

                    const itemsContainer = document.getElementById(`items-${listId}`);
                    if (!itemsContainer) return;

                    let html = '<div class="list-items">';
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => {
                            html += `
                                <div class="shopping-list-item d-flex flex-column ${item.is_checked ? 'checked' : ''}" id="item-${item.id}">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <input type="checkbox" class="form-check-input me-2" 
                                                ${item.is_checked ? 'checked' : ''} 
                                                onchange="toggleItem(${item.id}, this)"
                                                data-item-id="${item.id}">
                                            <div class="item-content d-flex align-items-center flex-grow-1">
                                                <span class="item-name">${item.name}</span>
                                                <span class="badge bg-secondary ms-2">${item.quantity}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center ms-4">
                                            <button class="btn btn-link text-primary p-0 me-3" onclick="startEdit(${item.id})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-link text-danger p-0" onclick="deleteItem(${item.id})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="item-edit">
                                        <div class="edit-form">
                                            <div class="edit-field">
                                                <label>Nome Articolo</label>
                                                <input type="text" class="form-control edit-name" value="${item.name}">
                                            </div>
                                            <div class="edit-field">
                                                <label>Quantità</label>
                                                <input type="number" class="form-control edit-quantity" value="${item.quantity}" min="1">
                                            </div>
                                            <div class="edit-actions">
                                                <button class="btn btn-secondary" onclick="cancelEdit(${item.id})">
                                                    Annulla
                                                </button>
                                                <button class="btn btn-primary" onclick="saveEdit(${item.id})">
                                                    Salva
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        });
                    } else {
                        html += '<div class="p-3 text-center text-muted">Nessun articolo nella lista</div>';
                    }
                    html += '</div>';
                    itemsContainer.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Errore nel caricamento degli articoli');
                });
        }

        // Toggle visualizzazione articoli
        function toggleListItems(listId, button) {
            const itemsContainer = document.getElementById(`items-${listId}`);
            const isVisible = itemsContainer.style.display !== 'none';
            
            if (!isVisible) {
                loadListItems(listId);
                currentListId = listId;
            }
            
            itemsContainer.style.display = isVisible ? 'none' : 'block';
            button.innerHTML = `<i class="bi bi-chevron-${isVisible ? 'down' : 'up'}"></i>`;
        }

        // Toggle stato articolo
        function toggleItem(itemId, checkbox) {
            if (!itemId) return;

            fetch('api/toggle_shopping_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: itemId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const itemDiv = checkbox.closest('.shopping-list-item');
                    if (itemDiv) {
                        itemDiv.classList.toggle('checked', checkbox.checked);
                    }
                } else {
                    throw new Error(data.error || 'Errore nell\'aggiornamento dell\'articolo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert checkbox state
                checkbox.checked = !checkbox.checked;
                alert('Errore: ' + error.message);
            });
        }

        // Gestione aggiunta rapida
        document.getElementById('quickAddButton').onclick = function() {
            const input = document.getElementById('quickAddInput');
            const quantityInput = document.getElementById('quickAddQuantity');
            const name = input.value.trim();
            const quantity = quantityInput.value;

            if (!name || !currentListId) {
                if (!currentListId) {
                    document.getElementById('noListSelected').style.display = 'block';
                }
                return;
            }

            document.getElementById('noListSelected').style.display = 'none';
            
            fetch('api/save_shopping_list_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    list_id: currentListId,
                    name: name,
                    quantity: quantity
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    input.value = '';
                    quantityInput.value = '1';
                    loadListItems(currentListId);
                    loadLists(); // Aggiorna i conteggi
                } else {
                    throw new Error(data.error || 'Errore nell\'aggiunta dell\'articolo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore: ' + error.message);
            });
        };

        // Gestione nuova lista
        function createNewList() {
            document.getElementById('listName').value = '';
            listModal.show();
        }

        function saveList() {
            const name = document.getElementById('listName').value.trim();
            if (!name) return;

            fetch('api/save_shopping_list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ name: name })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error);
                }
                listModal.hide();
                loadLists();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore nel salvataggio della lista');
            });
        }

        // Caricamento iniziale
        loadLists();

        function deleteList(listId) {
            if (!confirm('Sei sicuro di voler eliminare questa lista?')) {
                return;
            }

            fetch('api/delete_shopping_list.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: listId })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error);
                }
                if (currentListId === listId) {
                    currentListId = null;
                }
                loadLists();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore nell\'eliminazione della lista');
            });
        }

        function deleteItem(itemId) {
            if (!confirm('Vuoi rimuovere questo articolo dalla lista?')) {
                return;
            }

            fetch('api/delete_shopping_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: itemId })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error);
                }
                loadListItems(currentListId);
                loadLists(); // Aggiorna i conteggi
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore nella rimozione dell\'articolo');
            });
        }

        function startEdit(itemId) {
            const itemDiv = document.getElementById(`item-${itemId}`);
            if (!itemDiv) return;

            const contentDiv = itemDiv.querySelector('.item-content');
            const editDiv = itemDiv.querySelector('.item-edit');

            if (contentDiv && editDiv) {
                itemDiv.classList.add('editing');
                contentDiv.classList.add('hidden');
                editDiv.classList.add('active');
                
                // Focus sul campo nome
                const nameInput = editDiv.querySelector('.edit-name');
                if (nameInput) {
                    nameInput.focus();
                    nameInput.select();
                }
            }
        }

        function cancelEdit(itemId) {
            const itemDiv = document.getElementById(`item-${itemId}`);
            if (!itemDiv) return;

            const contentDiv = itemDiv.querySelector('.item-content');
            const editDiv = itemDiv.querySelector('.item-edit');

            if (contentDiv && editDiv) {
                itemDiv.classList.remove('editing');
                contentDiv.classList.remove('hidden');
                editDiv.classList.remove('active');
            }
        }

        function saveEdit(itemId) {
            const itemDiv = document.getElementById(`item-${itemId}`);
            if (!itemDiv) return;

            const nameInput = itemDiv.querySelector('.edit-name');
            const quantityInput = itemDiv.querySelector('.edit-quantity');
            
            const name = nameInput.value.trim();
            const quantity = quantityInput.value;

            if (!name) return;

            fetch('api/save_shopping_list_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: itemId,
                    name: name,
                    quantity: quantity
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    loadListItems(currentListId);
                    loadLists(); // Aggiorna i conteggi
                } else {
                    throw new Error(data.error || 'Errore nella modifica dell\'articolo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore: ' + error.message);
            });
        }
    </script>
</body>
</html>