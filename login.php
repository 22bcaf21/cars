<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Your CSS styles remain the same */
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form action="method1.php" method="post">
            <input type="email" name="email" placeholder="Enter Your Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php" class="toggle-btn">Sign Up</a></p>
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<p class="error">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
            unset($_SESSION['error_message']);
        }
        ?>
    </div>
</body>
</html>
