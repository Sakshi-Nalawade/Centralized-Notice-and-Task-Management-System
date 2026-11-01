<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

$message = '';

if (isset($_GET['del']) && is_numeric($_GET['del'])) {
    $id = (int)$_GET['del'];

    $stmt = mysqli_prepare($con, "DELETE FROM inwards WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: manage-inward.php");
    exit();
}

$query = mysqli_query($con, "SELECT * FROM inwards ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Manage Inward | MPSC Internal Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/inward.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include_once('./includes/navbar.php'); ?>

<div id="layoutSidenav">
    <?php include_once('./includes/sidebar.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-3">
                <div class="inward-page">

                    <div class="inward-panel">
                        <div class="inward-panel-body">

                            <div class="inward-top-row">
                                <h2 class="inward-title">Inward Listing</h2>
                                <a href="add-inward.php" class="inward-add-btn">Add</a>
                            </div>

                            <div class="inward-table-wrap">
                                <div class="table-responsive">
                                    <table class="table-inward-ref">
                                        <thead>
                                            <tr>
                                                <th>Idinward<br>Outward</th>
                                                <th>Inward<br>No</th>
                                                <th>Inward Entry Date</th>
                                                <th>Inward Title</th>
                                                <th>Inward Entry<br>By</th>
                                                <th>Inward Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($query && mysqli_num_rows($query) > 0) { ?>
                                                <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                                                    <tr>
                                                        <td><?php echo (int)$row['id']; ?></td>

                                                        <td><?php echo htmlspecialchars($row['inward_no']); ?></td>

                                                        <td>
                                                            <?php echo date('Y-m-d', strtotime($row['entry_date'])); ?><br>
                                                            <?php echo date('H:i:s', strtotime($row['entry_date'])); ?>
                                                        </td>

                                                        <td><?php echo htmlspecialchars($row['details']); ?></td>

                                                        <td><?php echo htmlspecialchars($row['inward_from']); ?></td>

                                                        <td>
                                                            <?php echo date('Y-m-d', strtotime($row['letter_date'])); ?>
                                                        </td>

                                                        <td>
                                                            <div class="inward-actions">
                                                                <a href="edit-inward.php?id=<?php echo (int)$row['id']; ?>" class="inward-edit-btn">
                                                                    <i class="fas fa-pen"></i> Edit
                                                                </a>

                                                                <a href="manage-inward.php?del=<?php echo (int)$row['id']; ?>"
                                                                   class="inward-delete-btn"
                                                                   onclick="return confirm('Delete this inward entry?');">
                                                                    <i class="fas fa-trash-alt"></i> Delete
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="7" class="inward-empty">No inward entries found.</td>
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