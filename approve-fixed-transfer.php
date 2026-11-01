<?php
/**
 * Approve/Reject Fixed Asset Transfer — Admin-only handler
 * Approve: Keeps original asset with "Transferred" status + creates copy in new branch
 * Reject: Reverts original asset status from transfer record
 */

include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location:fixed-asset-approval.php');
    exit();
}

// Get action type
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$transferId = isset($_POST['transfer_id']) ? (int) $_POST['transfer_id'] : 0;
$returnBranch = isset($_POST['branch']) ? trim($_POST['branch']) : '';

if ($transferId <= 0 || !in_array($action, ['approve', 'reject'])) {
    header('location:fixed-asset-approval.php?msg=invalid&branch=' . urlencode($returnBranch));
    exit();
}

// ─── APPROVE ───
if ($action === 'approve') {
    $assetId = isset($_POST['asset_id']) ? (int) $_POST['asset_id'] : 0;

    if ($assetId <= 0) {
        header('location:fixed-asset-approval.php?msg=invalid&branch=' . urlencode($returnBranch));
        exit();
    }

    // Fetch transfer record to get to_branch
    $fetchStmt = mysqli_prepare($con,
        "SELECT id, asset_id, to_branch FROM fixed_asset_transfers WHERE id = ? AND transfer_status = 'Pending'"
    );
    mysqli_stmt_bind_param($fetchStmt, "i", $transferId);
    mysqli_stmt_execute($fetchStmt);
    $fetchResult = mysqli_stmt_get_result($fetchStmt);
    $transfer = mysqli_fetch_assoc($fetchResult);
    mysqli_stmt_close($fetchStmt);

    if (!$transfer) {
        header('location:fixed-asset-approval.php?msg=invalid&branch=' . urlencode($returnBranch));
        exit();
    }

    // Fetch full original asset details for creating copy in destination branch
    $assetStmt = mysqli_prepare($con,
        "SELECT id, date, branch, category, product, company, rate, quantity, amount, serial_no, label, status FROM fixed_assets WHERE id = ?"
    );
    mysqli_stmt_bind_param($assetStmt, "i", $transfer['asset_id']);
    mysqli_stmt_execute($assetStmt);
    $assetResult = mysqli_stmt_get_result($assetStmt);
    $originalAsset = mysqli_fetch_assoc($assetResult);
    mysqli_stmt_close($assetStmt);

    if (!$originalAsset) {
        header('location:fixed-asset-approval.php?msg=invalid&branch=' . urlencode($returnBranch));
        exit();
    }

    // Begin transaction
    mysqli_begin_transaction($con);

    try {
        // 1. Update original fixed_assets — mark status as "Transferred" (keep in original branch)
        $updateAsset = mysqli_prepare($con,
            "UPDATE fixed_assets SET status = 'Transferred' WHERE id = ?"
        );
        mysqli_stmt_bind_param($updateAsset, "i", $transfer['asset_id']);
        mysqli_stmt_execute($updateAsset);
        mysqli_stmt_close($updateAsset);

        // 2. Create a copy of the asset in the destination branch with status "Available"
        $insertCopy = mysqli_prepare($con,
            "INSERT INTO fixed_assets (date, branch, category, product, company, rate, quantity, amount, serial_no, label, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available', NOW())"
        );
        mysqli_stmt_bind_param($insertCopy, "sssssdidss",
            $originalAsset['date'],
            $transfer['to_branch'],
            $originalAsset['category'],
            $originalAsset['product'],
            $originalAsset['company'],
            $originalAsset['rate'],
            $originalAsset['quantity'],
            $originalAsset['amount'],
            $originalAsset['serial_no'],
            $originalAsset['label']
        );
        mysqli_stmt_execute($insertCopy);
        mysqli_stmt_close($insertCopy);

        // 3. Update fixed_asset_transfers — mark as Approved + record approval timestamp
        $updateTransfer = mysqli_prepare($con,
            "UPDATE fixed_asset_transfers SET transfer_status = 'Approved', approved_at = NOW() WHERE id = ?"
        );
        mysqli_stmt_bind_param($updateTransfer, "i", $transferId);
        mysqli_stmt_execute($updateTransfer);
        mysqli_stmt_close($updateTransfer);

        mysqli_commit($con);
        header('location:fixed-asset-approval.php?msg=approved&branch=' . urlencode($returnBranch));
        exit();

    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("Fixed Transfer Approve exception: " . $e->getMessage());
        header('location:fixed-asset-approval.php?msg=error&branch=' . urlencode($returnBranch));
        exit();
    }
}

// ─── REJECT ───
if ($action === 'reject') {
    // Fetch the transfer record to get original status for reverting
    $checkStmt = mysqli_prepare($con,
        "SELECT id, asset_id, status FROM fixed_asset_transfers WHERE id = ? AND transfer_status = 'Pending'"
    );
    mysqli_stmt_bind_param($checkStmt, "i", $transferId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $transferRecord = mysqli_fetch_assoc($checkResult);
    mysqli_stmt_close($checkStmt);

    if (!$transferRecord) {
        header('location:fixed-asset-approval.php?msg=invalid&branch=' . urlencode($returnBranch));
        exit();
    }

    // Revert original asset status back to what it was before transfer request
    $originalStatus = !empty($transferRecord['status']) ? $transferRecord['status'] : 'Available';
    $revertStmt = mysqli_prepare($con,
        "UPDATE fixed_assets SET status = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($revertStmt, "si", $originalStatus, $transferRecord['asset_id']);
    mysqli_stmt_execute($revertStmt);
    mysqli_stmt_close($revertStmt);

    // Update transfer status to Rejected
    $rejectStmt = mysqli_prepare($con,
        "UPDATE fixed_asset_transfers SET transfer_status = 'Rejected' WHERE id = ?"
    );
    mysqli_stmt_bind_param($rejectStmt, "i", $transferId);
    mysqli_stmt_execute($rejectStmt);
    mysqli_stmt_close($rejectStmt);

    header('location:fixed-asset-approval.php?msg=rejected&branch=' . urlencode($returnBranch));
}

exit();
?>
