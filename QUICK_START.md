# 🚀 QUICK START GUIDE - Disbursement Archive System

## What Changed?

The disbursement module no longer **permanently deletes** records. Instead, it uses an **archive system**:
- ❌ **Delete button** → ✅ **Archive button**
- Records move to a safe "Archived Disbursements" section
- Can be retrieved or permanently deleted later

---

## 📋 3-Step Installation

### Step 1️⃣: Run SQL Migration
**Location:** `db_migrations/APPLY_THIS_SQL.sql`

Copy and paste this SQL into your database management tool (phpMyAdmin, MySQL Workbench, etc.):

```sql
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

### Step 2️⃣: Verify Files Are Updated

Check these files exist and are updated:
- ✅ `pages/disbursement.php` - Modified (archive button)
- ✅ `api/disbursements_api.php` - Modified (archive logic)
- ✅ `pages/archived_disbursement.php` - **New** (view archived)
- ✅ `api/archived_disbursements_api.php` - **New** (archive API)
- ✅ `layout/adminLayout.php` - Modified (added sidebar link)

### Step 3️⃣: Test It!

1. Go to **Disbursement** page
2. Click the archive button (📦 icon) on any record
3. Confirm in the modal
4. Record disappears from Disbursement list
5. Go to **Archived Disbursements** in sidebar
6. See the archived record
7. Click **Retrieve** button to restore it

---

## 🎯 User Actions

### Active Disbursements Page
| Button | Icon | Action |
|--------|------|--------|
| Release | ✅ | Mark as Released (if Pending) |
| Edit | ✏️ | Edit disbursement details |
| View | 👁️ | View full details |
| Archive | 📦 | Move to archive |

### Archived Disbursements Page (NEW)
| Button | Icon | Action |
|--------|------|--------|
| View | 👁️ | View full details + archive info |
| Retrieve | ↩️ | Restore to active disbursements |
| Delete | 🗑️ | Permanently delete (no recovery!) |

---

## 📊 Data Flow

### When You Archive:
```
User clicks Archive on Active Page
    ↓
Record copied to archived_disbursements table
Original marked as is_archived = 1
Notification created
    ↓
Record hidden from Disbursement list
Record visible in Archived Disbursements
```

### When You Retrieve:
```
User clicks Retrieve on Archived Page
    ↓
Record re-inserted to disbursements table
is_archived = 0
Notification created
    ↓
Record visible in Disbursement list again
Record hidden from Archived Disbursements
```

---

## 🔍 Database Queries

### Check your data:

```sql
-- See all ACTIVE disbursements (not archived)
SELECT * FROM disbursements WHERE is_archived = 0;

-- See all ARCHIVED disbursements
SELECT * FROM archived_disbursements;

-- Count of archived records
SELECT COUNT(*) FROM archived_disbursements;

-- See which user archived a record
SELECT voucher_no, archived_by, archived_at FROM archived_disbursements;
```

---

## 🆘 Troubleshooting

| Problem | Solution |
|---------|----------|
| Archive button doesn't work | Check API URL in disbursement.php (line ~200) |
| Archived page shows no records | Check archived_disbursements table exists |
| Can't retrieve records | Check archived_disbursements_api.php permissions |
| Notifications not showing | Check notifications table exists |

---

## 🔄 Workflow Example

**Scenario:** You created disbursement VCH-005 by mistake

1. ✅ Go to **Disbursement** page
2. ✅ Find VCH-005, click archive button 📦
3. ✅ Confirm "Archive this record"
4. ✅ Record disappears from list
5. ✅ Go to **Archived Disbursements** (new sidebar link)
6. ✅ Find VCH-005 in archived list
7. **Option A:** Click retrieve ↩️ to bring it back
8. **Option B:** Click delete 🗑️ to permanently remove (careful!)

---

## 📁 File Locations

```
financial2/
├── pages/
│   ├── disbursement.php (MODIFIED)
│   └── archived_disbursement.php (NEW)
├── api/
│   ├── disbursements_api.php (MODIFIED)
│   └── archived_disbursements_api.php (NEW)
├── layout/
│   └── adminLayout.php (MODIFIED)
├── db_migrations/
│   ├── archive_disbursements_system.sql (info)
│   └── APPLY_THIS_SQL.sql (IMPORT THIS)
└── ARCHIVE_SYSTEM_IMPLEMENTATION.md (full docs)
```

---

## ✨ Key Features

✅ **Archive instead of delete** - Safe removal  
✅ **Retrieve from archive** - Restore anytime  
✅ **Permanent delete option** - For final cleanup  
✅ **Archive tracking** - Know who archived and when  
✅ **Filter by status** - Find records quickly  
✅ **Notifications** - Track all actions  
✅ **No data loss** - Full audit trail  

---

## 📝 Important Notes

1. **Voucher numbers are preserved** in archive
2. **Original creation date** stays the same
3. **Archive timestamp** shows when it was archived
4. **Cannot undo permanent delete** - be careful!
5. **All IDs remain unique** across both tables

---

## 🎓 Expected Behavior

### What Users See

**Before:**
- Disbursement page with Delete button
- Once deleted, record was gone forever

**After:**
- Disbursement page with Archive button
- Archived records move to new "Archived Disbursements" page
- Can retrieve or permanently delete later
- Full audit trail of who archived what and when

---

## ⚡ Quick Checklist

- [ ] SQL migration executed
- [ ] Files uploaded to server
- [ ] Sidebar shows "Archived Disbursements" link
- [ ] Archive button appears on disbursement page
- [ ] Test archive with a dummy record
- [ ] Test retrieve from archived page
- [ ] Check notifications are created
- [ ] Verify database tables have data

---

## 🆘 Need Help?

Check the detailed documentation:
📄 `ARCHIVE_SYSTEM_IMPLEMENTATION.md` - Full technical guide

---

**System Status:** ✅ Ready to Deploy

**Last Updated:** February 5, 2026
