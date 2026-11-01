<?php
include_once('./includes/auth-employee.php');
include_once('./includes/config.php');

$branch = $_SESSION['branch_name'];

// ─── Handle Delete Action ───
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmtDel = mysqli_prepare($con, "DELETE FROM fixed_assets WHERE id=? AND branch=?");
    mysqli_stmt_bind_param($stmtDel, "is", $deleteId, $branch);
    mysqli_stmt_execute($stmtDel);
    mysqli_stmt_close($stmtDel);
    header("Location: fixed-assets.php?msg=deleted");
    exit();
}

// ─── Get Filter Values ───
$filterDate     = isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '';
$filterCategory = isset($_GET['filter_category']) ? trim($_GET['filter_category']) : '';
$filterStatus   = isset($_GET['filter_status']) ? trim($_GET['filter_status']) : '';

// ─── Category Definitions with Icons ───
$summaryBlocks = [
    'Dead Stock'          => ['icon' => 'fas fa-skull-crossbones', 'class' => 'dead-stock'],
    'Furniture'           => ['icon' => 'fas fa-couch',           'class' => 'furniture'],
    'Machinery'           => ['icon' => 'fas fa-cogs',            'class' => 'machinery'],
    'Vehicle'             => ['icon' => 'fas fa-car',             'class' => 'vehicle'],
];

$extraBlocks = [
    'Total'               => ['icon' => 'fas fa-layer-group',     'class' => 'total'],
    'Scrap'               => ['icon' => 'fas fa-recycle',          'class' => 'scrap'],
    'Scrap Transfer'      => ['icon' => 'fas fa-truck-loading',    'class' => 'scrap-transfer'],
    'Fixed Asset Transfer' => ['icon' => 'fas fa-exchange-alt',    'class' => 'fa-transfer'],
    'Sold'                => ['icon' => 'fas fa-hand-holding-usd', 'class' => 'sold'],
];

// ─── Build WHERE clause for all queries ───
$whereClauses = ["branch = ?"];
$params = [$branch];
$types  = "s";

if (!empty($filterDate)) {
    $whereClauses[] = "date = ?";
    $params[]       = $filterDate;
    $types         .= "s";
}
if (!empty($filterCategory)) {
    $whereClauses[] = "category = ?";
    $params[]       = $filterCategory;
    $types         .= "s";
}
if (!empty($filterStatus)) {
    $whereClauses[] = "status = ?";
    $params[]       = $filterStatus;
    $types         .= "s";
}

$whereSQL = implode(" AND ", $whereClauses);

// ─── Summary Counts — Category based ───
$categoryCounts = [];
$stmtSummary = mysqli_prepare($con, "SELECT category, COUNT(*) AS cnt FROM fixed_assets WHERE $whereSQL GROUP BY category");
mysqli_stmt_bind_param($stmtSummary, $types, ...$params);
mysqli_stmt_execute($stmtSummary);
$resSummary = mysqli_stmt_get_result($stmtSummary);
while ($row = mysqli_fetch_assoc($resSummary)) {
    $categoryCounts[$row['category']] = (int)$row['cnt'];
}
mysqli_stmt_close($stmtSummary);

// ─── Total Count ───
$stmtTotal = mysqli_prepare($con, "SELECT COUNT(*) AS cnt FROM fixed_assets WHERE $whereSQL");
mysqli_stmt_bind_param($stmtTotal, $types, ...$params);
mysqli_stmt_execute($stmtTotal);
$resTotal = mysqli_stmt_get_result($stmtTotal);
$totalCount = (int)mysqli_fetch_assoc($resTotal)['cnt'];
mysqli_stmt_close($stmtTotal);

// ─── Scrap Count ───
$scrapWhere = $whereClauses;
// Add status=Scrap if not already filtered by status
$scrapParams = $params;
$scrapTypes  = $types;
if (empty($filterStatus)) {
    $scrapWhere[] = "status = 'Scrap'";
} 
$scrapWhereSQL = implode(" AND ", $scrapWhere);
$stmtScrap = mysqli_prepare($con, "SELECT COUNT(*) AS cnt FROM fixed_assets WHERE $scrapWhereSQL");
mysqli_stmt_bind_param($stmtScrap, $scrapTypes, ...$scrapParams);
mysqli_stmt_execute($stmtScrap);
$resScrap = mysqli_stmt_get_result($stmtScrap);
$scrapCount = (int)mysqli_fetch_assoc($resScrap)['cnt'];
mysqli_stmt_close($stmtScrap);

// ─── Fetch main asset list ───
$stmtList = mysqli_prepare($con, "SELECT id, date, category, product, company, label, amount, status FROM fixed_assets WHERE $whereSQL ORDER BY id DESC");
mysqli_stmt_bind_param($stmtList, $types, ...$params);
mysqli_stmt_execute($stmtList);
$assetList = mysqli_stmt_get_result($stmtList);

// ─── Product Count Query ───
$stmtProd = mysqli_prepare($con, "SELECT category, product, COUNT(*) AS cnt FROM fixed_assets WHERE $whereSQL GROUP BY category, product ORDER BY category, product");
mysqli_stmt_bind_param($stmtProd, $types, ...$params);
mysqli_stmt_execute($stmtProd);
$prodCountList = mysqli_stmt_get_result($stmtProd);

// ─── Category Count Query ───
$stmtCat = mysqli_prepare($con, "SELECT category, COUNT(*) AS cnt FROM fixed_assets WHERE $whereSQL GROUP BY category ORDER BY category");
mysqli_stmt_bind_param($stmtCat, $types, ...$params);
mysqli_stmt_execute($stmtCat);
$catCountList = mysqli_stmt_get_result($stmtCat);

// ─── Success message ───
$success = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added')   $success = 'Fixed asset added successfully!';
    if ($_GET['msg'] === 'updated') $success = 'Fixed asset updated successfully!';
    if ($_GET['msg'] === 'deleted') $success = 'Fixed asset deleted successfully!';
}

// Dropdown options
$categoryOptions = ['Dead Stock', 'Furniture', 'Machinery', 'Vehicle'];
$statusOptions   = ['Available', 'Pending Transfer', 'Transferred', 'Request to Delete', 'Scrap', 'Request to Sale', 'Request to Repair'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Fixed Assets | MPSC Bank Portal</title>
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/fixed-assets.css?v=1" rel="stylesheet" />
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
                    <div class="fa-page">
                        <div class="fa-panel">

                            <!-- ── PAGE HEADER ── -->
                            <div class="fa-header">
                                <h1 class="fa-title"><i class="fas fa-box"></i> FIXED ASSETS</h1>
                                <div class="fa-branch-badge">
                                    <i class="fas fa-building"></i>
                                    <?php echo htmlspecialchars($branch); ?>
                                </div>
                            </div>

                            <?php if (!empty($success)) { ?>
                                <div class="fa-alert-success" id="faSuccessAlert">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php } ?>

                            <!-- ── TOP CONTROL / FILTER SECTION ── -->
                            <form method="GET" action="fixed-assets.php" id="filterForm">
                                <div class="fa-control-bar">
                                    <!-- Date Picker -->
                                    <div class="fa-control-group">
                                        <label><i class="fas fa-calendar-alt"></i> Date</label>
                                        <input type="date" name="filter_date" class="fa-control-input" id="filterDate"
                                            value="<?php echo htmlspecialchars($filterDate); ?>" />
                                    </div>

                                    <!-- Category Dropdown -->
                                    <div class="fa-control-group">
                                        <label><i class="fas fa-th-large"></i> Category</label>
                                        <select name="filter_category" class="fa-control-input" id="filterCategory">
                                            <option value="">-- All Categories --</option>
                                            <?php foreach ($categoryOptions as $cat) { ?>
                                                <option value="<?php echo htmlspecialchars($cat); ?>"
                                                    <?php echo ($filterCategory === $cat) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <!-- Status Dropdown -->
                                    <div class="fa-control-group">
                                        <label><i class="fas fa-info-circle"></i> Status</label>
                                        <select name="filter_status" class="fa-control-input" id="filterStatus">
                                            <option value="">-- All Statuses --</option>
                                            <?php foreach ($statusOptions as $st) { ?>
                                                <option value="<?php echo htmlspecialchars($st); ?>"
                                                    <?php echo ($filterStatus === $st) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($st); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="fa-control-actions">
                                        <a href="add-fixed-asset.php" class="fa-btn fa-btn-primary" id="btnAddProduct">
                                            <i class="fas fa-plus-circle"></i> Add Product
                                        </a>
                                        <button type="submit" class="fa-btn fa-btn-refresh" id="btnRefresh">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- ── SUMMARY DASHBOARD BLOCKS ── -->
                            <div class="fa-summary-grid">
                                <?php foreach ($summaryBlocks as $blockName => $blockMeta) {
                                    $cnt = isset($categoryCounts[$blockName]) ? $categoryCounts[$blockName] : 0;
                                ?>
                                    <div class="fa-card">
                                        <div class="fa-card-icon-wrap <?php echo $blockMeta['class']; ?>">
                                            <i class="<?php echo $blockMeta['icon']; ?>"></i>
                                        </div>
                                        <div class="fa-card-label"><?php echo htmlspecialchars($blockName); ?></div>
                                        <div class="fa-card-count"><?php echo $cnt; ?></div>
                                    </div>
                                <?php } ?>

                                <?php foreach ($extraBlocks as $blockName => $blockMeta) {
                                    $cnt = 0;
                                    if ($blockName === 'Total') {
                                        $cnt = $totalCount;
                                    } elseif ($blockName === 'Scrap') {
                                        $cnt = ($filterStatus === 'Scrap' || empty($filterStatus)) ? $scrapCount : 0;
                                    }
                                    // Scrap Transfer, Fixed Asset Transfer, Sold = future modules (show 0 for now)
                                ?>
                                    <div class="fa-card <?php echo ($blockName === 'Total') ? 'fa-card-total' : ''; ?>">
                                        <div class="fa-card-icon-wrap <?php echo $blockMeta['class']; ?>">
                                            <i class="<?php echo $blockMeta['icon']; ?>"></i>
                                        </div>
                                        <div class="fa-card-label"><?php echo htmlspecialchars($blockName); ?></div>
                                        <div class="fa-card-count"><?php echo $cnt; ?></div>
                                    </div>
                                <?php } ?>
                            </div>

                            <!-- ── MAIN TABLE — FIXED ASSETS LIST ── -->
                            <div class="fa-table-wrap" id="printableArea">
                                <div class="fa-table-header-bar">
                                    <span><i class="fas fa-list"></i> Fixed Assets List — <?php echo htmlspecialchars($branch); ?></span>
                                    <span class="fa-table-count"><?php echo $totalCount; ?> records</span>
                                </div>
                                <div class="fa-table-scroll">
                                    <table class="fa-table" id="faMainTable">
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
                                                <th class="no-print">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sr = 1;
                                            if ($assetList && mysqli_num_rows($assetList) > 0) {
                                                while ($asset = mysqli_fetch_assoc($assetList)) {
                                                    $statusClass = '';
                                                    switch ($asset['status']) {
                                                        case 'Available':
                                                            $statusClass = 'fa-status-available';
                                                            break;
                                                        case 'Pending Transfer':
                                                            $statusClass = 'fa-status-pending-transfer';
                                                            break;
                                                        case 'Transferred':
                                                            $statusClass = 'fa-status-transferred';
                                                            break;
                                                        case 'Request to Delete':
                                                            $statusClass = 'fa-status-request-delete';
                                                            break;
                                                        case 'Scrap':
                                                            $statusClass = 'fa-status-scrap';
                                                            break;
                                                        case 'Request to Sale':
                                                            $statusClass = 'fa-status-request-sale';
                                                            break;
                                                        case 'Request to Repair':
                                                            $statusClass = 'fa-status-request-repair';
                                                            break;
                                                    }
                                            ?>
                                                    <tr>
                                                        <td><?php echo $sr++; ?></td>
                                                        <td><?php echo htmlspecialchars($asset['date']); ?></td>
                                                        <td><?php echo htmlspecialchars($asset['category']); ?></td>
                                                        <td><?php echo htmlspecialchars($asset['product']); ?></td>
                                                        <td><?php echo htmlspecialchars($asset['company'] ?? '—'); ?></td>
                                                        <td><?php echo htmlspecialchars($asset['label'] ?? '—'); ?></td>
                                                        <td>₹<?php echo number_format((float)$asset['amount'], 2); ?></td>
                                                        <td>
                                                            <span class="fa-status-badge <?php echo $statusClass; ?>">
                                                                <?php echo htmlspecialchars($asset['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="no-print">
                                                            <div class="fa-action-cell">
                                                                <a href="edit-fixed-asset.php?id=<?php echo $asset['id']; ?>" class="fa-edit-btn" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button type="button" class="fa-delete-btn" title="Delete"
                                                                    onclick="confirmDelete(<?php echo $asset['id']; ?>)">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                            ?>
                                                <tr>
                                                    <td colspan="9" class="fa-empty-row">
                                                        <i class="fas fa-inbox"></i> No fixed assets found. Click "Add Product" to get started.
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- ── PRODUCT COUNT & CATEGORY COUNT TABLES (side by side) ── -->
                            <div class="fa-tables-row">
                                <!-- Product Count Table -->
                                <div class="fa-table-wrap">
                                    <div class="fa-table-header-bar">
                                        <span><i class="fas fa-boxes"></i> Product Count</span>
                                    </div>
                                    <div class="fa-table-scroll">
                                        <table class="fa-table" id="faProductTable">
                                            <thead>
                                                <tr>
                                                    <th>Sr.No</th>
                                                    <th>Category</th>
                                                    <th>Product</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $psr = 1;
                                                if ($prodCountList && mysqli_num_rows($prodCountList) > 0) {
                                                    while ($prow = mysqli_fetch_assoc($prodCountList)) {
                                                ?>
                                                        <tr>
                                                            <td><?php echo $psr++; ?></td>
                                                            <td><?php echo htmlspecialchars($prow['category']); ?></td>
                                                            <td><?php echo htmlspecialchars($prow['product']); ?></td>
                                                            <td><strong><?php echo (int)$prow['cnt']; ?></strong></td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                ?>
                                                    <tr>
                                                        <td colspan="4" class="fa-empty-row">
                                                            <i class="fas fa-inbox"></i> No data
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Category Count Table -->
                                <div class="fa-table-wrap">
                                    <div class="fa-table-header-bar">
                                        <span><i class="fas fa-chart-pie"></i> Category Count</span>
                                    </div>
                                    <div class="fa-table-scroll">
                                        <table class="fa-table" id="faCategoryTable">
                                            <thead>
                                                <tr>
                                                    <th>Sr.No</th>
                                                    <th>Category</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $csr = 1;
                                                $catTotal = 0;
                                                if ($catCountList && mysqli_num_rows($catCountList) > 0) {
                                                    while ($crow = mysqli_fetch_assoc($catCountList)) {
                                                        $catTotal += (int)$crow['cnt'];
                                                ?>
                                                        <tr>
                                                            <td><?php echo $csr++; ?></td>
                                                            <td><?php echo htmlspecialchars($crow['category']); ?></td>
                                                            <td><strong><?php echo (int)$crow['cnt']; ?></strong></td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                ?>
                                                    <tr>
                                                        <td colspan="3" class="fa-empty-row">
                                                            <i class="fas fa-inbox"></i> No data
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                                <!-- TOTAL ROW -->
                                                <?php if ($catTotal > 0) { ?>
                                                    <tr class="fa-total-row">
                                                        <td></td>
                                                        <td>TOTAL</td>
                                                        <td><?php echo $catTotal; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

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

    <!-- Delete Confirmation Modal -->
    <div class="fa-modal-overlay" id="deleteModal">
        <div class="fa-modal-box">
            <div class="fa-modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="fa-modal-title">Delete Fixed Asset?</div>
            <div class="fa-modal-text">This action cannot be undone. The record will be permanently removed.</div>
            <div class="fa-modal-actions">
                <button class="fa-btn fa-btn-cancel" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <a href="#" class="fa-btn fa-btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt"></i> Delete
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="./js/scripts.js"></script>
    <script>
        // Auto-hide success alert
        const alertEl = document.getElementById('faSuccessAlert');
        if (alertEl) {
            setTimeout(() => {
                alertEl.style.transition = 'opacity 0.5s';
                alertEl.style.opacity = '0';
                setTimeout(() => alertEl.remove(), 500);
            }, 4000);
        }

        // Delete confirmation
        function confirmDelete(id) {
            document.getElementById('confirmDeleteBtn').href = 'fixed-assets.php?delete=' + id +
                '<?php
                    $qs = '';
                    if (!empty($filterDate)) $qs .= '&filter_date=' . urlencode($filterDate);
                    if (!empty($filterCategory)) $qs .= '&filter_category=' . urlencode($filterCategory);
                    if (!empty($filterStatus)) $qs .= '&filter_status=' . urlencode($filterStatus);
                    echo $qs;
                ?>';
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Close modal on overlay click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
    </script>
</body>

</html>
