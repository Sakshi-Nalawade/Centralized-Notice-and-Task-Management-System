<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

$message = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location:manage-circulars.php');
    exit();
}

$id = (int)$_GET['id'];

/* Remove document request */
if (isset($_GET['remove_doc']) && $_GET['remove_doc'] == '1') {
    $getFile = mysqli_prepare($con, "SELECT file_name FROM circulars WHERE id=?");
    mysqli_stmt_bind_param($getFile, "i", $id);
    mysqli_stmt_execute($getFile);
    $fileResult = mysqli_stmt_get_result($getFile);
    $fileRow = mysqli_fetch_assoc($fileResult);
    mysqli_stmt_close($getFile);

    if ($fileRow && !empty($fileRow['file_name'])) {
        $oldPath = __DIR__ . '/uploads/circulars/' . $fileRow['file_name'];
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }

        $upd = mysqli_prepare($con, "UPDATE circulars SET file_name='', original_file_name='' WHERE id=?");
        mysqli_stmt_bind_param($upd, "i", $id);
        mysqli_stmt_execute($upd);
        mysqli_stmt_close($upd);
    }

    header("location:edit-circular.php?id=" . $id);
    exit();
}

/* Update record */
if (isset($_POST['submit'])) {
    $department = trim($_POST['department']);
    $circular_no = trim($_POST['circular_no']);
    $publish_date = trim($_POST['publish_date']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($department) || empty($circular_no) || empty($publish_date) || empty($title)) {
        $error = "Please fill all required fields.";
    } else {
        /* First update text fields */
        $stmt = mysqli_prepare($con, "UPDATE circulars SET department=?, circular_no=?, publish_date=?, title=?, description=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssssi", $department, $circular_no, $publish_date, $title, $description, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            /* If new PDF uploaded */
            if (isset($_FILES['circular_file']) && $_FILES['circular_file']['error'] == 0) {
                $allowedTypes = ['application/pdf'];
                $fileTmp = $_FILES['circular_file']['tmp_name'];
                $originalFileName = $_FILES['circular_file']['name'];
                $fileSize = $_FILES['circular_file']['size'];
                $fileType = mime_content_type($fileTmp);

                if (!in_array($fileType, $allowedTypes)) {
                    $error = "Only PDF files are allowed.";
                } elseif ($fileSize > 5 * 1024 * 1024) {
                    $error = "File size must be less than 5 MB.";
                } else {
                    /* fetch old file */
                    $oldStmt = mysqli_prepare($con, "SELECT file_name FROM circulars WHERE id=?");
                    mysqli_stmt_bind_param($oldStmt, "i", $id);
                    mysqli_stmt_execute($oldStmt);
                    $oldRes = mysqli_stmt_get_result($oldStmt);
                    $oldRow = mysqli_fetch_assoc($oldRes);
                    mysqli_stmt_close($oldStmt);

                    if ($oldRow && !empty($oldRow['file_name'])) {
                        $oldPath = __DIR__ . '/uploads/circulars/' . $oldRow['file_name'];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
                    $newFileName = 'circular_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
                    $uploadPath = __DIR__ . '/uploads/circulars/' . $newFileName;

                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        $fileStmt = mysqli_prepare($con, "UPDATE circulars SET file_name=?, original_file_name=? WHERE id=?");
                        mysqli_stmt_bind_param($fileStmt, "ssi", $newFileName, $originalFileName, $id);
                        mysqli_stmt_execute($fileStmt);
                        mysqli_stmt_close($fileStmt);

                        $message = "Circular updated successfully.";
                        header('location:manage-circulars.php');
                        exit();
                    } else {
                        $error = "File upload failed while replacing old document.";
                    }
                }
            } else {
                $message = "Circular updated successfully.";
                header('location:manage-circulars.php');
                exit();
            }
        } else {
            mysqli_stmt_close($stmt);
            $error = "Failed to update circular.";
        }
    }
}

/* Fetch circular */
$getStmt = mysqli_prepare($con, "SELECT * FROM circulars WHERE id=?");
mysqli_stmt_bind_param($getStmt, "i", $id);
mysqli_stmt_execute($getStmt);
$result = mysqli_stmt_get_result($getStmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($getStmt);

if (!$row) {
    header('location:manage-circulars.php');
    exit();
}

/* Departments */
$deptQuery = mysqli_query($con, "SELECT department_name FROM departments WHERE is_active=1 ORDER BY department_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Edit Circular | MPSC Internal Portal</title>
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
                <div class="edit-circular-page">

                    <?php if (!empty($message)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php } ?>

                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="edit-circular-panel">
                        <h2 class="edit-circular-title">Bank Circular Edit</h2>

                        <div class="edit-circular-body">
                            <form method="post" enctype="multipart/form-data" class="edit-circular-form">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label class="edit-label">Department</label>
                                                <select name="department" class="form-select" required>
                                                    <option value="">select department</option>
                                                    <?php while ($dept = mysqli_fetch_assoc($deptQuery)) { ?>
                                                        <option value="<?php echo htmlspecialchars($dept['department_name']); ?>"
                                                            <?php echo ($row['department'] == $dept['department_name']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="edit-label">Entry Date</label>
                                                <input type="text" class="form-control" value="<?php echo date('d-m-Y H:i A', strtotime($row['created_at'])); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label class="edit-label"><span class="req">*</span>Outward Number</label>
                                                <input type="text" name="circular_no" class="form-control" value="<?php echo htmlspecialchars($row['circular_no']); ?>" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="edit-label"><span class="req">*</span>Circular Date</label>
                                                <input type="date" name="publish_date" class="form-control" value="<?php echo htmlspecialchars($row['publish_date']); ?>" required>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="edit-label"><span class="req">*</span>Circular Title</label>
                                            <textarea name="title" class="form-control" rows="3" required><?php echo htmlspecialchars($row['title']); ?></textarea>
                                        </div>

                                        <div class="mb-4">
                                            <label class="edit-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($row['description']); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label class="edit-label"><span class="req">*</span>Upload PDF Document</label>

                                        <div class="doc-action-row">
                                            <?php if (!empty($row['file_name'])) { ?>
                                                <a href="uploads/circulars/<?php echo rawurlencode($row['file_name']); ?>" target="_blank" class="doc-btn-view">View Document</a>
                                                <a href="edit-circular.php?id=<?php echo $id; ?>&remove_doc=1" class="doc-btn-remove"
                                                   onclick="return confirm('Remove current document?');">Remove Document</a>
                                            <?php } ?>
                                        </div>

                                        <div class="pdf-preview-box mb-3">
                                            <?php if (!empty($row['file_name'])) { ?>
                                                <iframe src="uploads/circulars/<?php echo rawurlencode($row['file_name']); ?>"></iframe>
                                            <?php } else { ?>
                                                <div style="color:#fff; padding:20px;">No document available.</div>
                                            <?php } ?>
                                        </div>

                                        <input type="file" name="circular_file" class="form-control" accept=".pdf">
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" name="submit" class="save-btn-ref">
                                        <i class="fas fa-check"></i>Save
                                    </button>
                                </div>
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