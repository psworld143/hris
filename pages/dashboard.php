<?php
$stats = getDashboardStats($pdo);
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Welcome to SEAIT HRIS - Human Resource Information System</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Employees -->
        <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Employees</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['total_employees']; ?></p>
                </div>
            </div>
        </div>

        <!-- Present Today -->
        <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Present Today</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['present_today']; ?></p>
                </div>
            </div>
        </div>

        <!-- Absent Today -->
        <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400">
                    <i class="fas fa-user-times text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Absent Today</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['absent_today']; ?></p>
                </div>
            </div>
        </div>

        <!-- Pending Leaves -->
        <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400">
                    <i class="fas fa-calendar-alt text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Leaves</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['pending_leaves']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activities -->
        <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Recent Activities</h2>
            <div class="space-y-4">
                <?php
                $stmt = $pdo->query("
                    SELECT 'New Employee' as type, CONCAT(e.first_name, ' ', e.last_name) as description, e.created_at as date
                    FROM employees e 
                    ORDER BY e.created_at DESC 
                    LIMIT 5
                ");
                $activities = $stmt->fetchAll();
                
                if ($activities): ?>
                    <?php foreach ($activities as $activity): ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $activity['type']; ?></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo $activity['description']; ?></p>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo formatDate($activity['date']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No recent activities</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-4">
                <a href="?page=employees&action=add" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg text-center transition-colors duration-200">
                    <i class="fas fa-user-plus text-2xl mb-2"></i>
                    <p class="text-sm font-medium">Add Employee</p>
                </a>
                <a href="?page=attendance&action=mark" class="bg-green-600 hover:bg-green-700 text-white p-4 rounded-lg text-center transition-colors duration-200">
                    <i class="fas fa-clock text-2xl mb-2"></i>
                    <p class="text-sm font-medium">Mark Attendance</p>
                </a>
                <a href="?page=leave&action=request" class="bg-yellow-600 hover:bg-yellow-700 text-white p-4 rounded-lg text-center transition-colors duration-200">
                    <i class="fas fa-calendar-plus text-2xl mb-2"></i>
                    <p class="text-sm font-medium">Request Leave</p>
                </a>
                <a href="?page=payroll&action=generate" class="bg-purple-600 hover:bg-purple-700 text-white p-4 rounded-lg text-center transition-colors duration-200">
                    <i class="fas fa-money-bill-wave text-2xl mb-2"></i>
                    <p class="text-sm font-medium">Generate Payroll</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Department Overview -->
    <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Department Overview</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employees</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Present Today</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Absent Today</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-dark-700">
                    <?php
                    $stmt = $pdo->query("
                        SELECT 
                            d.name as department_name,
                            COUNT(e.id) as total_employees,
                            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_today,
                            SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_today
                        FROM departments d
                        LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
                        LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = CURDATE()
                        GROUP BY d.id, d.name
                        ORDER BY total_employees DESC
                    ");
                    $departments = $stmt->fetchAll();
                    
                    foreach ($departments as $dept): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo $dept['department_name']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?php echo $dept['total_employees']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400">
                                <?php echo $dept['present_today']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 dark:text-red-400">
                                <?php echo $dept['absent_today']; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 