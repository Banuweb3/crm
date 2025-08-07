<?php
$page_title = 'Notes Management';
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
                $content = sanitizeInput($_POST['content']);
                $related_to_type = $_POST['related_to_type'] ? sanitizeInput($_POST['related_to_type']) : null;
                $related_to_id = $_POST['related_to_id'] ? (int)$_POST['related_to_id'] : null;
                
                $query = "INSERT INTO notes (title, content, related_to_type, related_to_id, created_by) 
                         VALUES (?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$title, $content, $related_to_type, $related_to_id, $_SESSION['user_id']])) {
                    $message = 'Note added successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Add Note', "Added note: $title");
                } else {
                    $message = 'Error adding note.';
                    $message_type = 'danger';
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $title = sanitizeInput($_POST['title']);
                $content = sanitizeInput($_POST['content']);
                $related_to_type = $_POST['related_to_type'] ? sanitizeInput($_POST['related_to_type']) : null;
                $related_to_id = $_POST['related_to_id'] ? (int)$_POST['related_to_id'] : null;
                
                $query = "UPDATE notes SET title=?, content=?, related_to_type=?, related_to_id=? WHERE id=? AND created_by=?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$title, $content, $related_to_type, $related_to_id, $id, $_SESSION['user_id']])) {
                    $message = 'Note updated successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Update Note', "Updated note ID: $id");
                } else {
                    $message = 'Error updating note.';
                    $message_type = 'danger';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $query = "DELETE FROM notes WHERE id=? AND created_by=?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$id, $_SESSION['user_id']])) {
                    $message = 'Note deleted successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Delete Note', "Deleted note ID: $id");
                } else {
                    $message = 'Error deleting note.';
                    $message_type = 'danger';
                }
                break;
        }
    }
}

// Get all notes with related information
$query = "SELECT n.*, 
                 u.first_name as creator_first_name, u.last_name as creator_last_name,
                 CASE 
                    WHEN n.related_to_type = 'lead' THEN CONCAT(l.first_name, ' ', l.last_name, ' (Lead)')
                    WHEN n.related_to_type = 'customer' THEN CONCAT(c.first_name, ' ', c.last_name, ' (Customer)')
                    WHEN n.related_to_type = 'task' THEN CONCAT('Task: ', t.title)
                    ELSE 'General Note'
                 END as related_to_name
          FROM notes n
          LEFT JOIN users u ON n.created_by = u.id
          LEFT JOIN leads l ON n.related_to_type = 'lead' AND n.related_to_id = l.id
          LEFT JOIN customers c ON n.related_to_type = 'customer' AND n.related_to_id = c.id
          LEFT JOIN tasks t ON n.related_to_type = 'task' AND n.related_to_id = t.id
          ORDER BY n.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get leads for dropdown
$query = "SELECT id, first_name, last_name, company FROM leads ORDER BY first_name, last_name";
$stmt = $db->prepare($query);
$stmt->execute();
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get customers for dropdown
$query = "SELECT id, first_name, last_name, company FROM customers ORDER BY first_name, last_name";
$stmt = $db->prepare($query);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get tasks for dropdown
$query = "SELECT id, title FROM tasks WHERE status != 'completed' ORDER BY title";
$stmt = $db->prepare($query);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-sticky-note"></i> Notes Management</h1>
        <p class="text-muted">Keep track of important information and communications.</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Add Note Button -->
<div class="row mb-3">
    <div class="col-12">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
            <i class="fas fa-plus"></i> Add New Note
        </button>
    </div>
</div>

<!-- Notes Grid -->
<div class="row">
    <?php foreach ($notes as $note): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($note['title'] ?: 'Untitled Note'); ?></h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="editNote(<?php echo htmlspecialchars(json_encode($note)); ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </a></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteNote(<?php echo $note['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($note['content'], 0, 200))); ?><?php echo strlen($note['content']) > 200 ? '...' : ''; ?></p>
                    
                    <?php if ($note['related_to_name'] != 'General Note'): ?>
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-link"></i> <?php echo htmlspecialchars($note['related_to_name']); ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($note['creator_first_name'] . ' ' . $note['creator_last_name']); ?>
                        <br>
                        <i class="fas fa-clock"></i> <?php echo formatDateTime($note['created_at']); ?>
                    </small>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($notes)): ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No notes found</h4>
                <p class="text-muted">Start by adding your first note!</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" placeholder="Optional title for the note">
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Content *</label>
                        <textarea class="form-control" name="content" rows="6" required placeholder="Enter your note content here..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="related_to_type" class="form-label">Related To</label>
                            <select class="form-select" name="related_to_type" id="related_to_type" onchange="updateNoteRelatedOptions()">
                                <option value="">General Note</option>
                                <option value="lead">Lead</option>
                                <option value="customer">Customer</option>
                                <option value="task">Task</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="related_to_id" class="form-label">Select Item</label>
                            <select class="form-select" name="related_to_id" id="related_to_id" disabled>
                                <option value="">Select...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Note Modal -->
<div class="modal fade" id="editNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="edit_title" placeholder="Optional title for the note">
                    </div>

                    <div class="mb-3">
                        <label for="edit_content" class="form-label">Content *</label>
                        <textarea class="form-control" name="content" id="edit_content" rows="6" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_related_to_type" class="form-label">Related To</label>
                            <select class="form-select" name="related_to_type" id="edit_related_to_type" onchange="updateEditNoteRelatedOptions()">
                                <option value="">General Note</option>
                                <option value="lead">Lead</option>
                                <option value="customer">Customer</option>
                                <option value="task">Task</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_related_to_id" class="form-label">Select Item</label>
                            <select class="form-select" name="related_to_id" id="edit_related_to_id">
                                <option value="">Select...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data for dropdowns
const noteLeads = <?php echo json_encode($leads); ?>;
const noteCustomers = <?php echo json_encode($customers); ?>;
const noteTasks = <?php echo json_encode($tasks); ?>;

function updateNoteRelatedOptions() {
    const type = document.getElementById('related_to_type').value;
    const select = document.getElementById('related_to_id');

    select.innerHTML = '<option value="">Select...</option>';

    if (type === 'lead') {
        select.disabled = false;
        noteLeads.forEach(lead => {
            select.innerHTML += `<option value="${lead.id}">${lead.first_name} ${lead.last_name} - ${lead.company || 'No Company'}</option>`;
        });
    } else if (type === 'customer') {
        select.disabled = false;
        noteCustomers.forEach(customer => {
            select.innerHTML += `<option value="${customer.id}">${customer.first_name} ${customer.last_name} - ${customer.company || 'No Company'}</option>`;
        });
    } else if (type === 'task') {
        select.disabled = false;
        noteTasks.forEach(task => {
            select.innerHTML += `<option value="${task.id}">${task.title}</option>`;
        });
    } else {
        select.disabled = true;
    }
}

function updateEditNoteRelatedOptions() {
    const type = document.getElementById('edit_related_to_type').value;
    const select = document.getElementById('edit_related_to_id');

    select.innerHTML = '<option value="">Select...</option>';

    if (type === 'lead') {
        select.disabled = false;
        noteLeads.forEach(lead => {
            select.innerHTML += `<option value="${lead.id}">${lead.first_name} ${lead.last_name} - ${lead.company || 'No Company'}</option>`;
        });
    } else if (type === 'customer') {
        select.disabled = false;
        noteCustomers.forEach(customer => {
            select.innerHTML += `<option value="${customer.id}">${customer.first_name} ${customer.last_name} - ${customer.company || 'No Company'}</option>`;
        });
    } else if (type === 'task') {
        select.disabled = false;
        noteTasks.forEach(task => {
            select.innerHTML += `<option value="${task.id}">${task.title}</option>`;
        });
    } else {
        select.disabled = true;
    }
}

function editNote(note) {
    document.getElementById('edit_id').value = note.id;
    document.getElementById('edit_title').value = note.title || '';
    document.getElementById('edit_content').value = note.content;
    document.getElementById('edit_related_to_type').value = note.related_to_type || '';

    updateEditNoteRelatedOptions();

    if (note.related_to_id) {
        setTimeout(() => {
            document.getElementById('edit_related_to_id').value = note.related_to_id;
        }, 100);
    }

    var modal = new bootstrap.Modal(document.getElementById('editNoteModal'));
    modal.show();
}

function deleteNote(id) {
    if (confirmDelete('Are you sure you want to delete this note?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
