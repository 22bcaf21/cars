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
    $error_message = "Database error: " . htmlspecialchars($e->getMessage());
}

// Fetch user_id from users table
try {
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
} catch (Exception $e) {
    $error_message = "Error: " . htmlspecialchars($e->getMessage());
    $conn->close();
    header("Location: /carservice/login.php");
    exit();
}

// Initialize variables
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$current_booking = null;
$total_amount = 0;
$services_map = [
    'oil_change' => ['name' => 'Oil Change', 'price' => 30],
    'brake_repair' => ['name' => 'Brake Repair', 'price' => 100],
    'tyre_service' => ['name' => 'Tyre Service', 'price' => 50],
    'car_wash' => ['name' => 'Car Wash', 'price' => 15]
];

// Fetch current booking details
if ($booking_id > 0) {
    try {
        $stmt = $conn->prepare("SELECT services, vehicle_name, preferred_date FROM booking WHERE book_id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($services, $vehicle_name, $preferred_date);
        if ($stmt->fetch()) {
            $current_booking = [
                'services' => explode(',', $services),
                'vehicle_name' => $vehicle_name,
                'preferred_date' => $preferred_date
            ];
            foreach ($current_booking['services'] as $service) {
                $total_amount += $services_map[$service]['price'] ?? 0;
            }
        } else {
            $error_message = "Booking not found or you don't have permission to view it.";
        }
        $stmt->close();
    } catch (Exception $e) {
        $error_message = "Error fetching booking: " . htmlspecialchars($e->getMessage());
    }
}

// Fetch all bookings for the user
$bookings = [];
try {
    $stmt = $conn->prepare("SELECT book_id, services, vehicle_name, preferred_date FROM booking WHERE user_id = ? ORDER BY preferred_date DESC");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    $error_message = "Error fetching bookings: " . htmlspecialchars($e->getMessage());
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Service - Booking Receipt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3a8a, #6b21a8);
            overflow-x: hidden;
        }
        .booking-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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
    <div class="container bg-white rounded-2xl shadow-2xl p-8 w-full max-w-2xl fade-in">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Booking Receipt</h1>
            <p class="text-lg text-gray-600 mt-2">Hello, <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'Guest'); ?>! Here’s your booking summary.</p>
        </div>

        <?php if ($current_booking): ?>
            <div class="booking-card bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Current Booking (ID: <?php echo htmlspecialchars($booking_id); ?>)</h2>
                <div class="space-y-3">
                    <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($current_booking['vehicle_name']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($current_booking['preferred_date']))); ?></p>
                    <p><strong>Services:</strong></p>
                    <ul class="space-y-2">
                        <?php foreach ($current_booking['services'] as $service): ?>
                            <li class="flex justify-between">
                                <span><?php echo htmlspecialchars($services_map[$service]['name'] ?? $service); ?></span>
                                <span class="text-blue-600">$<?php echo $services_map[$service]['price'] ?? 0; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="text-xl font-semibold text-gray-800 mt-4">Total: <span class="text-blue-600">$<?php echo $total_amount; ?></span></p>
                </div>
            </div>
        <?php else: ?>
            <p class="text-gray-600 mb-6">No current booking found.</p>
        <?php endif; ?>

        <h2 class="text-xl font-semibold text-gray-800 mb-4">All Your Bookings</h2>
        <?php if (!empty($bookings)): ?>
            <div class="space-y-4">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['book_id']); ?></p>
                        <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['vehicle_name']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($booking['preferred_date']))); ?></p>
                        <p><strong>Services:</strong></p>
                        <ul class="space-y-2">
                            <?php
                            $booking_total = 0;
                            foreach (explode(',', $booking['services']) as $service) {
                                $service = trim($service);
                                $booking_total += $services_map[$service]['price'] ?? 0;
                                ?>
                                <li class="flex justify-between">
                                    <span><?php echo htmlspecialchars($services_map[$service]['name'] ?? $service); ?></span>
                                    <span class="text-blue-600">$<?php echo $services_map[$service]['price'] ?? 0; ?></span>
                                </li>
                            <?php } ?>
                        </ul>
                        <p class="text-xl font-semibold text-gray-800 mt-4">Total: <span class="text-blue-600">$<?php echo $booking_total; ?></span></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-600 mb-6">No previous bookings found.</p>
        <?php endif; ?>

        <div class="flex justify-center gap-4 mt-6">
            <a href="/carservice/home.php" class="btn animate-gradient text-white px-6 py-3 rounded-lg font-medium hover:opacity-90 transition-opacity">Book Another Service</a>
            <a href="/carservice/login.php" class="btn bg-red-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-700 transition-colors">Logout</a>
        </div>

        <?php if (isset($error_message)): ?>
            <p class="error text-red-600 text-sm mt-4"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>