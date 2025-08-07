<?php
$page_title = 'Customer Management';
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
                $address = sanitizeInput($_POST['address']);
                $city = sanitizeInput($_POST['city']);
                $state = sanitizeInput($_POST['state']);
                $zip_code = sanitizeInput($_POST['zip_code']);
                $country = sanitizeInput($_POST['country']);
                
                $query = "INSERT INTO customers (first_name, last_name, email, phone, company, address, city, state, zip_code, country, assigned_to) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$first_name, $last_name, $email, $phone, $company, $address, $city, $state, $zip_code, $country, $_SESSION['user_id']])) {
                    $message = 'Customer added successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Add Customer', "Added customer: $first_name $last_name");
                } else {
                    $message = 'Error adding customer.';
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
                $address = sanitizeInput($_POST['address']);
                $city = sanitizeInput($_POST['city']);
                $state = sanitizeInput($_POST['state']);
                $zip_code = sanitizeInput($_POST['zip_code']);
                $country = sanitizeInput($_POST['country']);
                
                $query = "UPDATE customers SET first_name=?, last_name=?, email=?, phone=?, company=?, address=?, city=?, state=?, zip_code=?, country=? WHERE id=?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$first_name, $last_name, $email, $phone, $company, $address, $city, $state, $zip_code, $country, $id])) {
                    $message = 'Customer updated successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Update Customer', "Updated customer ID: $id");
                } else {
                    $message = 'Error updating customer.';
                    $message_type = 'danger';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $query = "DELETE FROM customers WHERE id=?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$id])) {
                    $message = 'Customer deleted successfully!';
                    $message_type = 'success';
                    logActivity($_SESSION['user_id'], 'Delete Customer', "Deleted customer ID: $id");
                } else {
                    $message = 'Error deleting customer.';
                    $message_type = 'danger';
                }
                break;
        }
    }
}

// Get all customers
$query = "SELECT c.*, u.first_name as assigned_first_name, u.last_name as assigned_last_name,
                 l.first_name as lead_first_name, l.last_name as lead_last_name
          FROM customers c 
          LEFT JOIN users u ON c.assigned_to = u.id 
          LEFT JOIN leads l ON c.lead_id = l.id
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-users"></i> Customer Management</h1>
        <p class="text-muted">Manage your customer database and maintain relationships.</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Add Customer Button -->
<div class="row mb-3">
    <div class="col-12">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="fas fa-plus"></i> Add New Customer
        </button>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-list"></i> All Customers</h5>
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
                        <th>Location</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo $customer['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                <?php if ($customer['lead_id']): ?>
                                    <br><small class="text-muted"><i class="fas fa-exchange-alt"></i> From Lead</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['company']); ?></td>
                            <td>
                                <?php 
                                $location = array_filter([$customer['city'], $customer['state'], $customer['country']]);
                                echo htmlspecialchars(implode(', ', $location));
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($customer['assigned_first_name'] . ' ' . $customer['assigned_last_name']); ?></td>
                            <td><?php echo formatDate($customer['created_at']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" 
                                            onclick="viewCustomer(<?php echo $customer['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="editCustomer(<?php echo htmlspecialchars(json_encode($customer)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteCustomer(<?php echo $customer['id']; ?>)">
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

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Customer</h5>
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

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" name="city">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" name="state">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="zip_code" class="form-label">ZIP Code</label>
                            <input type="text" class="form-control" name="zip_code">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" class="form-control" name="country" value="USA">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
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

                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_city" class="form-label">City</label>
                            <input type="text" class="form-control" name="city" id="edit_city">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_state" class="form-label">State</label>
                            <input type="text" class="form-control" name="state" id="edit_state">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_zip_code" class="form-label">ZIP Code</label>
                            <input type="text" class="form-control" name="zip_code" id="edit_zip_code">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_country" class="form-label">Country</label>
                        <input type="text" class="form-control" name="country" id="edit_country">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCustomer(customer) {
    document.getElementById('edit_id').value = customer.id;
    document.getElementById('edit_first_name').value = customer.first_name;
    document.getElementById('edit_last_name').value = customer.last_name;
    document.getElementById('edit_email').value = customer.email || '';
    document.getElementById('edit_phone').value = customer.phone || '';
    document.getElementById('edit_company').value = customer.company || '';
    document.getElementById('edit_address').value = customer.address || '';
    document.getElementById('edit_city').value = customer.city || '';
    document.getElementById('edit_state').value = customer.state || '';
    document.getElementById('edit_zip_code').value = customer.zip_code || '';
    document.getElementById('edit_country').value = customer.country || '';

    var modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
    modal.show();
}

function deleteCustomer(id) {
    if (confirmDelete('Are you sure you want to delete this customer?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function viewCustomer(id) {
    // This could open a detailed view modal or redirect to a customer detail page
    window.location.href = 'customer_detail.php?id=' + id;
}
</script>

<?php include 'includes/footer.php'; ?>
