<?php
// User Creation Tool
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $role = $_POST['role'];
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $message = 'All fields are required.';
        $message_type = 'danger';
    } else {
        // Check if username or email already exists
        $query = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetchColumn() > 0) {
            $message = 'Username or email already exists.';
            $message_type = 'danger';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $query = "INSERT INTO users (username, email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $role])) {
                $message = "User '$username' created successfully! Password: $password";
                $message_type = 'success';
            } else {
                $message = 'Error creating user.';
                $message_type = 'danger';
            }
        }
    }
}

// Get all users
$query = "SELECT id, username, email, first_name, last_name, role, created_at FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>User Creation Tool</h1>
        <p class="text-muted">Create new users for the CRM system</p>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Create New User</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" required minlength="6">
                                <div class="form-text">Minimum 6 characters</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">Role *</label>
                                <select class="form-select" name="role" required>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Create User</button>
                            <a href="login.php" class="btn btn-secondary">Go to Login</a>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Existing Users (<?php echo count($users); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($users) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Name</th>
                                            <th>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $user['role'] == 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No users found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick SQL Commands</h5>
                    </div>
                    <div class="card-body">
                        <h6>Add User via SQL (phpMyAdmin):</h6>
                        <code style="display: block; background: #f8f9fa; padding: 10px; margin: 10px 0;">
-- Add regular user (password: admin123)<br>
INSERT INTO users (username, email, password, first_name, last_name, role) <br>
VALUES ('new_user', 'user@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'First', 'Last', 'user');
                        </code>
                        
                        <h6>Add Admin via SQL:</h6>
                        <code style="display: block; background: #f8f9fa; padding: 10px; margin: 10px 0;">
-- Add admin user (password: admin123)<br>
INSERT INTO users (username, email, password, first_name, last_name, role) <br>
VALUES ('new_admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin');
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
