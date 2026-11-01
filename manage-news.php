<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

if (isset($_GET['del']) && is_numeric($_GET['del'])) {
    $id = (int)$_GET['del'];

    $getStmt = mysqli_prepare($con, "SELECT image_name FROM news WHERE id=?");
    mysqli_stmt_bind_param($getStmt, "i", $id);
    mysqli_stmt_execute($getStmt);
    $res = mysqli_stmt_get_result($getStmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($getStmt);

    if ($row && !empty($row['image_name'])) {
        $imgPath = __DIR__ . '/uploads/news/' . $row['image_name'];
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
    }

    $stmt = mysqli_prepare($con, "DELETE FROM news WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: manage-news.php");
    exit();
}

$query = mysqli_query($con, "SELECT * FROM news ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Manage News | MPSC Internal Portal</title>
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
                    <div class="news-panel">
                        <div class="news-panel-body">

                            <div class="news-top-row">
                                <h2 class="news-title">News Listing</h2>
                                <a href="add-news.php" class="news-add-btn">Add News</a>
                            </div>

                            <div class="news-table-wrap">
                                <div class="table-responsive">
                                    <table class="table-news-ref">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Image</th>
                                                <th>Summary</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($query && mysqli_num_rows($query) > 0) { ?>
                                                <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                                                    <tr>
                                                        <td><?php echo (int)$row['id']; ?></td>
                                                        <td>
                                                            <img src="./uploads/news/<?php echo htmlspecialchars($row['image_name']); ?>" class="news-thumb" alt="News">
                                                        </td>
                                                        <td><?php echo nl2br(htmlspecialchars($row['summary'])); ?></td>
                                                        <td><?php echo date('d-m-Y h:i A', strtotime($row['created_at'])); ?></td>
                                                        <td>
                                                            <div class="news-actions">
                                                                <a href="edit-news.php?id=<?php echo (int)$row['id']; ?>" class="news-edit-btn">
                                                                    <i class="fas fa-pen"></i> Edit
                                                                </a>
                                                                <a href="manage-news.php?del=<?php echo (int)$row['id']; ?>" class="news-delete-btn" onclick="return confirm('Delete this news?');">
                                                                    <i class="fas fa-trash-alt"></i> Delete
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="5" class="news-empty">No news available.</td>
                                                </tr>
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