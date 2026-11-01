<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

$message = '';

// Delete photo event
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idphoto_event = (int)$_GET['id'];

    // First delete from DB using prepared statement
    $stmt = mysqli_prepare($con, "DELETE FROM photo_event WHERE idphoto_event=?");
    mysqli_stmt_bind_param($stmt, "i", $idphoto_event);
    $msg = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($msg) {
        // Delete associated image files
        $dir = "./assets/photo_gallery/" . $idphoto_event . "/";

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        $url = $dir . $file;
                        if (file_exists($url)) {
                            unlink($url);
                        }
                    }
                }
                closedir($dh);
            }
            rmdir($dir);
        }

        $message = "Photo event deleted successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Photo Event List | MPSC Internal Portal</title>
        <link href="./css/styles.css" rel="stylesheet" />
        <link href="./css/custom.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
        <style>
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            th { background-color: #f4f4f4; }
            .pagination { margin: 20px 0; }
            .pagination a { text-decoration: none; padding: 8px 12px; border: 1px solid #ddd; margin: 0 5px; }
            .pagination a.active { background-color: #007bff; color: white; }
        </style>
    </head>
    <body class="sb-nav-fixed">
      <?php include_once('includes/navbar.php');?>
        <div id="layoutSidenav">
         <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Photo Event List</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="manage-circulars.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Photo Event List</li>
                        </ol>

                        <?php if (!empty($message)) { ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                        <?php } ?>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Photo Event List
                                <div class="btn btn-success btn-sm pull-right" onclick="window.location='photo_gallery.php'"> <i class="fas fa-plus"></i> Add</div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                   <thead>
                                        <tr>
                                            <th>Sno.</th>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Visible</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            // Set pagination variables
                                            $limit = 10;
                                            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                                            $start = ($page - 1) * $limit;

                                            // Get total records
                                            $countResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM photo_event");
                                            $countRow = mysqli_fetch_assoc($countResult);
                                            $totalRecords = (int)$countRow['total'];
                                            $totalPages = ceil($totalRecords / $limit);

                                            // Fetch paginated records using prepared statement
                                            $stmt = mysqli_prepare($con, "SELECT * FROM photo_event LIMIT ?, ?");
                                            mysqli_stmt_bind_param($stmt, "ii", $start, $limit);
                                            mysqli_stmt_execute($stmt);
                                            $ret = mysqli_stmt_get_result($stmt);
                                            $cnt = 1 + $start;
                                            while ($row = mysqli_fetch_assoc($ret)) { ?>
                                          <tr>
                                          <td><?php echo $cnt;?></td>
                                                <td><?php echo htmlspecialchars($row['pe_title']);?></td>
                                              <td><?php echo htmlspecialchars($row['pe_description']);?></td>
                                              <td><?php echo $row["pe_visible"] == 1 ? 'Yes' : 'No';?></td>
                                              <td>
                                                  <a href="photo_gallery.php?id=<?php echo (int)$row['idphoto_event'];?>" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                  </a>
                                                 <a href="photo_event_list.php?id=<?php echo (int)$row['idphoto_event'];?>" class="btn btn-danger btn-sm" onClick="return confirm('Do you really want to delete this event?');"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                              </td>
                                          </tr>
                                          <?php $cnt++; }
                                          mysqli_stmt_close($stmt);
                                        ?>
                                    </tbody>
                                </table>
                                <?php if ($totalPages > 1) { ?>
                                <div class="pagination">
                                    <?php if ($page > 1) { ?>
                                        <a href="?page=<?php echo $page - 1; ?>">« Prev</a>
                                    <?php } ?>

                                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                                        <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                    <?php } ?>

                                    <?php if ($page < $totalPages) { ?>
                                        <a href="?page=<?php echo $page + 1; ?>">Next »</a>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </main>
  <?php include('./includes/footer.php');?>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="./js/scripts.js"></script>
    </body>
</html>