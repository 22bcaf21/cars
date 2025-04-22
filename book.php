<?php
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_email']) || empty($_SESSION['user_email'])) {
    header("Location: /carservice/login.php");
    exit();
}

// Include database connection
try {
    include("databaseconnection.php");
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    // Log the error for debugging (uncomment to enable)
    // error_log($error_message, 3, "errors.log");
}

// Initialize variables
$selected_services = $_POST['services'] ?? [];
$total_amount = 0;
$services_map = [
    'oil_change' => 30,
    'brake_repair' => 100,
    'tyre_service' => 50,
    'car_wash' => 15
];
foreach ($selected_services as $service) {
    $total_amount += $services_map[$service] ?? 0;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_booking'])) {
    $vehicle_name = trim($_POST['vehicle_name'] ?? '');
    $preferred_date = trim($_POST['preferred_date'] ?? '');

    // Validate inputs
    if (empty($selected_services) || empty($vehicle_name) || empty($preferred_date)) {
        $error_message = "All fields and at least one service are required!";
    } elseif (strtotime($preferred_date) < strtotime(date('Y-m-d'))) {
        $error_message = "Preferred date must be in the future!";
    } else {
        try {
            // Fetch user_id from users table
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("s", $_SESSION['user_email']);
            $stmt->execute();
            $stmt->bind_result($user_id);
            if (!$stmt->fetch()) {
                throw new Exception("User not found for email: " . $_SESSION['user_email']);
            }
            $stmt->close();

            // Convert services array to comma-separated string
            $services_string = implode(',', $selected_services);

            // Insert booking into database
            $stmt = $conn->prepare("INSERT INTO booking (user_id, services, vehicle_name, preferred_date) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("isss", $user_id, $services_string, $vehicle_name, $preferred_date);
            if ($stmt->execute()) {
                $booking_id = $stmt->insert_id;
                $stmt->close();
                $conn->close();
                header("Location: /carservice/receipt.php?booking_id=" . $booking_id);
                exit();
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            $error_message = "Booking error: " . $e->getMessage();
            // Log the error for debugging (uncomment to enable)
            // error_log($error_message, 3, "errors.log");
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Service - Confirm Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3a8a, #6b21a8);
            overflow-x: hidden;
        }
        .service-item {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .service-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        input[type="text"],
        input[type="date"] {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="date"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        .animate-gradient {
            background: linear-gradient(45deg, #3b82f6, #8b5cf6, #ec4899);
            background-size: 300%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .error {
            animation: shake 0.3s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="container bg-white rounded-2xl shadow-2xl p-8 w-full max-w-lg fade-in">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Confirm Your Booking</h1>
            <p class="text-lg text-gray-600 mt-2">Hello, <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'Guest'); ?>! Review and confirm your booking details.</p>
        </div>

        <?php if (!empty($selected_services)): ?>
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Selected Services</h2>
                <ul class="space-y-3">
                    <?php foreach ($selected_services as $service): ?>
                        <li class="service-item bg-gray-50 rounded-lg p-3 flex justify-between items-center border border-gray-200">
                            <span class="text-gray-700">
                                <?php
                                switch ($service) {
                                    case 'oil_change':
                                        echo 'Oil Change';
                                        break;
                                    case 'brake_repair':
                                        echo 'Brake Repair';
                                        break;
                                    case 'tyre_service':
                                        echo 'Tyre Service';
                                        break;
                                    case 'car_wash':
                                        echo 'Car Wash';
                                        break;
                                    default:
                                        echo htmlspecialchars($service);
                                }
                                ?>
                            </span>
                            <span class="text-blue-600 font-medium">$<?php echo $services_map[$service] ?? 0; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="text-xl font-semibold text-gray-800 mt-4">Total Amount: <span class="text-blue-600">$<?php echo $total_amount; ?></span></p>
            </div>

            <form action="" method="post" class="space-y-4">
                <?php foreach ($selected_services as $service): ?>
                    <input type="hidden" name="services[]" value="<?php echo htmlspecialchars($service); ?>">
                <?php endforeach; ?>
                <div>
                    <input type="text" name="vehicle_name" placeholder="Vehicle Name (e.g., Toyota Camry)" required
                           class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 focus:border-blue-500" value="<?php echo htmlspecialchars($_POST['vehicle_name'] ?? ''); ?>">
                </div>
                <div>
                    <input type="date" name="preferred_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                           class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 focus:border-blue-500" value="<?php echo htmlspecialchars($_POST['preferred_date'] ?? ''); ?>">
                </div>
                <div class="flex justify-center gap-4 mt-6">
                    <button type="submit" name="confirm_booking" class="btn animate-gradient text-white px-6 py-3 rounded-lg font-medium hover:opacity-90 transition-opacity">Confirm Booking</button>
                    <a href="/carservice/home.php" class="btn bg-gray-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-700 transition-colors">Back to Services</a>
                </div>
            </form>
        <?php else: ?>
            <p class="text-gray-600 mb-6">No services selected. Please choose at least one service.</p>
            <div class="flex justify-center">
                <a href="/carservice/home.php" class="btn bg-gray-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-700 transition-colors">Back to Services</a>
            </div>
        <?php endif; ?>

        <div class="flex justify-center mt-6">
            <a href="/carservice/login.php" class="btn bg-red-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-700 transition-colors">Logout</a>
        </div>

        <?php if (isset($error_message)): ?>
            <p class="error text-red-600 text-sm mt-4"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    </div>

    <script>
        // Add subtle animation on form submission
        document.querySelector('form')?.addEventListener('submit', (e) => {
            const button = e.target.querySelector('button[type="submit"]');
            button.classList.add('scale-95');
            setTimeout(() => button.classList.remove('scale-95'), 200);
        });
    </script>
</body>
</html>