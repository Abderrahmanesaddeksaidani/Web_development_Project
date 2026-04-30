-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2026 at 01:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: library_db
--

-- --------------------------------------------------------

--
-- Table structure for table books
--

CREATE TABLE books (
  id int(11) NOT NULL,
  title varchar(255) NOT NULL,
  author varchar(100) DEFAULT NULL,
  isbn varchar(20) DEFAULT NULL,
  category varchar(50) DEFAULT NULL,
  total_copies int(11) DEFAULT 1,
  available_copies int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table books
--

INSERT INTO books (id, title, author, isbn, category, total_copies, available_copies) VALUES
(1, 'Introduction to PHP', 'John Smith', '123456789', 'Programming', 5, 5),
(3, 'Web Security Guide', 'Alex Wilson', '555666777', 'Security', 2, 2),
(5, 'The rise of the Devil', 'Saddek Saidani', NULL, 'Adolf Hitler', 20, 18),
(6, 'Vitamin Drink', 'algeria', NULL, '', 75, 75);

-- --------------------------------------------------------

--
-- Table structure for table loans
--

CREATE TABLE loans (
  id int(11) NOT NULL,
  user_id int(11) DEFAULT NULL,
  book_id int(11) DEFAULT NULL,
  borrowed_at date DEFAULT NULL,
  due_date date DEFAULT NULL,
  returned_at date DEFAULT NULL,
  status enum('active','returned','overdue') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table loans
--

INSERT INTO loans (id, user_id, book_id, borrowed_at, due_date, returned_at, status) VALUES
(4, 4, 1, '2026-04-24', '2026-05-08', '2026-04-23', 'returned'),
(5, 4, 5, '2026-04-24', '2026-05-08', NULL, 'active'),
(6, 5, 5, '2026-04-24', '2026-05-08', NULL, 'active'),
(7, 4, 5, '2026-04-24', '2026-05-08', '2026-04-23', 'returned');

-- --------------------------------------------------------

--
-- Table structure for table users
--

CREATE TABLE users (
  id int(11) NOT NULL,
  name varchar(100) NOT NULL,
  email varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  role enum('admin','librarian','student','faculty') DEFAULT 'student',
  active tinyint(1) DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table users
--

INSERT INTO users (id, name, email, password, role, active, created_at) VALUES
(4, 'Islam Abada', 'admin@flms.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2026-04-23 22:08:39'),
(5, 'Saddek Saidani', 'Saddek@gmail.com', '$2y$10$AjHQwmLb9LuOIA3fSRxUxuIvawlHg47Nk7nI.DlLwMQ94TwNveIfO', 'admin', 1, '2026-04-23 22:48:26'),
(6, 'Wiw', 'wiw@gmail.com', '$2y$10$y/x4Sv081Z0fglvEENGHl.k8V5gBDyouv8JjBnPQKaqiwvmyysc1q', 'student', 1, '2026-04-23 22:57:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table books
--
ALTER TABLE books
  ADD PRIMARY KEY (id);

--
-- Indexes for table loans
--
ALTER TABLE loans
  ADD PRIMARY KEY (id),
  ADD KEY user_id (user_id),
  ADD KEY book_id (book_id);

--
-- Indexes for table users
--
ALTER TABLE users
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY email (email);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table books
--
ALTER TABLE books
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table loans
--
ALTER TABLE loans
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
  --
-- AUTO_INCREMENT for table users
--
ALTER TABLE users
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table loans
--
ALTER TABLE loans
  ADD CONSTRAINT loans_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id),
  ADD CONSTRAINT loans_ibfk_2 FOREIGN KEY (book_id) REFERENCES books (id);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
