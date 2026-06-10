<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'seait_hris');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
function createTables($pdo) {
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        role ENUM('admin', 'hr', 'employee') DEFAULT 'employee',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Departments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Positions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS positions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        department_id INT,
        salary_range VARCHAR(50),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
    )");

    // Employees table
    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id VARCHAR(20) UNIQUE NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE,
        phone VARCHAR(20),
        address TEXT,
        date_of_birth DATE,
        hire_date DATE,
        position_id INT,
        department_id INT,
        salary DECIMAL(10,2),
        status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
    )");

    // Attendance table
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT,
        date DATE,
        time_in TIME,
        time_out TIME,
        status ENUM('present', 'absent', 'late', 'half_day') DEFAULT 'present',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
    )");

    // Leave types table
    $pdo->exec("CREATE TABLE IF NOT EXISTS leave_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        days_allowed INT DEFAULT 0,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Leave requests table
    $pdo->exec("CREATE TABLE IF NOT EXISTS leave_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT,
        leave_type_id INT,
        start_date DATE,
        end_date DATE,
        reason TEXT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        approved_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE,
        FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
    )");

    // Payroll table
    $pdo->exec("CREATE TABLE IF NOT EXISTS payroll (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT,
        month INT,
        year INT,
        basic_salary DECIMAL(10,2),
        allowances DECIMAL(10,2),
        deductions DECIMAL(10,2),
        net_salary DECIMAL(10,2),
        status ENUM('pending', 'paid') DEFAULT 'pending',
        payment_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
    )");

    // Insert default data
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (username, password, email, role) VALUES ('admin', '$hashedPassword', 'admin@seait.edu.ph', 'admin')");
    }

    // Insert default departments
    $defaultDepartments = [
        ['Human Resources', 'Manages employee relations and HR policies'],
        ['Information Technology', 'Handles IT infrastructure and systems'],
        ['Finance', 'Manages financial operations and accounting'],
        ['Academic Affairs', 'Oversees academic programs and faculty'],
        ['Student Services', 'Provides student support and services']
    ];

    foreach ($defaultDepartments as $dept) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE name = ?");
        $stmt->execute([$dept[0]]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
            $stmt->execute($dept);
        }
    }

    // Insert default positions
    $defaultPositions = [
        ['HR Manager', 1, '50,000 - 70,000', 'Manages HR operations and policies'],
        ['IT Manager', 2, '60,000 - 80,000', 'Oversees IT infrastructure and systems'],
        ['Finance Manager', 3, '55,000 - 75,000', 'Manages financial operations'],
        ['Academic Dean', 4, '70,000 - 90,000', 'Oversees academic programs'],
        ['Student Services Coordinator', 5, '35,000 - 45,000', 'Coordinates student services']
    ];

    foreach ($defaultPositions as $pos) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM positions WHERE title = ?");
        $stmt->execute([$pos[0]]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO positions (title, department_id, salary_range, description) VALUES (?, ?, ?, ?)");
            $stmt->execute($pos);
        }
    }

    // Insert default leave types
    $defaultLeaveTypes = [
        ['Vacation Leave', 15, 'Annual vacation leave'],
        ['Sick Leave', 10, 'Medical and health-related leave'],
        ['Maternity Leave', 105, 'Maternity leave for female employees'],
        ['Paternity Leave', 7, 'Paternity leave for male employees'],
        ['Bereavement Leave', 5, 'Leave for family bereavement']
    ];

    foreach ($defaultLeaveTypes as $leave) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM leave_types WHERE name = ?");
        $stmt->execute([$leave[0]]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO leave_types (name, days_allowed, description) VALUES (?, ?, ?)");
            $stmt->execute($leave);
        }
    }
}

// Initialize database tables
createTables($pdo);
?> 