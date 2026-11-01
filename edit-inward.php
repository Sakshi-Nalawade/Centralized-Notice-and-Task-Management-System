<?php
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location:manage-inward.php');
    exit();
}

$id = (int)$_GET['id'];
$error = '';

// Fetch existing record
$stmt = mysqli_prepare($con, "SELECT * FROM inwards WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    header('location:manage-inward.php');
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $inward_no   = trim($_POST['inward_no']);
    $letter_date = trim($_POST['letter_date']);
    $inward_from = trim($_POST['inward_from']);
    $details     = trim($_POST['details']);

    if (empty($inward_no) || empty($letter_date) || empty($inward_from) || empty($details)) {
        $error = "Please fill all required fields.";
    } else {
        // Check if inward_no is unique (excluding current record)
        $checkStmt = mysqli_prepare($con, "SELECT id FROM inwards WHERE inward_no=? AND id!=?");
        mysqli_stmt_bind_param($checkStmt, "ii", $inward_no, $id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $error = "Inward number already exists. Please enter a unique inward number.";
            mysqli_stmt_close($checkStmt);
        } else {
            mysqli_stmt_close($checkStmt);

            $updateStmt = mysqli_prepare($con, "UPDATE inwards SET inward_no=?, letter_date=?, inward_from=?, details=? WHERE id=?");
            mysqli_stmt_bind_param($updateStmt, "isssi", $inward_no, $letter_date, $inward_from, $details, $id);

            if (mysqli_stmt_execute($updateStmt)) {
                mysqli_stmt_close($updateStmt);
                header("Location: manage-inward.php");
                exit();
            } else {
                $error = "Database error while updating inward entry.";
                mysqli_stmt_close($updateStmt);
            }
        }
    }

    // Refresh row data on error so form shows submitted values
    $row['inward_no']   = $inward_no;
    $row['letter_date']  = $letter_date;
    $row['inward_from']  = $inward_from;
    $row['details']      = $details;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Edit Inward | MPSC Internal Portal</title>
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

                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php } ?>

                        <div class="inward-panel">
                            <div class="inward-panel-body">

                                <div class="inward-top-row">
                                    <h2 class="inward-title">Edit Inward</h2>
                                    <a href="manage-inward.php" class="inward-edit-btn">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                </div>

                                <form method="post">
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <label class="inward-form-label"><span class="req">*</span>Inward No</label>
                                            <input type="number" name="inward_no" class="form-control" required
                                                value="<?php echo (int)$row['inward_no']; ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="inward-form-label"><span class="req">*</span>Letter Date</label>
                                            <input type="date" name="letter_date" class="form-control" required
                                                value="<?php echo htmlspecialchars($row['letter_date']); ?>">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="inward-form-label"><span class="req">*</span>Inward From</label>
                                            <input type="text" name="inward_from" class="form-control" required
                                                value="<?php echo htmlspecialchars($row['inward_from']); ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <label class="inward-form-label"><span class="req">*</span>Details</label>
                                            <textarea name="details" class="form-control" rows="3" required><?php echo htmlspecialchars($row['details']); ?></textarea>
                                        </div>
                                    </div>

                                    <button type="submit" name="submit" class="inward-save-btn">
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