<?php
// Fix admin password
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Generate new hash for admin123
$new_password = 'admin123';
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

echo "<h1>Password Fix Tool</h1>";
echo "<p><strong>New Password:</strong> $new_password</p>";
echo "<p><strong>New Hash:</strong> $new_hash</p>";

// Test the new hash
if (password_verify($new_password, $new_hash)) {
    echo "<p style='color: green;'>✅ New hash verification: SUCCESS</p>";
    
    // Update the database
    try {
        $query = "UPDATE users SET password = ? WHERE username = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->execute([$new_hash]);
        
        echo "<p style='color: green;'>✅ Database updated successfully!</p>";
        echo "<p><strong>You can now login with:</strong></p>";
        echo "<ul>";
        echo "<li>Username: <strong>admin</strong></li>";
        echo "<li>Password: <strong>admin123</strong></li>";
        echo "</ul>";
        
        echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
        
    } catch(Exception $e) {
        echo "<p style='color: red;'>❌ Database update failed: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ New hash verification: FAILED</p>";
}

// Show current user info
echo "<h2>Current Admin User Info:</h2>";
try {
    $query = "SELECT id, username, email, password FROM users WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<ul>";
        echo "<li>ID: {$user['id']}</li>";
        echo "<li>Username: {$user['username']}</li>";
        echo "<li>Email: {$user['email']}</li>";
        echo "<li>Password Hash: " . substr($user['password'], 0, 30) . "...</li>";
        echo "</ul>";
        
        // Test current password
        echo "<h3>Password Test:</h3>";
        if (password_verify('admin123', $user['password'])) {
            echo "<p style='color: green;'>✅ Current password works with 'admin123'</p>";
        } else {
            echo "<p style='color: red;'>❌ Current password does NOT work with 'admin123'</p>";
        }
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
