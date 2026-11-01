<?php
include_once('./includes/auth-employee.php');
include_once('./includes/config.php');

$branch = $_SESSION['branch_name'];

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

// ─── Fetch ALL fixed assets for this branch ───
$assetList = false;
$totalAvailable = 0;
$dbError = '';

$stmt = mysqli_prepare($con, "SELECT id, date, category, product, company, label, amount, status FROM fixed_assets WHERE branch = ? ORDER BY id DESC");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $branch);
    mysqli_stmt_execute($stmt);
    $assetList = mysqli_stmt_get_result($stmt);
    $totalAvailable = ($assetList) ? mysqli_num_rows($assetList) : 0;
} else {
    $dbError = 'Database error: ' . mysqli_error($con);
}

// ─── Messages ───
$success = '';
$error = '';
$warning = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'transferred':
            $count = isset($_GET['count']) ? (int) $_GET['count'] : 0;
            $success = ($count > 0)
                ? $count . ' fixed asset(s) have been submitted for transfer successfully! Waiting for admin approval.'
                : 'Fixed assets have been submitted for transfer successfully! Waiting for admin approval.';
            break;
        case 'partial':
            $transferred = isset($_GET['transferred']) ? (int) $_GET['transferred'] : 0;
            $skipped = isset($_GET['skipped']) ? (int) $_GET['skipped'] : 0;
            $warning = $transferred . ' asset(s) submitted for transfer. ' . $skipped . ' asset(s) were skipped because they already have pending transfer requests.';
            break;
        case 'all_pending':
            $count = isset($_GET['count']) ? (int) $_GET['count'] : 0;
            $error = 'Transfer not submitted — ' . ($count > 1 ? 'all ' . $count . ' selected assets already have' : 'the selected asset already has') . ' pending transfer request(s). Please wait for admin approval on existing requests before re-submitting.';
            break;
        case 'no_matching':
            $error = 'Transfer not submitted — the selected asset(s) were not found for your branch. They may have already been transferred. Please refresh the page and try again.';
            break;
        case 'insert_failed':
            $error = 'Transfer not submitted — a database error occurred while saving the transfer request. Please contact the administrator.';
            break;
        case 'db_error':
            $error = 'A database connection or table error occurred. Please contact the administrator.';
            break;
        case 'no_assets':
            $error = 'Please select at least one asset to transfer.';
            break;
        case 'no_branch':
            $error = 'Please select a target branch for transfer.';
            break;
        case 'same_branch':
            $error = 'Cannot transfer assets to the same branch.';
            break;
        case 'error':
            $error = 'An unexpected error occurred. Please try again or contact the administrator.';
            break;
    }
}
if (!empty($dbError)) {
    $error = $dbError;
}

// ─── Branch list ───
$branches = [
    'kundal(HO)', 'palus', 'sawantpur', 'takari', 'sangli',
    'savlaj', 'Tasgaon', 'market yard sangli',
    'kolhapur', 'bhilawdi', 'vasagde', 'chinchni'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Fixed Asset Transfer | MPSC Bank Portal</title>
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/it-assets.css" rel="stylesheet" />
    <link href="./css/it-transfer.css?v=2" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">

    <!-- ── Navbar ── -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background:#099c78;">
        <a class="navbar-brand ps-3" href="entry-forms.php">SDCC</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="navbar-nav ms-auto me-3 me-lg-4 align-items-center">
            <li class="nav-item me-3 text-white">
                <?php echo htmlspecialchars($_SESSION['branch_name']); ?>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['login']); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <?php include_once('./includes/entry-sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-3">
                    <div class="it-transfer-page">
                        <div class="it-transfer-panel">

                            <!-- ── Back Link ── -->
                            <a href="fixed-assets.php" class="itt-back-link">
                                <i class="fas fa-arrow-left"></i> Back to Fixed Assets Dashboard
                            </a>

                            <!-- ── PAGE HEADER ── -->
                            <div class="itt-header">
                                <h1 class="itt-title"><i class="fas fa-exchange-alt"></i> FIXED ASSET TRANSFER LIST</h1>
                                <div class="itt-branch-badge">
                                    <i class="fas fa-building"></i>
                                    <?php echo htmlspecialchars($branch); ?>
                                </div>
                            </div>

                            <?php if (!empty($success)) { ?>
                                <div class="itt-alert-success" id="alertMsg">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php } ?>

                            <?php if (!empty($warning)) { ?>
                                <div class="itt-alert-warning" id="alertMsg" style="background:linear-gradient(135deg,#fff8e1,#fff3c4);color:#e65100;border:1px solid #ffcc80;padding:14px 20px;border-radius:10px;margin-bottom:18px;font-weight:500;display:flex;align-items:center;gap:10px;">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($warning); ?>
                                </div>
                            <?php } ?>

                            <?php if (!empty($error)) { ?>
                                <div class="itt-alert-error" id="alertMsg">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php } ?>

                            <!-- ── ASSETS TABLE ── -->
                            <form action="process-fixed-transfer.php" method="POST" id="transferForm">
                                <div class="itt-table-wrap" id="printableArea">
                                    <div class="itt-table-header-bar">
                                        <span><i class="fas fa-list"></i> Fixed Assets — <?php echo htmlspecialchars($branch); ?></span>
                                        <span class="itt-table-count"><?php echo $totalAvailable; ?> records</span>
                                    </div>
                                    <div class="itt-table-scroll">
                                        <table class="itt-table" id="transferTable">
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
                                                    <th class="no-print">
                                                        <div class="itt-select-all-wrap">
                                                            <input type="checkbox" id="selectAll">
                                                            <label for="selectAll">Select All</label>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sr = 1;
                                                if ($assetList && mysqli_num_rows($assetList) > 0) {
                                                    mysqli_data_seek($assetList, 0);
                                                    while ($asset = mysqli_fetch_assoc($assetList)) {
                                                        $statusClass = '';
                                                        $isTransferable = true;
                                                        switch ($asset['status']) {
                                                            case 'Available': $statusClass = 'status-available'; break;
                                                            case 'Pending Transfer': $statusClass = 'status-pending-transfer'; $isTransferable = false; break;
                                                            case 'Transferred': $statusClass = 'status-transferred'; $isTransferable = false; break;
                                                            case 'Request to Delete': $statusClass = 'status-delete'; break;
                                                            case 'Scrap': $statusClass = 'status-scrap'; break;
                                                            case 'Request to Sale': $statusClass = 'status-repair'; break;
                                                            case 'Request to Repair': $statusClass = 'status-repair'; break;
                                                        }
                                                ?>
                                                        <tr<?php if (!$isTransferable) echo ' style="opacity:0.6;"'; ?>>
                                                            <td><?php echo $sr++; ?></td>
                                                            <td><?php echo htmlspecialchars($asset['date']); ?></td>
                                                            <td><?php echo htmlspecialchars($asset['category']); ?></td>
                                                            <td><?php echo htmlspecialchars($asset['product']); ?></td>
                                                            <td><?php echo htmlspecialchars($asset['company'] ?? '—'); ?></td>
                                                            <td><?php echo htmlspecialchars($asset['label'] ?? '—'); ?></td>
                                                            <td>₹<?php echo number_format((float)$asset['amount'], 2); ?></td>
                                                            <td>
                                                                <span class="itt-status-badge <?php echo $statusClass; ?>">
                                                                    <?php echo htmlspecialchars($asset['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td class="no-print">
                                                                <div class="itt-checkbox-wrap">
                                                                    <?php if ($isTransferable) { ?>
                                                                        <input type="checkbox" name="asset_ids[]" value="<?php echo $asset['id']; ?>" id="asset_<?php echo $asset['id']; ?>">
                                                                        <label for="asset_<?php echo $asset['id']; ?>">Select</label>
                                                                    <?php } else { ?>
                                                                        <span style="font-size:11px;color:#94a3b8;font-style:italic;">N/A</span>
                                                                    <?php } ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                ?>
                                                    <tr>
                                                        <td colspan="9" class="itt-empty-row">
                                                            <i class="fas fa-inbox"></i> No fixed assets found for this branch.
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- ── TRANSFER ACTION BAR ── -->
                                <div class="itt-transfer-bar">
                                    <div class="itt-transfer-group">
                                        <label for="targetBranch">
                                            <i class="fas fa-map-marker-alt"></i> Transfer To Branch:
                                        </label>
                                        <select name="to_branch" id="targetBranch" class="itt-select" required>
                                            <option value="">— Select Branch —</option>
                                            <?php foreach ($branches as $b) {
                                                if (strtolower(trim($b)) === strtolower(trim($branch))) continue;
                                            ?>
                                                <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <button type="button" class="itt-btn itt-btn-transfer" id="btnTransfer" <?php if ($totalAvailable === 0) echo 'disabled style="opacity:0.5;cursor:not-allowed;"'; ?>>
                                        <i class="fas fa-exchange-alt"></i> Fixed Asset Transfer
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-3 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="small text-muted">MPSC Bank Portal</div>
                </div>
            </footer>
        </div>
    </div>

    <!-- ── Confirmation Modal ── -->
    <div class="itt-modal-overlay" id="transferModal">
        <div class="itt-modal">
            <div class="itt-modal-icon transfer">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="itt-modal-title">Confirm Transfer</div>
            <div class="itt-modal-text" id="modalText">
                Are you sure you want to transfer the selected fixed assets?
            </div>
            <div class="itt-modal-actions">
                <button class="itt-modal-btn-cancel" id="modalCancel">Cancel</button>
                <button class="itt-modal-btn-confirm transfer" id="modalConfirm">Yes, Transfer</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="./js/scripts.js"></script>
    <script>
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('input[name="asset_ids[]"]').forEach(cb => cb.checked = this.checked);
            });
        }

        const btnTransfer = document.getElementById('btnTransfer');
        if (btnTransfer) {
            btnTransfer.addEventListener('click', function() {
                if (this.disabled) return;
                const checked = document.querySelectorAll('input[name="asset_ids[]"]:checked');
                const branch = document.getElementById('targetBranch').value;
                if (checked.length === 0) { alert('Please select at least one asset to transfer.'); return; }
                if (!branch) { alert('Please select a target branch for transfer.'); return; }
                document.getElementById('modalText').innerHTML =
                    'You are about to transfer <strong>' + checked.length + ' fixed asset(s)</strong> to <strong>' + branch + '</strong>.<br>This will be sent for admin approval.';
                document.getElementById('transferModal').classList.add('active');
            });
        }

        document.getElementById('modalCancel')?.addEventListener('click', () => document.getElementById('transferModal').classList.remove('active'));
        document.getElementById('modalConfirm')?.addEventListener('click', () => document.getElementById('transferForm').submit());
        document.getElementById('transferModal')?.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('active'); });

        const alertEl = document.getElementById('alertMsg');
        if (alertEl) { setTimeout(() => { alertEl.style.transition = 'opacity 0.5s'; alertEl.style.opacity = '0'; setTimeout(() => alertEl.remove(), 500); }, 5000); }
    </script>
</body>

</html>
