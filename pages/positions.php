<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $title = sanitize($_POST['title']);
        $departmentId = $_POST['department_id'];
        $salaryRange = sanitize($_POST['salary_range']);
        $description = sanitize($_POST['description']);
        
        $stmt = $pdo->prepare("INSERT INTO positions (title, department_id, salary_range, description) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$title, $departmentId, $salaryRange, $description])) {
            $message = "Position added successfully!";
            $action = 'list';
        } else {
            $message = "Error adding position.";
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $title = sanitize($_POST['title']);
        $departmentId = $_POST['department_id'];
        $salaryRange = sanitize($_POST['salary_range']);
        $description = sanitize($_POST['description']);
        
        $stmt = $pdo->prepare("UPDATE positions SET title = ?, department_id = ?, salary_range = ?, description = ? WHERE id = ?");
        if ($stmt->execute([$title, $departmentId, $salaryRange, $description, $id])) {
            $message = "Position updated successfully!";
            $action = 'list';
        } else {
            $message = "Error updating position.";
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM positions WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Position deleted successfully!";
    } else {
        $message = "Error deleting position.";
    }
    $action = 'list';
}

// Get departments for dropdown
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Positions</h1>
                <p class="text-gray-600 mt-2">Manage job positions and roles</p>
            </div>
            <a href="?page=positions&action=add" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Add Position
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <!-- Position List -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Position List</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary Range</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employees</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $stmt = $pdo->query("
                                SELECT p.*, d.name as department_name, COUNT(e.id) as employee_count 
                                FROM positions p 
                                LEFT JOIN departments d ON p.department_id = d.id 
                                LEFT JOIN employees e ON p.id = e.position_id AND e.status = 'active'
                                GROUP BY p.id 
                                ORDER BY p.title
                            ");
                            $positions = $stmt->fetchAll();
                            
                            foreach ($positions as $pos): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo $pos['title']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $pos['department_name']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $pos['salary_range']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $pos['employee_count']; ?> employees
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?page=positions&action=edit&id=<?php echo $pos['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <a href="?page=positions&delete=<?php echo $pos['id']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Are you sure you want to delete this position?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($action === 'add'): ?>
        <!-- Add Position Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Add New Position</h2>
            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Position Title</label>
                        <input type="text" name="title" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select name="department_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Salary Range</label>
                        <input type="text" name="salary_range" placeholder="e.g., 50,000 - 70,000" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="?page=positions" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Add Position</button>
                </div>
            </form>
        </div>
    <?php elseif ($action === 'edit' && isset($_GET['id'])): ?>
        <!-- Edit Position Form -->
        <?php
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM positions WHERE id = ?");
        $stmt->execute([$id]);
        $position = $stmt->fetch();
        
        if (!$position) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Position not found.</div>';
            return;
        }
        ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Edit Position</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $position['id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Position Title</label>
                        <input type="text" name="title" value="<?php echo $position['title']; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select name="department_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo $position['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo $dept['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Salary Range</label>
                        <input type="text" name="salary_range" value="<?php echo $position['salary_range']; ?>" 
                               placeholder="e.g., 50,000 - 70,000" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo $position['description']; ?></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="?page=positions" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Update Position</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div> 