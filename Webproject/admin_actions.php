<?php
require_once 'db.php';
session_start();

// 1. SECURITY: Only logged-in Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit("Unauthorized access.");
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- ACTION: ADD BOOK ---
if ($action === 'add_book' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']);
    $author   = trim($_POST['author']);
    $category = trim($_POST['category']);
    $copies   = (int)$_POST['copies'];

    if (!empty($title) && !empty($author)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, category, total_copies, available_copies) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $category, $copies, $copies]);
            header("Location: index.php?page=admin&status=success");
            exit;
        } catch (PDOException $e) {
            die("Error adding book: " . $e->getMessage());
        }
    }
}

// --- ACTION: DELETE BOOK ---
if ($action === 'delete_book' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $pdo->beginTransaction();
        // Delete history first
        $pdo->prepare("DELETE FROM loans WHERE book_id = ?")->execute([$id]);
        // Delete book
        $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);
        $pdo->commit();
        header("Location: index.php?page=admin&status=success");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}

// --- NEW ACTION: RETURN BOOK ---
if ($action === 'return_book' && isset($_GET['loan_id'])) {
    $loanId = (int)$_GET['loan_id'];

    try {
        $pdo->beginTransaction();

        // 1. Get the book_id associated with this loan
        $stmt = $pdo->prepare("SELECT book_id FROM loans WHERE id = ? AND returned_at IS NULL");
        $stmt->execute([$loanId]);
        $loan = $stmt->fetch();

        if ($loan) {
            $bookId = $loan['book_id'];

            // 2. Increase available copies in books table
            $updateStock = $pdo->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
            $updateStock->execute([$bookId]);

            // 3. Mark loan as returned
            $updateLoan = $pdo->prepare("UPDATE loans SET returned_at = NOW(), status = 'returned' WHERE id = ?");
            $updateLoan->execute([$loanId]);

            $pdo->commit();
            header("Location: index.php?page=admin&status=success");
            exit;
        } else {
            throw new Exception("Loan record not found or already returned.");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error returning book: " . $e->getMessage());
    }
}

// Default redirect if no action matches
header("Location: index.php?page=admin");
exit;