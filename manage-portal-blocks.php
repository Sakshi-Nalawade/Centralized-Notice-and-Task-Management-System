<?php
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    // Get file info before deleting
    $delStmt = mysqli_prepare($con, "SELECT file_name FROM portal_blocks WHERE id=?");
    mysqli_stmt_bind_param($delStmt, "i", $delId);
    mysqli_stmt_execute($delStmt);
    $delResult = mysqli_stmt_get_result($delStmt);
    $delRow = mysqli_fetch_assoc($delResult);
    mysqli_stmt_close($delStmt);

    if ($delRow) {
        // Delete the uploaded file if it exists
        if (!empty($delRow['file_name'])) {
            $filePath = __DIR__ . '/uploads/portal-blocks/' . $delRow['file_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        // Delete the record
        $delStmt2 = mysqli_prepare($con, "DELETE FROM portal_blocks WHERE id=?");
        mysqli_stmt_bind_param($delStmt2, "i", $delId);
        mysqli_stmt_execute($delStmt2);
        mysqli_stmt_close($delStmt2);
    }
    header("Location: manage-portal-blocks.php");
    exit();
}

$query = mysqli_query($con, "SELECT * FROM portal_blocks WHERE is_active=1 ORDER BY display_order ASC, id ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Manage Portal Downloads | MPSC Internal Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/portal-blocks.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <?php include_once('./includes/navbar.php'); ?>

    <div id="layoutSidenav">
        <?php include_once('./includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-3">
                    <div class="portal-blocks-page">
                        <div class="portal-blocks-panel">
                            <div class="portal-blocks-panel-body">

                                <div class="portal-blocks-top-row">
                                    <h2 class="portal-blocks-title">Portal Download Blocks</h2>
                                    <a href="add-portal-block.php" class="pb-add-new-btn">
                                        <i class="fas fa-plus"></i> Add New Block
                                    </a>
                                </div>

                                <div class="portal-blocks-table-wrap">
                                    <div class="table-responsive">
                                        <table class="table-portal-blocks">
                                            <thead>
                                                <tr>
                                                    <th>Order</th>
                                                    <th>Block Title</th>
                                                    <th>File Type</th>
                                                    <th>File Uploaded</th>
                                                    <th>Current File</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($query && mysqli_num_rows($query) > 0) { ?>
                                                    <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                                                        <tr>
                                                            <td><?php echo (int)$row['display_order']; ?></td>
                                                            <td><?php echo htmlspecialchars($row['block_title']); ?></td>
                                                            <td><span class="pb-file-badge"><?php echo strtoupper(htmlspecialchars($row['file_type'])); ?></span></td>
                                                            <td>
                                                                <?php if (!empty($row['file_name'])) { ?>
                                                                    <span class="pb-status-yes">Yes</span>
                                                                <?php } else { ?>
                                                                    <span class="pb-status-no">No</span>
                                                                <?php } ?>
                                                            </td>
                                                            <td>
                                                                <?php echo !empty($row['file_name']) ? htmlspecialchars($row['file_name']) : '-'; ?>
                                                            </td>
                                                            <td>
                                                                <div class="pb-actions-wrap">
                                                                    <a href="add-portal-block-file.php?id=<?php echo (int)$row['id']; ?>" class="pb-add-btn">
                                                                        <i class="fas fa-upload"></i> Add File
                                                                    </a>

                                                                    <a href="edit-portal-blocks.php?id=<?php echo (int)$row['id']; ?>" class="pb-action-btn">
                                                                        <i class="fas fa-pen"></i> Edit File
                                                                    </a>

                                                                    <a href="manage-portal-blocks.php?delete=<?php echo (int)$row['id']; ?>" class="pb-delete-btn" onclick="return confirm('Are you sure you want to delete this block?');">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
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