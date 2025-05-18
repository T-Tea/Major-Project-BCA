-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2025 at 05:02 PM
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
-- Database: `hostel`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `password`, `address`, `phone_number`) VALUES
(1, 'Bapan', '$2y$10$dF4FU/8DFx3scVtyXuJhGuzhU7SKMQNnJRsRAZfAoq7JMvpEwWQZu', 'Girl\'s Hostel Building', '7698117492');

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `lights_total` int(11) DEFAULT 0,
  `lights_working` int(11) DEFAULT 0,
  `lights_defective` int(11) DEFAULT 0,
  `fans_total` int(11) DEFAULT 0,
  `fans_working` int(11) DEFAULT 0,
  `fans_defective` int(11) DEFAULT 0,
  `doors_total` int(11) DEFAULT 0,
  `doors_working` int(11) DEFAULT 0,
  `doors_defective` int(11) DEFAULT 0,
  `windows_total` int(11) DEFAULT 0,
  `windows_working` int(11) DEFAULT 0,
  `windows_defective` int(11) DEFAULT 0,
  `power_outlets_total` int(11) DEFAULT 0,
  `power_outlets_working` int(11) DEFAULT 0,
  `power_outlets_defective` int(11) DEFAULT 0,
  `solar_bulbs_total` int(11) DEFAULT 0,
  `solar_bulbs_working` int(11) DEFAULT 0,
  `solar_bulbs_defective` int(11) DEFAULT 0,
  `condition_status` enum('working','defective') DEFAULT 'working',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `room_number`, `lights_total`, `lights_working`, `lights_defective`, `fans_total`, `fans_working`, `fans_defective`, `doors_total`, `doors_working`, `doors_defective`, `windows_total`, `windows_working`, `windows_defective`, `power_outlets_total`, `power_outlets_working`, `power_outlets_defective`, `solar_bulbs_total`, `solar_bulbs_working`, `solar_bulbs_defective`, `condition_status`, `last_updated`) VALUES
(1, '202', 3, 1, 2, 2, 1, 1, 2, 2, 0, 2, 2, 0, 2, 2, 0, 2, 2, 0, '', '2025-04-20 15:29:20'),
(3, '101', 3, 2, 1, 2, 1, 1, 2, 2, 0, 2, 2, 0, 2, 2, 0, 2, 2, 0, '', '2025-04-20 15:41:27');

-- --------------------------------------------------------

--
-- Table structure for table `checkout`
--

CREATE TABLE `checkout` (
  `id` int(11) NOT NULL,
  `hosteller_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `from_datetime` datetime NOT NULL,
  `to_datetime` datetime NOT NULL,
  `description` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `warden_remarks` text DEFAULT NULL,
  `submit_datetime` datetime NOT NULL,
  `response_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checkout`
--

INSERT INTO `checkout` (`id`, `hosteller_id`, `subject`, `from_datetime`, `to_datetime`, `description`, `file_path`, `status`, `warden_remarks`, `submit_datetime`, `response_datetime`) VALUES
(1, 13, 'Hospital Stay', '2025-04-17 19:56:00', '2025-04-18 07:56:00', 'I have to stay with my aunt at the Greenwood hospital for one night at the request of my family.', NULL, 'rejected', 'Not specific enough', '2025-04-17 11:26:49', '2025-04-23 21:29:27'),
(5, 7, 'Family Issues', '2025-04-24 17:20:00', '2025-04-26 09:20:00', 'I kindly request a leave from 24th April to 26th April to go back to my hometown for family business', NULL, 'approved', '', '2025-04-24 12:51:54', '2025-04-24 16:32:14'),
(6, 8, 'Sick Leave', '2025-04-24 16:23:00', '2025-04-28 09:23:00', 'I got admitted to Hospital for scrub typhus, i kindly request that you give me leave during my stay at the hospital, thank you for understanding', NULL, 'approved', '', '2025-04-24 12:53:53', '2025-04-24 16:32:17'),
(7, 9, 'Family Visit', '2025-04-24 16:26:00', '2025-04-25 21:30:00', 'I need to stay out for one night to visit my family coming to Aizawl, they are staying on Chaltlang', NULL, 'pending', NULL, '2025-04-24 12:57:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hostellers`
--

CREATE TABLE `hostellers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `course` varchar(10) NOT NULL,
  `semester` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `room` int(10) NOT NULL,
  `building` varchar(255) NOT NULL,
  `mess_fee` tinyint(1) NOT NULL DEFAULT 0,
  `room_rent` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostellers`
--

INSERT INTO `hostellers` (`id`, `name`, `password`, `course`, `semester`, `phone_number`, `room`, `building`, `mess_fee`, `room_rent`) VALUES
(6, 'Lalbiakdika', '$2y$10$g4sm2FMcWf14fmTCB3oe7OnKENIadMiT784m89Ni2/QU7B7nuJ5fu', 'BCA', '4', '9872661521', 101, 'Boys Hostel', 0, 1),
(7, 'Lalhminghlua', '$2y$10$9oaYYmHk.6W175KNJjyoaOhzZSoxAnkAixy64mLn0biu6LGIsWeru', 'BCA', '4', '6033202797', 101, 'Boys Hostel', 1, 1),
(8, 'Malsawmtluanga', '$2y$10$yzlBit9q51Y.3.iw5J5H1uTWqiTbR.zjTVz/dBuNl17hDUgTuEBGW', 'BCA', '4', '8794727828', 102, 'Boys Hostel', 0, 0),
(9, 'Zodinmawia', '$2y$10$oWULa09sG/py7gimq2fDguaubnVSrC6JqBTqtfrV.PlM1xnRP.wqG', 'BCA', '4', '8923597342', 102, 'Boys Hostel', 0, 0),
(10, 'Isaac Lalhmunmawia', '$2y$10$qK9E4/R5X98R1yfuCqJAC.HqmhNQT/mEnGdbJHN8DBS3qHkSNghv.', 'BCA', '4', '7618273934', 103, 'Boys Hostel', 0, 0),
(11, 'PC Zohmingsanga', '$2y$10$QU5dyAWHVvFQGv93vF04xuxFDOJVQw9pwQ3eT8CAkf4V44wiS0qA6', 'DETE', '4', '7629882432', 103, 'Boys Hostel', 0, 0),
(12, 'Lalbuatsaiha', '$2y$10$d1r.RfmprEFGcc6/MLBqneKLEShbpUeVgyLaZqFWz8BmR6hUngBGK', 'BCA', '6', '9863145210', 201, 'Boys Hostel', 0, 0),
(13, 'T Lalbiakhlua', '$2y$10$ESO/4UFhpHGSNFzNdWBL2uq2hheFgrDzkNFGZTyouM.tDWpypQx3W', 'BCA', '6', '6009232405', 202, 'Boys Hostel', 1, 1),
(14, 'C Khaitei', '$2y$10$ccV6Qyyi../IsfxaeDcV4.Yq9lcxpM6JpKeOb5QGZf3IK8zrYgPbK', 'BCA', '4', '9362625110', 203, 'Boys Hostel', 0, 0),
(15, 'Pecos ', '$2y$10$tn.ACP1yLmVKuypMWpIMEe.JHfNfqry0Ew7KEVvMeJxSOS6HMS/hC', 'DETE', '4', '8675467345', 203, 'Boys Hostel', 0, 0),
(18, 'Z Hrotlo', '$2y$10$IhW7KaGE58cYIsufC798YunkZS79TjpYr2x516SjgxS.cnizfhmA2', 'bca', '2', '8367593842', 301, 'Girls', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `sender_role` enum('warden','admin') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `date_sent` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `sender_role`, `title`, `description`, `attachment`, `date_sent`) VALUES
(1, 'warden', 'Room Inspection', 'Room inspections will be held this Friday at 10 AM.', NULL, '2025-04-15 15:14:02'),
(2, 'admin', 'Fee Reminder', 'Last date for fee payment is 25th April.', 'uploads/fee_schedule.pdf', '2025-04-15 15:14:02'),
(3, 'warden', 'Water Supply', 'Water supply will be disrupted from 2 PM to 4 PM tomorrow.', NULL, '2025-04-15 15:14:02'),
(4, 'admin', 'Swatch Bharat', 'There will be a swatch bharat on the date of 22th April 2025, every hosteller should be present, there will be refreshments', NULL, '2025-04-22 15:06:36'),
(5, 'warden', 'Gate close', 'This is to notify hostellers that from today onwards, the gate will close from 7:30pm ', NULL, '2025-04-22 15:12:38');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fee_type` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `payment_date` datetime NOT NULL,
  `status` varchar(20) DEFAULT 'completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `user_id`, `fee_type`, `amount`, `payment_method`, `transaction_id`, `payment_date`, `status`) VALUES
(1, 13, 'room_rent', 1500.00, 'upi', 'TXN2062226', '2025-04-28 16:54:14', 'completed'),
(2, 13, 'mess_fee', 15000.00, 'upi', 'TXN4557582', '2025-04-28 16:58:46', 'completed'),
(3, 7, 'mess_fee', 15000.00, 'upi', 'TXN9158110', '2025-04-28 17:38:13', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `payment_request`
--

CREATE TABLE `payment_request` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room` varchar(10) NOT NULL,
  `building` varchar(50) DEFAULT NULL,
  `occupant` varchar(100) NOT NULL,
  `fee_type` enum('room_rent','mess_fee') NOT NULL,
  `requested_change` varchar(20) DEFAULT 'mark as paid',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_request`
--

INSERT INTO `payment_request` (`request_id`, `user_id`, `room`, `building`, `occupant`, `fee_type`, `requested_change`, `request_date`, `status`) VALUES
(1, 6, '101', 'Boys Hostel', 'Lalbiakdika', 'room_rent', 'mark as paid', '2025-04-19 18:23:36', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `complaint_type` varchar(50) NOT NULL,
  `recipient` enum('warden','admin') NOT NULL,
  `subject` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('read','unread') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `user_id`, `complaint_type`, `recipient`, `subject`, `description`, `attachment_path`, `status`, `created_at`) VALUES
(3, 13, 'Maintenance Request', 'admin', 'Ceiling Fan not turning on', 'One of my ceiling fan in my room (202) is not turning on, may i request a maintenance check on it since it is beginnning to get hotter as summer rolls by', NULL, 'read', '2025-04-18 07:34:17'),
(9, 7, 'Room Issue', 'warden', 'werwer', 'werwerwerwerwer', NULL, 'read', '2025-04-22 14:36:19'),
(16, 8, 'Noise Complaint', 'warden', '3rd Floor', 'The 3rd floor hostellers are always very noisy even after 10pm, no amount of telling is not working, please help', NULL, 'unread', '2025-04-24 10:54:49'),
(17, 8, 'Noise Complaint', 'admin', '3rd Floor', 'The 3rd floor hostellers are always very noisy even after 10pm, no amount of telling is not working, please help', NULL, 'unread', '2025-04-24 10:55:29'),
(18, 12, 'Room Issue', 'warden', 'Missing desk', 'My room is missing a desk, may i request a desk to use', NULL, 'unread', '2025-04-24 10:58:21'),
(19, 14, 'Facility Issue', 'admin', 'Broken window', 'One of our windows on room 203 is not closing properly, please help', NULL, 'read', '2025-04-24 10:59:38');

-- --------------------------------------------------------

--
-- Table structure for table `warden`
--

CREATE TABLE `warden` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `building` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warden`
--

INSERT INTO `warden` (`id`, `name`, `password`, `address`, `phone_number`, `building`) VALUES
(1, 'Lalsiamkima', '$2y$10$s/DPiLGCsIbEz0tMozw9R.mrJI/Zzwcc.dhHGmWHOJG7SZFOk7Dxa', 'Girl\'s Hostel', '9612715924', NULL),
(2, 'Director nupui', '$2y$10$ClzOAqn5X0UyZ33vxdfmeerllxZAtLIJJZkXd8OQa/JOc3p5kkQqW', 'Girl\'s Hostel', '9836482313', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`);

--
-- Indexes for table `checkout`
--
ALTER TABLE `checkout`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hosteller_id` (`hosteller_id`);

--
-- Indexes for table `hostellers`
--
ALTER TABLE `hostellers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_request`
--
ALTER TABLE `payment_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `warden`
--
ALTER TABLE `warden`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `checkout`
--
ALTER TABLE `checkout`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `hostellers`
--
ALTER TABLE `hostellers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_request`
--
ALTER TABLE `payment_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `warden`
--
ALTER TABLE `warden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checkout`
--
ALTER TABLE `checkout`
  ADD CONSTRAINT `checkout_hosteller_fk` FOREIGN KEY (`hosteller_id`) REFERENCES `hostellers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment_request`
--
ALTER TABLE `payment_request`
  ADD CONSTRAINT `payment_request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `hostellers` (`id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `hostellers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
