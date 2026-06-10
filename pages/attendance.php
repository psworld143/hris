<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'mark') {
    $employeeId = $_POST['employee_id'];
    $date = $_POST['date'];
    $timeIn = $_POST['time_in'];
    $timeOut = $_POST['time_out'];
    $status = $_POST['status'];
    
    // Check if attendance already exists for this employee and date
    $stmt = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
    $stmt->execute([$employeeId, $date]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing attendance
        $stmt = $pdo->prepare("UPDATE attendance SET time_in = ?, time_out = ?, status = ? WHERE employee_id = ? AND date = ?");
        if ($stmt->execute([$timeIn, $timeOut, $status, $employeeId, $date])) {
            $message = "Attendance updated successfully!";
        } else {
            $message = "Error updating attendance.";
        }
    } else {
        // Insert new attendance
        $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, time_in, time_out, status) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$employeeId, $date, $timeIn, $timeOut, $status])) {
            $message = "Attendance marked successfully!";
        } else {
            $message = "Error marking attendance.";
        }
    }
}

// Get employees for dropdown
$employees = $pdo->query("SELECT id, employee_id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY last_name, first_name")->fetchAll();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Attendance</h1>
                <p class="text-gray-600 mt-2">Manage employee attendance and time tracking</p>
            </div>
            <a href="?page=attendance&action=mark" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-clock mr-2"></i>Mark Attendance
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'mark'): ?>
        <!-- Mark Attendance Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Mark Attendance</h2>
            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                        <select name="employee_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>">
                                    <?php echo $employee['employee_id'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Time In</label>
                        <input type="time" name="time_in" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Time Out</label>
                        <input type="time" name="time_out" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="half_day">Half Day</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="?page=attendance" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Mark Attendance</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Attendance List -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Today's Attendance</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT a.*, e.employee_id, e.first_name, e.last_name 
                                FROM attendance a 
                                JOIN employees e ON a.employee_id = e.id 
                                WHERE a.date = CURDATE() 
                                ORDER BY e.last_name, e.first_name
                            ");
                            $stmt->execute();
                            $attendance = $stmt->fetchAll();
                            
                            if ($attendance): ?>
                                <?php foreach ($attendance as $record): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo $record['first_name'] . ' ' . $record['last_name']; ?>
                                            </div>
                                            <div class="text-sm text-gray-500"><?php echo $record['employee_id']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo formatDate($record['date']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $record['time_in'] ? formatTime($record['time_in']) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $record['time_out'] ? formatTime($record['time_out']) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $record['status'] === 'present' ? 'bg-green-100 text-green-800' : 
                                                    ($record['status'] === 'late' ? 'bg-yellow-100 text-yellow-800' : 
                                                    ($record['status'] === 'half_day' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')); ?>">
                                                <?php echo ucfirst($record['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="?page=attendance&action=edit&id=<?php echo $record['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No attendance records for today</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Weekly Attendance Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">This Week's Attendance Summary</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Present</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Late</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Half Day</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT 
                                e.id,
                                e.employee_id,
                                e.first_name,
                                e.last_name,
                                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                                SUM(CASE WHEN a.status = 'half_day' THEN 1 ELSE 0 END) as half_days
                            FROM employees e
                            LEFT JOIN attendance a ON e.id = a.employee_id 
                                AND a.date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
                                AND a.date <= CURDATE()
                            WHERE e.status = 'active'
                            GROUP BY e.id, e.employee_id, e.first_name, e.last_name
                            ORDER BY e.last_name, e.first_name
                        ");
                        $stmt->execute();
                        $weeklySummary = $stmt->fetchAll();
                        
                        foreach ($weeklySummary as $summary): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $summary['first_name'] . ' ' . $summary['last_name']; ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo $summary['employee_id']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                    <?php echo $summary['present_days']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                    <?php echo $summary['absent_days']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                    <?php echo $summary['late_days']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600">
                                    <?php echo $summary['half_days']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div> 