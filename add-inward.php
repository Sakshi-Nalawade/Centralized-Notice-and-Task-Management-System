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

if (isset($_POST['submit'])) {
    $inward_no = trim($_POST['inward_no']);
    $letter_date = trim($_POST['letter_date']);
    $inward_from = trim($_POST['inward_from']);
    $details = trim($_POST['details']);

    if (empty($inward_no) || empty($letter_date) || empty($inward_from) || empty($details)) {
        $error = "Please fill all required fields.";
    } else {
        $checkStmt = mysqli_prepare($con, "SELECT id FROM inwards WHERE inward_no=?");
        mysqli_stmt_bind_param($checkStmt, "i", $inward_no);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $error = "Inward number already exists. Please enter a unique inward number.";
        } else {
            $stmt = mysqli_prepare($con, "INSERT INTO inwards (inward_no, letter_date, inward_from, details) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isss", $inward_no, $letter_date, $inward_from, $details);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_stmt_close($checkStmt);
                header("Location: manage-inward.php");
                exit();
            } else {
                $error = "Database error while saving inward entry.";
                mysqli_stmt_close($stmt);
            }
        }

        mysqli_stmt_close($checkStmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Add Inward | MPSC Internal Portal</title>
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
                                    <h2 class="inward-title">Add</h2>
                                </div>

                                <form method="post">
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <label class="inward-form-label"><span class="req">*</span>Inward No</label>
                                            <input type="number" name="inward_no" class="form-control" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="inward-form-label"><span class="req">*</span>Letter Date</label>
                                            <input type="date" name="letter_date" class="form-control" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="inward-form-label"><span class="req">*</span>Inward From</label>
                                            <input type="text" name="inward_from" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <label class="inward-form-label"><span class="req">*</span>Details</label>
                                            <textarea name="details" class="form-control" rows="3" required></textarea>
                                        </div>
                                    </div>

                                    <button type="submit" name="submit" class="inward-save-btn">
                                        <i class="fas fa-check"></i> Save
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