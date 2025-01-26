<style>
.navbar-bottom {
    position: fixed;
    bottom: 0;
    width: 100%;
    background-color: #fff;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
}
.navbar-bottom .nav-link {
    text-align: center;
    padding: 0.75rem 0;
    color: #6c757d;
    position: relative;
}
.navbar-bottom .nav-link.active {
    color: #0d6efd;
}
.navbar-bottom .nav-link i {
    font-size: 1.3rem;
    display: block;
    margin-bottom: 0.2rem;
}
.navbar-bottom .nav-link span {
    font-size: 0.7rem;
    display: block;
}
</style>

<nav class="navbar-bottom">
    <div class="container py-2">
        <div class="row text-center align-items-center">
            <div class="col">
                <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="bi bi-house"></i>
                    <span>Home</span>
                </a>
            </div>
            <div class="col">
                <a href="transactions.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Movimenti</span>
                </a>
            </div>
            <div class="col">
                <a href="shopping_lists.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'shopping_lists.php' ? 'active' : ''; ?>">
                    <i class="bi bi-cart3"></i>
                    <span>Lista Spesa</span>
                </a>
            </div>
            <div class="col">
                <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i>
                    <span>Impostazioni</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
// Carica i conti per la griglia
function loadAccountGrid() {
    fetch('api/get_accounts.php')
        .then(response => response.json())
        .then(data => {
            const accountGrid = document.querySelector('.account-grid');
            accountGrid.innerHTML = data.accounts.map(account => `
                <div class="account-item">
                    <input type="radio" class="btn-check" name="account_id" 
                           id="account_${account.id}" value="${account.id}" required>
                    <label class="btn btn-outline-primary w-100 text-center" 
                           for="account_${account.id}">
                        <i class="bi ${account.icon_class}"></i><br>
                        <small>${account.name}</small>
                    </label>
                </div>
            `).join('');
        });
}

// Carica le categorie per la griglia
function loadCategoryGrid() {
    fetch('api/get_categories.php')
        .then(response => response.json())
        .then(data => {
            const categoryGrid = document.querySelector('.category-grid');
            
            // Funzione per filtrare e visualizzare le categorie
            function filterAndDisplayCategories(type) {
                const filteredCategories = data.categories.filter(cat => cat.type === type);
                categoryGrid.innerHTML = filteredCategories.map(category => `
                    <div class="category-item">
                        <input type="radio" class="btn-check" name="category_id" 
                               id="category_${category.id}" value="${category.id}" required>
                        <label class="btn btn-outline-primary w-100 text-center" 
                               for="category_${category.id}">
                            <i class="bi ${category.icon_class}"></i><br>
                            <small>${category.name}</small>
                        </label>
                    </div>
                `).join('');
            }

            // Filtra inizialmente per spese
            filterAndDisplayCategories('expense');

            // Aggiungi listener per il cambio tipo
            document.querySelectorAll('input[name="type"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    filterAndDisplayCategories(e.target.value);
                });
            });
        });
}

// Salva movimento rapido
document.getElementById('saveQuickTransaction').addEventListener('click', () => {
    const form = document.getElementById('quickTransactionForm');
    const formData = new FormData(form);
    formData.append('date', new Date().toISOString().split('T')[0]);

    fetch('api/save_transaction.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Errore durante il salvataggio');
        }
    });
});

// Carica i dati quando il modal viene aperto
document.getElementById('quickTransactionModal').addEventListener('show.bs.modal', () => {
    loadAccountGrid();
    loadCategoryGrid();
});
</script>
