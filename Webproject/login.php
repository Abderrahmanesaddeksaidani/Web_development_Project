<?php
require_once 'db.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify user exists and password matches the hash
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Faculty Library</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg); margin: 0; font-family: 'Inter', sans-serif;">

    <div class="auth-card" style="background: var(--surface); padding: 40px; border-radius: var(--radius); box-shadow: var(--shadow); width: 100%; max-width: 400px; border: 1px solid var(--border);">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 40px; margin-bottom: 10px;">📚</div>
            <h1 style="font-size: 24px; margin: 0; color: var(--text);">Welcome Back</h1>
            <p style="color: var(--text-muted); margin-top: 8px;">Please enter your details to sign in</p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 20px; font-size: 14px; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text);">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required 
                       style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text);">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required
                       style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600; cursor: pointer; border: none; border-radius: 8px; background: var(--primary); color: white; transition: opacity 0.2s;">
                Sign In
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px; border-top: 1px solid var(--border); padding-top: 20px;">
            <p style="font-size: 14px; color: var(--text-muted); margin: 0;">
                Don't have an account? 
                <a href="signup.php" style="color: var(--primary); text-decoration: none; font-weight: 600; margin-left: 5px;">Sign up for free</a>
            </p>
        </div>

    </div>

</body>
</html>