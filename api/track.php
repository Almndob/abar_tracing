<?php
header('Content-Type: application/json');
require_once "../includes/db.php";

// Function to get a random point within a defined area (for simulation)
function getRandomLocation() {
    // Area around Ha'il, KSA (for simulation)
    // Ha'il coordinates: 27.5250° N, 41.6900° E
    $minLat = 27.4;
    $maxLat = 27.7;
    $minLon = 41.5;
    $maxLon = 41.8;

    $lat = $minLat + mt_rand() / mt_getrandmax() * ($maxLat - $minLat);
    $lon = $minLon + mt_rand() / mt_getrandmax() * ($maxLon - $minLon);

    return [
        'latitude' => round($lat, 8),
        'longitude' => round($lon, 8)
    ];
}

// Function to simulate a truck's movement (simple random for demo)
function simulateTruckMovement($truck_id) {
    global $link;
    $location = getRandomLocation();
    $sql = "INSERT INTO tracking_history (truck_id, latitude, longitude) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "idd", $truck_id, $location['latitude'], $location['longitude']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Check for truck_number or shipment_id
$search_term = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($search_term)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a Truck Number or Shipment ID.']);
    exit;
}

// Find truck/shipment details
$sql = "
    SELECT 
        t.id as truck_id, 
        t.truck_number, 
        t.driver_name, 
        t.status as truck_status,
        s.shipment_id,
        s.origin,
        s.destination,
        s.estimated_arrival,
        s.status as shipment_status
    FROM trucks t
    LEFT JOIN shipments s ON t.id = s.truck_id
    WHERE t.truck_number = ? OR s.shipment_id = ?
    LIMIT 1
";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($data = mysqli_fetch_assoc($result)) {
        $truck_id = $data['truck_id'];

        // 1. Simulate movement (for demo purposes)
        simulateTruckMovement($truck_id);

        // 2. Get the latest location
        $location_sql = "SELECT latitude, longitude, timestamp FROM tracking_history WHERE truck_id = ? ORDER BY timestamp DESC LIMIT 1";
        if ($loc_stmt = mysqli_prepare($link, $location_sql)) {
            mysqli_stmt_bind_param($loc_stmt, "i", $truck_id);
            mysqli_stmt_execute($loc_stmt);
            $loc_result = mysqli_stmt_get_result($loc_stmt);
            $location_data = mysqli_fetch_assoc($loc_result);
            mysqli_stmt_close($loc_stmt);
        }

        // 3. Prepare the final response
        $response = [
            'success' => true,
            'truck_info' => [
                'Truck Number' => $data['truck_number'],
                'Driver Name' => $data['driver_name'],
                'Current Status' => $data['truck_status'],
                'Shipment ID' => $data['shipment_id'] ?? 'N/A',
                'Origin' => $data['origin'] ?? 'N/A',
                'Destination' => $data['destination'] ?? 'N/A',
                'Estimated Arrival' => $data['estimated_arrival'] ? date('M d, Y H:i A', strtotime($data['estimated_arrival'])) : 'N/A',
                'Shipment Status' => $data['shipment_status'] ?? 'N/A',
                'Last Updated' => $location_data ? date('H:i:s A', strtotime($location_data['timestamp'])) : 'N/A',
            ],
            'location' => $location_data ? [
                'lat' => (float)$location_data['latitude'],
                'lng' => (float)$location_data['longitude']
            ] : null
        ];

        echo json_encode($response);

    } else {
        echo json_encode(['success' => false, 'message' => 'Truck or Shipment ID not found.']);
    }

    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}

mysqli_close($link);
?>
