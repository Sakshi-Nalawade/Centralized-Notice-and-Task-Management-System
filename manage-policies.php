<?php
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

$query = mysqli_query($con, "SELECT * FROM portal_policies WHERE is_active=1 ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Manage Policies | MPSC Internal Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/policies.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include_once('./includes/navbar.php'); ?>

<div id="layoutSidenav">
    <?php include_once('./includes/sidebar.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-3">
                <div class="policies-page">
                    <div class="policies-panel">
                        <div class="policies-panel-body">

                            <div class="policies-top-row">
                                <h2 class="policies-title">Portal Policies</h2>
                            </div>

                            <div class="policies-table-wrap">
                                <div class="table-responsive">
                                    <table class="table-policies">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Policy Title</th>
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
                                                        <td><?php echo (int)$row['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($row['policy_title']); ?></td>
                                                        <td><span class="policy-file-badge">PDF</span></td>
                                                        <td>
                                                            <?php if (!empty($row['file_name'])) { ?>
                                                                <span class="policy-status-yes">Yes</span>
                                                            <?php } else { ?>
                                                                <span class="policy-status-no">No</span>
                                                            <?php } ?>
                                                        </td>
                                                        <td><?php echo !empty($row['file_name']) ? htmlspecialchars($row['file_name']) : '-'; ?></td>
                                                        <td>
                                                            <div class="policy-actions-wrap">
                                                                <a href="add-policy-file.php?id=<?php echo (int)$row['id']; ?>" class="policy-add-btn">
                                                                    <i class="fas fa-upload"></i> Add File
                                                                </a>
                                                                <a href="edit-policy.php?id=<?php echo (int)$row['id']; ?>" class="policy-edit-btn">
                                                                    <i class="fas fa-pen"></i> Edit File
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