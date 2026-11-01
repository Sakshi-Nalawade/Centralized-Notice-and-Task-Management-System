<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('./includes/auth-employee.php');
include_once('./includes/config.php');

$branch = $_SESSION['branch_name'];

// ─── Ensure it_asset_transfers table exists ───
mysqli_query($con, "CREATE TABLE IF NOT EXISTS `it_asset_transfers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `asset_id` INT NOT NULL,
    `from_branch` VARCHAR(255) NOT NULL,
    `to_branch` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `product` VARCHAR(255) NOT NULL,
    `label_name` VARCHAR(255) DEFAULT NULL,
    `status` VARCHAR(100) DEFAULT NULL,
    `transfer_status` VARCHAR(50) DEFAULT 'Pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// ─── Fetch ALL assets for this branch (same data as IT Assets Dashboard) ───
$assetList = false;
$totalAvailable = 0;
$dbError = '';

$stmt = mysqli_prepare($con, "SELECT id, category, product, label_name, status, created_at FROM it_assets WHERE branch_name = ? ORDER BY id DESC");
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
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'transferred') $success = 'Assets have been submitted for transfer successfully! Waiting for admin approval.';
    if ($_GET['msg'] === 'no_assets') $error = 'Please select at least one asset to transfer.';
    if ($_GET['msg'] === 'no_branch') $error = 'Please select a target branch for transfer.';
    if ($_GET['msg'] === 'same_branch') $error = 'Cannot transfer assets to the same branch.';
    if ($_GET['msg'] === 'error') $error = 'An error occurred. Please try again.';
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
    <title>IT Asset Transfer | MPSC Bank Portal</title>
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
                            <a href="manage-it-assets.php" class="itt-back-link">
                                <i class="fas fa-arrow-left"></i> Back to IT Assets Dashboard
                            </a>

                            <!-- ── PAGE HEADER ── -->
                            <div class="itt-header">
                                <h1 class="itt-title"><i class="fas fa-exchange-alt"></i> IT ASSET TRANSFER LIST</h1>
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

                            <?php if (!empty($error)) { ?>
                                <div class="itt-alert-error" id="alertMsg">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php } ?>

                            <!-- ── ASSETS TABLE (Available Only) ── -->
                            <form action="process-transfer.php" method="POST" id="transferForm">
                                <div class="itt-table-wrap" id="printableArea">
                                    <div class="itt-table-header-bar">
                                        <span><i class="fas fa-list"></i> IT Assets — <?php echo htmlspecialchars($branch); ?></span>
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
                                                    <th>Label</th>
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
                                                    // Reset pointer since we used num_rows above
                                                    mysqli_data_seek($assetList, 0);
                                                    while ($asset = mysqli_fetch_assoc($assetList)) {
                                                ?>
                                                        <tr>
                                                            <td><?php echo $sr++; ?></td>
                                                            <td><?php echo date('d-M-Y', strtotime($asset['created_at'])); ?></td>
                                                            <td><?php echo htmlspecialchars($asset['category']); ?></td>
                                                            <td><?php echo htmlspecialchars($asset['product']); ?></td>
                                                            <td><?php echo htmlspecialchars($asset['label_name'] ?? '—'); ?></td>
                                                            <td>
                                                                <?php
                                                                    $statusClass = '';
                                                                    switch ($asset['status']) {
                                                                        case 'Available': $statusClass = 'status-available'; break;
                                                                        case 'Not Available': $statusClass = 'status-unavailable'; break;
                                                                        case 'Send To Repair': $statusClass = 'status-repair'; break;
                                                                        case 'Request to delete': $statusClass = 'status-delete'; break;
                                                                        case 'Replaced': $statusClass = 'status-replaced'; break;
                                                                        case 'Scrap': $statusClass = 'status-scrap'; break;
                                                                    }
                                                                ?>
                                                                <span class="itt-status-badge <?php echo $statusClass; ?>">
                                                                    <?php echo htmlspecialchars($asset['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td class="no-print">
                                                                <div class="itt-checkbox-wrap">
                                                                    <input type="checkbox" name="asset_ids[]" value="<?php echo $asset['id']; ?>" id="asset_<?php echo $asset['id']; ?>">
                                                                    <label for="asset_<?php echo $asset['id']; ?>">Select</label>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                ?>
                                                    <tr>
                                                        <td colspan="7" class="itt-empty-row">
                                                            <i class="fas fa-inbox"></i> No assets found for this branch.
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- ── TRANSFER ACTION BAR (Always visible) ── -->
                                <div class="itt-transfer-bar">
                                    <div class="itt-transfer-group">
                                        <label for="targetBranch">
                                            <i class="fas fa-map-marker-alt"></i> Transfer To Branch:
                                        </label>
                                        <select name="to_branch" id="targetBranch" class="itt-select" required>
                                            <option value="">— Select Branch —</option>
                                            <?php foreach ($branches as $b) {
                                                // Don't show current branch
                                                if (strtolower(trim($b)) === strtolower(trim($branch))) continue;
                                            ?>
                                                <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <button type="button" class="itt-btn itt-btn-transfer" id="btnTransfer" <?php if ($totalAvailable === 0) echo 'disabled style="opacity:0.5;cursor:not-allowed;"'; ?>>
                                        <i class="fas fa-exchange-alt"></i> IT Asset Transfer
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
                Are you sure you want to transfer the selected assets?
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
        // Select All checkbox
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="asset_ids[]"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        // Transfer button — validate & show modal
        const btnTransfer = document.getElementById('btnTransfer');
        if (btnTransfer) {
            btnTransfer.addEventListener('click', function() {
                if (this.disabled) return;

                const checked = document.querySelectorAll('input[name="asset_ids[]"]:checked');
                const branch = document.getElementById('targetBranch').value;

                if (checked.length === 0) {
                    alert('Please select at least one asset to transfer.');
                    return;
                }

                if (!branch) {
                    alert('Please select a target branch for transfer.');
                    return;
                }

                // Update modal text
                document.getElementById('modalText').innerHTML =
                    'You are about to transfer <strong>' + checked.length + ' asset(s)</strong> to <strong>' + branch + '</strong>.<br>This will be sent for admin approval.';

                // Show modal
                document.getElementById('transferModal').classList.add('active');
            });
        }

        // Modal cancel
        const modalCancel = document.getElementById('modalCancel');
        if (modalCancel) {
            modalCancel.addEventListener('click', function() {
                document.getElementById('transferModal').classList.remove('active');
            });
        }

        // Modal confirm — submit form
        const modalConfirm = document.getElementById('modalConfirm');
        if (modalConfirm) {
            modalConfirm.addEventListener('click', function() {
                document.getElementById('transferForm').submit();
            });
        }

        // Close modal on overlay click
        const transferModal = document.getElementById('transferModal');
        if (transferModal) {
            transferModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        }

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
