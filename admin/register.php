<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB(); // ✅ এখানে DB connection নাও

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $fullName = trim($_POST['full_name']);
    $role     = 'super_admin'; // default role

    // bcrypt hash
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password, full_name, role) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword, $fullName, $role]);

    echo "Registration successful!";
}
?>

<form method="POST">
    Username: <input type="text" name="username" required><br>
    Email: <input type="email" name="email" required><br>
    Full Name: <input type="text" name="full_name" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Register</button>
</form>
