<?php
// Simple migration script to add account_type and description columns to chart_of_accounts
// Usage: php migrate_chart_of_accounts.php (or open via browser if you prefer)

// Adjust path to db.php if necessary
require_once __DIR__ . '/../api/db.php';

function columnExists($conn, $table, $column) {
    $sql = "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return intval($res['cnt']) > 0;
}

$table = 'chart_of_accounts';
$added = [];

if (!columnExists($conn, $table, 'account_type')) {
    $sql = "ALTER TABLE `$table` ADD COLUMN `account_type` VARCHAR(100) NOT NULL DEFAULT 'Asset'";
    if ($conn->query($sql) === TRUE) {
        $added[] = 'account_type';
    } else {
        echo "Error adding account_type: " . $conn->error . PHP_EOL;
    }
} else {
    echo "account_type already exists\n";
}

if (!columnExists($conn, $table, 'description')) {
    $sql = "ALTER TABLE `$table` ADD COLUMN `description` TEXT DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        $added[] = 'description';
    } else {
        echo "Error adding description: " . $conn->error . PHP_EOL;
    }
} else {
    echo "description already exists\n";
}

if (count($added)) {
    echo "Added columns: " . implode(', ', $added) . PHP_EOL;
} else {
    echo "No changes made.\n";
}

echo "Migration complete.\n";
