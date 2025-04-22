<?php
$servername = "my-mysql";
$username = "root";
$password = "root"; // Replace with your actual MySQL root password
$database = "CarService"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to MySQL on server: $servername";

// Optional: Close the connection
$conn->close();
?>
