-- ============================================================
-- ARCHIVE DISBURSEMENTS SYSTEM
-- This migration adds archive functionality to disbursements
-- ============================================================

-- 1. Create the archived_disbursements table
CREATE TABLE IF NOT EXISTS `archived_disbursements` (
  `id` int(11) NOT NULL,
  `voucher_no` varchar(50) NOT NULL,
  `vendor` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('Released','Pending') DEFAULT 'Pending',
  `disbursement_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_by` varchar(255) DEFAULT NULL,
  `archive_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_voucher_no` (`voucher_no`),
  KEY `idx_archived_at` (`archived_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Add is_archived flag to disbursements table (optional, but useful for soft deletes)
ALTER TABLE `disbursements` 
ADD COLUMN `is_archived` tinyint(1) NOT NULL DEFAULT 0 AFTER `created_at`,
ADD COLUMN `archived_at` timestamp NULL AFTER `is_archived`;

-- 3. Create index for faster queries on archived records
ALTER TABLE `disbursements`
ADD INDEX `idx_is_archived` (`is_archived`);

-- ============================================================
-- OPTIONAL: If you want to keep the old deleted records
-- You can restore them from archived_disbursements
-- Or you can run this to clean up permanent deletions:
-- DELETE FROM archived_disbursements WHERE archived_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
-- ============================================================
