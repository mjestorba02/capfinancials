-- Migration: add account_type and description to chart_of_accounts
-- Safe for MySQL 8+ (uses ADD COLUMN IF NOT EXISTS)

ALTER TABLE `chart_of_accounts`
  ADD COLUMN IF NOT EXISTS `account_type` VARCHAR(100) NOT NULL DEFAULT 'Asset',
  ADD COLUMN IF NOT EXISTS `description` TEXT DEFAULT NULL;

-- If your MySQL version does not support ADD COLUMN IF NOT EXISTS,
-- use the PHP migration script included in the same folder.
