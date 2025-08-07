<?php
$page_title = 'Admin Panel';
require_once 'config/database.php';
include 'includes/header.php';

// Check if user is admin
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $username = sanitizeInput($_POST['username']);
                $email = sanitizeInput($_POST['email']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $first_name = sanitizeInput($_POST['first_name']);
                $last_name = sanitizeInput($_POST['last_name']);
                $role = sanitizeInput($_POST['role']);
                
                // Check if username or email already exists
                $query = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$username, $email]);
                
                if ($stmt->fetchColumn() > 0) {
                    $message = 'Username or email already exists.';
                    $message_type = 'danger';
                } else {
                    $query = "INSERT INTO users (username, email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    
                    if ($stmt->execute([$username, $email, $password, $first_name, $last_name, $role])) {
                        $message = 'User added successfully!';
                        $message_type = 'success';
                        logActivity($_SESSION['user_id'], 'Add User', "Added user: $username");
                    } else {
                        $message = 'Error adding user.';
                        $message_type = 'danger';
                    }
                }
                break;
                
            case 'edit_user':
                $id = (int)$_POST['id'];
                $username = sanitizeInput($_POST['username']);
                $email = sanitizeInput($_POST['email']);
                $first_name = sanitizeInput($_POST['first_name']);
                $last_name = sanitizeInput($_POST['last_name']);
                $role = sanitizeInput($_POST['role']);
                
                // Check if username or email already exists for other users
                $query = "SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$username, $email, $id]);
                
                if ($stmt->fetchColumn() > 0) {
                    $message = 'Username or email already exists.';
                    $message_type = 'danger';
                } else {
                    $query = "UPDATE users SET username=?, email=?, first_name=?, last_name=?, role=? WHERE id=?";
                    $stmt = $db->prepare($query);
                    
                    if ($stmt->execute([$username, $email, $first_name, $last_name, $role, $id])) {
                        $message = 'User updated successfully!';
                        $message_type = 'success';
                        logActivity($_SESSION['user_id'], 'Update User', "Updated user ID: $id");
                    } else {
                        $message = 'Error updating user.';
                        $message_type = 'danger';
                    }
                }
                break;
                
            case 'delete_user':
                $id = (int)$_POST['id'];
                
                // Don't allow deleting own account
                if ($id == $_SESSION['user_id']) {
                    $message = 'You cannot delete your own account.';
                    $message_type = 'danger';
                } else {
                    $query = "DELETE FROM users WHERE id=?";
                    $stmt = $db->prepare($query);
                    
                    if ($stmt->execute([$id])) {
                        $message = 'User deleted successfully!';
                        $message_type = 'success';
                        logActivity($_SESSION['user_id'], 'Delete User', "Deleted user ID: $id");
                    } else {
                        $message = 'Error deleting user.';
                        $message_type = 'danger';
                    }
                }
                break;
                
            case 'reset_password':
                $id = (int)$_POST['id'];
                $new_password = generatePassword(8);
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $query = "UPDATE users SET password=? WHERE id=?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$hashed_password, $id])) {
                    $message = "Password reset successfully! New password: <strong>$new_password</strong>";
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Reset Password', "Reset password for user ID: $id");
                } else {
                    $message = 'Error resetting password.';
                    $message_type = 'danger';
                }
                break;
        }
    }
}

// Get all users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get system statistics
$stats = [];

// Total users
$query = "SELECT COUNT(*) as total FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total leads
$query = "SELECT COUNT(*) as total FROM leads";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_leads'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total customers
$query = "SELECT COUNT(*) as total FROM customers";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total tasks
$query = "SELECT COUNT(*) as total FROM tasks";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_tasks'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total notes
$query = "SELECT COUNT(*) as total FROM notes";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_notes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-cog"></i> Admin Panel</h1>
        <p class="text-muted">System administration and user management.</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- System Statistics -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body text-center">
                <h4><?php echo $stats['total_users']; ?></h4>
                <p class="mb-0">Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body text-center">
                <h4><?php echo $stats['total_leads']; ?></h4>
                <p class="mb-0">Leads</p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body text-center">
                <h4><?php echo $stats['total_customers']; ?></h4>
                <p class="mb-0">Customers</p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body text-center">
                <h4><?php echo $stats['total_tasks']; ?></h4>
                <p class="mb-0">Tasks</p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-white bg-secondary">
            <div class="card-body text-center">
                <h4><?php echo $stats['total_notes']; ?></h4>
                <p class="mb-0">Notes</p>
            </div>
        </div>
    </div>
</div>

<!-- User Management -->
<div class="row mb-3">
    <div class="col-12">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus"></i> Add New User
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-users"></i> User Management</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['role'] == 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" 
                                            onclick="resetPassword(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deleteUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_user">

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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role *</label>
                        <select class="form-select" name="role" id="edit_role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_first_name').value = user.first_name;
    document.getElementById('edit_last_name').value = user.last_name;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;

    var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}

function deleteUser(id) {
    if (confirmDelete('Are you sure you want to delete this user? This action cannot be undone.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function resetPassword(id) {
    if (confirm('Are you sure you want to reset this user\'s password? A new random password will be generated.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
