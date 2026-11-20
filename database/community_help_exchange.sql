-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2026 at 08:52 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `community_help_exchange`
--

-- --------------------------------------------------------

--
-- Table structure for table `help_offers`
--

CREATE TABLE `help_offers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `availability` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `help_offers`
--

INSERT INTO `help_offers` (`id`, `user_id`, `title`, `description`, `category`, `location`, `availability`, `created_at`) VALUES
(1, 2, 'Basic Python Tutoring', 'I can help beginners understand Python programming.', 'Education', 'Bengaluru', 'weekends', '2026-11-20 18:46:24'),
(2, 2, 'Free Mathematics Tutoring', 'I can help students understand basic mathematics concepts.', 'Education', 'Bengaluru', 'Weekends', '2026-11-20 19:21:17');

-- --------------------------------------------------------

--
-- Table structure for table `help_requests`
--

CREATE TABLE `help_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `status` enum('Open','In Progress','Completed') DEFAULT 'Open',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `help_requests`
--

INSERT INTO `help_requests` (`id`, `user_id`, `title`, `description`, `category`, `location`, `preferred_date`, `status`, `created_at`) VALUES
(1, 2, 'Need help with Python', 'Looking for someone to explain basic Python programming concepts.', 'Education', 'Bengaluru', '2026-12-01', 'In Progress', '2026-11-20 18:42:47');

-- --------------------------------------------------------

--
-- Table structure for table `responses`
--

CREATE TABLE `responses` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `responder_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `responses`
--

INSERT INTO `responses` (`id`, `request_id`, `responder_id`, `message`, `status`, `created_at`) VALUES
(1, 1, 4, 'oki', 'Accepted', '2026-11-20 19:12:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Test User', 'testuser@example.com', '$2y$12$x/chzEeqhOBLa3Dvzs/Fcuw6MnN1bDJQwZB.8qt2DNOlNaNGR7RO2', '2026-11-16 06:24:46'),
(2, 'Praneethaa S K', 'definitelynotpraneethaa@gmail.com', '$2y$12$2slsVZ8d0wTtU38o61gOyO4ojwKlN.ua0Cq5uT15JnkdwpRmL8mfi', '2026-11-20 18:35:19'),
(3, 'Ryo Ryo', 'chocosatoshiiryou@gmail.com', '$2y$12$Jr922zqal0.ICws4RbLspO00UWzx3cg2N3uL8NL0RmaHTHIa214VO', '2026-11-20 19:02:53'),
(4, 'Ryo Ryo', 'chocorozusatoshiiryou@gmail.com', '$2y$12$T9UJ5oE0qP6sCbvtoYRjT.ryhpa2ZI4eS0j0ihUg.T3DifEht8e7S', '2026-11-20 19:09:30'),
(5, 'Ryo 2', 'usharanimeip@gmail.com', '$2y$12$TJsIj6QShMJdfe0kaRlcp.9h9YCQs.Citr8.RVC.Y8DS6MwoGNe66', '2026-11-20 19:34:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `help_offers`
--
ALTER TABLE `help_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `help_requests`
--
ALTER TABLE `help_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `responder_id` (`responder_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `help_offers`
--
ALTER TABLE `help_offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `help_requests`
--
ALTER TABLE `help_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `responses`
--
ALTER TABLE `responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `help_offers`
--
ALTER TABLE `help_offers`
  ADD CONSTRAINT `help_offers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `help_requests`
--
ALTER TABLE `help_requests`
  ADD CONSTRAINT `help_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `responses`
--
ALTER TABLE `responses`
  ADD CONSTRAINT `responses_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `help_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `responses_ibfk_2` FOREIGN KEY (`responder_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
