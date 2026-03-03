<!-- last updated on06-Feb-2026 at 7:41 am -->


<?php
header('Content-Type: application/json');

$servername = "localhost";
$username   = "root";
$password   = "Password@123";
$dbname     = "wadeadamdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$month = isset($_GET['month']) ? intval($_GET['month']) : 0;
$year  = isset($_GET['year']) ? intval($_GET['year']) : 0;

$where = [];
if ($month) $where[] = "MONTH(`ORDER_DATE`) = $month";
if ($year)  $where[] = "YEAR(`ORDER_DATE`) = $year";

$sql = "
    SELECT 
        `SUPPLIER NAME` AS name,
        COUNT(*) AS total_orders,
        SUM(`VALUE`) AS total_value
    FROM `orders`
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " GROUP BY `SUPPLIER NAME`
          ORDER BY total_value DESC";

$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'name' => $row['name'],
            'total_orders' => intval($row['total_orders']),
            'total_value' => floatval($row['total_value'])
        ];
    }
}

$totalSpend  = array_sum(array_column($data, 'total_value'));
$totalOrders = array_sum(array_column($data, 'total_orders'));
$topSupplier = isset($data[0]['name']) ? $data[0]['name'] : '—';

echo json_encode([
    'kpis' => [
        'totalSpend'  => $totalSpend,
        'totalOrders' => $totalOrders,
        'topSupplier' => $topSupplier
    ],
    'top5' => array_slice($data, 0, 5)
]);

$conn->close();
