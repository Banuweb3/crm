<?php
// Generate a fresh password hash for admin123
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Password Hash Generator</h2>";
echo "<p><strong>Password:</strong> $password</p>";
echo "<p><strong>Hash:</strong> $hash</p>";

echo "<h3>SQL to update admin user:</h3>";
echo "<code>UPDATE users SET password = '$hash' WHERE username = 'admin';</code>";

// Test the hash
echo "<h3>Verification Test:</h3>";
if (password_verify($password, $hash)) {
    echo "<p style='color: green;'>✅ Hash verification: SUCCESS</p>";
} else {
    echo "<p style='color: red;'>❌ Hash verification: FAILED</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
code { background: #f4f4f4; padding: 10px; display: block; margin: 10px 0; }
</style>
