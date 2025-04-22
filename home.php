<?php
session_start();
// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_email'])) {
    header("Location: /carservice/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Service - Premium Auto Care</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3a8a, #6b21a8);
            overflow-x: hidden;
        }
        .service-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .checkbox-custom {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #6b7280;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .checkbox-custom:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2.5-2.5a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3E%3C/svg%3E");
        }
        .description {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .description.active {
            max-height: 200px;
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="container bg-white rounded-2xl shadow-2xl p-8 w-full max-w-2xl fade-in">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800">Premium Auto Care</h1>
            <p class="text-lg text-gray-600 mt-2">Welcome, <?php echo htmlspecialchars($_SESSION['user_email']); ?>! Choose from our premium services below.</p>
        </div>

        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Our Services</h2>
        <form action="/carservice/book.php" method="post" class="space-y-4">
            <div class="space-y-4">
                <div class="service-card bg-gray-50 rounded-lg p-4 flex items-start gap-4 border border-gray-200">
                    <input type="checkbox" name="services[]" value="oil_change" id="oil_change" class="checkbox-custom">
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <span class="service-name font-medium text-gray-800 cursor-pointer" data-target="oil_change_desc">Oil Change</span>
                            <span class="text-blue-600 font-medium">$30</span>
                        </div>
                        <p class="description text-gray-600 text-sm mt-2" id="oil_change_desc">Keep your engine running smoothly with a full oil change using premium synthetic oil.</p>
                    </div>
                </div>
                <div class="service-card bg-gray-50 rounded-lg p-4 flex items-start gap-4 border border-gray-200">
                    <input type="checkbox" name="services[]" value="brake_repair" id="brake_repair" class="checkbox-custom">
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <span class="service-name font-medium text-gray-800 cursor-pointer" data-target="brake_repair_desc">Brake Repair</span>
                            <span class="text-blue-600 font-medium">$100</span>
                        </div>
                        <p class="description text-gray-600 text-sm mt-2" id="brake_repair_desc">Ensure safety with expert brake inspections, pad replacements, and system diagnostics.</p>
                    </div>
                </div>
                <div class="service-card bg-gray-50 rounded-lg p-4 flex items-start gap-4 border border-gray-200">
                    <input type="checkbox" name="services[]" value="tyre_service" id="tyre_service" class="checkbox-custom">
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <span class="service-name font-medium text-gray-800 cursor-pointer" data-target="tyre_service_desc">Tyre Service</span>
                            <span class="text-blue-600 font-medium">$50</span>
                        </div>
                        <p class="description text-gray-600 text-sm mt-2" id="tyre_service_desc">Includes tyre rotation, balancing, alignment, and pressure checks for optimal performance.</p>
                    </div>
                </div>
                <div class="service-card bg-gray-50 rounded-lg p-4 flex items-start gap-4 border border-gray-200">
                    <input type="checkbox" name="services[]" value="car_wash" id="car_wash" class="checkbox-custom">
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <span class="service-name font-medium text-gray-800 cursor-pointer" data-target="car_wash_desc">Car Wash</span>
                            <span class="text-blue-600 font-medium">$15</span>
                        </div>
                        <p class="description text-gray-600 text-sm mt-2" id="car_wash_desc">Get your car sparkling clean with our eco-friendly, thorough washing and waxing service.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-center gap-4 mt-8">
                <button type="submit" class="btn animate-gradient text-white px-6 py-3 rounded-lg font-medium hover:opacity-90 transition-opacity">Book Selected Services</button>
                <a href="/carservice/login.php" class="btn bg-red-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-700 transition-colors">Logout</a>
            </div>
        </form>
    </div>

    <script>
        // Toggle description visibility with smooth animation
        document.querySelectorAll('.service-name').forEach(name => {
            name.addEventListener('click', () => {
                const targetId = name.getAttribute('data-target');
                const description = document.getElementById(targetId);
                description.classList.toggle('active');
            });
        });

        // Add subtle animation on checkbox interaction
        document.querySelectorAll('.checkbox-custom').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const card = checkbox.closest('.service-card');
                card.classList.add('scale-105');
                setTimeout(() => card.classList.remove('scale-105'), 200);
            });
        });
    </script>
</body>
</html>