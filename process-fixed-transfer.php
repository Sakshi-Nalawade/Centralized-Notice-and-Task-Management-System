<?php
/**
 * Process Fixed Asset Transfer — Handles employee transfer requests
 * Inserts selected fixed assets into fixed_asset_transfers table
 * Does NOT modify the original fixed_assets table
 */

include_once('./includes/auth-employee.php');
include_once('./includes/config.php');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location:fixed-asset-transfer.php');
    exit();
}

$branch = $_SESSION['branch_name'];

// Validate asset_ids
if (!isset($_POST['asset_ids']) || !is_array($_POST['asset_ids']) || count($_POST['asset_ids']) === 0) {
    header('location:fixed-asset-transfer.php?msg=no_assets');
    exit();
}

// Validate target branch
if (!isset($_POST['to_branch']) || empty(trim($_POST['to_branch']))) {
    header('location:fixed-asset-transfer.php?msg=no_branch');
    exit();
}

$to_branch = trim($_POST['to_branch']);

// Prevent transfer to same branch
if (strtolower(trim($branch)) === strtolower(trim($to_branch))) {
    header('location:fixed-asset-transfer.php?msg=same_branch');
    exit();
}

// ─── Ensure fixed_asset_transfers table exists (safety net) ───
$createTable = mysqli_query($con, "CREATE TABLE IF NOT EXISTS `fixed_asset_transfers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `asset_id` INT NOT NULL,
    `from_branch` VARCHAR(255) NOT NULL,
    `to_branch` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `product` VARCHAR(255) NOT NULL,
    `company` VARCHAR(255) DEFAULT NULL,
    `label` VARCHAR(255) DEFAULT NULL,
    `amount` DECIMAL(12,2) DEFAULT 0,
    `status` VARCHAR(100) DEFAULT NULL,
    `transfer_status` VARCHAR(50) DEFAULT 'Pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `approved_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if (!$createTable) {
    error_log("Fixed Transfer - Failed to create/verify table: " . mysqli_error($con));
    header('location:fixed-asset-transfer.php?msg=db_error');
    exit();
}

$asset_ids = $_POST['asset_ids'];

// Begin transaction
mysqli_begin_transaction($con);

try {
    $transferredCount = 0;
    $skippedPending   = 0;
    $skippedNotFound  = 0;
    $failedInsert     = 0;

    foreach ($asset_ids as $assetId) {
        $assetId = (int) $assetId;

        // Fetch asset details — verify it belongs to this branch
        $selectStmt = mysqli_prepare($con,
            "SELECT id, category, product, company, label, amount, status FROM fixed_assets WHERE id = ? AND branch = ?"
        );

        if (!$selectStmt) {
            error_log("Fixed Transfer - Failed to prepare SELECT: " . mysqli_error($con));
            $failedInsert++;
            continue;
        }

        mysqli_stmt_bind_param($selectStmt, "is", $assetId, $branch);
        mysqli_stmt_execute($selectStmt);
        $result = mysqli_stmt_get_result($selectStmt);
        $asset = mysqli_fetch_assoc($result);
        mysqli_stmt_close($selectStmt);

        if (!$asset) {
            $skippedNotFound++;
            continue;
        }

        // Check if this asset already has a pending transfer
        $checkStmt = mysqli_prepare($con,
            "SELECT id FROM fixed_asset_transfers WHERE asset_id = ? AND transfer_status = 'Pending'"
        );

        if (!$checkStmt) {
            error_log("Fixed Transfer - Failed to prepare CHECK: " . mysqli_error($con));
            $failedInsert++;
            continue;
        }

        mysqli_stmt_bind_param($checkStmt, "i", $assetId);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        $hasPending = mysqli_num_rows($checkResult) > 0;
        mysqli_stmt_close($checkStmt);

        if ($hasPending) {
            $skippedPending++;
            continue;
        }

        // Insert into fixed_asset_transfers
        $insertStmt = mysqli_prepare($con,
            "INSERT INTO fixed_asset_transfers (asset_id, from_branch, to_branch, category, product, company, label, amount, status, transfer_status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())"
        );

        if (!$insertStmt) {
            error_log("Fixed Transfer - Failed to prepare INSERT: " . mysqli_error($con));
            $failedInsert++;
            continue;
        }

        mysqli_stmt_bind_param($insertStmt, "issssssds",
            $asset['id'],
            $branch,
            $to_branch,
            $asset['category'],
            $asset['product'],
            $asset['company'],
            $asset['label'],
            $asset['amount'],
            $asset['status']
        );

        if (!mysqli_stmt_execute($insertStmt)) {
            error_log("Fixed Transfer - Failed to execute INSERT: " . mysqli_stmt_error($insertStmt));
            $failedInsert++;
        } else {
            $transferredCount++;

            // Update original asset status to 'Pending Transfer'
            $updateStatus = mysqli_prepare($con,
                "UPDATE fixed_assets SET status = 'Pending Transfer' WHERE id = ?"
            );
            if ($updateStatus) {
                mysqli_stmt_bind_param($updateStatus, "i", $assetId);
                mysqli_stmt_execute($updateStatus);
                mysqli_stmt_close($updateStatus);
            }
        }

        mysqli_stmt_close($insertStmt);
    }

    // ─── Build redirect based on results ───
    if ($transferredCount > 0) {
        mysqli_commit($con);

        if ($skippedPending > 0) {
            // Partial success — some transferred, some skipped
            header('location:fixed-asset-transfer.php?msg=partial&transferred=' . $transferredCount . '&skipped=' . $skippedPending);
        } else {
            // Full success
            header('location:fixed-asset-transfer.php?msg=transferred&count=' . $transferredCount);
        }
    } else {
        mysqli_rollback($con);

        // Determine specific failure reason
        if ($skippedPending > 0 && $skippedNotFound === 0 && $failedInsert === 0) {
            header('location:fixed-asset-transfer.php?msg=all_pending&count=' . $skippedPending);
        } elseif ($skippedNotFound > 0 && $skippedPending === 0 && $failedInsert === 0) {
            header('location:fixed-asset-transfer.php?msg=no_matching');
        } elseif ($failedInsert > 0) {
            header('location:fixed-asset-transfer.php?msg=insert_failed');
        } else {
            header('location:fixed-asset-transfer.php?msg=error');
        }
    }

} catch (Exception $e) {
    mysqli_rollback($con);
    error_log("Fixed Transfer exception: " . $e->getMessage());
    header('location:fixed-asset-transfer.php?msg=db_error');
}

exit();
?>
