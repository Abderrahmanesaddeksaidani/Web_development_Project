<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['book_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$bookId = $_POST['book_id'];
$borrowDate = date('Y-m-d');
$dueDate = date('Y-m-d', strtotime('+14 days')); // Standard 2-week loan

try {
    // Start a transaction to ensure both steps happen together
    $pdo->beginTransaction();

    // 1. Check if book is available
    $stmt = $pdo->prepare("SELECT available_copies FROM books WHERE id = ? FOR UPDATE");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();

    if ($book && $book['available_copies'] > 0) {
        // 2. Reduce available copies
        $updateStmt = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
        $updateStmt->execute([$bookId]);

        // 3. Record the loan
        $loanStmt = $pdo->prepare("INSERT INTO loans (user_id, book_id, borrowed_at, due_date, status) VALUES (?, ?, ?, ?, 'active')");
        $loanStmt->execute([$userId, $bookId, $borrowDate, $dueDate]);

        $pdo->commit();
        header("Location: index.php?page=my-loans&status=success");
    } else {
        $pdo->rollBack();
        header("Location: index.php?page=catalog&status=unavailable");
    }
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error processing loan: " . $e->getMessage());
} 
