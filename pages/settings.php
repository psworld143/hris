<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'general';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add_user') {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashedPassword, $role])) {
            $message = "User added successfully!";
        } else {
            $message = "Error adding user.";
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (password_verify($currentPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                    $message = "Password changed successfully!";
                } else {
                    $message = "Error changing password.";
                }
            } else {
                $message = "New passwords do not match.";
            }
        } else {
            $message = "Current password is incorrect.";
        }
    }
}

// Get users for management
$users = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
                <p class="text-gray-600 mt-2">System configuration and user management</p>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Settings Navigation -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6">
                <a href="?page=settings&action=general" 
                   class="py-4 px-1 border-b-2 font-medium text-sm <?php echo $action === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                    General
                </a>
                <a href="?page=settings&action=users" 
                   class="py-4 px-1 border-b-2 font-medium text-sm <?php echo $action === 'users' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                    Users
                </a>
                <a href="?page=settings&action=change_password" 
                   class="py-4 px-1 border-b-2 font-medium text-sm <?php echo $action === 'change_password' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                    Change Password
                </a>
            </nav>
        </div>

        <div class="p-6">
            <?php if ($action === 'general'): ?>
                <!-- General Settings -->
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">System Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-900 mb-2">System Details</h3>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <div><strong>PHP Version:</strong> <?php echo phpversion(); ?></div>
                                    <div><strong>Database:</strong> MySQL</div>
                                    <div><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></div>
                                    <div><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></div>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-900 mb-2">Database Statistics</h3>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <?php
                                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
                                    $employeeCount = $stmt->fetch()['count'];
                                    
                                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM departments");
                                    $deptCount = $stmt->fetch()['count'];
                                    
                                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                                    $userCount = $stmt->fetch()['count'];
                                    ?>
                                    <div><strong>Active Employees:</strong> <?php echo $employeeCount; ?></div>
                                    <div><strong>Departments:</strong> <?php echo $deptCount; ?></div>
                                    <div><strong>System Users:</strong> <?php echo $userCount; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Application Settings</h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <h3 class="font-medium text-gray-900">Auto-generate Employee IDs</h3>
                                    <p class="text-sm text-gray-500">Automatically generate unique employee IDs when adding new employees</p>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm text-green-600 font-medium">Enabled</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <h3 class="font-medium text-gray-900">Attendance Tracking</h3>
                                    <p class="text-sm text-gray-500">Track employee attendance and calculate deductions</p>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm text-green-600 font-medium">Enabled</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <h3 class="font-medium text-gray-900">Leave Management</h3>
                                    <p class="text-sm text-gray-500">Manage employee leave requests and approvals</p>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm text-green-600 font-medium">Enabled</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($action === 'users'): ?>
                <!-- User Management -->
                <div class="space-y-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-900">User Management</h2>
                        <a href="?page=settings&action=add_user" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>Add User
                        </a>
                    </div>

                    <?php if ($action === 'add_user'): ?>
                        <!-- Add User Form -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add New User</h3>
                            <form method="POST">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                        <input type="text" name="username" required 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="email" name="email" required 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                        <input type="password" name="password" required 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                        <select name="role" required 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">Select Role</option>
                                            <option value="admin">Admin</option>
                                            <option value="hr">HR</option>
                                            <option value="employee">Employee</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end space-x-3">
                                    <a href="?page=settings&action=users" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</a>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Add User</button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- User List -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $user['username']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $user['email']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php echo $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 
                                                        ($user['role'] === 'hr' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo formatDate($user['created_at']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <a href="?page=settings&action=edit_user&id=<?php echo $user['id']; ?>" 
                                                       class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                                    <a href="?page=settings&action=delete_user&id=<?php echo $user['id']; ?>" 
                                                       class="text-red-600 hover:text-red-900"
                                                       onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                                <?php else: ?>
                                                    <span class="text-gray-500">Current User</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($action === 'change_password'): ?>
                <!-- Change Password -->
                <div class="max-w-md">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Change Password</h2>
                    <form method="POST">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                <input type="password" name="current_password" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                <input type="password" name="new_password" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                <input type="password" name="confirm_password" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 