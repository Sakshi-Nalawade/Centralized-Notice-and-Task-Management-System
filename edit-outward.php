<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

$error = '';

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage-outward.php");
    exit();
}

$id = (int)$_GET['id'];

// Fetch existing record
$fetchStmt = mysqli_prepare($con, "SELECT * FROM outwards WHERE id = ? AND is_active = 1");
mysqli_stmt_bind_param($fetchStmt, "i", $id);
mysqli_stmt_execute($fetchStmt);
$result = mysqli_stmt_get_result($fetchStmt);

if (!$result || mysqli_num_rows($result) == 0) {
    mysqli_stmt_close($fetchStmt);
    header("Location: manage-outward.php");
    exit();
}

$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($fetchStmt);

// Handle form submission
if (isset($_POST['submit'])) {
    $outward_no    = trim($_POST['outward_no']);
    $outward_date  = trim($_POST['outward_date']);
    $outward_title = trim($_POST['outward_title']);
    $entry_by      = trim($_POST['entry_by']);

    if (empty($outward_no) || empty($outward_date) || empty($outward_title) || empty($entry_by)) {
        $error = "Please fill all required fields.";
    } else {
        // Check for duplicate outward_no (exclude current record)
        $checkStmt = mysqli_prepare($con, "SELECT id FROM outwards WHERE outward_no = ? AND id != ?");
        mysqli_stmt_bind_param($checkStmt, "ii", $outward_no, $id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $error = "Outward number already exists. Please enter a unique outward number.";
        } else {
            $stmt = mysqli_prepare($con, "UPDATE outwards SET outward_no = ?, outward_title = ?, entry_by = ?, outward_date = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "isssi", $outward_no, $outward_title, $entry_by, $outward_date, $id);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_stmt_close($checkStmt);
                header("Location: manage-outward.php");
                exit();
            } else {
                $error = "Something went wrong while updating outward entry.";
                mysqli_stmt_close($stmt);
            }
        }

        mysqli_stmt_close($checkStmt);
    }

    // Refresh data for display after validation error
    $data['outward_no']    = $outward_no;
    $data['outward_date']  = $outward_date;
    $data['outward_title'] = $outward_title;
    $data['entry_by']      = $entry_by;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Edit Outward | MPSC Internal Portal</title>
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
                                <h2 class="outward-title">Edit Outward Entry</h2>
                                <a href="manage-outward.php" class="outward-back-link">
                                    <i class="fas fa-arrow-left"></i> Back to Listing
                                </a>
                            </div>

                            <form method="post" action="edit-outward.php?id=<?php echo $id; ?>">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="outward-form-label"><span class="req">*</span> Outward No</label>
                                        <input type="number" name="outward_no" class="form-control"
                                               value="<?php echo htmlspecialchars($data['outward_no']); ?>"
                                               required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="outward-form-label"><span class="req">*</span> Outward Date</label>
                                        <input type="date" name="outward_date" class="form-control"
                                               value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($data['outward_date']))); ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="outward-form-label"><span class="req">*</span> Outward Title</label>
                                        <input type="text" name="outward_title" class="form-control"
                                               value="<?php echo htmlspecialchars($data['outward_title']); ?>"
                                               required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="outward-form-label"><span class="req">*</span> Outward Entry By</label>
                                        <input type="text" name="entry_by" class="form-control"
                                               value="<?php echo htmlspecialchars($data['entry_by']); ?>"
                                               required>
                                    </div>
                                </div>

                                <button type="submit" name="submit" class="outward-save-btn">
                                    <i class="fas fa-save"></i> Update
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