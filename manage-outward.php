<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

// Soft delete
if (isset($_GET['del']) && is_numeric($_GET['del'])) {
    $id = (int)$_GET['del'];

    $stmt = mysqli_prepare($con, "UPDATE outwards SET is_active = 0 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: manage-outward.php");
    exit();
}

// Fetch only active entries
$query = mysqli_query($con, "SELECT * FROM outwards WHERE is_active = 1 ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Manage Outward | MPSC Internal Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/outward.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include_once('./includes/navbar.php'); ?>

<div id="layoutSidenav">
    <?php include_once('./includes/sidebar.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-3">
                <div class="outward-page">

                    <div class="outward-panel">
                        <div class="outward-panel-body">

                            <div class="outward-top-row">
                                <h2 class="outward-title">Outward Listing</h2>
                                <a href="add-outward.php" class="outward-add-btn">Add</a>
                            </div>

                            <div class="outward-table-wrap">
                                <div class="table-responsive">
                                    <table class="table-outward-ref">
                                        <thead>
                                            <tr>
                                                <th>Sr.<br>No</th>
                                                <th>Outward<br>No</th>
                                                <th>Outward Entry Date</th>
                                                <th>Outward Title</th>
                                                <th>Outward Entry<br>By</th>
                                                <th>Outward Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($query && mysqli_num_rows($query) > 0) { ?>
                                                <?php $sr = 1; ?>
                                                <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                                                    <tr>
                                                        <td><?php echo $sr++; ?></td>
                                                        <td><?php echo htmlspecialchars($row['outward_no']); ?></td>
                                                        <td>
                                                            <?php echo date('Y-m-d H:i:s', strtotime($row['entry_date'])); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['outward_title']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['entry_by']); ?></td>
                                                        <td>
                                                            <?php echo date('Y-m-d', strtotime($row['outward_date'])); ?>
                                                        </td>
                                                        <td>
                                                            <div class="outward-actions">
                                                                <a href="edit-outward.php?id=<?php echo (int)$row['id']; ?>" class="outward-edit-btn">
                                                                    <i class="fas fa-pen"></i> Edit
                                                                </a>
                                                                <a href="manage-outward.php?del=<?php echo (int)$row['id']; ?>"
                                                                   class="outward-delete-btn"
                                                                   onclick="return confirm('Delete this outward entry?');">
                                                                    <i class="fas fa-trash-alt"></i> Delete
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="7" class="outward-empty">No outward entries found.</td>
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