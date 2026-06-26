<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "../includes/db.php";

// 1. Shipment Status Distribution Report
$status_data = [];
$sql_status = "SELECT status, COUNT(*) as count FROM shipments GROUP BY status";
if ($result = mysqli_query($link, $sql_status)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $status_data[$row['status']] = $row['count'];
    }
}
$status_labels = json_encode(array_keys($status_data));
$status_counts = json_encode(array_values($status_data));

// 2. Truck Performance Report (Shipments per Truck)
$truck_performance = [];
$sql_performance = "
    SELECT 
        t.truck_number, 
        COUNT(s.id) as shipment_count 
    FROM trucks t
    LEFT JOIN shipments s ON t.id = s.truck_id
    GROUP BY t.truck_number
    ORDER BY shipment_count DESC
";
if ($result = mysqli_query($link, $sql_performance)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $truck_performance[$row['truck_number']] = $row['shipment_count'];
    }
}
$truck_labels = json_encode(array_keys($truck_performance));
$shipment_counts = json_encode(array_values($truck_performance));

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Abar Tracing</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Dashboard Specific Styles (Dark Mode) */
        body {
            background-color: #7A2A8A;
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #7A2A8A;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.2);
        }
        .sidebar h2 {
            color: var(--accent-color);
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 10px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: background-color 0.3s, color 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--primary-color);
            color: var(--white-color);
        }
        .sidebar-menu i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .main-content {
            flex-grow: 1;
            padding: 40px;
        }
        .content-box {
            background-color: #7A2A8A;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }
        .chart-container {
            height: 400px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Abar Tracing</h2>
            <div class="sidebar-menu">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="trucks.php"><i class="fas fa-truck"></i> Trucks</a>
                <a href="shipments.php"><i class="fas fa-box"></i> Deliveries</a>
                <a href="reports.php" class="active"><i class="fas fa-chart-line"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-content">
            <h1>System Reports</h1>
            
            <div class="reports-grid">
                <!-- Shipment Status Distribution -->
                <div class="content-box">
                    <h2>Shipment Status Distribution</h2>
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Truck Performance -->
                <div class="content-box">
                    <h2>Truck Performance (Shipments)</h2>
                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart 1: Shipment Status Distribution (Doughnut Chart)
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $status_labels; ?>,
                datasets: [{
                    label: 'Shipment Count',
                    data: <?php echo $status_counts; ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)', // Red (Delayed)
                        'rgba(54, 162, 235, 0.8)', // Blue (In Transit)
                        'rgba(255, 206, 86, 0.8)', // Yellow (Pending)
                        'rgba(75, 192, 192, 0.8)', // Green (Delivered)
                    ],
                    borderColor: '#1e293b',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#f8fafc'
                        }
                    },
                    title: {
                        display: false,
                    }
                }
            }
        });

        // Chart 2: Truck Performance (Bar Chart)
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $truck_labels; ?>,
                datasets: [{
                    label: 'Shipments Handled',
                    data: <?php echo $shipment_counts; ?>,
                    backgroundColor: 'rgba(255, 122, 0, 0.8)', // Accent Color
                    borderColor: 'var(--accent-color)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#374151'
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    },
                    x: {
                        grid: {
                            color: '#374151'
                        },
                        ticks: {
                            color: '#94a3b8'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#f8fafc'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
