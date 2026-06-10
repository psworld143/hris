<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $employeeId = generateEmployeeId();
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $dateOfBirth = $_POST['date_of_birth'];
        $hireDate = $_POST['hire_date'];
        $positionId = $_POST['position_id'];
        $departmentId = $_POST['department_id'];
        $salary = $_POST['salary'];
        
        $stmt = $pdo->prepare("
            INSERT INTO employees (employee_id, first_name, last_name, email, phone, address, date_of_birth, hire_date, position_id, department_id, salary)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$employeeId, $firstName, $lastName, $email, $phone, $address, $dateOfBirth, $hireDate, $positionId, $departmentId, $salary])) {
            $message = "Employee added successfully!";
            $action = 'list';
        } else {
            $message = "Error adding employee.";
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $dateOfBirth = $_POST['date_of_birth'];
        $hireDate = $_POST['hire_date'];
        $positionId = $_POST['position_id'];
        $departmentId = $_POST['department_id'];
        $salary = $_POST['salary'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("
            UPDATE employees 
            SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, date_of_birth = ?, hire_date = ?, position_id = ?, department_id = ?, salary = ?, status = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([$firstName, $lastName, $email, $phone, $address, $dateOfBirth, $hireDate, $positionId, $departmentId, $salary, $status, $id])) {
            $message = "Employee updated successfully!";
            $action = 'list';
        } else {
            $message = "Error updating employee.";
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Employee deleted successfully!";
    } else {
        $message = "Error deleting employee.";
    }
    $action = 'list';
}

// Get departments and positions for forms
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
$positions = $pdo->query("SELECT * FROM positions ORDER BY title")->fetchAll();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Employees</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Manage employee information</p>
            </div>
            <a href="?page=employees&action=add" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>Add Employee
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <!-- Employee List -->
        <div class="bg-white dark:bg-dark-800 rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Employee List</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                        <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employee ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Salary</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-dark-700">
                            <?php
                            $stmt = $pdo->query("
                                SELECT e.*, d.name as department_name, p.title as position_title 
                                FROM employees e 
                                LEFT JOIN departments d ON e.department_id = d.id 
                                LEFT JOIN positions p ON e.position_id = p.id 
                                ORDER BY e.last_name, e.first_name
                            ");
                            $employees = $stmt->fetchAll();
                            
                            foreach ($employees as $employee): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo $employee['employee_id']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo $employee['email']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo $employee['department_name']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo $employee['position_title']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo formatCurrency($employee['salary']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $employee['status'] === 'active' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 
                                                ($employee['status'] === 'inactive' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'); ?>">
                                            <?php echo ucfirst($employee['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?page=employees&action=edit&id=<?php echo $employee['id']; ?>" 
                                           class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-3">Edit</a>
                                        <a href="?page=employees&delete=<?php echo $employee['id']; ?>" 
                                           class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                           onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($action === 'add'): ?>
        <!-- Add Employee Form -->
        <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Add New Employee</h2>
            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                        <input type="text" name="first_name" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                        <input type="text" name="last_name" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                        <input type="text" name="phone" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
                        <textarea name="address" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hire Date</label>
                        <input type="date" name="hire_date" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                        <select name="department_id" required 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Position</label>
                        <select name="position_id" required 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?php echo $pos['id']; ?>"><?php echo $pos['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Salary</label>
                        <input type="number" name="salary" step="0.01" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="?page=employees" class="bg-gray-300 dark:bg-dark-600 hover:bg-gray-400 dark:hover:bg-dark-500 text-gray-800 dark:text-white px-4 py-2 rounded-lg transition-colors duration-200">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">Add Employee</button>
                </div>
            </form>
        </div>
    <?php elseif ($action === 'edit' && isset($_GET['id'])): ?>
        <!-- Edit Employee Form -->
        <?php
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        $employee = $stmt->fetch();
        
        if (!$employee) {
            echo '<div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">Employee not found.</div>';
            return;
        }
        ?>
        <div class="bg-white dark:bg-dark-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Edit Employee</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Employee ID</label>
                        <input type="text" value="<?php echo $employee['employee_id']; ?>" disabled 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md bg-gray-100 dark:bg-dark-600 dark:text-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <select name="status" required 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                            <option value="active" <?php echo $employee['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $employee['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="terminated" <?php echo $employee['status'] === 'terminated' ? 'selected' : ''; ?>>Terminated</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                        <input type="text" name="first_name" value="<?php echo $employee['first_name']; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo $employee['last_name']; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo $employee['email']; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                        <input type="text" name="phone" value="<?php echo $employee['phone']; ?>" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
                        <textarea name="address" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white"><?php echo $employee['address']; ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?php echo $employee['date_of_birth']; ?>" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hire Date</label>
                        <input type="date" name="hire_date" value="<?php echo $employee['hire_date']; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                        <select name="department_id" required 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo $employee['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo $dept['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Position</label>
                        <select name="position_id" required 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?php echo $pos['id']; ?>" <?php echo $employee['position_id'] == $pos['id'] ? 'selected' : ''; ?>>
                                    <?php echo $pos['title']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Salary</label>
                        <input type="number" name="salary" step="0.01" value="<?php echo $employee['salary']; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-dark-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-dark-700 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="?page=employees" class="bg-gray-300 dark:bg-dark-600 hover:bg-gray-400 dark:hover:bg-dark-500 text-gray-800 dark:text-white px-4 py-2 rounded-lg transition-colors duration-200">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">Update Employee</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div> 