<?php
// Start session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "../includes/db.php";

// Function to fetch data for top cards
function getCardData($link) {
    $data = [
        'active_trucks' => 0,
        'deliveries_completed' => 0,
        'late_deliveries' => 0
    ];

    // Active Trucks
    $sql_trucks = "SELECT COUNT(*) FROM trucks WHERE status = 'On Route'";
    if ($result = mysqli_query($link, $sql_trucks)) {
        $data['active_trucks'] = mysqli_fetch_array($result)[0];
    }

    // Deliveries Completed
    $sql_completed = "SELECT COUNT(*) FROM shipments WHERE status = 'Delivered'";
    if ($result = mysqli_query($link, $sql_completed)) {
        $data['deliveries_completed'] = mysqli_fetch_array($result)[0];
    }

    // Late Deliveries (Example: estimated_arrival is in the past and status is not Delivered)
    $sql_late = "SELECT COUNT(*) FROM shipments WHERE estimated_arrival < NOW() AND status != 'Delivered'";
    if ($result = mysqli_query($link, $sql_late)) {
        $data['late_deliveries'] = mysqli_fetch_array($result)[0];
    }

    return $data;
}

$card_data = getCardData($link);

// Function to fetch data for the truck table
function getTrucksData($link) {
    $trucks = [];
    $sql = "SELECT t.id, t.truck_number, t.driver_name, t.status, s.shipment_id FROM trucks t LEFT JOIN shipments s ON t.id = s.truck_id ORDER BY t.id DESC";
    if ($result = mysqli_query($link, $sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $trucks[] = $row;
        }
    }
    return $trucks;
}

$trucks_data = getTrucksData($link);

// Prepare data for Chart.js (Simulated/Placeholder data)
$chart_labels = json_encode(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']);
$chart_data = json_encode([12, 19, 3, 5, 2, 3, 7]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Abar Tracing</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Dashboard Specific Styles (Dark Mode) */
        body {
            background-color: #7A2A8A; /* Dark background */
            color: #f8fafc; /* Light text */
            font-family: 'Inter', sans-serif;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #7A2A8A; /* Darker sidebar */
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
        .top-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .card-item {
            background-color: #7A2A8A;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-item .icon {
            font-size: 2.5rem;
            color: var(--accent-color);
        }
        .card-item .details h3 {
            margin: 0;
            font-size: 2rem;
            font-family: var(--mono-font);
        }
        .card-item .details p {
            margin: 0;
            color: #94a3b8;
        }
        .charts-section {
            background-color: #7A2A8A;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 40px;
        }
        .charts-section h2 {
            color: var(--white-color);
            margin-bottom: 20px;
        }
        .table-section {
            background-color: #7A2A8A;
            padding: 20px;
            border-radius: 10px;
        }
        .table-section h2 {
            color: var(--white-color);
            margin-bottom: 20px;
        }
        .truck-table {
            width: 100%;
            border-collapse: collapse;
        }
        .truck-table th, .truck-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #374151;
        }
        .truck-table th {
            background-color: #374151;
            color: var(--accent-color);
            font-weight: 600;
        }
        .truck-table tr:hover {
            background-color: #374151;
        }
        .action-links a {
            color: var(--accent-color);
            margin-right: 10px;
            text-decoration: none;
        }
        .btn-add {
            background-color: #10b981; /* Green for Add */
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Abar Tracing</h2>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="trucks.php"><i class="fas fa-truck"></i> Trucks</a>
                <a href="shipments.php"><i class="fas fa-box"></i> Deliveries</a>
                <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-content">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>

            <!-- Top Cards -->
            <div class="top-cards">
                <div class="card-item">
                    <div class="details">
                        <h3><?php echo $card_data['active_trucks']; ?></h3>
                        <p>Active Trucks</p>
                    </div>
                    <div class="icon"><i class="fas fa-truck-moving"></i></div>
                </div>
                <div class="card-item">
                    <div class="details">
                        <h3><?php echo $card_data['deliveries_completed']; ?></h3>
                        <p>Deliveries Completed</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
                <div class="card-item">
                    <div class="details">
                        <h3><?php echo $card_data['late_deliveries']; ?></h3>
                        <p>Late Deliveries</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <h2>Weekly Delivery Performance</h2>
                <div style="max-width: 100%; height: 400px;"><canvas id="deliveryChart"></canvas></div>
            </div>

            <!-- Truck Table -->
            <div class="table-section">
                <h2>Trucks Overview</h2>
                <a href="trucks.php?action=add" class="btn-add"><i class="fas fa-plus"></i> Add New Truck</a>
                <table class="truck-table">
                    <thead>
                        <tr>
                            <th>Truck ID</th>
                            <th>Driver Name</th>
                            <th>Status</th>
                            <th>Shipment ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($trucks_data)): ?>
                            <?php foreach ($trucks_data as $truck): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($truck['truck_number']); ?></td>
                                    <td><?php echo htmlspecialchars($truck['driver_name']); ?></td>
                                    <td><?php echo htmlspecialchars($truck['status']); ?></td>
                                    <td><?php echo htmlspecialchars($truck['shipment_id'] ?? 'N/A'); ?></td>
                                    <td class="action-links">
                                        <a href="trucks.php?action=edit&id=<?php echo $truck['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="trucks.php?action=delete&id=<?php echo $truck['id']; ?>" onclick="return confirm('Are you sure you want to delete this truck?');"><i class="fas fa-trash-alt"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No trucks found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Chart.js implementation
        const ctx = document.getElementById('deliveryChart').getContext('2d');
        const deliveryChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $chart_labels; ?>,
                datasets: [{
                    label: 'Deliveries Completed',
                    data: <?php echo $chart_data; ?>,
                    backgroundColor: 'rgba(255, 122, 0, 0.2)',
                    borderColor: 'var(--accent-color)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
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
<?php mysqli_close($link); ?>
