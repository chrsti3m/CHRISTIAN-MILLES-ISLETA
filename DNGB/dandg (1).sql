-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2024 at 04:29 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dandg`
--

-- --------------------------------------------------------

--
-- Table structure for table `banana_type`
--

CREATE TABLE `banana_type` (
  `banana_type_id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banana_type`
--

INSERT INTO `banana_type` (`banana_type_id`, `type_name`, `description`) VALUES
(66851, 'Latundan', 'Description for Latundan'),
(66852, 'Saba', 'Description for Saba'),
(66853, 'Lakatan', 'Description for Lakatan'),
(66854, 'Señorita', 'Description for Señorita'),
(66855, 'Senorita', 'Description for Senorita'),
(66858, 'Minios', 'Description for Minios'),
(66859, 'Latundan', 'Description for Latundan'),
(66860, 'Señorita', 'Description for Señorita'),
(66861, 'Lakatan', 'Description for Lakatan');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `purchase_order_id` int(11) DEFAULT NULL,
  `quantity_in_stock` decimal(10,2) DEFAULT NULL,
  `receive_date` date DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `storage_location` varchar(255) DEFAULT NULL,
  `batch_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

CREATE TABLE `purchase_order` (
  `purchase_order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `quantity_ordered` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `order_status` varchar(255) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `status_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order`
--

INSERT INTO `purchase_order` (`purchase_order_id`, `user_id`, `supplier_id`, `order_date`, `quantity_ordered`, `total_cost`, `order_status`, `delivery_date`, `status_updated_at`) VALUES
(1, 102, 836, '2024-09-30', 5.00, 360.00, 'Order Shipped', '2024-10-01', '2024-09-30 20:16:40'),
(2, 102, 836, '2024-09-30', 10.00, 720.00, 'Order Loaded', '2024-10-02', '2024-09-30 16:34:40'),
(3, 102, 835, '2024-09-30', 2.00, 138.00, 'Order Placed', '2024-10-02', '2024-09-30 14:37:32'),
(4, 102, 836, '2024-09-30', 20.00, 1440.00, 'Order Loaded', '2024-10-02', '2024-09-30 16:34:42');

-- --------------------------------------------------------

--
-- Table structure for table `sales_transaction`
--

CREATE TABLE `sales_transaction` (
  `sales_transaction_id` int(11) NOT NULL,
  `tric_inventory_id` int(11) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `quantity_sold` decimal(10,2) DEFAULT NULL,
  `sold_price_per_kilo` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`supplier_id`, `supplier_name`, `contact_info`, `location`, `created_at`, `user_id`) VALUES
(831, 'Supplier Lakatan', '0998587458', 'Mindoro', '2024-09-11 18:45:01', NULL),
(832, 'Supplier Saba', '09965856325', 'Davao', '2024-09-11 18:52:09', NULL),
(833, 'Supplier Latundan', '09965785432', 'Bukidnon', '2024-09-24 11:44:19', NULL),
(834, 'Banana Trading', '09965852147', 'Davao', '2024-09-27 01:53:15', 103),
(835, 'Cheramie Trading', '09963584855', 'CDO', '2024-09-27 08:03:55', 104),
(836, 'South Trading', '0998565896', 'VIsayas', '2024-09-27 09:22:29', 105);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_banana`
--

CREATE TABLE `supplier_banana` (
  `supplier_id` int(11) NOT NULL,
  `banana_type_id` int(11) NOT NULL,
  `cost_per_unit` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_banana`
--

INSERT INTO `supplier_banana` (`supplier_id`, `banana_type_id`, `cost_per_unit`) VALUES
(104, 66854, 69.00),
(104, 66855, 66.00),
(835, 66851, 69.00),
(835, 66852, 20.00),
(835, 66853, 79.00),
(835, 66858, 100.00),
(835, 66859, 72.00),
(835, 66860, 69.00),
(836, 66861, 72.00);

-- --------------------------------------------------------

--
-- Table structure for table `tricycle`
--

CREATE TABLE `tricycle` (
  `tricycle_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tricycle_inventory`
--

CREATE TABLE `tricycle_inventory` (
  `tric_inventory_id` int(11) NOT NULL,
  `tricycle_id` int(11) DEFAULT NULL,
  `inventory_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `quantity_allocated` decimal(10,2) DEFAULT NULL,
  `selling_price_per_kilo` decimal(10,2) DEFAULT NULL,
  `date_allocated` date DEFAULT NULL,
  `allocated_unit_expiration` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `contact_info` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `name`, `role`, `contact_info`, `password`, `email`) VALUES
(101, 'Dan', 'Admin', '09959275657', '$2y$10$LKvZIdq41du57Ny7DGNzpefT0cxP5y9/Q.CdIM3MVcSZXyRp7tm4y', 'admin@gmail.com'),
(102, 'Mark', 'Staff', '09947143028', '$2y$10$cUcTj0su4le69knSIlVWR.k/VJvdcqxP24aLkP1Cj6reKDjHF0z5K', 'mark@gmail.com'),
(103, 'Banana Trading', 'Supplier', '09965852147', '$2y$10$BBsJamvxZz6EBN44ksGQyOeyyX75cAU2r1maiU9pRdyhYDajtq3dO', 'bananat@gmail.com'),
(104, 'Cheramie Trading', 'Supplier', '09963584855', '$2y$10$MYJWvLsWr/pzRYuP4ZcL.umy8YpkvW4pyzejvrEbVNb/bA1H/ml4q', 'cheramie@gmail.com'),
(105, 'South Trading', 'Supplier', '0998565896', '$2y$10$CeY1yusBbVX6iWIjZhHHyO5h.2aWEHrLe9mKE.as750hTrm3YeTHO', 'south@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `waste`
--

CREATE TABLE `waste` (
  `waste_id` int(11) NOT NULL,
  `sales_transaction_id` int(11) DEFAULT NULL,
  `waste_date` date DEFAULT NULL,
  `quantity_wasted` decimal(10,2) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banana_type`
--
ALTER TABLE `banana_type`
  ADD PRIMARY KEY (`banana_type_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `purchase_order_id` (`purchase_order_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD PRIMARY KEY (`purchase_order_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sales_transaction`
--
ALTER TABLE `sales_transaction`
  ADD PRIMARY KEY (`sales_transaction_id`),
  ADD KEY `tric_inventory_id` (`tric_inventory_id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `supplier_banana`
--
ALTER TABLE `supplier_banana`
  ADD PRIMARY KEY (`supplier_id`,`banana_type_id`),
  ADD KEY `banana_type_id` (`banana_type_id`);

--
-- Indexes for table `tricycle`
--
ALTER TABLE `tricycle`
  ADD PRIMARY KEY (`tricycle_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tricycle_inventory`
--
ALTER TABLE `tricycle_inventory`
  ADD PRIMARY KEY (`tric_inventory_id`),
  ADD KEY `tricycle_id` (`tricycle_id`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `waste`
--
ALTER TABLE `waste`
  ADD PRIMARY KEY (`waste_id`),
  ADD KEY `sales_transaction_id` (`sales_transaction_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banana_type`
--
ALTER TABLE `banana_type`
  MODIFY `banana_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66862;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `purchase_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sales_transaction`
--
ALTER TABLE `sales_transaction`
  MODIFY `sales_transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=837;

--
-- AUTO_INCREMENT for table `tricycle`
--
ALTER TABLE `tricycle`
  MODIFY `tricycle_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tricycle_inventory`
--
ALTER TABLE `tricycle_inventory`
  MODIFY `tric_inventory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `waste`
--
ALTER TABLE `waste`
  MODIFY `waste_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_order` (`purchase_order_id`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`),
  ADD CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD CONSTRAINT `purchase_order_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `sales_transaction`
--
ALTER TABLE `sales_transaction`
  ADD CONSTRAINT `sales_transaction_ibfk_1` FOREIGN KEY (`tric_inventory_id`) REFERENCES `tricycle_inventory` (`tric_inventory_id`);

--
-- Constraints for table `supplier`
--
ALTER TABLE `supplier`
  ADD CONSTRAINT `supplier_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `supplier_banana`
--
ALTER TABLE `supplier_banana`
  ADD CONSTRAINT `supplier_banana_ibfk_2` FOREIGN KEY (`banana_type_id`) REFERENCES `banana_type` (`banana_type_id`) ON DELETE CASCADE;

--
-- Constraints for table `tricycle`
--
ALTER TABLE `tricycle`
  ADD CONSTRAINT `tricycle_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `tricycle_inventory`
--
ALTER TABLE `tricycle_inventory`
  ADD CONSTRAINT `tricycle_inventory_ibfk_1` FOREIGN KEY (`tricycle_id`) REFERENCES `tricycle` (`tricycle_id`),
  ADD CONSTRAINT `tricycle_inventory_ibfk_2` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`inventory_id`);

--
-- Constraints for table `waste`
--
ALTER TABLE `waste`
  ADD CONSTRAINT `waste_ibfk_1` FOREIGN KEY (`sales_transaction_id`) REFERENCES `sales_transaction` (`sales_transaction_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
