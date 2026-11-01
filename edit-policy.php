<?php
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location:manage-policies.php');
    exit();
}

$id = (int)$_GET['id'];
$error = '';

$stmt = mysqli_prepare($con, "SELECT * FROM portal_policies WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    header('location:manage-policies.php');
    exit();
}

if (isset($_POST['submit'])) {
    $fileName = $row['file_name']; // keep old file by default

    if (!empty($_FILES['policy_file']['name'])) {
        $uploadDir = __DIR__ . '/uploads/policies/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newFileName = time() . '_' . basename($_FILES['policy_file']['name']);
        $targetPath = $uploadDir . $newFileName;
        $ext = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            $error = "Only PDF files are allowed.";
        } else {
            if (move_uploaded_file($_FILES['policy_file']['tmp_name'], $targetPath)) {
                if (!empty($row['file_name'])) {
                    $oldPath = $uploadDir . $row['file_name'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $fileName = $newFileName;
            } else {
                $error = "Failed to upload file.";
            }
        }
    }

    if (empty($error)) {
        $update = mysqli_prepare($con, "UPDATE portal_policies SET file_name=? WHERE id=?");
        mysqli_stmt_bind_param($update, "si", $fileName, $id);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);

        header("Location: manage-policies.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Edit Policy File | MPSC Internal Portal</title>
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
                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="policies-panel">
                        <div class="policies-panel-body">

                            <div class="policies-top-row">
                                <h2 class="policies-title">Edit Policy File</h2>
                            </div>

                            <form method="post" enctype="multipart/form-data">
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <label class="policy-form-label">Policy Name</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['policy_title']); ?>" readonly>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <label class="policy-form-label">Current File</label>
                                        <?php if (!empty($row['file_name'])) { ?>
                                            <div class="mb-2">
                                                <a href="./uploads/policies/<?php echo rawurlencode($row['file_name']); ?>" target="_blank" class="btn btn-info btn-sm">
                                                    View Current File
                                                </a>
                                            </div>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['file_name']); ?>" readonly>
                                        <?php } else { ?>
                                            <input type="text" class="form-control" value="No file uploaded" readonly>
                                        <?php } ?>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <label class="policy-form-label">Upload New PDF</label>
                                        <input type="file" name="policy_file" class="form-control" accept=".pdf">
                                        <div class="policy-current-file">
                                            If you do not choose a new file, the existing file will remain unchanged.
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" name="submit" class="policy-save-btn">
                                    <i class="fas fa-check"></i> Update
                                </button>
                            </form>

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