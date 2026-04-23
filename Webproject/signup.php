<?php
require_once 'db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = 'student'; // Default role for new signups

    // Check if email already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->fetch()) {
        $error = "This email is already registered.";
    } else {
        // Hash password and insert
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $hashedPassword, $role])) {
            $success = "Account created! You can now log in.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Faculty Library</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg);">

    <div class="auth-card" style="background: var(--surface); padding: 40px; border-radius: var(--radius); box-shadow: var(--shadow); width: 100%; max-width: 400px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-size: 24px; margin-bottom: 8px;">Create Account</h1>
            <p style="color: var(--text-muted);">Join the Faculty Library System</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="color: red; margin-bottom: 20px;"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" style="color: green; margin-bottom: 20px;"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="John Doe" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="email@example.com" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600;">Register</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <p style="font-size: 14px;">Already have an account? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Log In</a></p>
        </div>
    </div>
</body>
</html>