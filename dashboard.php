<?php
$page_title = 'Dashboard';
require_once 'config/database.php';
include 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total leads
$query = "SELECT COUNT(*) as total FROM leads";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_leads'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// New leads (this month)
$query = "SELECT COUNT(*) as total FROM leads WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['new_leads'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total customers
$query = "SELECT COUNT(*) as total FROM customers";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pending tasks
$query = "SELECT COUNT(*) as total FROM tasks WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_tasks'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Overdue tasks
$query = "SELECT COUNT(*) as total FROM tasks WHERE status = 'pending' AND due_date < CURRENT_DATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['overdue_tasks'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Lead conversion rate
$query = "SELECT COUNT(*) as converted FROM leads WHERE status = 'converted'";
$stmt = $db->prepare($query);
$stmt->execute();
$converted_leads = $stmt->fetch(PDO::FETCH_ASSOC)['converted'];
$stats['conversion_rate'] = $stats['total_leads'] > 0 ? round(($converted_leads / $stats['total_leads']) * 100, 1) : 0;

// Recent leads
$query = "SELECT id, first_name, last_name, company, status, created_at FROM leads ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent tasks
$query = "SELECT t.id, t.title, t.due_date, t.priority, t.status, 
                 CASE 
                    WHEN t.related_to_type = 'lead' THEN CONCAT(l.first_name, ' ', l.last_name)
                    WHEN t.related_to_type = 'customer' THEN CONCAT(c.first_name, ' ', c.last_name)
                    ELSE 'General'
                 END as related_to_name
          FROM tasks t
          LEFT JOIN leads l ON t.related_to_type = 'lead' AND t.related_to_id = l.id
          LEFT JOIN customers c ON t.related_to_type = 'customer' AND t.related_to_id = c.id
          WHERE t.status IN ('pending', 'in_progress')
          ORDER BY t.due_date ASC, t.priority DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$upcoming_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lead status distribution
$query = "SELECT status, COUNT(*) as count FROM leads GROUP BY status";
$stmt = $db->prepare($query);
$stmt->execute();
$lead_status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <p class="text-muted">Welcome back, <?php echo $_SESSION['user_name']; ?>! Here's your CRM overview.</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $stats['total_leads']; ?></h4>
                        <p class="card-text">Total Leads</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-plus fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $stats['total_customers']; ?></h4>
                        <p class="card-text">Total Customers</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $stats['pending_tasks']; ?></h4>
                        <p class="card-text">Pending Tasks</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-tasks fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $stats['conversion_rate']; ?>%</h4>
                        <p class="card-text">Conversion Rate</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $stats['new_leads']; ?></h4>
                        <p class="card-text">New Leads This Month</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-plus fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card text-white <?php echo $stats['overdue_tasks'] > 0 ? 'bg-danger' : 'bg-success'; ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $stats['overdue_tasks']; ?></h4>
                        <p class="card-text">Overdue Tasks</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-user-plus"></i> Recent Leads</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_leads)): ?>
                    <p class="text-muted">No leads found.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_leads as $lead): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($lead['company']); ?></small><br>
                                    <small class="text-muted"><?php echo formatDateTime($lead['created_at']); ?></small>
                                </div>
                                <span class="badge <?php echo getStatusBadgeClass($lead['status']); ?>">
                                    <?php echo ucfirst($lead['status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="leads.php" class="btn btn-primary btn-sm">View All Leads</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-tasks"></i> Upcoming Tasks</h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcoming_tasks)): ?>
                    <p class="text-muted">No upcoming tasks.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcoming_tasks as $task): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?php echo htmlspecialchars($task['title']); ?></strong><br>
                                        <small class="text-muted">Related to: <?php echo htmlspecialchars($task['related_to_name']); ?></small><br>
                                        <small class="text-muted">Due: <?php echo $task['due_date'] ? formatDate($task['due_date']) : 'No due date'; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge <?php echo getPriorityBadgeClass($task['priority']); ?>">
                                            <?php echo ucfirst($task['priority']); ?>
                                        </span><br>
                                        <span class="badge <?php echo getStatusBadgeClass($task['status']); ?> mt-1">
                                            <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="tasks.php" class="btn btn-primary btn-sm">View All Tasks</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
