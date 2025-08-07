<?php
// Test database connection
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Database Connection: SUCCESS ✅</h2>";
    
    // Check if users table exists and has data
    $query = "SELECT COUNT(*) as count FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Users table has {$result['count']} records</p>";
    
    // Check admin user specifically
    $query = "SELECT username, email, role FROM users WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<h3>Admin User Found ✅</h3>";
        echo "<p>Username: " . $admin['username'] . "</p>";
        echo "<p>Email: " . $admin['email'] . "</p>";
        echo "<p>Role: " . $admin['role'] . "</p>";
    } else {
        echo "<h3>Admin User NOT Found ❌</h3>";
        echo "<p>The admin user doesn't exist in the database.</p>";
    }
    
} catch(Exception $e) {
    echo "<h2>Database Connection: FAILED ❌</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #333; }
h3 { color: #666; }
p { margin: 5px 0; }
</style>
