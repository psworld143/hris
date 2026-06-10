<?php
// Helper functions for the HRIS system

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Generate employee ID
function generateEmployeeId() {
    return 'EMP' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Format time
function formatTime($time) {
    return date('h:i A', strtotime($time));
}

// Format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Get employee name by ID
function getEmployeeName($pdo, $employeeId) {
    $stmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM employees WHERE id = ?");
    $stmt->execute([$employeeId]);
    $result = $stmt->fetch();
    return $result ? $result['name'] : 'Unknown';
}

// Get department name by ID
function getDepartmentName($pdo, $departmentId) {
    $stmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt->execute([$departmentId]);
    $result = $stmt->fetch();
    return $result ? $result['name'] : 'Unknown';
}

// Get position title by ID
function getPositionTitle($pdo, $positionId) {
    $stmt = $pdo->prepare("SELECT title FROM positions WHERE id = ?");
    $stmt->execute([$positionId]);
    $result = $stmt->fetch();
    return $result ? $result['title'] : 'Unknown';
}

// Check if user has permission
function hasPermission($role, $requiredRole) {
    $roleHierarchy = [
        'admin' => 3,
        'hr' => 2,
        'employee' => 1
    ];
    
    return $roleHierarchy[$role] >= $roleHierarchy[$requiredRole];
}

// Get current month and year
function getCurrentMonthYear() {
    return [
        'month' => date('n'),
        'year' => date('Y')
    ];
}

// Calculate working days between two dates
function getWorkingDays($startDate, $endDate) {
    $begin = strtotime($startDate);
    $end = strtotime($endDate);
    
    if ($begin > $end) {
        return 0;
    }
    
    $workingDays = 0;
    $weekends = 0;
    
    for($i = $begin; $i <= $end; $i = $i + 86400) {
        $day = date("N", $i);
        if($day == 6 || $day == 7) {
            $weekends++;
        }
    }
    
    $workingDays = (($end - $begin) / 86400) + 1 - $weekends;
    return $workingDays;
}

// Get attendance status
function getAttendanceStatus($timeIn, $expectedTime = '08:00:00') {
    if (!$timeIn) return 'absent';
    
    $timeInObj = new DateTime($timeIn);
    $expectedObj = new DateTime($expectedTime);
    
    if ($timeInObj > $expectedObj) {
        return 'late';
    }
    
    return 'present';
}

// Calculate salary deductions
function calculateDeductions($basicSalary, $absences = 0, $lateDays = 0) {
    $dailyRate = $basicSalary / 22; // Assuming 22 working days per month
    $absenceDeduction = $absences * $dailyRate;
    $lateDeduction = $lateDays * ($dailyRate * 0.25); // 25% deduction for late
    
    return $absenceDeduction + $lateDeduction;
}

// Get dashboard statistics
function getDashboardStats($pdo) {
    $stats = [];
    
    // Total employees
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees WHERE status = 'active'");
    $stats['total_employees'] = $stmt->fetch()['total'];
    
    // Total departments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM departments");
    $stats['total_departments'] = $stmt->fetch()['total'];
    
    // Present today
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM attendance WHERE date = CURDATE() AND status = 'present'");
    $stmt->execute();
    $stats['present_today'] = $stmt->fetch()['total'];
    
    // Absent today
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM attendance WHERE date = CURDATE() AND status = 'absent'");
    $stmt->execute();
    $stats['absent_today'] = $stmt->fetch()['total'];
    
    // Pending leave requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM leave_requests WHERE status = 'pending'");
    $stats['pending_leaves'] = $stmt->fetch()['total'];
    
    // This month's payroll
    $currentMonth = date('n');
    $currentYear = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM payroll WHERE month = ? AND year = ?");
    $stmt->execute([$currentMonth, $currentYear]);
    $stats['monthly_payroll'] = $stmt->fetch()['total'];
    
    return $stats;
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number
function isValidPhone($phone) {
    return preg_match('/^[0-9+\-\s\(\)]+$/', $phone);
}

// Generate random password
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

// Log activity
function logActivity($pdo, $userId, $action, $details = '') {
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$userId, $action, $details]);
}

// Get user role
function getUserRole($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result ? $result['role'] : 'employee';
}

// Check if employee exists
function employeeExists($pdo, $employeeId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE employee_id = ?");
    $stmt->execute([$employeeId]);
    return $stmt->fetchColumn() > 0;
}

// Get employee details
function getEmployeeDetails($pdo, $employeeId) {
    $stmt = $pdo->prepare("
        SELECT e.*, d.name as department_name, p.title as position_title 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        LEFT JOIN positions p ON e.position_id = p.id 
        WHERE e.id = ?
    ");
    $stmt->execute([$employeeId]);
    return $stmt->fetch();
}

// Get leave balance
function getLeaveBalance($pdo, $employeeId, $leaveTypeId) {
    $stmt = $pdo->prepare("
        SELECT lt.days_allowed, COALESCE(SUM(DATEDIFF(lr.end_date, lr.start_date) + 1), 0) as used_days
        FROM leave_types lt
        LEFT JOIN leave_requests lr ON lt.id = lr.leave_type_id AND lr.employee_id = ? AND lr.status = 'approved'
        WHERE lt.id = ?
        GROUP BY lt.id
    ");
    $stmt->execute([$employeeId, $leaveTypeId]);
    $result = $stmt->fetch();
    
    if ($result) {
        return $result['days_allowed'] - $result['used_days'];
    }
    
    return 0;
}
?> 