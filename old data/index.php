<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    <link rel="stylesheet" href="style.css">
    <title class="page-title">Supplier Spend Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin-bottom: 50px; width: 100%; }
        th, td { border: 1px solid #888; padding: 2px; text-align: left; }
        th { background-color: #c0d8e9; }
        h2 { text-align: center; margin-bottom: 20px; }
        canvas { display: block; margin: 0 auto 40px; width: 500px; height: 300px; }
        select { font-size: 14px; padding: 4px; }
    </style>
</head>
<body>
     <img class="logo" src="assets/2wadeadamlogo.jpeg" alt="wadeadam_logo">
    <h2 class="page-title">Supplier Spend Overview</h2>

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
 <canvas id="supplierChart" width="800" height="400"></canvas>

<!-- Chart Types Button -->
<div style="text-align:center; margin-top: 20px; bborder-top: 1px solid #ccc; padding-top: 10px; bgcolor: #f9f9f9;">
    <label for="chartType">Change Chart Type: </label>
    <select id="chartType">
        <option value="pie">Pie</option>
        <option value="bar">Bar</option>
        <option value="doughnut">Doughnut</option>
        <option value="scatter">Scatter</option>
        <option value="line">Line</option>
        
    </select>
</div>

<script>
    const labels = <?= json_encode($labels) ?>;
    const values = <?= json_encode($values) ?>;

    
    const colors = labels.map(() => `hsl(${Math.random() * 360}, 70%, 60%)`);

    
    let ctx = document.getElementById('supplierChart').getContext('2d');
    let chart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Supplier Value',
                data: values,
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 10 }, boxWidth: 12 }
                }
            }
        }
    });

    
    document.getElementById('chartType').addEventListener('change', function() {
        const newType = this.value;

        chart.destroy();
        
        let newData = { labels: labels, datasets: [{
            label: 'Supplier Value',
            data: (newType === 'scatter') ? values.map((v,i) => ({x:i+1, y:v})) : values,
            backgroundColor: colors,
            borderColor: '#fff',
            borderWidth: 1,
            borderRadius: 5,
            barPercentage: 0.7
        }]};

        chart = new Chart(ctx, {
            type: newType,
            data: newData,
            options: {
                responsive: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 10 }, boxWidth: 12 }
                    }
                },
                scales: (newType === 'scatter' || newType === 'bar' || newType === 'line') ? {
                    x: { beginAtZero: true, title: { display: true, text: 'Supplier Index' } },
                    y: { beginAtZero: true, title: { display: true, text: 'Value' } }
                } : {}
            }
        });
    });
</script>
<!-- Bar Chart Ends-->

<?php else: ?>
    <p>No data found in table <strong><?= htmlspecialchars($tableName) ?></strong>.</p>
<?php endif; ?>

<?php $conn->close(); ?>
</body>
</html>
