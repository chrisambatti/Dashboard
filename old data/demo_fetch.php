<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "Password@123", "wadeadamdb");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$month = $_GET['month'] ?? '';
$year  = $_GET['year'] ?? '';

$where = [];
if ($month) $where[] = "MONTH(`ORDER_DATE`) = " . intval($month);
if ($year)  $where[] = "YEAR(`ORDER_DATE`) = " . intval($year);

$sql = "
    SELECT 
        `SUPPLIER NAME` AS name,
        COUNT(*) AS total_orders,
        SUM(`VALUE`) AS total_value
    FROM orders
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " GROUP BY `SUPPLIER NAME`
          ORDER BY total_value DESC";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    'kpis' => [
        'totalSpend'  => array_sum(array_column($data, 'total_value')),
        'totalOrders' => array_sum(array_column($data, 'total_orders')),
        'topSupplier' => $data[0]['name'] ?? ''
    ],
    'top5' => array_slice($data, 0, 5)
]);

$conn->close();
