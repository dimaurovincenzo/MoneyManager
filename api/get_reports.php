<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $month = isset($_GET['month']) ? intval($_GET['month']) : null;

    // Costruisci le date di inizio e fine
    if ($month) {
        $start_date = sprintf('%d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', strtotime($start_date));
    } else {
        $start_date = sprintf('%d-01-01', $year);
        $end_date = sprintf('%d-12-31', $year);
    }

    // Riepilogo totali
    $sql = "SELECT 
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense
            FROM transactions 
            WHERE date BETWEEN ? AND ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result->fetch_assoc();

    // Converti i valori in float
    $summary['total_income'] = floatval($summary['total_income']);
    $summary['total_expense'] = floatval($summary['total_expense']);
    $summary['balance'] = $summary['total_income'] - $summary['total_expense'];

    // Andamento per periodo
    $trend = ['labels' => [], 'income' => [], 'expense' => []];

    if ($month) {
        // Dati giornalieri per vista mensile
        $days_in_month = date('t', strtotime($start_date));
        $sql = "WITH RECURSIVE dates AS (
                    SELECT DATE(?) as date
                    UNION ALL
                    SELECT DATE_ADD(date, INTERVAL 1 DAY)
                    FROM dates
                    WHERE date < DATE(?)
                )
                SELECT 
                    DATE_FORMAT(d.date, '%d/%m') as label,
                    COALESCE(SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END), 0) as income,
                    COALESCE(SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END), 0) as expense
                FROM dates d
                LEFT JOIN transactions t ON DATE(t.date) = d.date
                GROUP BY d.date
                ORDER BY d.date";
    } else {
        // Dati mensili per vista annuale
        $sql = "WITH RECURSIVE months AS (
                    SELECT DATE(?) as date
                    UNION ALL
                    SELECT DATE_ADD(date, INTERVAL 1 MONTH)
                    FROM months
                    WHERE date < DATE(?)
                )
                SELECT 
                    DATE_FORMAT(m.date, '%m/%Y') as label,
                    COALESCE(SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END), 0) as income,
                    COALESCE(SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END), 0) as expense
                FROM months m
                LEFT JOIN transactions t ON DATE_FORMAT(t.date, '%Y-%m') = DATE_FORMAT(m.date, '%Y-%m')
                GROUP BY DATE_FORMAT(m.date, '%Y-%m')
                ORDER BY m.date";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $trend['labels'][] = $row['label'];
        $trend['income'][] = floatval($row['income']);
        $trend['expense'][] = floatval($row['expense']);
    }

    // Spese per categoria
    $sql = "SELECT 
                c.name,
                i.icon_class,
                c.color,
                COALESCE(SUM(t.amount), 0) as total
            FROM categories c
            JOIN icons i ON c.icon_id = i.id
            LEFT JOIN transactions t ON t.category_id = c.id AND t.date BETWEEN ? AND ?
            WHERE c.type = 'expense'
            GROUP BY c.id, c.name, i.icon_class, c.color
            HAVING total > 0
            ORDER BY total DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $expenses_by_category = [
        'labels' => [], 
        'values' => [],
        'icons' => [],
        'colors' => []
    ];

    while ($row = $result->fetch_assoc()) {
        $expenses_by_category['labels'][] = $row['name'];
        $expenses_by_category['values'][] = floatval($row['total']);
        $expenses_by_category['icons'][] = $row['icon_class'];
        $expenses_by_category['colors'][] = $row['color'];
    }

    // Entrate per categoria
    $sql = "SELECT 
                c.name,
                i.icon_class,
                c.color,
                COALESCE(SUM(t.amount), 0) as total
            FROM categories c
            JOIN icons i ON c.icon_id = i.id
            LEFT JOIN transactions t ON t.category_id = c.id AND t.date BETWEEN ? AND ?
            WHERE c.type = 'income'
            GROUP BY c.id, c.name, i.icon_class, c.color
            HAVING total > 0
            ORDER BY total DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $income_by_category = [
        'labels' => [], 
        'values' => [],
        'icons' => [],
        'colors' => []
    ];

    while ($row = $result->fetch_assoc()) {
        $income_by_category['labels'][] = $row['name'];
        $income_by_category['values'][] = floatval($row['total']);
        $income_by_category['icons'][] = $row['icon_class'];
        $income_by_category['colors'][] = $row['color'];
    }

    $response = [
        'summary' => $summary,
        'trend' => $trend,
        'expenses_by_category' => $expenses_by_category,
        'income_by_category' => $income_by_category
    ];

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    error_log("Report error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
