<?php
$page_title = 'Leads Management';
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
                $first_name = sanitizeInput($_POST['first_name']);
                $last_name = sanitizeInput($_POST['last_name']);
                $email = sanitizeInput($_POST['email']);
                $phone = sanitizeInput($_POST['phone']);
                $company = sanitizeInput($_POST['company']);
                $status = sanitizeInput($_POST['status']);
                $source = sanitizeInput($_POST['source']);
                
                $query = "INSERT INTO leads (first_name, last_name, email, phone, company, status, source, assigned_to) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$first_name, $last_name, $email, $phone, $company, $status, $source, $_SESSION['user_id']])) {
                    $message = 'Lead added successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Add Lead', "Added lead: $first_name $last_name");
                } else {
                    $message = 'Error adding lead.';
                    $message_type = 'danger';
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $first_name = sanitizeInput($_POST['first_name']);
                $last_name = sanitizeInput($_POST['last_name']);
                $email = sanitizeInput($_POST['email']);
                $phone = sanitizeInput($_POST['phone']);
                $company = sanitizeInput($_POST['company']);
                $status = sanitizeInput($_POST['status']);
                $source = sanitizeInput($_POST['source']);
                
                $query = "UPDATE leads SET first_name=?, last_name=?, email=?, phone=?, company=?, status=?, source=? WHERE id=?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$first_name, $last_name, $email, $phone, $company, $status, $source, $id])) {
                    $message = 'Lead updated successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Update Lead', "Updated lead ID: $id");
                } else {
                    $message = 'Error updating lead.';
                    $message_type = 'danger';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $query = "DELETE FROM leads WHERE id=?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$id])) {
                    $message = 'Lead deleted successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Delete Lead', "Deleted lead ID: $id");
                } else {
                    $message = 'Error deleting lead.';
                    $message_type = 'danger';
                }
                break;
                
            case 'convert':
                $id = (int)$_POST['id'];
                
                // Get lead data
                $query = "SELECT * FROM leads WHERE id=?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
                $lead = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($lead) {
                    // Insert into customers table
                    $query = "INSERT INTO customers (lead_id, first_name, last_name, email, phone, company, assigned_to) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    
                    if ($stmt->execute([$id, $lead['first_name'], $lead['last_name'], $lead['email'], 
                                      $lead['phone'], $lead['company'], $lead['assigned_to']])) {
                        // Update lead status to converted
                        $query = "UPDATE leads SET status='converted' WHERE id=?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$id]);
                        
                        $message = 'Lead converted to customer successfully!';
                        $message_type = 'success';
                        logActivity($_SESSION['user_id'], 'Convert Lead', "Converted lead ID: $id to customer");
                    } else {
                        $message = 'Error converting lead to customer.';
                        $message_type = 'danger';
                    }
                }
                break;
        }
    }
}

// Get all leads
$query = "SELECT l.*, u.first_name as assigned_first_name, u.last_name as assigned_last_name 
          FROM leads l 
          LEFT JOIN users u ON l.assigned_to = u.id 
          ORDER BY l.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get users for assignment dropdown
$query = "SELECT id, first_name, last_name FROM users ORDER BY first_name, last_name";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-user-plus"></i> Leads Management</h1>
        <p class="text-muted">Manage your sales leads and track their progress through the sales funnel.</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Add Lead Button -->
<div class="row mb-3">
    <div class="col-12">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
            <i class="fas fa-plus"></i> Add New Lead
        </button>
    </div>
</div>

<!-- Leads Table -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-list"></i> All Leads</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Source</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td><?php echo $lead['id']; ?></td>
                            <td><?php echo htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($lead['email']); ?></td>
                            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                            <td><?php echo htmlspecialchars($lead['company']); ?></td>
                            <td>
                                <span class="badge <?php echo getStatusBadgeClass($lead['status']); ?>">
                                    <?php echo ucfirst($lead['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($lead['source']); ?></td>
                            <td><?php echo htmlspecialchars($lead['assigned_first_name'] . ' ' . $lead['assigned_last_name']); ?></td>
                            <td><?php echo formatDate($lead['created_at']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="editLead(<?php echo htmlspecialchars(json_encode($lead)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($lead['status'] != 'converted'): ?>
                                        <button type="button" class="btn btn-outline-success" 
                                                onclick="convertLead(<?php echo $lead['id']; ?>)">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteLead(<?php echo $lead['id']; ?>)">
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

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">

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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="company" class="form-label">Company</label>
                        <input type="text" class="form-control" name="company">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="source" class="form-label">Source</label>
                            <select class="form-select" name="source">
                                <option value="website">Website</option>
                                <option value="referral">Referral</option>
                                <option value="cold_call">Cold Call</option>
                                <option value="email">Email Campaign</option>
                                <option value="social_media">Social Media</option>
                                <option value="trade_show">Trade Show</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Lead Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editLeadForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" id="edit_phone">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_company" class="form-label">Company</label>
                        <input type="text" class="form-control" name="company" id="edit_company">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="converted">Converted</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_source" class="form-label">Source</label>
                            <select class="form-select" name="source" id="edit_source">
                                <option value="website">Website</option>
                                <option value="referral">Referral</option>
                                <option value="cold_call">Cold Call</option>
                                <option value="email">Email Campaign</option>
                                <option value="social_media">Social Media</option>
                                <option value="trade_show">Trade Show</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editLead(lead) {
    document.getElementById('edit_id').value = lead.id;
    document.getElementById('edit_first_name').value = lead.first_name;
    document.getElementById('edit_last_name').value = lead.last_name;
    document.getElementById('edit_email').value = lead.email || '';
    document.getElementById('edit_phone').value = lead.phone || '';
    document.getElementById('edit_company').value = lead.company || '';
    document.getElementById('edit_status').value = lead.status;
    document.getElementById('edit_source').value = lead.source || '';

    var modal = new bootstrap.Modal(document.getElementById('editLeadModal'));
    modal.show();
}

function deleteLead(id) {
    if (confirmDelete('Are you sure you want to delete this lead?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function convertLead(id) {
    if (confirm('Are you sure you want to convert this lead to a customer?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="convert"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
