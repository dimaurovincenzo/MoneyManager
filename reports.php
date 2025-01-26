<?php
require_once 'includes/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - My VDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="includes/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-4 mb-5 pb-5">
        <h2 class="mb-4">Report</h2>

        <!-- Filtri Periodo -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" id="yearSelect">
                            <!-- Gli anni verranno caricati dinamicamente -->
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="monthSelect">
                            <option value="">Tutto l'anno</option>
                            <option value="1">Gennaio</option>
                            <option value="2">Febbraio</option>
                            <option value="3">Marzo</option>
                            <option value="4">Aprile</option>
                            <option value="5">Maggio</option>
                            <option value="6">Giugno</option>
                            <option value="7">Luglio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Settembre</option>
                            <option value="10">Ottobre</option>
                            <option value="11">Novembre</option>
                            <option value="12">Dicembre</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100" id="updateButton">
                            Aggiorna
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riepilogo -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Riepilogo</h5>
                <div class="row text-center g-3">
                    <div class="col-4">
                        <div class="text-success small">Entrate</div>
                        <div class="h5 text-nowrap" id="totalIncome">€0,00</div>
                    </div>
                    <div class="col-4">
                        <div class="text-danger small">Uscite</div>
                        <div class="h5 text-nowrap" id="totalExpense">€0,00</div>
                    </div>
                    <div class="col-4">
                        <div class="text-primary small">Saldo</div>
                        <div class="h5 text-nowrap" id="totalBalance">€0,00</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafici e Statistiche -->
        <div class="row g-4">
            <!-- Grafico Entrate/Uscite -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Andamento Entrate/Uscite</h5>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analisi Uscite per Categoria -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Analisi Uscite per Categoria</h5>
                        <div class="chart-container mb-3" style="height: 300px;">
                            <canvas id="expensePieChart"></canvas>
                        </div>
                        <div class="category-stats" id="expenseStats">
                            <!-- Le statistiche delle uscite verranno caricate qui -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analisi Entrate per Categoria -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Analisi Entrate per Categoria</h5>
                        <div class="chart-container mb-3" style="height: 300px;">
                            <canvas id="incomePieChart"></canvas>
                        </div>
                        <div class="category-stats" id="incomeStats">
                            <!-- Le statistiche delle entrate verranno caricate qui -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Bottom -->
    <?php include 'includes/bottom_navbar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variabili per i grafici
        let trendChart = null;
        let expensePieChart = null;
        let incomePieChart = null;

        // Funzione per formattare i numeri in valuta
        function formatCurrency(amount) {
            return new Intl.NumberFormat('it-IT', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        }

        // Inizializza il selettore degli anni
        function initYearSelect() {
            const yearSelect = document.getElementById('yearSelect');
            if (!yearSelect) return;

            yearSelect.innerHTML = '';
            
            const startYear = 2024;
            const currentYear = new Date().getFullYear();
            
            // Se siamo prima del 2024, mostra solo 2024
            if (currentYear < startYear) {
                const option = document.createElement('option');
                option.value = startYear;
                option.textContent = startYear;
                yearSelect.appendChild(option);
                return;
            }

            // Altrimenti mostra da 2024 fino all'anno corrente
            for (let year = currentYear; year >= startYear; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }
        }

        // Carica i dati dei report
        async function loadReports() {
            try {
                const yearSelect = document.getElementById('yearSelect');
                const monthSelect = document.getElementById('monthSelect');
                
                if (!yearSelect || !monthSelect) {
                    throw new Error('Elementi del form non trovati');
                }

                const year = yearSelect.value;
                const month = monthSelect.value;
                
                const response = await fetch(`api/get_reports.php?year=${year}&month=${month}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }

                updateSummary(data.summary);
                updateTrendChart(data.trend);
                updateExpenseStats(data.expenses_by_category);
                updateIncomeStats(data.income_by_category);
            } catch (error) {
                console.error('Error:', error);
                alert('Errore durante il caricamento dei report: ' + error.message);
            }
        }

        // Aggiorna il riepilogo
        function updateSummary(summary) {
            const incomeElement = document.getElementById('totalIncome');
            const expenseElement = document.getElementById('totalExpense');
            const balanceElement = document.getElementById('totalBalance');

            if (incomeElement) {
                incomeElement.textContent = formatCurrency(summary.total_income);
            }
            if (expenseElement) {
                expenseElement.textContent = formatCurrency(summary.total_expense);
            }
            if (balanceElement) {
                balanceElement.textContent = formatCurrency(summary.balance);
            }
        }

        // Aggiorna il grafico dell'andamento
        function updateTrendChart(data) {
            const canvas = document.getElementById('trendChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            
            if (trendChart) {
                trendChart.destroy();
            }

            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Entrate',
                            data: data.income,
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            fill: true
                        },
                        {
                            label: 'Uscite',
                            data: data.expense,
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatCurrency(context.raw);
                                }
                            }
                        }
                    }
                }
            });
        }

        // Aggiorna le statistiche delle uscite
        function updateExpenseStats(data) {
            const canvas = document.getElementById('expensePieChart');
            const statsContainer = document.getElementById('expenseStats');
            
            if (!canvas || !statsContainer) return;

            // Aggiorna il grafico a torta
            const ctx = canvas.getContext('2d');
            if (expensePieChart) {
                expensePieChart.destroy();
            }

            expensePieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: data.colors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: ${formatCurrency(value)} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Aggiorna la lista dettagliata
            const total = data.values.reduce((a, b) => a + b, 0);
            statsContainer.innerHTML = data.labels.map((label, index) => `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <i class="bi ${data.icons[index]} me-2" style="color: ${data.colors[index]}"></i>
                        <span>${label}</span>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">${formatCurrency(data.values[index])}</div>
                        <small class="text-muted">${((data.values[index] / total) * 100).toFixed(1)}%</small>
                    </div>
                </div>
            `).join('');
        }

        // Aggiorna le statistiche delle entrate
        function updateIncomeStats(data) {
            const canvas = document.getElementById('incomePieChart');
            const statsContainer = document.getElementById('incomeStats');
            
            if (!canvas || !statsContainer) return;

            // Aggiorna il grafico a torta
            const ctx = canvas.getContext('2d');
            if (incomePieChart) {
                incomePieChart.destroy();
            }

            incomePieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: data.colors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: ${formatCurrency(value)} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Aggiorna la lista dettagliata
            const total = data.values.reduce((a, b) => a + b, 0);
            statsContainer.innerHTML = data.labels.map((label, index) => `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <i class="bi ${data.icons[index]} me-2" style="color: ${data.colors[index]}"></i>
                        <span>${label}</span>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">${formatCurrency(data.values[index])}</div>
                        <small class="text-muted">${((data.values[index] / total) * 100).toFixed(1)}%</small>
                    </div>
                </div>
            `).join('');
        }

        // Inizializza la pagina
        function initializePage() {
            try {
                initYearSelect();
                
                // Imposta l'anno e il mese corrente
                const now = new Date();
                const yearSelect = document.getElementById('yearSelect');
                const monthSelect = document.getElementById('monthSelect');
                const updateButton = document.getElementById('updateButton');
                
                if (!yearSelect || !monthSelect || !updateButton) {
                    throw new Error('Elementi della pagina non trovati');
                }

                yearSelect.value = now.getFullYear();
                monthSelect.value = now.getMonth() + 1;

                // Aggiungi event listener per il pulsante di aggiornamento
                updateButton.addEventListener('click', loadReports);
                
                // Carica i report iniziali
                loadReports();
            } catch (error) {
                console.error('Error:', error);
                alert('Errore durante l\'inizializzazione: ' + error.message);
            }
        }

        // Aspetta che il DOM sia completamente caricato
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializePage);
        } else {
            initializePage();
        }
    </script>
</body>
</html>
