<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);



require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - My VDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="includes/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard</h2>
            <button class="btn btn-outline-primary" id="toggleValues">
                <i class="bi bi-eye"></i> Mostra Valori
            </button>
        </div>
        
        <!-- Saldo Totale e Statistiche Principali -->
        <div class="row g-4 mb-4">
            <!-- Saldo Totale -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Saldo Totale</h5>
                        <div class="d-flex align-items-center">
                            <h2 class="text-primary mb-0 hidden-value" data-value="true">€<span id="totalBalance">0.00</span></h2>
                            <span class="trend-indicator" id="balanceTrend"></span>
                        </div>
                        <small class="text-muted">Rispetto al mese precedente</small>
                    </div>
                </div>
            </div>

            <!-- Statistiche Mensili -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Statistiche Mensili</h5>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-success">Entrate</div>
                                <h4 class="hidden-value" data-value="true">€<span id="monthlyIncome">0.00</span></h4>
                            </div>
                            <div class="col-6">
                                <div class="text-danger">Uscite</div>
                                <h4 class="hidden-value" data-value="true">€<span id="monthlyExpense">0.00</span></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conti -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">I Tuoi Conti</h5>
                <div class="row g-3" id="accountsList">
                    <!-- I conti verranno caricati qui dinamicamente -->
                </div>
            </div>
        </div>

        <!-- Ultimi Movimenti -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Ultimi Movimenti</h5>
                    <a href="transactions.php" class="btn btn-sm btn-outline-primary">
                        Vedi Tutti
                    </a>
                </div>
                <div id="recentTransactions">
                    <!-- I movimenti verranno caricati qui -->
                </div>
            </div>
        </div>

        <!-- Spese per Categoria -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Top Categorie del Mese</h5>
                <div id="topCategories">
                    <!-- Le categorie verranno caricate qui -->
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Bottom -->
    <?php include 'includes/bottom_navbar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestione visibilità valori
        let valuesVisible = false;
        const toggleButton = document.getElementById('toggleValues');
        
        toggleButton.addEventListener('click', () => {
            valuesVisible = !valuesVisible;
            document.querySelectorAll('[data-value="true"]').forEach(el => {
                el.classList.toggle('visible', valuesVisible);
            });
            toggleButton.innerHTML = valuesVisible ? 
                '<i class="bi bi-eye-slash"></i> Nascondi Valori' : 
                '<i class="bi bi-eye"></i> Mostra Valori';
        });

        // Funzione per formattare i numeri come valuta
        function formatCurrency(amount) {
            const value = parseFloat(amount);
            return isNaN(value) ? '0.00' : value.toFixed(2);
        }

        // Funzione per aggiornare il saldo totale
        function updateTotalBalance() {
            fetch('api/get_total_balance.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalBalance').textContent = formatCurrency(data.balance);
                    
                    // Aggiorna l'indicatore di trend
                    const trend = document.getElementById('balanceTrend');
                    if (data.trend > 0) {
                        trend.innerHTML = '<i class="bi bi-arrow-up-circle-fill trend-up"></i>';
                        trend.title = `+${formatCurrency(data.trend)}€ rispetto al mese scorso`;
                    } else if (data.trend < 0) {
                        trend.innerHTML = '<i class="bi bi-arrow-down-circle-fill trend-down"></i>';
                        trend.title = `${formatCurrency(data.trend)}€ rispetto al mese scorso`;
                    }
                });
        }

        // Funzione per aggiornare le statistiche mensili
        function updateMonthlyStats() {
            fetch('api/get_monthly_stats.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('monthlyIncome').textContent = formatCurrency(data.income);
                    document.getElementById('monthlyExpense').textContent = formatCurrency(data.expense);
                });
        }

        // Funzione per aggiornare la lista dei conti
        function updateAccounts() {
            fetch('api/get_accounts.php')
                .then(response => response.json())
                .then(data => {
                    const accountsList = document.getElementById('accountsList');
                    accountsList.innerHTML = data.accounts.map(account => `
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="category-icon" style="background-color: ${account.color}20">
                                            <i class="bi ${account.icon_class}" style="color: ${account.color}"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">${account.name}</h6>
                                            <div class="hidden-value" data-value="true">
                                                €${formatCurrency(account.current_balance)}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                });
        }

        // Funzione per aggiornare i movimenti recenti
        function updateRecentTransactions() {
            fetch('api/get_recent_transactions.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recentTransactions');
                    container.innerHTML = data.transactions.map(t => `
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="category-icon" style="background-color: ${t.category_color}20">
                                    <i class="bi ${t.category_icon}" style="color: ${t.category_color}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">${t.category_name}</div>
                                    <small class="text-muted">${t.account_name} • ${new Date(t.date).toLocaleDateString()}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="hidden-value ${t.type === 'expense' ? 'text-danger' : 'text-success'}" 
                                     data-value="true">
                                    ${t.type === 'expense' ? '-' : '+'}€${formatCurrency(t.amount)}
                                </div>
                            </div>
                        </div>
                    `).join('');
                });
        }

        // Funzione per aggiornare le top categorie
        function updateTopCategories() {
            fetch('api/get_top_categories.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.success || !data.categories.length) {
                        document.getElementById('topCategories').innerHTML = `
                            <div class="text-center text-muted py-3">
                                Nessuna spesa registrata questo mese
                            </div>`;
                        return;
                    }

                    const container = document.getElementById('topCategories');
                    container.innerHTML = data.categories.map(cat => `
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="category-icon" style="background-color: ${cat.color}20">
                                    <i class="bi ${cat.icon}" style="color: ${cat.color}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">${cat.name}</div>
                                            <small class="text-muted">${cat.transaction_count} movimenti</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="hidden-value" data-value="true">€${formatCurrency(cat.amount)}</div>
                                            <small class="text-muted">${cat.percentage.toFixed(1)}%</small>
                                        </div>
                                    </div>
                                    <div class="progress mt-1" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: ${cat.percentage}%; background-color: ${cat.color}"
                                             aria-valuenow="${cat.percentage}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    // Aggiungi il totale delle spese
                    if (data.total_expense > 0) {
                        container.innerHTML += `
                            <div class="border-top pt-2 mt-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Totale Spese</strong>
                                    <div class="hidden-value" data-value="true">
                                        €${formatCurrency(data.total_expense)}
                                    </div>
                                </div>
                            </div>`;
                    }
                })
                .catch(error => {
                    document.getElementById('topCategories').innerHTML = `
                        <div class="text-center text-danger py-3">
                            Errore nel caricamento delle categorie
                        </div>`;
                });
        }

        // Aggiornamento iniziale
        updateTotalBalance();
        updateMonthlyStats();
        updateAccounts();
        updateRecentTransactions();
        updateTopCategories();

        // Aggiornamento periodico
        setInterval(() => {
            updateTotalBalance();
            updateMonthlyStats();
            updateAccounts();
            updateRecentTransactions();
            updateTopCategories();
        }, 30000);
    </script>
</body>
</html>
