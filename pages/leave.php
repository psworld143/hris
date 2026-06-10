<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'request') {
        $employeeId = $_POST['employee_id'];
        $leaveTypeId = $_POST['leave_type_id'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $reason = sanitize($_POST['reason']);
        
        $stmt = $pdo->prepare("
            INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, reason)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$employeeId, $leaveTypeId, $startDate, $endDate, $reason])) {
            $message = "Leave request submitted successfully!";
            $action = 'list';
        } else {
            $message = "Error submitting leave request.";
        }
    } elseif ($action === 'approve' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $approvedBy = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("UPDATE leave_requests SET status = ?, approved_by = ? WHERE id = ?");
        if ($stmt->execute([$status, $approvedBy, $id])) {
            $message = "Leave request " . $status . " successfully!";
            $action = 'list';
        } else {
            $message = "Error updating leave request.";
        }
    }
}

// Get employees and leave types for forms
$employees = $pdo->query("SELECT id, employee_id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY last_name, first_name")->fetchAll();
$leaveTypes = $pdo->query("SELECT * FROM leave_types ORDER BY name")->fetchAll();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Leave Management</h1>
                <p class="text-gray-600 mt-2">Manage employee leave requests and approvals</p>
            </div>
            <a href="?page=leave&action=request" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-calendar-plus mr-2"></i>Request Leave
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'request'): ?>
        <!-- Leave Request Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Request Leave</h2>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
                        <select name="leave_type_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Leave Type</option>
                            <?php foreach ($leaveTypes as $type): ?>
                                <option value="<?php echo $type['id']; ?>">
                                    <?php echo $type['name'] . ' (' . $type['days_allowed'] . ' days)'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" name="start_date" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" name="end_date" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                        <textarea name="reason" rows="4" required 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="?page=leave" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Submit Request</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Leave Requests List -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Leave Requests</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $stmt = $pdo->query("
                                SELECT lr.*, e.employee_id, e.first_name, e.last_name, lt.name as leave_type_name
                                FROM leave_requests lr 
                                JOIN employees e ON lr.employee_id = e.id 
                                JOIN leave_types lt ON lr.leave_type_id = lt.id 
                                ORDER BY lr.created_at DESC
                            ");
                            $leaveRequests = $stmt->fetchAll();
                            
                            foreach ($leaveRequests as $request): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo $request['first_name'] . ' ' . $request['last_name']; ?>
                                        </div>
                                        <div class="text-sm text-gray-500"><?php echo $request['employee_id']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $request['leave_type_name']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo formatDate($request['start_date']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo formatDate($request['end_date']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo getWorkingDays($request['start_date'], $request['end_date']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $request['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                ($request['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <button onclick="approveLeave(<?php echo $request['id']; ?>, 'approved')" 
                                                    class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                            <button onclick="approveLeave(<?php echo $request['id']; ?>, 'rejected')" 
                                                    class="text-red-600 hover:text-red-900">Reject</button>
                                        <?php else: ?>
                                            <span class="text-gray-500"><?php echo ucfirst($request['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Leave Balance Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Leave Balance Summary</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allowed Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
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
                                lt.id as leave_type_id,
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
                        $leaveBalances = $stmt->fetchAll();
                        
                        foreach ($leaveBalances as $balance): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $balance['first_name'] . ' ' . $balance['last_name']; ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo $balance['emp_id']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $balance['leave_type_name']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $balance['days_allowed']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $balance['used_days']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php 
                                    $remaining = $balance['days_allowed'] - $balance['used_days'];
                                    $colorClass = $remaining > 0 ? 'text-green-600' : 'text-red-600';
                                    ?>
                                    <span class="<?php echo $colorClass; ?> font-medium">
                                        <?php echo $remaining; ?>
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

<script>
function approveLeave(id, status) {
    if (confirm('Are you sure you want to ' + status + ' this leave request?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="status" value="${status}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script> 