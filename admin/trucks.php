<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "../includes/db.php";

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = $success = '';

// Handle form submission for Add/Edit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $truck_number = trim($_POST['truck_number']);
    $driver_name = trim($_POST['driver_name']);
    $status = trim($_POST['status']);
    $post_id = $_POST['id'] ?? null;

    if (empty($truck_number) || empty($driver_name) || empty($status)) {
        $error = "All fields are required.";
    } else {
        if ($post_id) {
            // Edit
            $sql = "UPDATE trucks SET truck_number = ?, driver_name = ?, status = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssi", $truck_number, $driver_name, $status, $post_id);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Truck updated successfully.";
                    $action = 'list';
                } else {
                    $error = "Error updating truck: " . mysqli_error($link);
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            // Add
            $sql = "INSERT INTO trucks (truck_number, driver_name, status) VALUES (?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sss", $truck_number, $driver_name, $status);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Truck added successfully.";
                    $action = 'list';
                } else {
                    $error = "Error adding truck: " . mysqli_error($link);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Handle Delete action
if ($action == 'delete' && $id) {
    // Delete related tracking history first
    $sql_history = "DELETE FROM tracking_history WHERE truck_id = ?";
    if ($stmt_history = mysqli_prepare($link, $sql_history)) {
        mysqli_stmt_bind_param($stmt_history, "i", $id);
        mysqli_stmt_execute($stmt_history);
        mysqli_stmt_close($stmt_history);
    }
    // Delete related shipments
    $sql_shipments = "DELETE FROM shipments WHERE truck_id = ?";
    if ($stmt_shipments = mysqli_prepare($link, $sql_shipments)) {
        mysqli_stmt_bind_param($stmt_shipments, "i", $id);
        mysqli_stmt_execute($stmt_shipments);
        mysqli_stmt_close($stmt_shipments);
    }
    // Delete truck
    $sql = "DELETE FROM trucks WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Truck deleted successfully.";
        } else {
            $error = "Error deleting truck: " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    }
    $action = 'list';
}

// Fetch data for list view
$trucks = [];
if ($action == 'list') {
    $sql = "SELECT * FROM trucks ORDER BY id DESC";
    if ($result = mysqli_query($link, $sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $trucks[] = $row;
        }
    }
}

// Fetch data for edit view
$truck_data = ['truck_number' => '', 'driver_name' => '', 'status' => ''];
if ($action == 'edit' && $id) {
    $sql = "SELECT * FROM trucks WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $truck_data = $row;
        } else {
            $error = "Truck not found.";
            $action = 'list';
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trucks - Abar Tracing</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Reusing dashboard styles for consistency */
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
            background-color: #ad6ec1ff;
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
            background-color: #7A2A8A;
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
            background-color: #ad6ec1ff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .truck-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .truck-table th, .truck-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #374151;
        }
        .truck-table th {
            background-color:#7A2A8A;
            color: var(--accent-color);
            font-weight: 600;
        }
        .truck-table tr:hover {
            background-color: #374151;
        }
        .action-links a {
            color: var(--accent-color);
            margin-right: 15px;
            text-decoration: none;
        }
        .btn-add {
            background-color: #10b981;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #374151;
            background-color: #374151;
            color: #f8fafc;
        }
        .btn-submit {
            background-color: var(--accent-color);
            color: var(--white-color);
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Abar Tracing</h2>
            <div class="sidebar-menu">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="trucks.php" class="active"><i class="fas fa-truck"></i> Trucks</a>
                <a href="shipments.php"><i class="fas fa-box"></i> Deliveries</a>
                <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>
                <a href="setting.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-content">
            <h1>Manage Trucks</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($action == 'list'): ?>
                <div class="content-box">
                    <a href="trucks.php?action=add" class="btn-add"><i class="fas fa-plus"></i> Add New Truck</a>
                    <table class="truck-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Truck Number</th>
                                <th>Driver Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($trucks)): ?>
                                <?php foreach ($trucks as $truck): ?>
                                    <tr>
                                        <td><?php echo $truck['id']; ?></td>
                                        <td><?php echo htmlspecialchars($truck['truck_number']); ?></td>
                                        <td><?php echo htmlspecialchars($truck['driver_name']); ?></td>
                                        <td><?php echo htmlspecialchars($truck['status']); ?></td>
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
            <?php elseif ($action == 'add' || $action == 'edit'): ?>
                <div class="content-box">
                    <h2><?php echo ($action == 'edit' ? 'Edit Truck' : 'Add New Truck'); ?></h2>
                    <form action="trucks.php" method="POST">
                        <?php if ($action == 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="truck_number">Truck Number</label>
                            <input type="text" id="truck_number" name="truck_number" class="form-control" value="<?php echo htmlspecialchars($truck_data['truck_number']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="driver_name">Driver Name</label>
                            <input type="text" id="driver_name" name="driver_name" class="form-control" value="<?php echo htmlspecialchars($truck_data['driver_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="Parked" <?php echo ($truck_data['status'] == 'Parked' ? 'selected' : ''); ?>>Parked</option>
                                <option value="On Route" <?php echo ($truck_data['status'] == 'On Route' ? 'selected' : ''); ?>>On Route</option>
                                <option value="Maintenance" <?php echo ($truck_data['status'] == 'Maintenance' ? 'selected' : ''); ?>>Maintenance</option>
                                <option value="Delivered" <?php echo ($truck_data['status'] == 'Delivered' ? 'selected' : ''); ?>>Delivered</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit"><?php echo ($action == 'edit' ? 'Update Truck' : 'Add Truck'); ?></button>
                        <a href="trucks.php" class="btn-submit" style="background-color: #6b7280; margin-left: 10px;">Cancel</a>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
