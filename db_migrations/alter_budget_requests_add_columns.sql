ALTER TABLE budget_requests
  ADD COLUMN amount_limit DECIMAL(12,2) NULL AFTER amount,
  ADD COLUMN attendance_required VARCHAR(10) DEFAULT 'No' AFTER amount_limit,
  ADD COLUMN item_list TEXT NULL AFTER attendance_required,
  ADD COLUMN approval_required ENUM('No','Yes') DEFAULT 'No' AFTER item_list,
  ADD COLUMN requesting_account VARCHAR(255) NULL AFTER approval_required,
  ADD COLUMN approval_account VARCHAR(255) NULL AFTER requesting_account;
