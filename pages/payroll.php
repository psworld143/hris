<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Handle payroll generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'generate') {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $employeeId = $_POST['employee_id'];
    
    // Get employee details
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch();
    
    if ($employee) {
        $basicSalary = $employee['salary'];
        
        // Calculate attendance deductions
        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absences,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
            FROM attendance 
            WHERE employee_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
        ");
        $stmt->execute([$employeeId, $month, $year]);
        $attendance = $stmt->fetch();
        
        $absences = $attendance['absences'] ?? 0;
        $lateDays = $attendance['late_days'] ?? 0;
        
        // Calculate deductions
        $dailyRate = $basicSalary / 22; // Assuming 22 working days per month
        $absenceDeduction = $absences * $dailyRate;
        $lateDeduction = $lateDays * ($dailyRate * 0.25); // 25% deduction for late
        $totalDeductions = $absenceDeduction + $lateDeduction;
        
        // Calculate allowances (example: 10% of basic salary)
        $allowances = $basicSalary * 0.10;
        
        // Calculate net salary
        $netSalary = $basicSalary + $allowances - $totalDeductions;
        
        // Check if payroll already exists
        $stmt = $pdo->prepare("SELECT id FROM payroll WHERE employee_id = ? AND month = ? AND year = ?");
        $stmt->execute([$employeeId, $month, $year]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing payroll
            $stmt = $pdo->prepare("
                UPDATE payroll 
                SET basic_salary = ?, allowances = ?, deductions = ?, net_salary = ?
                WHERE employee_id = ? AND month = ? AND year = ?
            ");
            if ($stmt->execute([$basicSalary, $allowances, $totalDeductions, $netSalary, $employeeId, $month, $year])) {
                $message = "Payroll updated successfully!";
            } else {
                $message = "Error updating payroll.";
            }
        } else {
            // Insert new payroll
            $stmt = $pdo->prepare("
                INSERT INTO payroll (employee_id, month, year, basic_salary, allowances, deductions, net_salary)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$employeeId, $month, $year, $basicSalary, $allowances, $totalDeductions, $netSalary])) {
                $message = "Payroll generated successfully!";
            } else {
                $message = "Error generating payroll.";
            }
        }
    }
}

// Handle payment
if (isset($_GET['pay'])) {
    $id = $_GET['pay'];
    $stmt = $pdo->prepare("UPDATE payroll SET status = 'paid', payment_date = CURDATE() WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Payment marked as completed!";
    } else {
        $message = "Error updating payment status.";
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
                <h1 class="text-3xl font-bold text-gray-900">Payroll</h1>
                <p class="text-gray-600 mt-2">Manage employee payroll and salary processing</p>
            </div>
            <a href="?page=payroll&action=generate" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-calculator mr-2"></i>Generate Payroll
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'generate'): ?>
        <!-- Generate Payroll Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Generate Payroll</h2>
            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                        <select name="month" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Month</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == date('n') ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                        <select name="year" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Year</option>
                            <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == date('Y') ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="?page=payroll" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Generate Payroll</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Payroll List -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Payroll Records</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basic Salary</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allowances</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Salary</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $stmt = $pdo->query("
                                SELECT p.*, e.employee_id, e.first_name, e.last_name 
                                FROM payroll p 
                                JOIN employees e ON p.employee_id = e.id 
                                ORDER BY p.year DESC, p.month DESC, e.last_name
                            ");
                            $payrolls = $stmt->fetchAll();
                            
                            if ($payrolls): ?>
                                <?php foreach ($payrolls as $payroll): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo $payroll['first_name'] . ' ' . $payroll['last_name']; ?>
                                            </div>
                                            <div class="text-sm text-gray-500"><?php echo $payroll['employee_id']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('F Y', mktime(0, 0, 0, $payroll['month'], 1, $payroll['year'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo formatCurrency($payroll['basic_salary']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                            <?php echo formatCurrency($payroll['allowances']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                            <?php echo formatCurrency($payroll['deductions']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo formatCurrency($payroll['net_salary']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $payroll['status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo ucfirst($payroll['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php if ($payroll['status'] === 'pending'): ?>
                                                <a href="?page=payroll&pay=<?php echo $payroll['id']; ?>" 
                                                   class="text-green-600 hover:text-green-900">Mark as Paid</a>
                                            <?php else: ?>
                                                <span class="text-gray-500">Paid on <?php echo formatDate($payroll['payment_date']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No payroll records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payroll Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Payroll Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <?php
                $currentMonth = date('n');
                $currentYear = date('Y');
                
                // Total payroll for current month
                $stmt = $pdo->prepare("
                    SELECT 
                        COUNT(*) as total_employees,
                        SUM(basic_salary) as total_basic,
                        SUM(allowances) as total_allowances,
                        SUM(deductions) as total_deductions,
                        SUM(net_salary) as total_net
                    FROM payroll 
                    WHERE month = ? AND year = ?
                ");
                $stmt->execute([$currentMonth, $currentYear]);
                $summary = $stmt->fetch();
                ?>
                
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-blue-600">Employees</div>
                    <div class="text-2xl font-bold text-blue-900"><?php echo $summary['total_employees'] ?? 0; ?></div>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-green-600">Total Basic Salary</div>
                    <div class="text-2xl font-bold text-green-900"><?php echo formatCurrency($summary['total_basic'] ?? 0); ?></div>
                </div>
                
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-yellow-600">Total Allowances</div>
                    <div class="text-2xl font-bold text-yellow-900"><?php echo formatCurrency($summary['total_allowances'] ?? 0); ?></div>
                </div>
                
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-red-600">Total Deductions</div>
                    <div class="text-2xl font-bold text-red-900"><?php echo formatCurrency($summary['total_deductions'] ?? 0); ?></div>
                </div>
            </div>
            
            <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                <div class="text-lg font-semibold text-gray-900">
                    Net Payroll for <?php echo date('F Y'); ?>: 
                    <span class="text-blue-600"><?php echo formatCurrency($summary['total_net'] ?? 0); ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div> 