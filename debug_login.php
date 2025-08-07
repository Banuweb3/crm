<?php
// Comprehensive login debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>CRM Login Debug Tool</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✅ Database connection: SUCCESS</p>";
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Database connection: FAILED - " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check if users table exists
echo "<h2>2. Users Table Check</h2>";
try {
    $query = "DESCRIBE users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    echo "<p style='color: green;'>✅ Users table exists</p>";
    
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Table Structure:</h3><ul>";
    foreach($columns as $col) {
        echo "<li>{$col['Field']} - {$col['Type']}</li>";
    }
    echo "</ul>";
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Users table: " . $e->getMessage() . "</p>";
}

// Test 3: Check all users
echo "<h2>3. All Users in Database</h2>";
try {
    $query = "SELECT id, username, email, role, password FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Password (first 20 chars)</th></tr>";
        foreach($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>" . substr($user['password'], 0, 20) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No users found in database!</p>";
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error fetching users: " . $e->getMessage() . "</p>";
}

// Test 4: Test login logic
echo "<h2>4. Login Logic Test</h2>";
$test_username = 'admin';
$test_password = 'admin123';

try {
    $query = "SELECT id, username, password, first_name, last_name, role FROM users WHERE username = ? OR email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$test_username, $test_username]);
    
    echo "<p>Searching for username: <strong>$test_username</strong></p>";
    echo "<p>Found <strong>" . $stmt->rowCount() . "</strong> matching users</p>";
    
    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<h3>User Found:</h3>";
        echo "<ul>";
        echo "<li>ID: {$user['id']}</li>";
        echo "<li>Username: {$user['username']}</li>";
        echo "<li>Name: {$user['first_name']} {$user['last_name']}</li>";
        echo "<li>Role: {$user['role']}</li>";
        echo "<li>Stored Password: {$user['password']}</li>";
        echo "</ul>";
        
        echo "<h3>Password Tests:</h3>";
        echo "<p>Test password: <strong>$test_password</strong></p>";
        
        // Test plain text comparison
        if ($test_password === $user['password']) {
            echo "<p style='color: green;'>✅ Plain text comparison: MATCH</p>";
        } else {
            echo "<p style='color: red;'>❌ Plain text comparison: NO MATCH</p>";
        }
        
        // Test password_verify
        if (password_verify($test_password, $user['password'])) {
            echo "<p style='color: green;'>✅ Password hash verification: MATCH</p>";
        } else {
            echo "<p style='color: red;'>❌ Password hash verification: NO MATCH</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ User not found or multiple users found</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Login test error: " . $e->getMessage() . "</p>";
}

// Test 5: Session test
echo "<h2>5. Session Test</h2>";
session_start();
if (session_status() == PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>✅ Sessions are working</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
} else {
    echo "<p style='color: red;'>❌ Sessions not working</p>";
}

// Test 6: Functions file
echo "<h2>6. Functions File Test</h2>";
try {
    require_once 'includes/functions.php';
    echo "<p style='color: green;'>✅ Functions file loaded successfully</p>";
    
    // Test a function
    if (function_exists('sanitizeInput')) {
        echo "<p style='color: green;'>✅ sanitizeInput function exists</p>";
    } else {
        echo "<p style='color: red;'>❌ sanitizeInput function missing</p>";
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Functions file error: " . $e->getMessage() . "</p>";
}

echo "<h2>Quick Fix SQL Commands</h2>";
echo "<p>If admin user is missing or password is wrong, run this in phpMyAdmin:</p>";
echo "<code style='background: #f4f4f4; padding: 10px; display: block;'>";
echo "-- Delete existing admin user<br>";
echo "DELETE FROM users WHERE username = 'admin';<br><br>";
echo "-- Insert admin user with plain text password<br>";
echo "INSERT INTO users (username, email, password, first_name, last_name, role)<br>";
echo "VALUES ('admin', 'admin@crm.com', 'admin123', 'Admin', 'User', 'admin');";
echo "</code>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
code { background: #f4f4f4; padding: 10px; display: block; margin: 10px 0; }
</style>
