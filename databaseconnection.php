<?php
$servername = "localhost"; 
$username = "root"; 
$password = "root"; 
$database = "CarService";
$port="3307";

// Create connection
$conn = new mysqli($servername, $username, $password, $database,$port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>