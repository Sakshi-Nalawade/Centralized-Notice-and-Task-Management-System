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
    $circular_no = trim($_POST['circular_no']);
    $title = trim($_POST['title']);
    $department = trim($_POST['department']);
    $description = trim($_POST['description']);
    $publish_date = trim($_POST['publish_date']);
    $created_by = $_SESSION['adminid'];

    if (empty($circular_no) || empty($title) || empty($department) || empty($publish_date)) {
        $error = "Please fill all required fields.";
    } elseif (!isset($_FILES['circular_file']) || $_FILES['circular_file']['error'] != 0) {
        $error = "Please upload a PDF file.";
    } else {
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
            $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
            $newFileName = 'circular_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
            $uploadPath = __DIR__ . '/uploads/circulars/' . $newFileName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $stmt = mysqli_prepare($con, "INSERT INTO circulars (circular_no, title, department, description, file_name, original_file_name, publish_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssssssi", $circular_no, $title, $department, $description, $newFileName, $originalFileName, $publish_date, $created_by);

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header('location:manage-circulars.php');
                    exit();
                } else {
                    $error = "Database error while saving circular.";
                    mysqli_stmt_close($stmt);
                }
            } else {
                $error = "File upload failed. Please check folder permissions.";
            }
        }
    }
}

$deptQuery = mysqli_query($con, "SELECT department_name FROM departments WHERE is_active = 1 ORDER BY department_name ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Add Circular | MPSC Internal Portal</title>
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
                    <div class="add-circular-page">

                        <?php if (!empty($message)) { ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                        <?php } ?>

                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php } ?>

                        <div class="add-circular-panel">
                            <h2 class="add-circular-title">Bank Circular Add</h2>

                            <div class="add-circular-body">
                                <form method="post" enctype="multipart/form-data" class="add-circular-form">
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <label class="add-label">Department</label>
                                                    <select name="department" class="form-select" required>
                                                        <option value="">select department</option>
                                                        <?php if ($deptQuery && mysqli_num_rows($deptQuery) > 0) { ?>
                                                            <?php while ($dept = mysqli_fetch_assoc($deptQuery)) { ?>
                                                                <option value="<?php echo htmlspecialchars($dept['department_name']); ?>">
                                                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                                                </option>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="add-label">Entry Date</label>
                                                    <input type="text" id="entryDateTime" class="form-control" readonly>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <label class="add-label"><span class="req">*</span>Outward Number</label>
                                                    <input type="text" name="circular_no" class="form-control" required>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="add-label"><span class="req">*</span>Circular Date</label>
                                                    <input type="date" name="publish_date" class="form-control" required>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="add-label"><span class="req">*</span>Circular Title</label>
                                                <textarea name="title" class="form-control" rows="3" required></textarea>
                                            </div>

                                            <div class="mb-4">
                                                <label class="add-label">Description</label>
                                                <textarea name="description" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <label class="add-label"><span class="req">*</span>Upload PDF Document</label>

                                            <div class="upload-doc-box">
                                                <div class="upload-doc-icon">
                                                    <i class="fas fa-download"></i>
                                                </div>

                                                <div class="upload-doc-text">Choose a file</div>

                                                <input type="file" name="circular_file" class="form-control" accept=".pdf" required>

                                                <button type="button" class="upload-btn-ref">
                                                    <i class="fas fa-link me-1"></i> Choose file to upload
                                                </button>
                                            </div>
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

    <script>
        function updateDateTime() {

            const now = new Date();

            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();

            let hours = now.getHours();
            let minutes = String(now.getMinutes()).padStart(2, '0');

            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;

            const formatted =
                day + "-" +
                month + "-" +
                year + " " +
                hours + ":" +
                minutes + " " +
                ampm;

            document.getElementById("entryDateTime").value = formatted;
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>
</body>

</html>