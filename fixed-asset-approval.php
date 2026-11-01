<?php
/**
 * Fixed Asset Approval — Admin-only page
 * View and approve/reject pending fixed asset transfer requests
 */

include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

// ─── Ensure fixed_asset_transfers table exists ───
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `fixed_asset_transfers` (
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

// ─── Branch list ───
$branches = [
    'kundal(HO)', 'palus', 'sawantpur', 'takari', 'sangli',
    'savlaj', 'Tasgaon', 'market yard sangli',
    'kolhapur', 'bhilawdi', 'vasagde', 'chinchni'
];

// ─── Selected branch filter ───
$selectedBranch = '';
if (isset($_GET['branch']) && !empty(trim($_GET['branch']))) {
    $selectedBranch = trim($_GET['branch']);
}

// ─── Fetch pending transfers for selected branch ───
$pendingList = null;
$pendingCount = 0;

if (!empty($selectedBranch)) {
    $stmt = mysqli_prepare($con,
        "SELECT id, asset_id, from_branch, to_branch, category, product, company, label, amount, status, transfer_status, created_at
         FROM fixed_asset_transfers
         WHERE to_branch = ? AND transfer_status = 'Pending'
         ORDER BY created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, "s", $selectedBranch);
    mysqli_stmt_execute($stmt);
    $pendingList = mysqli_stmt_get_result($stmt);
    $pendingCount = ($pendingList) ? mysqli_num_rows($pendingList) : 0;
}

// ─── Fetch stats for selected branch ───
$statPending = 0;
$statApproved = 0;
$statRejected = 0;

if (!empty($selectedBranch)) {
    $s1 = mysqli_prepare($con, "SELECT COUNT(*) as cnt FROM fixed_asset_transfers WHERE to_branch = ? AND transfer_status = 'Pending'");
    mysqli_stmt_bind_param($s1, "s", $selectedBranch);
    mysqli_stmt_execute($s1);
    $r1 = mysqli_stmt_get_result($s1);
    $statPending = mysqli_fetch_assoc($r1)['cnt'];
    mysqli_stmt_close($s1);

    $s2 = mysqli_prepare($con, "SELECT COUNT(*) as cnt FROM fixed_asset_transfers WHERE to_branch = ? AND transfer_status = 'Approved'");
    mysqli_stmt_bind_param($s2, "s", $selectedBranch);
    mysqli_stmt_execute($s2);
    $r2 = mysqli_stmt_get_result($s2);
    $statApproved = mysqli_fetch_assoc($r2)['cnt'];
    mysqli_stmt_close($s2);

    $s3 = mysqli_prepare($con, "SELECT COUNT(*) as cnt FROM fixed_asset_transfers WHERE to_branch = ? AND transfer_status = 'Rejected'");
    mysqli_stmt_bind_param($s3, "s", $selectedBranch);
    mysqli_stmt_execute($s3);
    $r3 = mysqli_stmt_get_result($s3);
    $statRejected = mysqli_fetch_assoc($r3)['cnt'];
    mysqli_stmt_close($s3);
}

// ─── Messages ───
$success = '';
$error = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'approved') $success = 'Fixed asset transfer approved successfully! Asset branch has been updated.';
    if ($_GET['msg'] === 'rejected') $success = 'Fixed asset transfer request has been rejected.';
    if ($_GET['msg'] === 'error') $error = 'An error occurred. Please try again.';
    if ($_GET['msg'] === 'invalid') $error = 'Invalid transfer request.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Fixed Asset Transfer Approval | MPSC Bank Portal</title>
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/it-transfer.css?v=1" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <?php include_once('./includes/navbar.php'); ?>

    <div id="layoutSidenav">
        <?php include_once('./includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-3">
                    <div class="it-transfer-page">
                        <div class="it-transfer-panel">

                            <!-- ── PAGE HEADER ── -->
                            <div class="itt-header">
                                <h1 class="itt-title"><i class="fas fa-clipboard-check"></i> FIXED ASSET TRANSFER APPROVAL</h1>
                            </div>

                            <?php if (!empty($success)) { ?>
                                <div class="itt-alert-success" id="alertMsg">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php } ?>

                            <?php if (!empty($error)) { ?>
                                <div class="itt-alert-error" id="alertMsg">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php } ?>

                            <!-- ── BRANCH FILTER ── -->
                            <form method="GET" action="fixed-asset-approval.php" class="itt-filter-bar">
                                <label for="branchFilter">
                                    <i class="fas fa-building"></i> Select Branch:
                                </label>
                                <select name="branch" id="branchFilter" class="itt-select" required>
                                    <option value="">— Select Branch —</option>
                                    <?php foreach ($branches as $b) { ?>
                                        <option value="<?php echo htmlspecialchars($b); ?>"
                                            <?php echo ($selectedBranch === $b) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($b); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <button type="submit" class="itt-filter-btn">
                                    <i class="fas fa-search"></i> Show Transfers
                                </button>
                            </form>

                            <?php if (!empty($selectedBranch)) { ?>

                                <!-- ── STATS ROW ── -->
                                <div class="itt-stats-row">
                                    <div class="itt-stat-card">
                                        <div class="itt-stat-icon pending"><i class="fas fa-clock"></i></div>
                                        <div class="itt-stat-info">
                                            <div class="itt-stat-label">Pending</div>
                                            <div class="itt-stat-count"><?php echo $statPending; ?></div>
                                        </div>
                                    </div>
                                    <div class="itt-stat-card">
                                        <div class="itt-stat-icon approved"><i class="fas fa-check"></i></div>
                                        <div class="itt-stat-info">
                                            <div class="itt-stat-label">Approved</div>
                                            <div class="itt-stat-count"><?php echo $statApproved; ?></div>
                                        </div>
                                    </div>
                                    <div class="itt-stat-card">
                                        <div class="itt-stat-icon rejected"><i class="fas fa-times"></i></div>
                                        <div class="itt-stat-info">
                                            <div class="itt-stat-label">Rejected</div>
                                            <div class="itt-stat-count"><?php echo $statRejected; ?></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ── PENDING TRANSFERS TABLE ── -->
                                <div class="itt-table-wrap" id="printableArea">
                                    <div class="itt-table-header-bar">
                                        <span><i class="fas fa-exchange-alt"></i> Pending Transfers — <?php echo htmlspecialchars($selectedBranch); ?></span>
                                        <span class="itt-table-count"><?php echo $pendingCount; ?> pending</span>
                                    </div>
                                    <div class="itt-table-scroll">
                                        <table class="itt-table" id="approvalTable">
                                            <thead>
                                                <tr>
                                                    <th>Sr.No</th>
                                                    <th>Date</th>
                                                    <th>Category</th>
                                                    <th>Product</th>
                                                    <th>Company</th>
                                                    <th>Label</th>
                                                    <th>Price</th>
                                                    <th>Status</th>
                                                    <th>From Branch</th>
                                                    <th class="no-print">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sr = 1;
                                                if ($pendingList && $pendingCount > 0) {
                                                    mysqli_data_seek($pendingList, 0);
                                                    while ($transfer = mysqli_fetch_assoc($pendingList)) {
                                                ?>
                                                        <tr>
                                                            <td><?php echo $sr++; ?></td>
                                                            <td><?php echo date('d-M-Y', strtotime($transfer['created_at'])); ?></td>
                                                            <td><?php echo htmlspecialchars($transfer['category']); ?></td>
                                                            <td><?php echo htmlspecialchars($transfer['product']); ?></td>
                                                            <td><?php echo htmlspecialchars($transfer['company'] ?? '—'); ?></td>
                                                            <td><?php echo htmlspecialchars($transfer['label'] ?? '—'); ?></td>
                                                            <td>₹<?php echo number_format((float)$transfer['amount'], 2); ?></td>
                                                            <td>
                                                                <span class="itt-status-badge status-pending">
                                                                    <?php echo htmlspecialchars($transfer['transfer_status']); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="itt-from-branch">
                                                                    <?php echo htmlspecialchars($transfer['from_branch']); ?>
                                                                </span>
                                                            </td>
                                                            <td class="no-print">
                                                                <div class="itt-action-btns">
                                                                    <button type="button" class="itt-btn-approve btn-approve-action"
                                                                        data-id="<?php echo $transfer['id']; ?>"
                                                                        data-asset-id="<?php echo $transfer['asset_id']; ?>"
                                                                        data-product="<?php echo htmlspecialchars($transfer['product']); ?>"
                                                                        data-from="<?php echo htmlspecialchars($transfer['from_branch']); ?>"
                                                                        data-to="<?php echo htmlspecialchars($transfer['to_branch']); ?>">
                                                                        <i class="fas fa-check"></i> Approve
                                                                    </button>
                                                                    <button type="button" class="itt-btn-reject btn-reject-action"
                                                                        data-id="<?php echo $transfer['id']; ?>"
                                                                        data-product="<?php echo htmlspecialchars($transfer['product']); ?>">
                                                                        <i class="fas fa-times"></i> Reject
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                ?>
                                                    <tr>
                                                        <td colspan="10" class="itt-empty-row">
                                                            <i class="fas fa-inbox"></i> No pending fixed asset transfer requests for this branch.
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            <?php } else { ?>
                                <div class="itt-alert-warning">
                                    <i class="fas fa-info-circle"></i> Please select a branch to view pending fixed asset transfer requests.
                                </div>
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </main>

            <?php include_once('./includes/footer.php'); ?>
        </div>
    </div>

    <!-- ── Approve Confirmation Modal ── -->
    <div class="itt-modal-overlay" id="approveModal">
        <div class="itt-modal">
            <div class="itt-modal-icon approve">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="itt-modal-title">Approve Transfer</div>
            <div class="itt-modal-text" id="approveModalText">
                Are you sure you want to approve this fixed asset transfer?
            </div>
            <div class="itt-modal-actions">
                <button class="itt-modal-btn-cancel" id="approveCancelBtn">Cancel</button>
                <form method="POST" action="approve-fixed-transfer.php" id="approveForm" style="display:inline;">
                    <input type="hidden" name="transfer_id" id="approveTransferId">
                    <input type="hidden" name="asset_id" id="approveAssetId">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="branch" id="approveBranch" value="<?php echo htmlspecialchars($selectedBranch); ?>">
                    <button type="submit" class="itt-modal-btn-confirm approve">Yes, Approve</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Reject Confirmation Modal ── -->
    <div class="itt-modal-overlay" id="rejectModal">
        <div class="itt-modal">
            <div class="itt-modal-icon reject">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="itt-modal-title">Reject Transfer</div>
            <div class="itt-modal-text" id="rejectModalText">
                Are you sure you want to reject this fixed asset transfer?
            </div>
            <div class="itt-modal-actions">
                <button class="itt-modal-btn-cancel" id="rejectCancelBtn">Cancel</button>
                <form method="POST" action="approve-fixed-transfer.php" id="rejectForm" style="display:inline;">
                    <input type="hidden" name="transfer_id" id="rejectTransferId">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="branch" id="rejectBranch" value="<?php echo htmlspecialchars($selectedBranch); ?>">
                    <button type="submit" class="itt-modal-btn-confirm reject">Yes, Reject</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="./js/scripts.js"></script>
    <script>
        // ── Approve buttons ──
        document.querySelectorAll('.btn-approve-action').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const assetId = this.getAttribute('data-asset-id');
                const product = this.getAttribute('data-product');
                const from = this.getAttribute('data-from');
                const to = this.getAttribute('data-to');

                document.getElementById('approveTransferId').value = id;
                document.getElementById('approveAssetId').value = assetId;
                document.getElementById('approveModalText').innerHTML =
                    'Approve transfer of <strong>' + product + '</strong> from <strong>' + from + '</strong> to <strong>' + to + '</strong>?<br><small>This will update the asset\'s branch in the system.</small>';

                document.getElementById('approveModal').classList.add('active');
            });
        });

        // ── Reject buttons ──
        document.querySelectorAll('.btn-reject-action').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const product = this.getAttribute('data-product');

                document.getElementById('rejectTransferId').value = id;
                document.getElementById('rejectModalText').innerHTML =
                    'Reject the transfer request for <strong>' + product + '</strong>?<br><small>The original asset will remain unchanged.</small>';

                document.getElementById('rejectModal').classList.add('active');
            });
        });

        // ── Modal close handlers ──
        document.getElementById('approveCancelBtn').addEventListener('click', () => {
            document.getElementById('approveModal').classList.remove('active');
        });

        document.getElementById('rejectCancelBtn').addEventListener('click', () => {
            document.getElementById('rejectModal').classList.remove('active');
        });

        ['approveModal', 'rejectModal'].forEach(modalId => {
            document.getElementById(modalId).addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('active');
            });
        });

        // Auto-hide alerts
        const alertEl = document.getElementById('alertMsg');
        if (alertEl) {
            setTimeout(() => {
                alertEl.style.transition = 'opacity 0.5s';
                alertEl.style.opacity = '0';
                setTimeout(() => alertEl.remove(), 500);
            }, 5000);
        }
    </script>
</body>

</html>
