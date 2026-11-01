<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

$message = '';
$msgType = 'success';

/* ─── Toggle active/inactive ─── */
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $newStatus = (int)$_GET['toggle'];

    $stmt = mysqli_prepare($con, "UPDATE circulars SET is_active=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ii", $newStatus, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $message = "Circular status updated successfully.";
}

/* ─── Permanent Delete ─── */
if (isset($_GET['del']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 1. Fetch the file name so we can delete the physical file
    $stmt = mysqli_prepare($con, "SELECT file_name FROM circulars WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $circular = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($circular) {
        // 2. Delete the uploaded PDF file from disk
        $filePath = __DIR__ . '/uploads/circulars/' . $circular['file_name'];
        if (!empty($circular['file_name']) && file_exists($filePath)) {
            unlink($filePath);
        }

        // 3. Delete the record from the database
        $stmt = mysqli_prepare($con, "DELETE FROM circulars WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Circular deleted successfully.";
        } else {
            $message = "Error deleting circular.";
            $msgType = 'danger';
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Circular not found.";
        $msgType = 'danger';
    }
}

/* ─── Collect all filters from GET ─── */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$department = isset($_GET['department']) ? trim($_GET['department']) : '';
$circular_date = isset($_GET['circular_date']) ? trim($_GET['circular_date']) : '';
$upload_date = isset($_GET['upload_date']) ? trim($_GET['upload_date']) : '';

/* ─── Build dynamic query ─── */
$sql = "SELECT * FROM circulars WHERE 1=1";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (circular_no LIKE ? OR title LIKE ? OR department LIKE ?)";
    $searchLike = "%$search%";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types .= 'sss';
}

if ($department !== '') {
    $sql .= " AND department = ?";
    $params[] = $department;
    $types .= 's';
}

/* Filter by Circular Date (publish_date) */
if ($circular_date !== '') {
    $sql .= " AND DATE(publish_date) = ?";
    $params[] = $circular_date;
    $types .= 's';
}

/* Filter by Upload Date (created_at) */
if ($upload_date !== '') {
    $sql .= " AND DATE(created_at) = ?";
    $params[] = $upload_date;
    $types .= 's';
}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($con, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$deptResult = mysqli_query($con, "SELECT department_name FROM departments WHERE is_active=1 ORDER BY department_name ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Manage Circulars | MPSC Internal Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/circular.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <?php include_once('./includes/navbar.php'); ?>

    <div id="layoutSidenav">
        <?php include_once('./includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-3">
                    <div class="circular-ref-page">

                        <?php if (!empty($message)) { ?>
                            <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
                        <?php } ?>

                        <div class="circular-ref-panel">
                            <div class="circular-ref-panel-body">

                                <div class="circular-ref-title-row">
                                    <h2 class="circular-ref-title">Bank Circulars Listing</h2>

                                    <form method="get" class="circular-ref-search">
                                        <!-- Preserve other filters when searching by text -->
                                        <input type="hidden" name="department" value="<?php echo htmlspecialchars($department); ?>">
                                        <input type="hidden" name="circular_date" value="<?php echo htmlspecialchars($circular_date); ?>">
                                        <input type="hidden" name="upload_date" value="<?php echo htmlspecialchars($upload_date); ?>">
                                        <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
                                        <button type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Single unified filter form -->
                                <form method="get" id="filterForm">
                                    <!-- Preserve text search when filtering -->
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">

                                    <div class="circular-ref-filter-row">
                                        <select name="department" class="dept-select">
                                            <option value="">All Department</option>
                                            <?php while ($dept = mysqli_fetch_assoc($deptResult)) { ?>
                                                <option value="<?php echo htmlspecialchars($dept['department_name']); ?>" <?php echo ($department == $dept['department_name']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>

                                        <div class="circular-ref-date-box">
                                            <label class="circular-ref-date-label">Circular Date</label>
                                            <input type="date" name="circular_date" value="<?php echo htmlspecialchars($circular_date); ?>" title="Filter by Circular Date">
                                            <i class="far fa-calendar-alt"></i>
                                        </div>

                                        <div class="circular-ref-date-box">
                                            <label class="circular-ref-date-label">Upload Date</label>
                                            <input type="date" name="upload_date" value="<?php echo htmlspecialchars($upload_date); ?>" title="Filter by Upload Date">
                                            <i class="far fa-calendar-alt"></i>
                                        </div>

                                        <button type="submit" class="circular-ref-icon-btn circular-ref-search-btn" title="Search by filters">
                                            <i class="fas fa-search"></i>
                                        </button>

                                        <a href="manage-circulars.php" class="circular-ref-icon-btn circular-ref-reset-btn" title="Reset all filters">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>

                                        <a href="add-circular.php" class="circular-ref-add-btn">
                                            <i class="fas fa-plus"></i> Add Circular
                                        </a>
                                    </div>
                                </form>

                                <!-- Active filter indicators -->
                                <?php if ($search !== '' || $department !== '' || $circular_date !== '' || $upload_date !== '') { ?>
                                    <div class="circular-ref-active-filters">
                                        <span class="filter-label"><i class="fas fa-filter"></i> Active Filters:</span>
                                        <?php if ($search !== '') { ?>
                                            <span class="filter-tag">Search: "<?php echo htmlspecialchars($search); ?>"</span>
                                        <?php } ?>
                                        <?php if ($department !== '') { ?>
                                            <span class="filter-tag">Dept: <?php echo htmlspecialchars($department); ?></span>
                                        <?php } ?>
                                        <?php if ($circular_date !== '') { ?>
                                            <span class="filter-tag">Circular Date: <?php echo date('d-m-Y', strtotime($circular_date)); ?></span>
                                        <?php } ?>
                                        <?php if ($upload_date !== '') { ?>
                                            <span class="filter-tag">Upload Date: <?php echo date('d-m-Y', strtotime($upload_date)); ?></span>
                                        <?php } ?>
                                        <a href="manage-circulars.php" class="filter-clear">Clear All</a>
                                    </div>
                                <?php } ?>

                                <div class="circular-ref-table-wrap">
                                    <div class="table-responsive">
                                        <table class="table-circular-ref">
                                            <thead>
                                                <tr>
                                                    <th>Sr.No.</th>
                                                    <th>Outward No</th>
                                                    <th>Circular Date</th>
                                                    <th>Circulars Title</th>
                                                    <th>Department</th>
                                                    <th>Upload Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sr = 1;
                                                if ($result && mysqli_num_rows($result) > 0) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                ?>
                                                        <tr>
                                                            <td><?php echo $sr++; ?></td>
                                                            <td><?php echo htmlspecialchars($row['circular_no']); ?></td>
                                                            <td><?php echo date('d-m-Y', strtotime($row['publish_date'])); ?></td>
                                                            <td>
                                                                <div class="circular-ref-title-text">
                                                                    <?php echo nl2br(htmlspecialchars($row['title'])); ?>
                                                                </div>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                                                            <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                                                            <td>
                                                                <div class="circular-ref-actions">
                                                                    <a href="edit-circular.php?id=<?php echo (int)$row['id']; ?>" class="circular-ref-edit">
                                                                        <i class="fas fa-pen me-1"></i>Edit
                                                                    </a>

                                                                    <a href="manage-circulars.php?id=<?php echo (int)$row['id']; ?>&del=1"
                                                                        class="circular-ref-delete"
                                                                        onclick="return confirm('Are you sure you want to permanently delete this circular? This action cannot be undone.');">
                                                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="7" class="circular-ref-empty">No circulars found.</td>
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

            <?php include_once('./includes/footer.php'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="./js/scripts.js"></script>
</body>

</html>