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
    $summary = trim($_POST['summary']);

    if (empty($summary)) {
        $error = "Please enter news summary.";
    } elseif (!isset($_FILES['news_image']) || $_FILES['news_image']['error'] != 0) {
        $error = "Please upload news image.";
    } else {
        $uploadDir = __DIR__ . '/uploads/news/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imgName = time() . '_' . basename($_FILES['news_image']['name']);
        $targetPath = $uploadDir . $imgName;
        $imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($imageFileType, $allowed)) {
            $error = "Only JPG, JPEG, PNG, WEBP images are allowed.";
        } else {
            if (move_uploaded_file($_FILES['news_image']['tmp_name'], $targetPath)) {
                $stmt = mysqli_prepare($con, "INSERT INTO news (summary, image_name) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "ss", $summary, $imgName);

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: manage-news.php");
                    exit();
                } else {
                    $error = "Database error while saving news.";
                    mysqli_stmt_close($stmt);
                }
            } else {
                $error = "Failed to upload image.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Add News | MPSC Internal Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/news.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include_once('./includes/navbar.php'); ?>

<div id="layoutSidenav">
    <?php include_once('./includes/sidebar.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-3">
                <div class="news-page">

                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="news-panel">
                        <div class="news-panel-body">
                            <div class="news-top-row">
                                <h2 class="news-title">Add News</h2>
                            </div>

                            <form method="post" enctype="multipart/form-data">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <label class="news-form-label"><span class="req">*</span>News Summary</label>
                                        <textarea name="summary" class="form-control" required></textarea>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="news-form-label"><span class="req">*</span>Upload News Image</label>
                                        <input type="file" name="news_image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                                    </div>
                                </div>

                                <button type="submit" name="submit" class="news-save-btn">
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