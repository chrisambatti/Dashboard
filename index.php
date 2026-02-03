<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "Password@123";
$dbname     = "wadeadamdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("<h2>Connection failed: " . $conn->connect_error . "</h2>");
}


$tableName = "orders";


$result = $conn->query("SELECT * FROM `$tableName`");

$labels = [];
$values = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Spend Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin-bottom: 90px; width: 100%; }
        th, td { border: 1px solid #888; padding: 2px; text-align: left; }
        th { background-color: #c0d8e9; }
        h2 { text-align: center; margin-bottom: 20px; }
        canvas { display: block; margin: 0 auto 60px;width: 600px; height: 600px; }
    </style>
</head>
<body>
    <h2>Supplier Spend Overview</h2>

<?php if ($result && $result->num_rows > 0): ?>

    <!-- Table -->
    <table>
        <tr>
            <?php
            $fields = $result->fetch_fields();
            foreach ($fields as $field):
                if ($field->name === "SR.NO"):
            ?>
                <th>SUPPLIER NAME</th>
            <?php else: ?>
                <th><?= htmlspecialchars($field->name) ?></th>
            <?php
                endif;
            endforeach;
            ?>
        </tr>

        <?php
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <?php foreach ($fields as $field): ?>
                <td>
                    <?php
                    if ($field->name === "SR.NO") {
                        echo htmlspecialchars($row['SUPPLIER NAME']);
                        $labels[] = $row['SUPPLIER NAME']; 
                    } elseif ($field->name === "VALUE") {
                        echo number_format($row['VALUE']);
                        $values[] = (float)$row['VALUE']; 
                    } else {
                        echo htmlspecialchars($row[$field->name]);
                    }
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Bar Chart Starts-->
    <canvas id="supplierChart" width="1100" height="390"></canvas>
    <script>
        const ctx = document.getElementById('supplierChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'VALUE',
                    data: <?= json_encode($values) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.7,
                }]
            },
            options: {
                responsive: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { 
                        ticks: { maxRotation: 65, minRotation: 15, font: { size: 11 } } 
                    },
                    y: { 
                        beginAtZero: true,
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    </script>
    <!-- Bar Chart Ends-->
<?php else: ?>
    <p>No data found in table <strong><?= htmlspecialchars($tableName) ?></strong>.</p>
<?php endif; ?>

<?php $conn->close(); ?>
</body>
</html>
