<?php
$reportType = isset($_GET['type']) ? $_GET['type'] : 'overview';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Reports</h1>
                <p class="text-gray-600 mt-2">HR analytics and reports</p>
            </div>
            <div class="flex space-x-2">
                <a href="?page=reports&type=overview" 
                   class="px-4 py-2 rounded-lg <?php echo $reportType === 'overview' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                    Overview
                </a>
                <a href="?page=reports&type=attendance" 
                   class="px-4 py-2 rounded-lg <?php echo $reportType === 'attendance' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                    Attendance
                </a>
                <a href="?page=reports&type=leave" 
                   class="px-4 py-2 rounded-lg <?php echo $reportType === 'leave' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                    Leave
                </a>
                <a href="?page=reports&type=payroll" 
                   class="px-4 py-2 rounded-lg <?php echo $reportType === 'payroll' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                    Payroll
                </a>
            </div>
        </div>
    </div>

    <?php if ($reportType === 'overview'): ?>
        <!-- Overview Report -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Employee Distribution by Department -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Employee Distribution by Department</h2>
                <div class="space-y-4">
                    <?php
                    $stmt = $pdo->query("
                        SELECT d.name, COUNT(e.id) as count
                        FROM departments d
                        LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
                        GROUP BY d.id, d.name
                        ORDER BY count DESC
                    ");
                    $deptStats = $stmt->fetchAll();
                    
                    foreach ($deptStats as $stat): ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700"><?php echo $stat['name']; ?></span>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <?php 
                                    $totalEmployees = array_sum(array_column($deptStats, 'count'));
                                    $percentage = $totalEmployees > 0 ? ($stat['count'] / $totalEmployees) * 100 : 0;
                                    ?>
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="text-sm text-gray-500"><?php echo $stat['count']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Monthly Attendance Trend -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Monthly Attendance Trend</h2>
                <div class="space-y-4">
                    <?php
                    $stmt = $pdo->query("
                        SELECT 
                            DATE_FORMAT(date, '%Y-%m') as month,
                            COUNT(*) as total_days,
                            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
                        FROM attendance 
                        WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                        GROUP BY DATE_FORMAT(date, '%Y-%m')
                        ORDER BY month DESC
                        LIMIT 6
                    ");
                    $attendanceTrend = $stmt->fetchAll();
                    
                    foreach ($attendanceTrend as $trend): ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">
                                <?php echo date('M Y', strtotime($trend['month'] . '-01')); ?>
                            </span>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-green-600"><?php echo $trend['present_days']; ?> present</span>
                                <span class="text-sm text-red-600"><?php echo $trend['absent_days']; ?> absent</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Activities</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- New Employees -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-900 mb-2">New Employees This Month</h3>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as count 
                        FROM employees 
                        WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?
                    ");
                    $stmt->execute([date('n'), date('Y')]);
                    $newEmployees = $stmt->fetch()['count'];
                    ?>
                    <div class="text-3xl font-bold text-blue-600"><?php echo $newEmployees; ?></div>
                </div>

                <!-- Leave Requests -->
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-yellow-900 mb-2">Pending Leave Requests</h3>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'");
                    $pendingLeaves = $stmt->fetch()['count'];
                    ?>
                    <div class="text-3xl font-bold text-yellow-600"><?php echo $pendingLeaves; ?></div>
                </div>

                <!-- Payroll Status -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-900 mb-2">This Month's Payroll</h3>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as count 
                        FROM payroll 
                        WHERE month = ? AND year = ?
                    ");
                    $stmt->execute([date('n'), date('Y')]);
                    $monthlyPayroll = $stmt->fetch()['count'];
                    ?>
                    <div class="text-3xl font-bold text-green-600"><?php echo $monthlyPayroll; ?></div>
                </div>
            </div>
        </div>

    <?php elseif ($reportType === 'attendance'): ?>
        <!-- Attendance Report -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Attendance Report</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Present Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absent Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Late Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $stmt = $pdo->query("
                            SELECT 
                                e.id,
                                e.employee_id,
                                e.first_name,
                                e.last_name,
                                d.name as department_name,
                                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                                COUNT(a.id) as total_days
                            FROM employees e
                            LEFT JOIN departments d ON e.department_id = d.id
                            LEFT JOIN attendance a ON e.id = a.employee_id 
                                AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                            WHERE e.status = 'active'
                            GROUP BY e.id, e.employee_id, e.first_name, e.last_name, d.name
                            ORDER BY e.last_name, e.first_name
                        ");
                        $attendanceReport = $stmt->fetchAll();
                        
                        foreach ($attendanceReport as $report): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $report['first_name'] . ' ' . $report['last_name']; ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo $report['employee_id']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $report['department_name']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                    <?php echo $report['present_days']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                    <?php echo $report['absent_days']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                    <?php echo $report['late_days']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php 
                                    $attendanceRate = $report['total_days'] > 0 ? 
                                        round(($report['present_days'] / $report['total_days']) * 100, 1) : 0;
                                    $colorClass = $attendanceRate >= 90 ? 'text-green-600' : 
                                        ($attendanceRate >= 80 ? 'text-yellow-600' : 'text-red-600');
                                    ?>
                                    <span class="<?php echo $colorClass; ?> font-medium"><?php echo $attendanceRate; ?>%</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($reportType === 'leave'): ?>
        <!-- Leave Report -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Leave Report</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allowed Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $stmt = $pdo->query("
                            SELECT 
                                e.id as employee_id,
                                e.employee_id as emp_id,
                                e.first_name,
                                e.last_name,
                                lt.name as leave_type_name,
                                lt.days_allowed,
                                COALESCE(SUM(DATEDIFF(lr.end_date, lr.start_date) + 1), 0) as used_days
                            FROM employees e
                            CROSS JOIN leave_types lt
                            LEFT JOIN leave_requests lr ON e.id = lr.employee_id 
                                AND lt.id = lr.leave_type_id 
                                AND lr.status = 'approved'
                            WHERE e.status = 'active'
                            GROUP BY e.id, e.employee_id, e.first_name, e.last_name, lt.id, lt.name, lt.days_allowed
                            ORDER BY e.last_name, e.first_name, lt.name
                        ");
                        $leaveReport = $stmt->fetchAll();
                        
                        foreach ($leaveReport as $report): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $report['first_name'] . ' ' . $report['last_name']; ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo $report['emp_id']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $report['leave_type_name']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $report['days_allowed']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $report['used_days']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php 
                                    $balance = $report['days_allowed'] - $report['used_days'];
                                    $colorClass = $balance > 0 ? 'text-green-600' : 'text-red-600';
                                    ?>
                                    <span class="<?php echo $colorClass; ?> font-medium"><?php echo $balance; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $statusClass = $balance > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                    $statusText = $balance > 0 ? 'Available' : 'Exhausted';
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($reportType === 'payroll'): ?>
        <!-- Payroll Report -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Payroll Report</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basic Salary</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allowances</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Salary</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $stmt = $pdo->query("
                            SELECT 
                                e.employee_id,
                                e.first_name,
                                e.last_name,
                                d.name as department_name,
                                e.salary as basic_salary,
                                COALESCE(p.allowances, 0) as allowances,
                                COALESCE(p.deductions, 0) as deductions,
                                COALESCE(p.net_salary, e.salary) as net_salary,
                                p.status
                            FROM employees e
                            LEFT JOIN departments d ON e.department_id = d.id
                            LEFT JOIN payroll p ON e.id = p.employee_id 
                                AND p.month = " . date('n') . " 
                                AND p.year = " . date('Y') . "
                            WHERE e.status = 'active'
                            ORDER BY e.last_name, e.first_name
                        ");
                        $payrollReport = $stmt->fetchAll();
                        
                        foreach ($payrollReport as $report): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $report['first_name'] . ' ' . $report['last_name']; ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo $report['employee_id']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $report['department_name']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo formatCurrency($report['basic_salary']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                    <?php echo formatCurrency($report['allowances']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                    <?php echo formatCurrency($report['deductions']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo formatCurrency($report['net_salary']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $statusClass = $report['status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                    $statusText = $report['status'] ? ucfirst($report['status']) : 'Not Generated';
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div> 