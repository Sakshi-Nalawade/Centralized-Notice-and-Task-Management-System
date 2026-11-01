<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

$error = '';

if (isset($_POST['submit'])) {
    $outward_no    = trim($_POST['outward_no']);
    $outward_date  = trim($_POST['outward_date']);
    $outward_title = trim($_POST['outward_title']);
    $entry_by      = trim($_POST['entry_by']);

    if (empty($outward_no) || empty($outward_date) || empty($outward_title) || empty($entry_by)) {
        $error = "Please fill all required fields.";
    } else {
        // Check for duplicate outward_no
        $checkStmt = mysqli_prepare($con, "SELECT id FROM outwards WHERE outward_no = ?");
        mysqli_stmt_bind_param($checkStmt, "i", $outward_no);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $error = "Outward number already exists. Please enter a unique outward number.";
        } else {
            $stmt = mysqli_prepare($con, "INSERT INTO outwards (outward_no, outward_title, entry_by, outward_date) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isss", $outward_no, $outward_title, $entry_by, $outward_date);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_stmt_close($checkStmt);
                header("Location: manage-outward.php");
                exit();
            } else {
                $error = "Something went wrong while saving outward entry.";
                mysqli_stmt_close($stmt);
            }
        }

        mysqli_stmt_close($checkStmt);
    }
}

// Auto-fill entry_by from session
$default_entry_by = isset($_SESSION['login']) ? $_SESSION['login'] : 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Add Outward | MPSC Internal Portal</title>
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

                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="outward-panel">
                        <div class="outward-panel-body">

                            <div class="outward-top-row">
                                <h2 class="outward-title">Add Outward Entry</h2>
                                <a href="manage-outward.php" class="outward-back-link">
                                    <i class="fas fa-arrow-left"></i> Back to Listing
                                </a>
                            </div>

                            <form method="post">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="outward-form-label"><span class="req">*</span> Outward No</label>
                                        <input type="number" name="outward_no" class="form-control"
                                               value="<?php echo isset($_POST['outward_no']) ? htmlspecialchars($_POST['outward_no']) : ''; ?>"
                                               required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="outward-form-label"><span class="req">*</span> Outward Date</label>
                                        <input type="date" name="outward_date" class="form-control"
                                               value="<?php echo isset($_POST['outward_date']) ? htmlspecialchars($_POST['outward_date']) : ''; ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="outward-form-label"><span class="req">*</span> Outward Title</label>
                                        <input type="text" name="outward_title" class="form-control"
                                               value="<?php echo isset($_POST['outward_title']) ? htmlspecialchars($_POST['outward_title']) : ''; ?>"
                                               required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="outward-form-label"><span class="req">*</span> Outward Entry By</label>
                                        <input type="text" name="entry_by" class="form-control"
                                               value="<?php echo isset($_POST['entry_by']) ? htmlspecialchars($_POST['entry_by']) : htmlspecialchars($default_entry_by); ?>"
                                               required>
                                    </div>
                                </div>

                                <button type="submit" name="submit" class="outward-save-btn">
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