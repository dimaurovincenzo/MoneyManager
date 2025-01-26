<?php
require_once '../includes/config.php';
requireLogin();

header('Content-Type: application/json');

// Parametri di paginazione
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 20;
$offset = ($page - 1) * $items_per_page;

// Costruzione della query base
$sql = "SELECT 
            t.*,
            a.name as account_name,
            c.name as category_name,
            c.color as category_color,
            i.icon_class as category_icon,
            c.type as category_type
        FROM transactions t
        JOIN accounts a ON t.account_id = a.id
        JOIN categories c ON t.category_id = c.id
        LEFT JOIN icons i ON c.icon_id = i.id
        WHERE 1=1";

$count_sql = "SELECT COUNT(*) as total FROM transactions t WHERE 1=1";
$params = [];
$types = "";

// Filtri
if (!empty($_GET['account_id'])) {
    $sql .= " AND t.account_id = ?";
    $count_sql .= " AND t.account_id = ?";
    $params[] = (int)$_GET['account_id'];
    $types .= "i";
}

if (!empty($_GET['category_id'])) {
    $sql .= " AND t.category_id = ?";
    $count_sql .= " AND t.category_id = ?";
    $params[] = (int)$_GET['category_id'];
    $types .= "i";
}

if (!empty($_GET['type'])) {
    $sql .= " AND t.type = ?";
    $count_sql .= " AND t.type = ?";
    $params[] = $_GET['type'];
    $types .= "s";
}

if (!empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $sql .= " AND (c.name LIKE ? OR a.name LIKE ? OR t.description LIKE ?)";
    $count_sql .= " AND (c.name LIKE ? OR a.name LIKE ? OR t.description LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

if (!empty($_GET['date_from'])) {
    $sql .= " AND t.date >= ?";
    $count_sql .= " AND t.date >= ?";
    $params[] = $_GET['date_from'];
    $types .= "s";
}

if (!empty($_GET['date_to'])) {
    $sql .= " AND t.date <= ?";
    $count_sql .= " AND t.date <= ?";
    $params[] = $_GET['date_to'];
    $types .= "s";
}

// Conteggio totale record
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $items_per_page);

// Query principale con ordinamento e paginazione
$sql .= " ORDER BY t.date DESC, t.created_at DESC LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = [
        'id' => $row['id'],
        'amount' => $row['amount'],
        'description' => $row['description'],
        'date' => $row['date'],
        'type' => $row['type'],
        'account_name' => $row['account_name'],
        'category_name' => $row['category_name'],
        'color' => $row['category_color'],
        'icon_class' => $row['category_icon'] ?? 'bi-tag-fill'
    ];
}

echo json_encode([
    'success' => true,
    'transactions' => $transactions,
    'has_more' => $page < $total_pages
]);
