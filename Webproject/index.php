<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'catalog';
$status = isset($_GET['status']) ? $_GET['status'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FLMS | Faculty Library</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

    <div class="app-shell">
        <nav class="navbar">
            <div class="brand">📚 Faculty Library</div>
            <div class="nav-links">
                <a href="index.php?page=catalog" class="<?= $page == 'catalog' ? 'active' : '' ?>">Catalog</a>
                <a href="index.php?page=my-loans" class="<?= $page == 'my-loans' ? 'active' : '' ?>">My Loans</a>
                <?php if ($_SESSION['user_role'] == 'admin'): ?>
                    <a href="index.php?page=admin" class="<?= $page == 'admin' ? 'active' : '' ?>">Admin Dashboard</a>
                <?php endif; ?>
            </div>
            <div class="nav-user">
                <span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="logout.php" class="btn btn-sm btn-secondary">Logout</a>
            </div>
        </nav>

        <main class="main" style="max-width: 1200px; margin: 0 auto; padding: 30px; width: 100%;">
            
            <?php if ($status == 'success'): ?>
                <div class="alert alert-info" style="background:#dcfce7; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0;">
                    Action completed successfully!
                </div>
            <?php endif; ?>

            <?php 
            switch ($page) {
                case 'catalog': renderCatalog($pdo); break;
                case 'my-loans': renderMyLoans($pdo, $_SESSION['user_id']); break;
                case 'admin': 
                    if ($_SESSION['user_role'] == 'admin') renderAdminDashboard($pdo); 
                    break;
                default: renderCatalog($pdo); break;
            }
            ?>
        </main>
    </div>

</body>
</html>

<?php
// --- 1. CATALOG ---
function renderCatalog($pdo) {
    $stmt = $pdo->query("SELECT * FROM books");
    $books = $stmt->fetchAll();
    echo '<h1>Library Catalog</h1><div class="book-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px; margin-top:20px;">';
    foreach ($books as $book) {
        ?>
        <div class="book-card" style="background:#fff; padding:20px; border-radius:12px; border:1px solid #eee; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3><?= htmlspecialchars($book['title']) ?></h3>
            <p style="color:#666;">by <?= htmlspecialchars($book['author']) ?></p>
            <div style="margin: 15px 0;">
                <span class="badge"><?= $book['available_copies'] ?> Available</span>
            </div>
            <form action="borrow.php" method="POST">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                <button type="submit" class="btn btn-primary" style="width:100%;" <?= $book['available_copies'] <= 0 ? 'disabled' : '' ?>>
                    <?= $book['available_copies'] <= 0 ? 'Out of Stock' : 'Borrow' ?>
                </button>
            </form>
        </div>
        <?php
    }
    echo '</div>';
}

// --- 2. MY LOANS (WITH RETURN BUTTON) ---
function renderMyLoans($pdo, $userId) {
    echo '<h1>My Borrowed Books</h1>';
    $stmt = $pdo->prepare("SELECT l.id as loan_id, b.title, l.due_date FROM loans l JOIN books b ON l.book_id = b.id WHERE l.user_id = ? AND l.returned_at IS NULL");
    $stmt->execute([$userId]);
    $loans = $stmt->fetchAll();

    if (!$loans) {
        echo '<p style="margin-top:20px; color:#666;">You have no active loans.</p>';
    } else {
        echo '<div style="background:#fff; border-radius:12px; border:1px solid #eee; margin-top:20px; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead style="background:#f9fafb;">
                        <tr><th style="padding:15px;">Book Title</th><th style="padding:15px;">Due Date</th><th style="padding:15px;">Action</th></tr>
                    </thead>
                    <tbody>';
        foreach ($loans as $loan) {
            echo "<tr>
                    <td style='padding:15px; border-top:1px solid #eee;'>{$loan['title']}</td>
                    <td style='padding:15px; border-top:1px solid #eee;'>{$loan['due_date']}</td>
                    <td style='padding:15px; border-top:1px solid #eee;'>
                        <a href='admin_actions.php?action=return_book&loan_id={$loan['loan_id']}' class='btn btn-sm' style='background:#f3f4f6; color:#374151; text-decoration:none; padding:5px 10px; border-radius:6px; font-size:12px; border:1px solid #d1d5db;'>Return Book</a>
                    </td>
                  </tr>";
        }
        echo '</tbody></table></div>';
    }
}

// --- 3. ADMIN DASHBOARD (WITH LOAN TRACKING) ---
function renderAdminDashboard($pdo) {
    echo '<h1>Admin Dashboard</h1>';
    
    // Part A: Add Book Form
    echo '
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #eee; margin: 25px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h2 style="font-size:18px; margin-bottom:15px;">Add New Book</h2>
        <form action="admin_actions.php?action=add_book" method="POST" style="display: flex; gap: 10px;">
            <input type="text" name="title" placeholder="Title" required style="flex:2; padding:10px; border-radius:8px; border:1px solid #ddd;">
            <input type="text" name="author" placeholder="Author" required style="flex:1; padding:10px; border-radius:8px; border:1px solid #ddd;">
            <input type="number" name="copies" value="1" min="1" style="width:70px; padding:10px; border-radius:8px; border:1px solid #ddd;">
            <button type="submit" class="btn btn-primary">Add Book</button>
        </form>
    </div>';

    // Part B: Active Loans Tracking (The new "Return" management)
    echo '<h2 style="font-size:18px; margin: 30px 0 10px 0;">Currently Borrowed Books</h2>';
    $loanStmt = $pdo->query("SELECT l.id as loan_id, u.name as user_name, b.title as book_title, l.borrowed_at 
                             FROM loans l 
                             JOIN users u ON l.user_id = u.id 
                             JOIN books b ON l.book_id = b.id 
                             WHERE l.returned_at IS NULL");
    $activeLoans = $loanStmt->fetchAll();

    echo '<div style="background:#fff; border:1px solid #eee; border-radius:12px; overflow:hidden; margin-bottom:30px;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead style="background:#f9fafb;">
                    <tr><th style="padding:12px;">Student</th><th style="padding:12px;">Book</th><th style="padding:12px;">Action</th></tr>
                </thead>
                <tbody>';
    if (!$activeLoans) echo '<tr><td colspan="3" style="padding:20px; text-align:center; color:#999;">No books are currently borrowed.</td></tr>';
    foreach ($activeLoans as $al) {
        echo "<tr>
                <td style='padding:12px; border-top:1px solid #eee;'>{$al['user_name']}</td>
                <td style='padding:12px; border-top:1px solid #eee;'>{$al['book_title']}</td>
                <td style='padding:12px; border-top:1px solid #eee;'>
                    <a href='admin_actions.php?action=return_book&loan_id={$al['loan_id']}' style='color:var(--primary); font-weight:600; text-decoration:none;'>Mark as Returned</a>
                </td>
              </tr>";
    }
    echo '</tbody></table></div>';

    // Part C: Inventory Management
    echo '<h2 style="font-size:18px; margin-bottom:10px;">Inventory Management</h2>';
    $stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC");
    $books = $stmt->fetchAll();
    echo '<div style="background:#fff; border:1px solid #eee; border-radius:12px; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead style="background:#f9fafb;">
                    <tr><th style="padding:12px;">Title</th><th style="padding:12px;">Stock</th><th style="padding:12px;">Action</th></tr>
                </thead>
                <tbody>';
    foreach ($books as $book) {
        echo "<tr>
                <td style='padding:12px; border-top:1px solid #eee;'>{$book['title']}</td>
                <td style='padding:12px; border-top:1px solid #eee;'>{$book['available_copies']} / {$book['total_copies']}</td>
                <td style='padding:12px; border-top:1px solid #eee;'>
                    <a href='admin_actions.php?action=delete_book&id={$book['id']}' style='color:red;' onclick='return confirm(\"Delete this book?\")'>Delete</a>
                </td>
              </tr>";
    }
    echo '</tbody></table></div>';
}
?>