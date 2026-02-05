-- ============================================================
-- DISBURSEMENT ARCHIVE SYSTEM - SQL MIGRATION SCRIPT
-- ============================================================
-- Paste this entire script into your database SQL editor
-- and execute it to set up the archive system
-- ============================================================

-- Step 1: Create the archived_disbursements table
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

-- Step 2: Add columns to disbursements table for archive tracking
ALTER TABLE `disbursements` 
ADD COLUMN `is_archived` tinyint(1) NOT NULL DEFAULT 0 AFTER `created_at`,
ADD COLUMN `archived_at` timestamp NULL AFTER `is_archived`;

-- Step 3: Create index for better query performance on archived flag
ALTER TABLE `disbursements`
ADD INDEX `idx_is_archived` (`is_archived`);

-- ============================================================
-- OPTIONAL CLEANUP SCRIPTS (Run these later if needed)
-- ============================================================

-- Option 1: View all archived records with archive date
-- SELECT * FROM archived_disbursements ORDER BY archived_at DESC;

-- Option 2: View count of archived records
-- SELECT COUNT(*) as archived_count FROM archived_disbursements;

-- Option 3: Delete archived records older than 2 years (runs periodically)
-- DELETE FROM archived_disbursements WHERE archived_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);

-- Option 4: View active disbursements (not archived)
-- SELECT * FROM disbursements WHERE is_archived = 0 ORDER BY id DESC;

-- Option 5: Re-activate all archived disbursements (CAREFUL!)
-- UPDATE disbursements SET is_archived = 0, archived_at = NULL WHERE is_archived = 1;

-- ============================================================
-- END OF MIGRATION SCRIPT
-- ============================================================
