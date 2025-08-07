<?php
$page_title = 'Task Management';
require_once 'config/database.php';
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $due_date = $_POST['due_date'] ? $_POST['due_date'] : null;
                $priority = sanitizeInput($_POST['priority']);
                $status = sanitizeInput($_POST['status']);
                $related_to_type = $_POST['related_to_type'] ? sanitizeInput($_POST['related_to_type']) : null;
                $related_to_id = $_POST['related_to_id'] ? (int)$_POST['related_to_id'] : null;
                
                $query = "INSERT INTO tasks (title, description, due_date, priority, status, assigned_to, related_to_type, related_to_id, created_by) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$title, $description, $due_date, $priority, $status, $_SESSION['user_id'], $related_to_type, $related_to_id, $_SESSION['user_id']])) {
                    $message = 'Task added successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Add Task', "Added task: $title");
                } else {
                    $message = 'Error adding task.';
                    $message_type = 'danger';
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $due_date = $_POST['due_date'] ? $_POST['due_date'] : null;
                $priority = sanitizeInput($_POST['priority']);
                $status = sanitizeInput($_POST['status']);
                $related_to_type = $_POST['related_to_type'] ? sanitizeInput($_POST['related_to_type']) : null;
                $related_to_id = $_POST['related_to_id'] ? (int)$_POST['related_to_id'] : null;
                
                $query = "UPDATE tasks SET title=?, description=?, due_date=?, priority=?, status=?, related_to_type=?, related_to_id=? WHERE id=?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$title, $description, $due_date, $priority, $status, $related_to_type, $related_to_id, $id])) {
                    $message = 'Task updated successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Update Task', "Updated task ID: $id");
                } else {
                    $message = 'Error updating task.';
                    $message_type = 'danger';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $query = "DELETE FROM tasks WHERE id=?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$id])) {
                    $message = 'Task deleted successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Delete Task', "Deleted task ID: $id");
                } else {
                    $message = 'Error deleting task.';
                    $message_type = 'danger';
                }
                break;
                
            case 'complete':
                $id = (int)$_POST['id'];
                $query = "UPDATE tasks SET status='completed' WHERE id=?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$id])) {
                    $message = 'Task marked as completed!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Complete Task', "Completed task ID: $id");
                } else {
                    $message = 'Error updating task status.';
                    $message_type = 'danger';
                }
                break;
        }
    }
}

// Get all tasks with related information
$query = "SELECT t.*, 
                 u.first_name as assigned_first_name, u.last_name as assigned_last_name,
                 c.first_name as creator_first_name, c.last_name as creator_last_name,
                 CASE 
                    WHEN t.related_to_type = 'lead' THEN CONCAT(l.first_name, ' ', l.last_name, ' (Lead)')
                    WHEN t.related_to_type = 'customer' THEN CONCAT(cust.first_name, ' ', cust.last_name, ' (Customer)')
                    ELSE 'General Task'
                 END as related_to_name
          FROM tasks t
          LEFT JOIN users u ON t.assigned_to = u.id
          LEFT JOIN users c ON t.created_by = c.id
          LEFT JOIN leads l ON t.related_to_type = 'lead' AND t.related_to_id = l.id
          LEFT JOIN customers cust ON t.related_to_type = 'customer' AND t.related_to_id = cust.id
          ORDER BY 
            CASE WHEN t.status = 'pending' THEN 1 
                 WHEN t.status = 'in_progress' THEN 2 
                 ELSE 3 END,
            t.due_date ASC, 
            CASE WHEN t.priority = 'high' THEN 1 
                 WHEN t.priority = 'medium' THEN 2 
                 ELSE 3 END";
$stmt = $db->prepare($query);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get leads for dropdown
$query = "SELECT id, first_name, last_name, company FROM leads WHERE status != 'converted' ORDER BY first_name, last_name";
$stmt = $db->prepare($query);
$stmt->execute();
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get customers for dropdown
$query = "SELECT id, first_name, last_name, company FROM customers ORDER BY first_name, last_name";
$stmt = $db->prepare($query);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-tasks"></i> Task Management</h1>
        <p class="text-muted">Manage your tasks and follow-ups to stay organized and productive.</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Add Task Button -->
<div class="row mb-3">
    <div class="col-12">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="fas fa-plus"></i> Add New Task
        </button>
    </div>
</div>

<!-- Tasks Table -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-list"></i> All Tasks</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Related To</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr class="<?php echo $task['due_date'] && $task['due_date'] < date('Y-m-d') && $task['status'] != 'completed' ? 'table-warning' : ''; ?>">
                            <td><?php echo $task['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                                <?php if ($task['description']): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($task['description'], 0, 50)) . (strlen($task['description']) > 50 ? '...' : ''); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($task['related_to_name']); ?></td>
                            <td>
                                <?php if ($task['due_date']): ?>
                                    <?php echo formatDate($task['due_date']); ?>
                                    <?php if ($task['due_date'] < date('Y-m-d') && $task['status'] != 'completed'): ?>
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Overdue</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">No due date</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo getPriorityBadgeClass($task['priority']); ?>">
                                    <?php echo ucfirst($task['priority']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo getStatusBadgeClass($task['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($task['assigned_first_name'] . ' ' . $task['assigned_last_name']); ?></td>
                            <td><?php echo htmlspecialchars($task['creator_first_name'] . ' ' . $task['creator_last_name']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($task['status'] != 'completed'): ?>
                                        <button type="button" class="btn btn-outline-success" 
                                                onclick="completeTask(<?php echo $task['id']; ?>)" title="Mark as Complete">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="editTask(<?php echo htmlspecialchars(json_encode($task)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteTask(<?php echo $task['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="pending" selected>Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="related_to_type" class="form-label">Related To</label>
                            <select class="form-select" name="related_to_type" id="related_to_type" onchange="updateRelatedOptions()">
                                <option value="">General Task</option>
                                <option value="lead">Lead</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="related_to_id" class="form-label">Select Contact</label>
                            <select class="form-select" name="related_to_id" id="related_to_id" disabled>
                                <option value="">Select...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="edit_due_date">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_priority" class="form-label">Priority</label>
                            <select class="form-select" name="priority" id="edit_priority">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_related_to_type" class="form-label">Related To</label>
                            <select class="form-select" name="related_to_type" id="edit_related_to_type" onchange="updateEditRelatedOptions()">
                                <option value="">General Task</option>
                                <option value="lead">Lead</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_related_to_id" class="form-label">Select Contact</label>
                            <select class="form-select" name="related_to_id" id="edit_related_to_id">
                                <option value="">Select...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data for dropdowns
const leads = <?php echo json_encode($leads); ?>;
const customers = <?php echo json_encode($customers); ?>;

function updateRelatedOptions() {
    const type = document.getElementById('related_to_type').value;
    const select = document.getElementById('related_to_id');

    select.innerHTML = '<option value="">Select...</option>';

    if (type === 'lead') {
        select.disabled = false;
        leads.forEach(lead => {
            select.innerHTML += `<option value="${lead.id}">${lead.first_name} ${lead.last_name} - ${lead.company || 'No Company'}</option>`;
        });
    } else if (type === 'customer') {
        select.disabled = false;
        customers.forEach(customer => {
            select.innerHTML += `<option value="${customer.id}">${customer.first_name} ${customer.last_name} - ${customer.company || 'No Company'}</option>`;
        });
    } else {
        select.disabled = true;
    }
}

function updateEditRelatedOptions() {
    const type = document.getElementById('edit_related_to_type').value;
    const select = document.getElementById('edit_related_to_id');

    select.innerHTML = '<option value="">Select...</option>';

    if (type === 'lead') {
        select.disabled = false;
        leads.forEach(lead => {
            select.innerHTML += `<option value="${lead.id}">${lead.first_name} ${lead.last_name} - ${lead.company || 'No Company'}</option>`;
        });
    } else if (type === 'customer') {
        select.disabled = false;
        customers.forEach(customer => {
            select.innerHTML += `<option value="${customer.id}">${customer.first_name} ${customer.last_name} - ${customer.company || 'No Company'}</option>`;
        });
    } else {
        select.disabled = true;
    }
}

function editTask(task) {
    document.getElementById('edit_id').value = task.id;
    document.getElementById('edit_title').value = task.title;
    document.getElementById('edit_description').value = task.description || '';
    document.getElementById('edit_due_date').value = task.due_date || '';
    document.getElementById('edit_priority').value = task.priority;
    document.getElementById('edit_status').value = task.status;
    document.getElementById('edit_related_to_type').value = task.related_to_type || '';

    updateEditRelatedOptions();

    if (task.related_to_id) {
        setTimeout(() => {
            document.getElementById('edit_related_to_id').value = task.related_to_id;
        }, 100);
    }

    var modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
    modal.show();
}

function deleteTask(id) {
    if (confirmDelete('Are you sure you want to delete this task?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function completeTask(id) {
    if (confirm('Mark this task as completed?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="complete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
