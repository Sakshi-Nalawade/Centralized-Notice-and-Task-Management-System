<?php
/**
 * Fixed Asset Transfer Report
 * Shows all approved transfers (final transferred assets)
 * Employee: auto-filtered by their branch
 * Admin: view all OR filter by branch dropdown
 */

// ─── Session & Auth (both roles allowed) ───
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userid']) || !isset($_SESSION['role'])) {
    header('location:index.php');
    exit();
}

include_once('./includes/config.php');

$role = trim(strtolower($_SESSION['role']));
$isAdmin = ($role === 'admin');
$isEmployee = ($role === 'employee');

// ─── Branch list (for admin filter) ───
$branches = [
    'kundal(HO)', 'palus', 'sawantpur', 'takari', 'sangli',
    'savlaj', 'Tasgaon', 'market yard sangli',
    'kolhapur', 'bhilawdi', 'vasagde', 'chinchni'
];

// ─── Determine filter branch ───
$filterBranch = '';

if ($isEmployee) {
    if (!isset($_SESSION['branch_name']) || empty($_SESSION['branch_name'])) {
        header('location:index.php');
        exit();
    }
    $filterBranch = $_SESSION['branch_name'];
} elseif ($isAdmin) {
    if (isset($_GET['branch']) && !empty(trim($_GET['branch']))) {
        $filterBranch = trim($_GET['branch']);
    }
}

// ─── Fetch approved transfers ───
$reportList = null;
$totalCount = 0;

if ($isEmployee) {
    $stmt = mysqli_prepare($con,
        "SELECT id, asset_id, from_branch, to_branch, category, product, company, label, amount, transfer_status, created_at, approved_at
         FROM fixed_asset_transfers
         WHERE (from_branch = ? OR to_branch = ?) AND transfer_status = 'Approved'
         ORDER BY approved_at DESC, id DESC"
    );
    mysqli_stmt_bind_param($stmt, "ss", $filterBranch, $filterBranch);
    mysqli_stmt_execute($stmt);
    $reportList = mysqli_stmt_get_result($stmt);
    $totalCount = ($reportList) ? mysqli_num_rows($reportList) : 0;
} elseif ($isAdmin && !empty($filterBranch)) {
    $stmt = mysqli_prepare($con,
        "SELECT id, asset_id, from_branch, to_branch, category, product, company, label, amount, transfer_status, created_at, approved_at
         FROM fixed_asset_transfers
         WHERE to_branch = ? AND transfer_status = 'Approved'
         ORDER BY approved_at DESC, id DESC"
    );
    mysqli_stmt_bind_param($stmt, "s", $filterBranch);
    mysqli_stmt_execute($stmt);
    $reportList = mysqli_stmt_get_result($stmt);
    $totalCount = ($reportList) ? mysqli_num_rows($reportList) : 0;
} elseif ($isAdmin) {
    $stmt = mysqli_prepare($con,
        "SELECT id, asset_id, from_branch, to_branch, category, product, company, label, amount, transfer_status, created_at, approved_at
         FROM fixed_asset_transfers
         WHERE transfer_status = 'Approved'
         ORDER BY approved_at DESC, id DESC"
    );
    mysqli_stmt_execute($stmt);
    $reportList = mysqli_stmt_get_result($stmt);
    $totalCount = ($reportList) ? mysqli_num_rows($reportList) : 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Fixed Asset Transfer Report | MPSC Bank Portal</title>
    <meta name="description" content="View approved fixed asset transfers in the MPSC Bank Portal." />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/it-transfer.css?v=1" rel="stylesheet" />
    <link href="./css/it-transfer-report.css?v=1" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">

    <?php if ($isAdmin) { ?>
        <?php include_once('./includes/navbar.php'); ?>
    <?php } else { ?>
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
    <?php } ?>

    <div id="layoutSidenav">
        <?php
        if ($isAdmin) {
            include_once('./includes/sidebar.php');
        } else {
            include_once('./includes/entry-sidebar.php');
        }
        ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-3">
                    <div class="it-transfer-page">
                        <div class="it-transfer-panel">

                            <!-- ── Back Link ── -->
                            <?php if ($isEmployee) { ?>
                                <a href="fixed-assets.php" class="itt-back-link">
                                    <i class="fas fa-arrow-left"></i> Back to Fixed Assets Dashboard
                                </a>
                            <?php } else { ?>
                                <a href="fixed-asset-approval.php" class="itt-back-link">
                                    <i class="fas fa-arrow-left"></i> Back to Transfer Approval
                                </a>
                            <?php } ?>

                            <!-- ── PAGE HEADER ── -->
                            <div class="itt-header">
                                <h1 class="itt-title"><i class="fas fa-file-alt"></i> FIXED ASSET TRANSFER REPORT</h1>
                                <?php if ($isEmployee) { ?>
                                    <div class="itt-branch-badge">
                                        <i class="fas fa-building"></i>
                                        <?php echo htmlspecialchars($_SESSION['branch_name']); ?>
                                    </div>
                                <?php } ?>
                            </div>

                            <?php if ($isAdmin) { ?>
                                <!-- ── ADMIN BRANCH FILTER ── -->
                                <form method="GET" action="fixed-asset-transfer-report.php" class="itt-filter-bar" id="reportFilterBar">
                                    <label for="branchFilter">
                                        <i class="fas fa-building"></i> Filter by Branch:
                                    </label>
                                    <select name="branch" id="branchFilter" class="itt-select">
                                        <option value="">— All Branches —</option>
                                        <?php foreach ($branches as $b) { ?>
                                            <option value="<?php echo htmlspecialchars($b); ?>"
                                                <?php echo ($filterBranch === $b) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($b); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <button type="submit" class="itt-filter-btn">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <?php if (!empty($filterBranch)) { ?>
                                        <a href="fixed-asset-transfer-report.php" class="itr-clear-btn">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    <?php } ?>
                                </form>
                            <?php } ?>

                            <!-- ── SUMMARY STAT ── -->
                            <div class="itr-summary-row">
                                <div class="itr-summary-card">
                                    <div class="itr-summary-icon">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                    <div class="itr-summary-info">
                                        <div class="itr-summary-label">Total Transferred</div>
                                        <div class="itr-summary-count"><?php echo $totalCount; ?></div>
                                    </div>
                                </div>
                                <?php if (!empty($filterBranch)) { ?>
                                    <div class="itr-summary-card accent">
                                        <div class="itr-summary-icon accent">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="itr-summary-info">
                                            <div class="itr-summary-label">Branch</div>
                                            <div class="itr-summary-branch"><?php echo htmlspecialchars($filterBranch); ?></div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <!-- ── REPORT TABLE ── -->
                            <div class="itt-table-wrap" id="printableArea">
                                <!-- Print-only header -->
                                <div class="itr-print-header">
                                    <h2>FIXED ASSET TRANSFER REPORT</h2>
                                    <?php if (!empty($filterBranch)) { ?>
                                        <p>Branch: <?php echo htmlspecialchars($filterBranch); ?></p>
                                    <?php } ?>
                                    <p>Date: <?php echo date('d-M-Y'); ?></p>
                                </div>

                                <div class="itt-table-header-bar">
                                    <span>
                                        <i class="fas fa-file-alt"></i>
                                        Approved Fixed Asset Transfers
                                        <?php if (!empty($filterBranch)) { ?>
                                            — <?php echo htmlspecialchars($filterBranch); ?>
                                        <?php } ?>
                                    </span>
                                    <div class="itr-table-actions">
                                        <span class="itt-table-count"><?php echo $totalCount; ?> records</span>
                                        <button type="button" class="itr-print-btn no-print" onclick="printReport()">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                    </div>
                                </div>

                                <!-- ── Search bar ── -->
                                <div class="itr-search-bar no-print">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="tableSearch" placeholder="Search by category, product, label, branch..." autocomplete="off">
                                </div>

                                <div class="itt-table-scroll">
                                    <table class="itt-table" id="reportTable">
                                        <thead>
                                            <tr>
                                                <th>Sr.No</th>
                                                <th>Transfer Request Date</th>
                                                <th>From Branch</th>
                                                <th>To Branch</th>
                                                <th>Category</th>
                                                <th>Product</th>
                                                <th>Company</th>
                                                <th>Label</th>
                                                <th>Price</th>
                                                <th>Received Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sr = 1;
                                            if ($reportList && $totalCount > 0) {
                                                mysqli_data_seek($reportList, 0);
                                                while ($row = mysqli_fetch_assoc($reportList)) {
                                            ?>
                                                    <tr>
                                                        <td><?php echo $sr++; ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($row['created_at'])); ?></td>
                                                        <td>
                                                            <span class="itt-from-branch">
                                                                <?php echo htmlspecialchars($row['from_branch']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="itr-branch-tag">
                                                                <?php echo htmlspecialchars($row['to_branch']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['product']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['company'] ?? '—'); ?></td>
                                                        <td><?php echo htmlspecialchars($row['label'] ?? '—'); ?></td>
                                                        <td>₹<?php echo number_format((float)$row['amount'], 2); ?></td>
                                                        <td>
                                                            <?php
                                                            if (!empty($row['approved_at'])) {
                                                                echo date('d-M-Y', strtotime($row['approved_at']));
                                                            } else {
                                                                echo '—';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <span class="itt-status-badge status-approved">
                                                                <i class="fas fa-check-circle"></i> Approved
                                                            </span>
                                                        </td>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                            ?>
                                                <tr>
                                                    <td colspan="11" class="itt-empty-row">
                                                        <i class="fas fa-inbox"></i>
                                                        No approved fixed asset transfers found.
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- ── PRINT BUTTON ── -->
                            <?php if ($totalCount > 0) { ?>
                                <div class="itr-print-action no-print">
                                    <button type="button" class="itr-print-main-btn" onclick="printReport()">
                                        <i class="fas fa-print"></i> Print Report
                                    </button>
                                </div>
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </main>

            <?php if ($isAdmin) { ?>
                <?php include_once('./includes/footer.php'); ?>
            <?php } else { ?>
                <footer class="py-3 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="small text-muted">MPSC Bank Portal</div>
                    </div>
                </footer>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="./js/scripts.js"></script>
    <script>
        // ── Live Table Search ──
        const searchInput = document.getElementById('tableSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const filter = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#reportTable tbody tr');
                rows.forEach(row => {
                    if (row.querySelector('.itt-empty-row')) return;
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }

        // ── Print Report Function ──
        function printReport() {
            window.print();
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
