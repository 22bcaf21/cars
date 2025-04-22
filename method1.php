<?php
include("databaseconnection.php"); // Ensure the file exists

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if (empty($email) || empty($password)) {
        $error_message = "All fields are required!";
        header("Location: /carservice/login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    if ($stmt === false) {
        $error_message = "Database error: Unable to prepare statement.";
        header("Location: /carservice/login.php");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($stored_password);
        $stmt->fetch();

        if (password_verify($password, $stored_password)) {
            session_start();
            $_SESSION['user_email'] = $email;
            header("Location: /carservice/home.php");
            exit();
        } else {
            $error_message = "Invalid email or password!";
            header("Location: /carservice/login.php");
            exit();
        }
    } else {
        $error_message = "User not found!";
        header("Location: /carservice/login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>