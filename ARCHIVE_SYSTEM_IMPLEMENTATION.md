# Disbursement Archive System - Implementation Summary

## 📋 Overview
The disbursement system has been upgraded to use an **archive-based** approach instead of permanent deletion. When a disbursement is archived, it's moved to a separate archive table and can be retrieved later if needed.

---

## 🔧 Architecture

### Process Flow:
1. **Active Disbursements** (disbursement.php)
   - Users view and manage active disbursements
   - Delete button → **Archive button** (archive icon)
   - When archived: Records move to `archived_disbursements` table
   
2. **Archived Disbursements** (archived_disbursement.php) - NEW MODULE
   - View all archived records with timestamps
   - **Retrieve** button → Restores to active disbursements
   - **Permanent Delete** button → Removes from archive (irreversible)

---

## 📂 Files Modified & Created

### 1. **Database Migration** ✅
**File:** `db_migrations/archive_disbursements_system.sql`
- Creates `archived_disbursements` table
- Adds `is_archived` and `archived_at` columns to `disbursements` table
- Creates indexes for performance

### 2. **Disbursement Page** ✅
**File:** `pages/disbursement.php`
- Replaced delete button with **archive button**
- Updated JavaScript function from `deleteDisbursement()` to `archiveDisbursement()`
- Archive action sends data to API with `action: "archive"`

### 3. **Disbursement API** ✅
**File:** `api/disbursements_api.php`
- **POST request:** Added archive action handler
  - Copies record to `archived_disbursements` table
  - Marks original as `is_archived = 1`
  - Creates notification for audit trail
- **DELETE request:** Updated to handle retrieval from archive

### 4. **Archived Disbursements Module** ✅ (NEW)
**File:** `pages/archived_disbursement.php`
- New page showing all archived records
- Features:
  - **View** - See full details including archive date/reason
  - **Retrieve** - Restore to active disbursements
  - **Permanent Delete** - Remove from archive (with confirmation)
  - Filter by status

### 5. **Archived Disbursements API** ✅ (NEW)
**File:** `api/archived_disbursements_api.php`
- **GET:** Fetch all/single archived records
- **POST:** Handle retrieve action
- **DELETE:** Permanently delete archived records

### 6. **Admin Layout Navigation** ✅
**File:** `layout/adminLayout.php`
- Added sidebar link to "Archived Disbursements"
- Archive icon: `<i class='bx bx-archive'></i>`
- Automatic highlight when on archived page

---

## 🗄️ Database Schema

### New Table: `archived_disbursements`
```sql
CREATE TABLE `archived_disbursements` (
  `id` int(11) PRIMARY KEY,
  `voucher_no` varchar(50),
  `vendor` varchar(255),
  `category` varchar(100),
  `amount` decimal(12,2),
  `status` enum('Released','Pending'),
  `disbursement_date` date,
  `created_at` timestamp,
  `archived_at` timestamp (when archived),
  `archived_by` varchar(255) (user who archived),
  `archive_reason` text (optional reason)
)
```

### Updated Table: `disbursements`
```sql
ALTER TABLE disbursements ADD COLUMN:
- is_archived tinyint(1) DEFAULT 0
- archived_at timestamp NULL
```

---

## 🚀 Implementation Steps

### Step 1: Import SQL Migration
Copy and run this SQL in your database:

```sql
-- ============================================================
-- ARCHIVE DISBURSEMENTS SYSTEM MIGRATION
-- ============================================================

-- Create the archived_disbursements table
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

-- Add columns to disbursements table
ALTER TABLE `disbursements` 
ADD COLUMN `is_archived` tinyint(1) NOT NULL DEFAULT 0 AFTER `created_at`,
ADD COLUMN `archived_at` timestamp NULL AFTER `is_archived`,
ADD INDEX `idx_is_archived` (`is_archived`);
```

### Step 2: Files to Deploy
Upload/modify these files in your project:
1. ✅ `pages/disbursement.php` - Modified
2. ✅ `api/disbursements_api.php` - Modified
3. ✅ `pages/archived_disbursement.php` - New
4. ✅ `api/archived_disbursements_api.php` - New
5. ✅ `layout/adminLayout.php` - Modified

### Step 3: Test the Flow
1. Go to **Disbursement** page
2. Click archive button on any record
3. Confirm the modal
4. Record moves to **Archived Disbursements**
5. Click retrieve to restore or delete permanently

---

## 🔄 Data Flow Diagrams

### Archive Process:
```
User clicks Archive Button
    ↓
JavaScript function: archiveDisbursement(id)
    ↓
POST /api/disbursements_api.php?action=archive
    ↓
Copy record to archived_disbursements table
Mark original as is_archived=1, archived_at=NOW()
Create notification
    ↓
Success: Record no longer visible in active list
Record visible in archived list
```

### Retrieve Process:
```
User clicks Retrieve Button (on archived page)
    ↓
JavaScript function: openRetrieveModal(id)
    ↓
POST /api/archived_disbursements_api.php?action=retrieve
    ↓
Re-insert into disbursements table with is_archived=0
Create notification
    ↓
Success: Record moves back to active disbursements
Record removed from archived list
```

### Permanent Delete Process:
```
User clicks Delete Button (on archived page)
    ↓
JavaScript function: openDeleteModal(id)
    ↓
DELETE /api/archived_disbursements_api.php
    ↓
DELETE FROM archived_disbursements WHERE id=?
    ↓
Complete removal - IRREVERSIBLE
```

---

## 📊 Key Features

### Active Disbursements Page
- ✅ Add new disbursement
- ✅ Edit disbursement
- ✅ View details
- ✅ Release disbursement
- ✅ **Archive** (new) - Instead of delete

### Archived Disbursements Page (NEW)
- ✅ View all archived records
- ✅ Filter by status
- ✅ View full details with archive metadata
- ✅ Retrieve to restore to active
- ✅ Permanent delete from archive
- ✅ Archive date and archived by tracking

---

## 🔐 Security & Audit Trail

The system maintains:
- **Archive timestamp** - When record was archived
- **Archived by** - User who archived the record
- **Archive reason** - Optional reason for archiving
- **Created at** - Original creation date (preserved)
- **Notification log** - All actions logged in notifications table

---

## 📝 SQL Query Examples

### View all active disbursements (not archived):
```sql
SELECT * FROM disbursements WHERE is_archived = 0 ORDER BY id DESC;
```

### View all archived disbursements:
```sql
SELECT * FROM archived_disbursements ORDER BY archived_at DESC;
```

### Get single disbursement with archive info:
```sql
SELECT d.*, a.archived_at, a.archived_by 
FROM disbursements d 
LEFT JOIN archived_disbursements a ON d.id = a.id 
WHERE d.id = ?;
```

### Cleanup old archived records (optional):
```sql
DELETE FROM archived_disbursements 
WHERE archived_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);
```

---

## 🐛 Testing Checklist

- [ ] Database migration executed successfully
- [ ] New "Archived Disbursements" link appears in sidebar
- [ ] Delete button changed to archive button on disbursement page
- [ ] Archive creates notification
- [ ] Archived records appear in archived page
- [ ] Retrieve button restores record to active
- [ ] Permanent delete removes from archive
- [ ] Filter by status works on archived page
- [ ] View details shows archive metadata
- [ ] No errors in PHP error log

---

## 🔗 API Endpoints

### Disbursements API
- `GET /api/disbursements_api.php` - Get all active disbursements
- `GET /api/disbursements_api.php?id=X` - Get single disbursement
- `POST /api/disbursements_api.php` - Create new disbursement
- `PUT /api/disbursements_api.php` - Update disbursement
- `POST /api/disbursements_api.php` (with action=archive) - Archive disbursement
- `DELETE /api/disbursements_api.php` - Delete disbursement

### Archived Disbursements API (NEW)
- `GET /api/archived_disbursements_api.php` - Get all archived records
- `GET /api/archived_disbursements_api.php?id=X` - Get single archived record
- `POST /api/archived_disbursements_api.php` (with action=retrieve) - Retrieve from archive
- `DELETE /api/archived_disbursements_api.php` - Permanently delete from archive

---

## 💾 Backup Recommendation

Before executing the migration:
1. Export current `disbursements` table
2. Backup entire database
3. Then run migration

This ensures you can rollback if needed.

---

## 📞 Support Notes

- All archived records retain original ID for easy tracking
- Voucher numbers are preserved in archive
- No data loss - only moved to archive table
- Can restore from archive at any time until permanently deleted
- Journal entries remain linked by reference_id if they exist

---

**Migration File Location:**
`db_migrations/archive_disbursements_system.sql`

**Last Updated:** February 5, 2026
