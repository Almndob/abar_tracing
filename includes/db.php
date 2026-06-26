<?php
// Database connection details
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Assuming default root user for sandbox environment
define('DB_PASSWORD', '');     // Assuming no password for default root user
define('DB_NAME', 'abar_tracing');

// Attempt to connect to MySQL database
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>

