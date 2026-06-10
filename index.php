<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit();
}

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'employees', 'departments', 'positions', 'attendance', 'leave', 'payroll', 'reports', 'settings'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff" id="theme-color">
    <title>SEAIT HRIS - Human Resource Information System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="assets/js/dark-mode.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <script>
        // Dark mode configuration
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-dark-900 h-full" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" 
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" 
      :class="{ 'dark': darkMode }">
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Navigation -->
        <nav class="bg-blue-600 dark:bg-dark-800 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center space-x-4">
                        <div class="text-2xl font-bold">SEAIT HRIS</div>
                        <div class="text-sm opacity-75">South East Asian Institute of Technology, Inc.</div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        
                        <!-- Dark Mode Toggle -->
                        <button @click="darkMode = !darkMode" 
                                class="p-2 rounded-lg bg-blue-500 hover:bg-blue-400 transition-colors duration-200"
                                :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                            <i class="fas fa-sun" x-show="darkMode" x-cloak></i>
                            <i class="fas fa-moon" x-show="!darkMode"></i>
                        </button>
                        
                        <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm">Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar and Main Content -->
        <div class="flex h-screen">
            <!-- Sidebar -->
            <div class="w-64 bg-white dark:bg-dark-800 shadow-lg">
                <div class="p-4">
                    <nav class="space-y-2">
                        <a href="?page=dashboard" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-dark-700 <?php echo $page === 'dashboard' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-tachometer-alt w-5"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="?page=employees" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-dark-700 <?php echo $page === 'employees' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-users w-5"></i>
                            <span>Employees</span>
                        </a>
                        <a href="?page=departments" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-dark-700 <?php echo $page === 'departments' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-building w-5"></i>
                            <span>Departments</span>
                        </a>
                        <a href="?page=positions" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-dark-700 <?php echo $page === 'positions' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-briefcase w-5"></i>
                            <span>Positions</span>
                        </a>
                        <a href="?page=attendance" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-dark-700 <?php echo $page === 'attendance' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-clock w-5"></i>
                            <span>Attendance</span>
                        </a>
                        <a href="?page=leave" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-dark-700 <?php echo $page === 'leave' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-calendar-alt w-5"></i>
                            <span>Leave Management</span>
                        </a>
                        <a href="?page=payroll" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-dark-700 <?php echo $page === 'payroll' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-money-bill-wave w-5"></i>
                            <span>Payroll</span>
                        </a>
                        <a href="?page=reports" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-dark-700 <?php echo $page === 'reports' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-chart-bar w-5"></i>
                            <span>Reports</span>
                        </a>
                        <a href="?page=settings" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-blue-50 dark:hover:bg-dark-700 <?php echo $page === 'settings' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-cog w-5"></i>
                            <span>Settings</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-8 overflow-y-auto">
                <?php include "pages/{$page}.php"; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Login Form -->
        <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-dark-900">
            <div class="max-w-md w-full bg-white dark:bg-dark-800 rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">SEAIT HRIS</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Human Resource Information System</p>
                </div>
                
                <!-- Dark Mode Toggle for Login Page -->
                <div class="flex justify-end mb-4">
                    <button @click="darkMode = !darkMode" 
                            class="p-2 rounded-lg bg-gray-200 dark:bg-dark-700 hover:bg-gray-300 dark:hover:bg-dark-600 transition-colors duration-200"
                            :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                        <i class="fas fa-sun text-yellow-500" x-show="darkMode" x-cloak></i>
                        <i class="fas fa-moon text-gray-600" x-show="!darkMode"></i>
                    </button>
                </div>
                
                <form action="login.php" method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="username">
                            Username
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-dark-700 dark:border-dark-600 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500" 
                               id="username" name="username" type="text" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="password">
                            Password
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-white dark:bg-dark-700 dark:border-dark-600 mb-3 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-blue-500" 
                               id="password" name="password" type="password" required>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" 
                                type="submit">
                            Sign In
                        </button>
                    </div>
                </form>
                
                <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                    <p>Default credentials:</p>
                    <p>Username: admin</p>
                    <p>Password: admin123</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</body>
</html> 