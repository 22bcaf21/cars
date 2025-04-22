<?php
$servername = "my-mysql"; 
$username = "root"; 
$password = "root"; 
$database = "CarService";


// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
