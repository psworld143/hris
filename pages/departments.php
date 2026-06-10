<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        
        $stmt = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
        if ($stmt->execute([$name, $description])) {
            $message = "Department added successfully!";
            $action = 'list';
        } else {
            $message = "Error adding department.";
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        
        $stmt = $pdo->prepare("UPDATE departments SET name = ?, description = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $id])) {
            $message = "Department updated successfully!";
            $action = 'list';
        } else {
            $message = "Error updating department.";
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Department deleted successfully!";
    } else {
        $message = "Error deleting department.";
    }
    $action = 'list';
}
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Departments</h1>
                <p class="text-gray-600 mt-2">Manage organizational departments</p>
            </div>
            <a href="?page=departments&action=add" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Add Department
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <!-- Department List -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Department List</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employees</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $stmt = $pdo->query("
                                SELECT d.*, COUNT(e.id) as employee_count 
                                FROM departments d 
                                LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
                                GROUP BY d.id 
                                ORDER BY d.name
                            ");
                            $departments = $stmt->fetchAll();
                            
                            foreach ($departments as $dept): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo $dept['name']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo $dept['description']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $dept['employee_count']; ?> employees
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?page=departments&action=edit&id=<?php echo $dept['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <a href="?page=departments&delete=<?php echo $dept['id']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Are you sure you want to delete this department?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($action === 'add'): ?>
        <!-- Add Department Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Add New Department</h2>
            <form method="POST">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department Name</label>
                        <input type="text" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="?page=departments" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Add Department</button>
                </div>
            </form>
        </div>
    <?php elseif ($action === 'edit' && isset($_GET['id'])): ?>
        <!-- Edit Department Form -->
        <?php
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        $department = $stmt->fetch();
        
        if (!$department) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Department not found.</div>';
            return;
        }
        ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Edit Department</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $department['id']; ?>">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department Name</label>
                        <input type="text" name="name" value="<?php echo $department['name']; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo $department['description']; ?></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="?page=departments" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Update Department</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div> 