<?php
require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorie - My VDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="includes/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Categorie</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="bi bi-plus-lg"></i> Nuova
            </button>
        </div>

        <ul class="nav nav-pills mb-4 justify-content-center" id="categoryTabs">
            <li class="nav-item">
                <a class="nav-link active" data-type="expense" href="#">Uscite</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-type="income" href="#">Entrate</a>
            </li>
        </ul>

        <div class="category-grid" id="categoriesGrid">
            <!-- Le categorie verranno caricate qui -->
        </div>
    </div>

    <!-- Modal Nuova/Modifica Categoria -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuova Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm">
                        <input type="hidden" id="categoryId" name="id">
                        
                        <div class="mb-3">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="typeExpense" 
                                       value="expense" checked>
                                <label class="btn btn-outline-danger" for="typeExpense">Uscita</label>
                                
                                <input type="radio" class="btn-check" name="type" id="typeIncome" 
                                       value="income">
                                <label class="btn btn-outline-success" for="typeIncome">Entrata</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Nome</label>
                            <input type="text" class="form-control form-control-lg" id="categoryName" 
                                   name="name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Icona</label>
                            <div class="icon-grid" id="iconGrid">
                                <!-- Le icone verranno caricate qui -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Colore</label>
                            <div class="color-picker" id="colorPicker">
                                <div class="color-option" style="background-color: #dc3545" data-color="#dc3545"></div>
                                <div class="color-option" style="background-color: #fd7e14" data-color="#fd7e14"></div>
                                <div class="color-option" style="background-color: #ffc107" data-color="#ffc107"></div>
                                <div class="color-option" style="background-color: #198754" data-color="#198754"></div>
                                <div class="color-option" style="background-color: #0dcaf0" data-color="#0dcaf0"></div>
                                <div class="color-option" style="background-color: #0d6efd" data-color="#0d6efd"></div>
                                <div class="color-option" style="background-color: #6f42c1" data-color="#6f42c1"></div>
                                <div class="color-option" style="background-color: #d63384" data-color="#d63384"></div>
                                <div class="color-option" style="background-color: #6c757d" data-color="#6c757d"></div>
                                <div class="color-option" style="background-color: #343a40" data-color="#343a40"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="saveCategory">Salva</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Bottom -->
    <?php include 'includes/bottom_navbar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentType = 'expense';

        // Carica le icone disponibili
        function loadIcons() {
            fetch('api/get_icons.php?type=category')
                .then(response => response.json())
                .then(data => {
                    const iconGrid = document.getElementById('iconGrid');
                    iconGrid.innerHTML = data.icons.map(icon => `
                        <div class="icon-item" data-icon-id="${icon.id}">
                            <i class="bi ${icon.icon_class}"></i>
                        </div>
                    `).join('');

                    // Event listener per la selezione delle icone
                    document.querySelectorAll('.icon-item').forEach(item => {
                        item.addEventListener('click', () => {
                            document.querySelectorAll('.icon-item').forEach(i => 
                                i.classList.remove('selected'));
                            item.classList.add('selected');
                        });
                    });
                });
        }

        // Gestione selezione colore
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.color-option').forEach(o => 
                    o.classList.remove('selected'));
                option.classList.add('selected');
            });
        });

        // Carica le categorie
        function loadCategories() {
            fetch('api/get_categories.php')
                .then(response => response.json())
                .then(data => {
                    const categoriesGrid = document.getElementById('categoriesGrid');
                    const categories = data.categories.filter(cat => cat.type === currentType);
                    
                    categoriesGrid.innerHTML = categories.map(category => `
                        <div class="category-item" style="background-color: ${category.color}20; border-color: ${category.color}">
                            <div class="category-actions">
                                <button class="btn btn-icon" onclick="editCategory(${category.id})">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button class="btn btn-icon" onclick="deleteCategory(${category.id})">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                            <i class="bi ${category.icon_class}" style="color: ${category.color}"></i>
                            <div class="name">${category.name}</div>
                        </div>
                    `).join('');
                });
        }

        // Gestione tabs
        document.querySelectorAll('#categoryTabs .nav-link').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('#categoryTabs .nav-link').forEach(t => 
                    t.classList.remove('active'));
                tab.classList.add('active');
                currentType = tab.dataset.type;
                loadCategories();
            });
        });

        // Salva categoria
        document.getElementById('saveCategory').addEventListener('click', () => {
            const form = document.getElementById('categoryForm');
            const selectedIcon = document.querySelector('.icon-item.selected');
            const selectedColor = document.querySelector('.color-option.selected');
            
            if (!selectedIcon) {
                alert('Seleziona un\'icona');
                return;
            }

            if (!selectedColor) {
                alert('Seleziona un colore');
                return;
            }

            const formData = new FormData(form);
            formData.append('icon_id', selectedIcon.dataset.iconId);
            formData.append('color', selectedColor.dataset.color);

            fetch('api/save_category.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCategories();
                    bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                    form.reset();
                    document.querySelectorAll('.icon-item').forEach(i => 
                        i.classList.remove('selected'));
                    document.querySelectorAll('.color-option').forEach(o => 
                        o.classList.remove('selected'));
                } else {
                    alert(data.error || 'Errore durante il salvataggio');
                }
            });
        });

        // Modifica categoria
        function editCategory(id) {
            fetch(`api/get_category.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('categoryId').value = data.id;
                    document.getElementById('categoryName').value = data.name;
                    
                    // Seleziona il tipo
                    document.querySelector(`input[name="type"][value="${data.type}"]`).checked = true;
                    
                    // Seleziona l'icona
                    document.querySelectorAll('.icon-item').forEach(item => {
                        if (item.dataset.iconId == data.icon_id) {
                            item.classList.add('selected');
                        }
                    });

                    // Seleziona il colore
                    document.querySelectorAll('.color-option').forEach(option => {
                        if (option.dataset.color === data.color) {
                            option.classList.add('selected');
                        }
                    });

                    document.querySelector('.modal-title').textContent = 'Modifica Categoria';
                    const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
                    modal.show();
                });
        }

        // Reset form quando il modal viene chiuso
        document.getElementById('categoryModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('categoryForm').reset();
            document.querySelectorAll('.icon-item').forEach(i => i.classList.remove('selected'));
            document.querySelectorAll('.color-option').forEach(o => o.classList.remove('selected'));
            document.querySelector('.modal-title').textContent = 'Nuova Categoria';
        });

        // Funzione per eliminare una categoria
        function deleteCategory(id) {
            fetch('api/delete_category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.hasTransactions) {
                    if (confirm(`Attenzione: ${data.transactionCount} transazioni verranno eliminate insieme alla categoria. Vuoi procedere?`)) {
                        // Richiama l'eliminazione con forceDelete
                        fetch('api/delete_category.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: id, forceDelete: true })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                loadCategories();
                            } else {
                                alert(data.error || 'Errore durante l\'eliminazione della categoria');
                            }
                        });
                    }
                } else if (data.success) {
                    loadCategories();
                } else {
                    alert(data.error || 'Errore durante l\'eliminazione della categoria');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore durante l\'eliminazione della categoria');
            });
        }

        // Caricamento iniziale
        loadIcons();
        loadCategories();
    </script>
</body>
</html>
