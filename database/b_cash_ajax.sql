-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 13, 2025 at 03:27 AM
-- Server version: 11.7.1-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `b_cash_ajax`
--

-- --------------------------------------------------------

--
-- Table structure for table `security_tokens`
--

CREATE TABLE `security_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `token_type` enum('login','reset','verify') NOT NULL,
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `sender_wallet_id` int(11) NOT NULL,
  `receiver_wallet_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_type` enum('send','receive','topup','withdraw','add_money','pay_bills') NOT NULL,
  `reference_number` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `sender_wallet_id`, `receiver_wallet_id`, `amount`, `transaction_type`, `reference_number`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 37, 37, 100.00, 'add_money', 'ADD202509060012199418', 'Money added to wallet', 'pending', '2025-09-06 00:12:19', '2025-09-06 00:12:19'),
(2, 37, 37, 50.00, 'add_money', 'ADD202509070421323967', 'Money added to wallet', 'pending', '2025-09-07 02:21:32', '2025-09-07 02:21:32'),
(3, 37, 37, 20.00, 'add_money', 'ADD202509070522003448', 'Money added to wallet', 'pending', '2025-09-07 03:22:00', '2025-09-07 03:22:00'),
(4, 36, 36, 20.00, 'add_money', 'ADD202509081146343150', 'Money added to wallet', 'pending', '2025-09-08 11:46:34', '2025-09-08 11:46:34'),
(19, 36, 37, 5.00, 'send', 'TXN2025090812504449616916768bed12487ad44.92588598', 'Direct test transfer', 'pending', '2025-09-08 12:50:44', '2025-09-08 12:50:44'),
(20, 36, 37, 5.00, 'receive', 'TXN20250908125044162803847268bed124881fe2.68374220', 'Direct test transfer', 'pending', '2025-09-08 12:50:44', '2025-09-08 12:50:44'),
(21, 36, 37, 5.00, 'send', 'TXN2025090813094588008157568bed599c4c993.23333913', 'Direct test transfer', 'pending', '2025-09-08 13:09:45', '2025-09-08 13:09:45'),
(22, 36, 37, 5.00, 'receive', 'TXN20250908130945118188306268bed599c54f58.63074657', 'Direct test transfer', 'pending', '2025-09-08 13:09:45', '2025-09-08 13:09:45'),
(23, 36, 37, 5.00, 'send', 'TXN2025090813122261016730668bed6364192d1.05889144', 'Test transfer', 'pending', '2025-09-08 13:12:22', '2025-09-08 13:12:22'),
(24, 36, 37, 5.00, 'receive', 'TXN20250908131222189854514368bed63641f3d7.47920965', 'Test transfer', 'pending', '2025-09-08 13:12:22', '2025-09-08 13:12:22'),
(25, 37, 36, 5.00, 'send', 'TXN2025090813221395283699268bed885122a25.37789602', 'Test transfer with phone number', 'pending', '2025-09-08 13:22:13', '2025-09-08 13:22:13'),
(26, 37, 36, 5.00, 'receive', 'TXN20250908132213195704762568bed885128953.27753701', 'Test transfer with phone number', 'pending', '2025-09-08 13:22:13', '2025-09-08 13:22:13'),
(27, 37, 36, 50.00, 'send', 'TXN2025090813231192992709968bed8bf5b29c4.69805124', '', 'pending', '2025-09-08 13:23:11', '2025-09-08 13:23:11'),
(28, 37, 36, 50.00, 'receive', 'TXN20250908132311181097650168bed8bf5b9a46.81365524', '', 'pending', '2025-09-08 13:23:11', '2025-09-08 13:23:11'),
(29, 36, 36, 20.00, 'add_money', 'ADD2025090813262888849613', 'Money added to wallet', 'pending', '2025-09-08 13:26:28', '2025-09-08 13:26:28'),
(30, 36, 36, 30.00, 'add_money', 'ADD2025090813383308357682', 'Money added to wallet', 'pending', '2025-09-08 13:38:33', '2025-09-08 13:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `pin_hash` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0,
  `verification_level` enum('basic','verified','premium') DEFAULT 'basic',
  `verification_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `login_attempts` int(11) DEFAULT 0,
  `last_login_attempt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `phone_number`, `email`, `full_name`, `birthdate`, `address`, `gender`, `password_hash`, `pin_hash`, `profile_picture`, `is_verified`, `is_admin`, `verification_level`, `verification_expires_at`, `created_at`, `updated_at`, `login_attempts`, `last_login_attempt`) VALUES
(38, '09982297807', 'kaathe@gmail.com', 'Dark Stalker Kaathe', '2003-12-03', 'Cabanatuan City', 'male', '$2y$10$NU57aIFeUBTQ6wrdERQF6.nJizG/c2oo6139XvP8Q6XlB.kcTnU3i', NULL, NULL, 0, 1, 'basic', NULL, '2025-08-31 03:54:20', '2025-09-08 13:35:49', 0, NULL),
(39, '09167572346', 'kyle@gmail.com', 'Kyle Marcelo', '2003-12-03', 'Cabanatuan City', 'male', '$2y$10$kJo0q51.gwHqrsLPB96neeWxes8XW1rbSWzVDfZyzaBl9eBYZ.m4a', NULL, NULL, 0, 0, 'basic', NULL, '2025-09-05 13:56:34', '2025-09-07 03:34:25', 0, NULL),
(42, '09123456791', NULL, 'Test User 3', NULL, NULL, NULL, '$2y$10$yb.ay9bOBNTUsqOqCCxh6ueCNEZJKJtixeiFXcSBlYrKHjU8vC0q6', NULL, NULL, 0, 0, 'basic', NULL, '2025-09-07 02:49:35', '2025-09-07 02:49:35', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_verification`
--

CREATE TABLE `user_verification` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `verification_status` enum('pending','verified','rejected','expired') DEFAULT 'pending',
  `id_document_type` enum('passport','drivers_license','national_id','other') NOT NULL,
  `id_document_number` varchar(100) NOT NULL,
  `id_document_front_path` varchar(255) DEFAULT NULL,
  `id_document_back_path` varchar(255) DEFAULT NULL,
  `id_document_ocr_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`id_document_ocr_data`)),
  `face_encoding` text DEFAULT NULL,
  `face_image_path` varchar(255) DEFAULT NULL,
  `liveness_score` decimal(3,2) DEFAULT NULL,
  `similarity_score` decimal(3,2) DEFAULT NULL,
  `verification_attempts` int(11) DEFAULT 0,
  `last_verification_attempt` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_verification`
--

INSERT INTO `user_verification` (`id`, `user_id`, `verification_status`, `id_document_type`, `id_document_number`, `id_document_front_path`, `id_document_back_path`, `id_document_ocr_data`, `face_encoding`, `face_image_path`, `liveness_score`, `similarity_score`, `verification_attempts`, `last_verification_attempt`, `verified_at`, `created_at`, `updated_at`) VALUES
(22, 38, 'pending', 'drivers_license', '41DD35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2025-08-31 03:54:20', '2025-08-31 03:54:20'),
(23, 39, 'pending', 'drivers_license', '41DD35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2025-09-05 13:56:34', '2025-09-05 13:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `verification_logs`
--

CREATE TABLE `verification_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('document_upload','face_capture','verification_attempt','verification_success','verification_failure') DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_logs`
--

INSERT INTO `verification_logs` (`id`, `user_id`, `action`, `metadata`, `ip_address`, `user_agent`, `created_at`) VALUES
(40, 38, '', '{\"document_type\":\"drivers_license\",\"document_number\":\"41DD35\"}', '::1', 'Mozilla/5.0 (Linux; Android 11.0; Surface Duo) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36 Edg/139.0.0.0', '2025-08-31 03:54:20'),
(41, 38, 'document_upload', '{\"side\":\"front\",\"filename\":\"front_68b3c76cdb84e.jpg\"}', '::1', 'Mozilla/5.0 (Linux; Android 11.0; Surface Duo) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36 Edg/139.0.0.0', '2025-08-31 03:54:20'),
(42, 38, 'document_upload', '{\"side\":\"back\",\"filename\":\"back_68b3c76ceab7c.jpg\"}', '::1', 'Mozilla/5.0 (Linux; Android 11.0; Surface Duo) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36 Edg/139.0.0.0', '2025-08-31 03:54:20'),
(43, 38, 'face_capture', '{\"filename\":\"face_68b3c76d261e4.jpg\"}', '::1', 'Mozilla/5.0 (Linux; Android 11.0; Surface Duo) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36 Edg/139.0.0.0', '2025-08-31 03:54:21'),
(44, 39, '', '{\"document_type\":\"drivers_license\",\"document_number\":\"41DD35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-05 13:56:34'),
(45, 39, 'document_upload', '{\"side\":\"front\",\"filename\":\"front_68baec128f591.jpg\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-05 13:56:34'),
(46, 39, 'document_upload', '{\"side\":\"back\",\"filename\":\"back_68baec1297571.jpg\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-05 13:56:34'),
(47, 39, 'face_capture', '{\"filename\":\"face_68baec12c3ba8.jpg\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-05 13:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `account_number` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `account_number`, `is_active`, `created_at`, `updated_at`) VALUES
(36, 38, 110.00, 'BC473572', 1, '2025-08-31 03:54:20', '2025-09-08 13:38:33'),
(37, 39, 130.00, 'BC754389', 1, '2025-09-05 13:56:34', '2025-09-08 13:23:11'),
(40, 42, 0.00, 'BC947151', 1, '2025-09-07 02:49:35', '2025-09-07 02:49:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `security_tokens`
--
ALTER TABLE `security_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `idx_transactions_sender` (`sender_wallet_id`),
  ADD KEY `idx_transactions_receiver` (`receiver_wallet_id`),
  ADD KEY `idx_transactions_reference` (`reference_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_phone` (`phone_number`);

--
-- Indexes for table `user_verification`
--
ALTER TABLE `user_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_verification_status` (`verification_status`);

--
-- Indexes for table `verification_logs`
--
ALTER TABLE `verification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `account_number` (`account_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `security_tokens`
--
ALTER TABLE `security_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `user_verification`
--
ALTER TABLE `user_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `verification_logs`
--
ALTER TABLE `verification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `security_tokens`
--
ALTER TABLE `security_tokens`
  ADD CONSTRAINT `security_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`sender_wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`receiver_wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_verification`
--
ALTER TABLE `user_verification`
  ADD CONSTRAINT `user_verification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `verification_logs`
--
ALTER TABLE `verification_logs`
  ADD CONSTRAINT `verification_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
