-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 05:16 PM
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
-- Database: `capfinancial`
--

-- --------------------------------------------------------

--
-- Table structure for table `allocation`
--

CREATE TABLE `allocation` (
  `id` int(11) NOT NULL,
  `department` varchar(255) NOT NULL,
  `project` varchar(255) NOT NULL,
  `allocated` decimal(15,2) NOT NULL,
  `used` decimal(15,2) DEFAULT 0.00,
  `remaining` decimal(15,2) GENERATED ALWAYS AS (`allocated` - `used`) STORED,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allocation`
--

INSERT INTO `allocation` (`id`, `department`, `project`, `allocated`, `used`, `created_at`) VALUES
(1, 'Maintenance', 'Facility Upkeep and Repair Initiative', 30000.00, 0.00, '2025-10-04 17:55:14'),
(2, 'Staff Training', 'SkillCare Development Program', 25000.00, 0.00, '2025-10-04 17:55:31'),
(3, 'IT & Software', 'Digital Health Systems Upgrade', 60000.00, 0.00, '2025-10-04 17:55:39'),
(4, 'Utilities', 'SustainCare Resource Management Project', 95000.00, 0.00, '2025-10-04 17:55:47'),
(5, 'Pharmacy Supplies', 'MediStock Replenishment Program', 120000.00, 0.00, '2025-10-04 17:55:53'),
(6, 'Security', 'SecurityCheck Solutions', 50000.00, 0.00, '2025-10-04 18:33:30'),
(7, 'utilities', 'electricity', 300.00, 0.00, '2025-10-05 02:50:03'),
(8, 'Laboratory', 'Hospital', 1250000.00, 0.00, '2025-10-05 04:54:32'),
(9, 'CORE 1', 'SALARIES', 20000.00, 0.00, '2025-10-18 20:10:21'),
(10, 'hr1', '10ten', 20000.00, 20000.00, '2026-01-27 16:59:08'),
(11, 'IT dept', 'System', 100000.00, 10000.00, '2026-01-27 17:30:42'),
(12, 'virtual assistant', 'ui dex', 60000.00, 50000.00, '2026-01-27 17:57:03');

-- --------------------------------------------------------

--
-- Table structure for table `budget_requests`
--

CREATE TABLE `budget_requests` (
  `id` int(11) NOT NULL,
  `request_id` varchar(20) NOT NULL,
  `department` varchar(100) NOT NULL,
  `purpose` text NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `amount_limit` decimal(12,2) DEFAULT NULL,
  `attendance_required` varchar(10) DEFAULT 'No',
  `item_list` text DEFAULT NULL,
  `approval_required` enum('No','Yes') DEFAULT 'No',
  `requesting_account` varchar(255) DEFAULT NULL,
  `approval_account` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Paid','Overdue','Rejected') DEFAULT 'Pending',
  `request_date` date DEFAULT curdate(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_requests`
--

INSERT INTO `budget_requests` (`id`, `request_id`, `department`, `purpose`, `amount`, `amount_limit`, `attendance_required`, `item_list`, `approval_required`, `requesting_account`, `approval_account`, `status`, `request_date`, `created_at`) VALUES
(26, 'REQ-001', 'hr1', 'salaries', 20000.00, NULL, 'No', NULL, 'No', NULL, NULL, 'Approved', '2025-10-19', '2025-10-19 04:28:30'),
(27, 'REQ-002', 'IT dept', 'For IT Salaries', 100000.00, NULL, 'No', NULL, 'No', NULL, NULL, 'Approved', '2026-01-27', '2026-01-27 09:30:11'),
(28, 'REQ-003', 'virtual assistant', 'better ui', 60000.00, NULL, 'No', NULL, 'No', NULL, NULL, 'Approved', '2026-01-27', '2026-01-27 09:56:46'),
(29, 'REQ-004', 'IT', 'Test equipment', 10000.00, 12000.00, 'No', 'laptop, mouse', 'Yes', '1000 Cash', '2000 Expense', 'Approved', '2026-02-05', '2026-02-04 16:01:03');

-- --------------------------------------------------------

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE `chart_of_accounts` (
  `id` int(11) NOT NULL,
  `account_code` varchar(20) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `category` varchar(255) NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_type` varchar(100) NOT NULL DEFAULT 'Asset',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chart_of_accounts`
--

INSERT INTO `chart_of_accounts` (`id`, `account_code`, `account_name`, `category`, `balance`, `created_at`, `account_type`, `description`) VALUES
(1, '1000', 'Cash', 'Current Asset', 100000.00, '2025-10-03 10:06:48', 'Asset', 'Cash on hand or in bank'),
(2, '1010', 'Accounts Receivable', 'Current Asset', 50000.00, '2025-10-03 10:07:16', 'Asset', 'Money owed by customers'),
(3, '1200', 'Inventory', 'Current Asset', 70000.00, '2025-10-03 10:07:35', 'Asset', 'Goods available for sale\n'),
(4, '1500', 'Equipment', 'Fixed Asset', 100000.00, '2025-10-03 10:07:55', 'Asset', 'Office Equipment, machines, etc.'),
(5, '2000', 'Accounts Payable', 'Current Liability', 100000.00, '2025-10-03 10:08:22', 'Liability', 'Money owed to suppliers'),
(6, '2100', 'Accrued Expenses', 'Current Liability', 65000.00, '2025-10-03 10:08:48', 'Asset', 'Expenses incurred but no yet paid'),
(9, '3000', 'Common Stock', 'Owner\'s Equity', 500000.00, '2025-10-03 11:10:40', 'Equity', 'Shareholder capital'),
(10, '3100', 'Retained Earnings', 'Owner\'s Equity', 100000.00, '2025-10-03 11:12:08', 'Equity', 'Cumulative profits retained'),
(11, '4000', 'Sales Revenue', 'Revenue', 80000.00, '2025-10-03 11:12:27', 'Income', 'Income from sales'),
(12, '4100', 'Service Revenue', 'Revenue', 60000.00, '2025-10-03 11:12:41', 'Income', 'income from services provided'),
(13, '5000', 'Cost of Goods Solds', 'COGS', 55000.00, '2025-10-03 11:13:48', 'Expense', 'Direct costs of products sold'),
(14, '6000', 'Rent Expense', 'Operating Expense', 85000.00, '2025-10-03 11:15:08', 'Expense', 'Office or store rent'),
(15, '6100', 'Salaries Expense', 'Operating Expense', 45000.00, '2025-10-03 11:15:27', 'Expense', 'Employee wages and salaries'),
(16, '6200', 'Utilities Expense', 'Operating Expense', 75000.00, '2025-10-03 11:15:45', 'Expense', 'Electricity, water, etc.'),
(17, '7000', 'Depreciation Expense', 'Non-operating Expense', 75900.00, '2025-10-03 11:16:05', 'Expense', 'Reduction in asset value'),
(18, '3001', 'Cash', 'Operating Expense', 2000.00, '2025-10-04 08:10:04', 'Liability', 'Expense');

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE `collections` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `customer` varchar(255) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `status` enum('Paid','Pending','Overdue') NOT NULL DEFAULT 'Pending',
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collections`
--

INSERT INTO `collections` (`id`, `invoice_no`, `customer`, `department`, `amount`, `status`, `date`, `created_at`) VALUES
(1, 'INV-001', 'Maria Dela Cruz', 'Radiology', 4800.00, 'Paid', '2025-10-05', '2025-10-04 17:39:02'),
(2, 'INV-002', 'John Santos', 'Emergency Room', 2350.00, 'Paid', '2025-10-04', '2025-10-04 17:39:17'),
(28, 'INV-026', 'sda', 'ghjs', 123.00, 'Paid', '2026-01-27', '2026-01-27 08:58:21'),
(29, 'INV-004', 'christoper', 'hr 4', 3000.00, 'Paid', '2026-02-10', '2026-02-01 13:06:44');

-- --------------------------------------------------------

--
-- Table structure for table `disbursements`
--

CREATE TABLE `disbursements` (
  `id` int(11) NOT NULL,
  `voucher_no` varchar(50) NOT NULL,
  `vendor` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('Released','Pending') DEFAULT 'Pending',
  `disbursement_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disbursements`
--

INSERT INTO `disbursements` (`id`, `voucher_no`, `vendor`, `category`, `amount`, `status`, `disbursement_date`, `created_at`) VALUES
(8, 'VCH-001', 'maria', 'Supplies', 0.00, 'Released', '2026-01-27', '2026-01-27 09:03:14'),
(11, 'VCH-002', 'josh', 'Supplies', 4000.00, 'Released', '2026-01-27', '2026-01-27 09:20:09'),
(13, 'VCH-004', 'LOVE', 'Salaries', 1000.00, 'Released', '2026-01-27', '2026-01-27 09:52:19');

-- --------------------------------------------------------

--
-- Table structure for table `journal_entries`
--

CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `account` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `debit` decimal(15,2) DEFAULT 0.00,
  `source_module` varchar(50) DEFAULT NULL,
  `reference_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `journal_entries`
--

INSERT INTO `journal_entries` (`id`, `entry_date`, `account`, `description`, `amount`, `credit`, `debit`, `source_module`, `reference_id`) VALUES
(1, '2025-10-04', 'Accounts Receivable', 'Payment approved for Invoice #INV-002 from John Santos', 0.00, 2350.00, 0.00, 'collections', 'INV-002'),
(2, '2025-10-04', 'Accounts Receivable', 'Payment approved for Invoice #INV-003 from Angela Reyes', 0.00, 1750.00, 0.00, 'collections', 'INV-003'),
(3, '2025-10-04', 'Accounts Receivable', 'Payment approved for Invoice #INV-004 from Roberto Garcia', 0.00, 6200.00, 0.00, 'collections', 'INV-004'),
(4, '2025-10-04', 'Accounts Receivable', 'Payment approved for Invoice #INV-007 from Patrick Lim', 0.00, 12800.00, 0.00, 'collections', 'INV-007'),
(5, '2025-10-04', 'Accounts Receivable', 'Payment approved for Invoice #INV-008 from Michelle Tan', 0.00, 3600.00, 0.00, 'collections', 'INV-008'),
(8, '2025-10-04', 'Accounts Payable', 'Payment approved for vendor AquaPure Utilities', 0.00, 0.00, 63800.00, 'Payments', '4'),
(10, '2025-10-04', 'Accounts Payable', 'Payment approved for vendor PharmaPlus Distributors', 0.00, 0.00, 97250.00, 'Payments', '5'),
(19, '2025-10-04', 'Disbursement', 'Disbursement paid to vendor HealthTech Solutions', 0.00, 0.00, 120000.00, 'Disbursements', NULL),
(20, '2025-10-04', 'Disbursement', 'Disbursement paid to vendor AquaPure Utilities', 0.00, 0.00, 63800.00, 'Disbursements', NULL),
(21, '2025-10-04', 'Disbursement', 'Disbursement paid to vendor PharmaPlus Distributors', 0.00, 0.00, 97250.00, 'Disbursements', NULL),
(22, '2025-10-04', 'Accounts Receivable', 'Payment approved for Invoice #INV-010 from Jennylyn Ramos', 0.00, 1250.00, 0.00, 'collections', 'INV-010'),
(23, '2025-10-05', 'Accounts Receivable', 'Payment approved for Invoice #INV-011 from mina', 0.00, 1000.00, 0.00, 'collections', 'INV-011'),
(24, '2025-10-05', 'Accounts Receivable', 'Payment approved for Invoice #INV-009 from Daniel Cruz', 0.00, 5400.00, 0.00, 'collections', 'INV-009'),
(25, '2025-10-05', 'Accounts Receivable', 'Payment approved for Invoice #INV-006 from Carlos Fernandez', 0.00, 2950.00, 0.00, 'collections', 'INV-006'),
(26, '2025-10-05', 'Accounts Receivable', 'Payment approved for Invoice #INV-005 from Liza Mendoza', 0.00, 3100.00, 0.00, 'collections', 'INV-005'),
(27, '2025-10-05', 'Accounts Receivable', 'Payment approved for Invoice #INV-001 from Maria Dela Cruz', 0.00, 4800.00, 0.00, 'collections', 'INV-001'),
(28, '2025-10-05', 'Accounts Receivable', 'Payment approved for Invoice #INV-012 from cathy', 0.00, 300.00, 0.00, 'collections', 'INV-012'),
(29, '2025-10-05', 'Accounts Payable', 'Payment approved for vendor HealthTech Solutions', 0.00, 0.00, 120000.00, 'Payments', '3'),
(30, '2025-10-05', 'Accounts Receivable', 'Payment approved for Invoice #INV-013 from Daniel Padilla', 0.00, 1205000.00, 0.00, 'collections', 'INV-013'),
(31, '2025-10-05', 'Accounts Payable', 'Payment approved for vendor Bestlink', 0.00, 0.00, 5000000.00, 'Payments', '7'),
(32, '2025-10-05', 'Accounts Receivable', 'Payment approved for Invoice #INV-014 from hdjsj', 0.00, 1000.00, 0.00, 'collections', 'INV-014'),
(33, '2025-10-07', 'Accounts Receivable', 'Payment approved for Invoice #INV-015 from sana', 0.00, 100000.00, 0.00, 'collections', 'INV-015'),
(34, '2025-10-13', 'Accounts Receivable', 'Payment approved for Invoice #INV-016 from mina', 0.00, 151152.00, 0.00, 'collections', 'INV-016'),
(35, '2025-10-13', 'Accounts Payable', 'Payment approved for vendor HealthTech Solutions', 0.00, 0.00, 1000.00, 'Payments', '8'),
(36, '2025-10-13', 'Accounts Receivable', 'Payment approved for Invoice #INV-017 from awd', 0.00, 1000.00, 0.00, 'collections', 'INV-017'),
(37, '2025-10-18', 'Accounts Payable', 'Payment approved for vendor maria', 0.00, 0.00, 0.00, 'Payments', '9'),
(38, '2026-01-27', 'Accounts Receivable', 'Payment approved for Invoice #INV-025 from irings', 0.00, 1000.00, 0.00, 'collections', 'INV-025'),
(39, '2026-01-27', 'Accounts Payable', 'Payment approved for vendor LOVE', 0.00, 0.00, 1000.00, 'Payments', '11'),
(40, '2026-01-27', 'Disbursement', 'Disbursement paid to vendor maria', 0.00, 0.00, 0.00, 'Disbursements', NULL),
(41, '2026-01-27', 'Accounts Payable', 'Payment approved for vendor LOVE', 0.00, 0.00, 1000.00, 'Payments', '11'),
(42, '2026-01-27', 'Disbursement', 'Disbursement paid to vendor josh', 0.00, 0.00, 4000.00, 'Disbursements', NULL),
(43, '2026-01-27', 'Accounts Receivable', 'Payment approved for Invoice #INV-026 from sda', 0.00, 123.00, 0.00, 'collections', 'INV-026'),
(44, '2026-02-01', 'Disbursement', 'Disbursement paid to vendor LOVE', 0.00, 0.00, 1000.00, 'Disbursements', NULL),
(45, '2026-02-01', 'Accounts Receivable', 'Payment approved for Invoice #INV-021 from semin', 0.00, 1999.00, 0.00, 'collections', 'INV-021'),
(46, '2026-02-01', 'Accounts Payable', 'Payment approved for vendor cath', 0.00, 0.00, 650.00, 'Payments', '14'),
(47, '2026-02-01', 'Accounts Receivable', 'Payment approved for Invoice #INV-004 from christoper', 0.00, 3000.00, 0.00, 'collections', 'INV-004');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `module`, `record_id`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 'collections', 2, 'Collection #INV-002 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-002', 0, '2025-10-04 17:41:34'),
(2, 'collections', 3, 'Collection #INV-003 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-003', 0, '2025-10-04 17:41:35'),
(3, 'collections', 4, 'Collection #INV-004 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-004', 0, '2025-10-04 17:41:37'),
(4, 'collections', 7, 'Collection #INV-007 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-007', 0, '2025-10-04 17:41:39'),
(5, 'collections', 8, 'Collection #INV-008 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-008', 0, '2025-10-04 17:41:40'),
(6, 'collections', 11, 'Collection #INV-011 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-011', 0, '2025-10-04 18:32:30'),
(7, 'collections', 10, 'Collection #INV-010 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-010', 0, '2025-10-04 21:00:18'),
(8, 'collections', 13, 'Collection #INV-011 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-011', 0, '2025-10-05 01:34:03'),
(9, 'collections', 9, 'Collection #INV-009 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-009', 0, '2025-10-05 02:21:29'),
(10, 'collections', 6, 'Collection #INV-006 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-006', 0, '2025-10-05 02:21:33'),
(11, 'collections', 5, 'Collection #INV-005 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-005', 0, '2025-10-05 02:21:37'),
(12, 'collections', 1, 'Collection #INV-001 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-001', 0, '2025-10-05 02:34:38'),
(13, 'collections', 14, 'Collection #INV-012 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-012', 0, '2025-10-05 02:45:01'),
(14, 'collections', 15, 'Collection #INV-013 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-013', 0, '2025-10-05 04:57:51'),
(15, 'collections', 16, 'Collection #INV-014 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-014', 0, '2025-10-05 10:37:50'),
(16, 'collections', 17, 'Collection #INV-015 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-015', 0, '2025-10-07 04:35:32'),
(17, 'collections', 18, 'Collection #INV-016 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-016', 0, '2025-10-13 04:05:27'),
(18, 'collections', 19, 'Collection #INV-017 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-017', 0, '2025-10-13 17:40:04'),
(19, 'collections', 27, 'Collection #INV-025 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-025', 0, '2026-01-27 08:31:23'),
(20, 'collections', 28, 'Collection #INV-026 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-026', 0, '2026-01-27 09:45:10'),
(21, 'collections', 23, 'Collection #INV-021 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-021', 0, '2026-02-01 08:43:13'),
(22, 'collections', 29, 'Collection #INV-004 updated. Status: Paid', 'sales_invoices.php?invoice_no=INV-004', 0, '2026-02-01 13:07:17');

-- --------------------------------------------------------

--
-- Table structure for table `otp_sessions`
--

CREATE TABLE `otp_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` int(11) NOT NULL,
  `is_used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_sessions`
--

INSERT INTO `otp_sessions` (`id`, `user_id`, `email`, `otp_code`, `is_used`, `created_at`, `expires_at`) VALUES
(1, 6, '03.markjohn.30@gmail.com', 631739, 1, '2026-02-04 15:48:00', '2026-02-04 23:53:00'),
(2, 20, 'emjey.estorba.02@gmail.com', 730411, 1, '2026-02-04 16:14:58', '2026-02-05 00:19:58');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_id` varchar(20) NOT NULL,
  `vendor` varchar(100) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_id`, `vendor`, `payment_date`, `amount`, `status`, `created_at`) VALUES
(9, 'PAY-001', 'maria', '2025-10-19', 0.00, 'Completed', '2025-10-18 19:37:27'),
(10, 'PAY-002', 'josh', '2025-10-19', 4000.00, 'Completed', '2025-10-18 19:37:49'),
(11, 'PAY-003', 'LOVE', '2025-10-19', 1000.00, 'Completed', '2025-10-18 20:13:09'),
(14, 'PAY-004', 'cath', '2026-02-02', 650.00, 'Completed', '2026-02-01 09:55:23');

-- --------------------------------------------------------

--
-- Table structure for table `planning`
--

CREATE TABLE `planning` (
  `id` int(11) NOT NULL,
  `request_id` varchar(20) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `planning`
--

INSERT INTO `planning` (`id`, `request_id`, `department`, `purpose`, `amount`, `approved_at`) VALUES
(1, 'REQ-009', 'Utilities', 'Electricity and water bill allocation for October', 95000.00, '2025-10-04 17:51:45'),
(2, 'REQ-007', 'Housekeeping', 'Purchase of cleaning and sanitation materials', 35000.00, '2025-10-04 17:52:43'),
(3, 'REQ-006', 'Pharmacy Supplies', 'Restocking essential medicines and antibiotics', 120000.00, '2025-10-04 17:53:08'),
(4, 'REQ-001', 'Maintenance', 'Air conditioning repair in patient wards', 45000.00, '2025-10-04 17:53:29'),
(5, 'REQ-001', 'Maintenance', 'Air conditioning repair in patient wards', 45000.00, '2025-10-04 17:55:14'),
(6, 'REQ-004', 'Staff Training', 'Seminar on infection control and patient safety', 25000.00, '2025-10-04 17:55:31'),
(7, 'REQ-005', 'IT & Software', 'Upgrade of hospital management system licenses', 60000.00, '2025-10-04 17:55:39'),
(8, 'REQ-009', 'Utilities', 'Electricity and water bill allocation for October', 95000.00, '2025-10-04 17:55:47'),
(9, 'REQ-006', 'Pharmacy Supplies', 'Restocking essential medicines and antibiotics', 120000.00, '2025-10-04 17:55:53'),
(10, 'REQ-010', 'Security', 'Installation of additional CCTV cameras', 50000.00, '2025-10-04 18:33:30'),
(11, 'REQ-011', 'utilities', 'electricity', 100.00, '2025-10-05 02:50:03'),
(12, 'REQ-012', 'Laboratory', 'Equipment', 1250000.00, '2025-10-05 04:54:33'),
(13, 'REQ-007', 'Housekeeping', 'Purchase of cleaning and sanitation materials', 35000.00, '2025-10-18 19:50:38'),
(14, 'REQ-012', 'HR3', 'SALARIES', 10000.00, '2025-10-18 20:08:41'),
(15, 'REQ-013', 'CORE 1', 'MAINTENANCE', 20000.00, '2025-10-19 01:35:47'),
(16, 'REQ-015', 'hr 2', 'salaries', 20000.00, '2025-10-19 03:19:42'),
(17, 'REQ-016', 'hr 2', 'salaries', 20000.00, '2025-10-19 03:20:26'),
(18, 'REQ-017', 'core1', 'salaries', 10000.00, '2025-10-19 03:22:01'),
(19, 'REQ-017', 'core1', 'salaries', 1234.00, '2025-10-19 03:24:03'),
(20, 'REQ-013', 'dsaddsad', 'dsadsad', 123.00, '2025-10-19 03:26:20'),
(21, 'REQ-001', 'hr1', 'salaries', 20000.00, '2026-01-27 16:59:08'),
(22, 'REQ-002', 'IT dept', 'For IT Salaries', 100000.00, '2026-01-27 17:30:42'),
(23, 'REQ-003', 'virtual assistant', 'better ui', 60000.00, '2026-01-27 17:57:04'),
(24, 'REQ-004', 'IT', 'Test equipment', 10000.00, '2026-02-05 00:04:01'),
(25, 'REQ-003', 'virtual assistant', 'better ui', 60000.00, '2026-02-05 00:04:05'),
(26, 'REQ-001', 'hr1', 'salaries', 20000.00, '2026-02-05 00:04:27'),
(27, 'REQ-001', 'hr1', 'salaries', 20000.00, '2026-02-05 00:14:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `account_type` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `account_type`, `created_at`) VALUES
(4, 'lancess', 'sanchezlando333@gmail.com', '$2y$12$z/juAEbbQ6VklBj1rNx9s.ilJSPFEwN7IY/L4lKcXz2EtYoqdn3I2', 0, '2025-10-02 20:46:06'),
(5, 'CHUCHAY', 'Chuchay@gmail.com', '$2y$12$7SYsn.MmxEcXhAwAz4BiHuN0iJlnwM5xjqckZ.yhafkqj.6nSL5V6', 0, '2025-10-03 03:12:40'),
(6, 'Estorba', '03.markjohn.30@gmail.com', '$2y$10$E/LTsCIkcMy.B3U7UxgyNeRIQ21s4yuZVBNT9AgB0q9WybHA4i.ii', 1, '2025-10-03 19:03:03'),
(7, 'Bini', 'alfaismacawi@gmail.com', '$2y$10$xF23YYX5t6ZuWPO5rTExb.msEZvrOeRSPa/VpiHqvc7FwMLhguypC', 0, '2025-10-04 06:25:24'),
(8, 'mina', 'beltranmarie949@gmail.com', '$2y$10$Z74xBbB3KIhC7UXLlo4ifOyOR1F9Dn2943P7Km0M9I749NC60v62O', 0, '2025-10-04 10:05:26'),
(9, 'yasuo', 'yasuo@gmail.com', '$2y$10$2ai8KWeabtYoq/sDOVR1Dugfo6JjiBg4Uub6zx300LVvG9N376gS6', 0, '2025-10-04 13:04:51'),
(10, 'junerdy', 'cjunerdy@gmail.com', '$2y$10$P4AVSIefMVOnxWHSuPtbwex45KuQQLdVKXzYnnzW0UCM3ApgAC35G', 0, '2025-10-04 13:05:52'),
(11, 'reyn', 'dreynhshshs@gmail.com', '$2y$10$CLl4WhujgHhEjkMmSZ2qiezJ4b2qiu2SmCL2GxaUKtcJnAVWEZIV.', 0, '2025-10-04 17:12:53'),
(12, 'Daga', 'dagacosta577@gmail.com', '$2y$10$RGCepHr1OGGvYah.i0zsaOVIZD6vjntSC.RofJpc5zu69N4ttCWRW', 0, '2025-10-04 23:24:07'),
(13, 'Daga', 'dagacosta5777@gmail.com', '$2y$10$tJ0zdmQN1YkFop6.YXL92OSyMi5q.GWyP4OUvSp42./xh/rUNjGRO', 0, '2025-10-04 23:26:41'),
(14, 'Chayy', 'chandriaclove18@gmail.com', '$2y$10$VcXtyvmksBMQdsHPPJnsUuZaZAZMhFWOmeSm4Uktd1dNt7Uz5ihGa', 0, '2025-10-05 04:51:37'),
(15, 'rain', 'cruzzzandre99@gmail.com', '$2y$10$7csQ/hMtZ44LrcR1GPdhYOmrOQmH/E8URZR1Q0g0fqNhdeNOAiBM6', 0, '2025-10-20 06:14:52'),
(16, 'irings26263', 'iraymart26@gmail.com', '$2y$10$PrxYaU32BCvGPRFlC7LnqejYSRmDgs.z1RGM4Vk0HW34zpDwrPvF2', 0, '2026-01-27 07:39:38'),
(17, 'nicq', 'danicatimtim14@gmail.com', '$2y$10$BkTlVupHB2QxeUJeFc6.wuBji.XXRgA/6DS1c4inBTpysmmC6XweW', 0, '2026-01-30 09:58:57'),
(18, 'nica', 'danicatimtim9@gmail.com', '$2y$10$LEtzw7vHNubf0BWpCLRxi.cvsyHnY4IQCvHTUIx5SaFKu2X9V5ZyW', 0, '2026-01-30 10:03:11'),
(19, 'greggyboy', 'greggyvillas2002@gmail.com', '$2y$10$rDVCeVepRI/dlLueRLarf.7G4JG47KLhPV9s9pVUKbLJDwvtts4Uy', 0, '2026-02-01 12:59:50'),
(20, 'Mark John', 'emjey.estorba.02@gmail.com', '$2y$10$E/LTsCIkcMy.B3U7UxgyNeRIQ21s4yuZVBNT9AgB0q9WybHA4i.ii', 0, '2026-02-04 16:14:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allocation`
--
ALTER TABLE `allocation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `budget_requests`
--
ALTER TABLE `budget_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`);

--
-- Indexes for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_code` (`account_code`);

--
-- Indexes for table `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_no` (`voucher_no`);

--
-- Indexes for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp_sessions`
--
ALTER TABLE `otp_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_otp_code` (`otp_code`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_id` (`payment_id`);

--
-- Indexes for table `planning`
--
ALTER TABLE `planning`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `allocation`
--
ALTER TABLE `allocation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `budget_requests`
--
ALTER TABLE `budget_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `collections`
--
ALTER TABLE `collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `disbursements`
--
ALTER TABLE `disbursements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `journal_entries`
--
ALTER TABLE `journal_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `otp_sessions`
--
ALTER TABLE `otp_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `planning`
--
ALTER TABLE `planning`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `otp_sessions`
--
ALTER TABLE `otp_sessions`
  ADD CONSTRAINT `otp_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
