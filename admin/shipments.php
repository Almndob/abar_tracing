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

// Fetch all trucks for dropdown
$all_trucks = [];
$sql_trucks = "SELECT id, truck_number, driver_name FROM trucks ORDER BY truck_number ASC";
if ($result_trucks = mysqli_query($link, $sql_trucks)) {
    while ($row = mysqli_fetch_assoc($result_trucks)) {
        $all_trucks[] = $row;
    }
}

// Handle form submission for Add/Edit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shipment_id = trim($_POST['shipment_id']);
    $truck_id = $_POST['truck_id'];
    $origin = trim($_POST['origin']);
    $destination = trim($_POST['destination']);
    $estimated_arrival = trim($_POST['estimated_arrival']);
    $departure_date = trim($_POST['departure_date']);
    $arrival_date = trim($_POST['arrival_date']);
    $status = trim($_POST['status']);
    $post_id = $_POST['id'] ?? null;

    if (empty($shipment_id) || empty($truck_id) || empty($origin) || empty($destination) || empty($estimated_arrival) || empty($status)) {
        $error = "All required fields must be filled.";
    } else {
        $datetime_arrival = date('Y-m-d H:i:s', strtotime($estimated_arrival));
        $datetime_departure = !empty($departure_date) ? date('Y-m-d H:i:s', strtotime($departure_date)) : null;
        $datetime_arrival_actual = !empty($arrival_date) ? date('Y-m-d H:i:s', strtotime($arrival_date)) : null;

        if ($post_id) {
            // Edit
            $sql = "UPDATE shipments SET 
                        shipment_id = ?, 
                        truck_id = ?, 
                        origin = ?, 
                        destination = ?, 
                        estimated_arrival = ?, 
                        status = ?, 
                        departure_date = ?, 
                        arrival_date = ? 
                    WHERE id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sissssssi", $shipment_id, $truck_id, $origin, $destination, $datetime_arrival, $status, $datetime_departure, $datetime_arrival_actual, $post_id);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Shipment updated successfully.";
                    $action = 'list';
                } else {
                    $error = "Error updating shipment: " . mysqli_error($link);
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            // Add
            $sql = "INSERT INTO shipments 
                    (shipment_id, truck_id, origin, destination, estimated_arrival, status, departure_date, arrival_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sissssss", $shipment_id, $truck_id, $origin, $destination, $datetime_arrival, $status, $datetime_departure, $datetime_arrival_actual);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Shipment added successfully.";
                    $action = 'list';
                } else {
                    $error = "Error adding shipment: " . mysqli_error($link);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Handle Delete action
if ($action == 'delete' && $id) {
    $sql = "DELETE FROM shipments WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Shipment deleted successfully.";
        } else {
            $error = "Error deleting shipment: " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    }
    $action = 'list';
}

// Fetch data for list view
$shipments = [];
if ($action == 'list') {
    $sql = "SELECT s.*, t.truck_number, t.driver_name 
            FROM shipments s 
            JOIN trucks t ON s.truck_id = t.id 
            ORDER BY s.id DESC";
    if ($result = mysqli_query($link, $sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $shipments[] = $row;
        }
    }
}

// Fetch data for edit view
$shipment_data = [
    'shipment_id' => '', 
    'truck_id' => '', 
    'origin' => '', 
    'destination' => '', 
    'estimated_arrival' => '', 
    'departure_date' => '',
    'arrival_date' => '',
    'status' => ''
];

if ($action == 'edit' && $id) {
    $sql = "SELECT * FROM shipments WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $shipment_data = $row;
            $shipment_data['estimated_arrival'] = date('Y-m-d\TH:i', strtotime($shipment_data['estimated_arrival']));
            $shipment_data['departure_date'] = !empty($shipment_data['departure_date']) ? date('Y-m-d\TH:i', strtotime($shipment_data['departure_date'])) : '';
            $shipment_data['arrival_date'] = !empty($shipment_data['arrival_date']) ? date('Y-m-d\TH:i', strtotime($shipment_data['arrival_date'])) : '';
        } else {
            $error = "Shipment not found.";
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
<title>Manage Shipments - Abar Tracing</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
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
            background-color: #7A2A8A;

        }
        .content-box {
            background-color: #ad6ec1ff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .shipment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .shipment-table th, .shipment-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #374151;
        }
        .shipment-table th {
            background-color: #7A2A8A;
            color: var(--accent-color);
            font-weight: 600;
        }
        .shipment-table tr:hover {
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
</style>
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <h2>Abar Tracing</h2>
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="trucks.php"><i class="fas fa-truck"></i> Trucks</a>
            <a href="shipments.php" class="active"><i class="fas fa-box"></i> Deliveries</a>
            <a href="#"><i class="fas fa-chart-line"></i> Reports</a>
            <a href="#"><i class="fas fa-cog"></i> Settings</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1>Manage Shipments</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($action == 'list'): ?>
            <div class="content-box">
                <a href="shipments.php?action=add" class="btn-add"><i class="fas fa-plus"></i> Add New Shipment</a>
                <table class="shipment-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Shipment ID</th>
                            <th>Truck</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>ETA</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($shipments)): ?>
                            <?php foreach ($shipments as $shipment): ?>
                                <tr>
                                    <td><?php echo $shipment['id']; ?></td>
                                    <td><?php echo htmlspecialchars($shipment['shipment_id']); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['truck_number'] . ' (' . $shipment['driver_name'] . ')'); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['origin']); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['destination']); ?></td>
                                    <td><?php echo $shipment['departure_date'] ? date('Y-m-d H:i', strtotime($shipment['departure_date'])) : '-'; ?></td>
                                    <td><?php echo $shipment['arrival_date'] ? date('Y-m-d H:i', strtotime($shipment['arrival_date'])) : '-'; ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($shipment['estimated_arrival'])); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['status']); ?></td>
                                    <td class="action-links">
                                        <a href="shipments.php?action=edit&id=<?php echo $shipment['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="shipments.php?action=delete&id=<?php echo $shipment['id']; ?>" onclick="return confirm('Are you sure?');"><i class="fas fa-trash-alt"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">No shipments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <div class="content-box">
                <h2><?php echo ($action == 'edit' ? 'Edit Shipment' : 'Add New Shipment'); ?></h2>
                <form action="shipments.php" method="POST">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="shipment_id">Shipment ID</label>
                        <input type="text" id="shipment_id" name="shipment_id" class="form-control" value="<?php echo htmlspecialchars($shipment_data['shipment_id']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="truck_id">Assigned Truck</label>
                        <select id="truck_id" name="truck_id" class="form-control" required>
                            <option value="">Select Truck</option>
                            <?php foreach ($all_trucks as $truck): ?>
                                <option value="<?php echo $truck['id']; ?>" <?php echo ($shipment_data['truck_id'] == $truck['id'] ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($truck['truck_number'] . ' - ' . $truck['driver_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="origin">Origin</label>
                        <input type="text" id="origin" name="origin" class="form-control" value="<?php echo htmlspecialchars($shipment_data['origin']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="destination">Destination</label>
                        <input type="text" id="destination" name="destination" class="form-control" value="<?php echo htmlspecialchars($shipment_data['destination']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="departure_date">Departure Date & Time</label>
                        <input type="datetime-local" id="departure_date" name="departure_date" class="form-control" value="<?php echo htmlspecialchars($shipment_data['departure_date']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="arrival_date">Arrival Date & Time</label>
                        <input type="datetime-local" id="arrival_date" name="arrival_date" class="form-control" value="<?php echo htmlspecialchars($shipment_data['arrival_date']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="estimated_arrival">Estimated Arrival (ETA)</label>
                        <input type="datetime-local" id="estimated_arrival" name="estimated_arrival" class="form-control" value="<?php echo htmlspecialchars($shipment_data['estimated_arrival']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Pending" <?php echo ($shipment_data['status'] == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                            <option value="In Transit" <?php echo ($shipment_data['status'] == 'In Transit' ? 'selected' : ''); ?>>In Transit</option>
                            <option value="Delivered" <?php echo ($shipment_data['status'] == 'Delivered' ? 'selected' : ''); ?>>Delivered</option>
                            <option value="Delayed" <?php echo ($shipment_data['status'] == 'Delayed' ? 'selected' : ''); ?>>Delayed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-submit"><?php echo ($action == 'edit' ? 'Update Shipment' : 'Add Shipment'); ?></button>
                    <a href="shipments.php" class="btn-submit" style="background-color: #6b7280; margin-left: 10px;">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
