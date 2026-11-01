<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

if (!isset($_GET['uid']) || !is_numeric($_GET['uid'])) {
    header('location:manage-users.php');
    exit();
}

$userid = (int)$_GET['uid'];

$stmt = mysqli_prepare($con, "SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$queryResult = mysqli_stmt_get_result($stmt);
$result = mysqli_fetch_assoc($queryResult);
mysqli_stmt_close($stmt);

if (!$result) {
    header('location:manage-users.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>User Profile | MPSC Internal Portal</title>
        <link href="./css/styles.css" rel="stylesheet" />
        <link href="./css/custom.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
      <?php include_once('includes/navbar.php');?>
        <div id="layoutSidenav">
          <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">

                        <h1 class="mt-4"><?php echo htmlspecialchars($result['username']); ?>'s Profile</h1>
                        <div class="card mb-4">
                            <div class="card-body">
                                <a href="edit-profile.php?uid=<?php echo (int)$result['id'];?>">Edit</a>
                                <table class="table table-bordered">
                                   <tr>
                                    <th>Username</th>
                                       <td><?php echo htmlspecialchars($result['username']);?></td>
                                   </tr>
                                   <tr>
                                       <th>Role</th>
                                       <td><?php echo htmlspecialchars($result['role']);?></td>
                                   </tr>
                                   <tr>
                                       <th>Branch</th>
                                       <td><?php echo htmlspecialchars($result['branch_name'] ?? '');?></td>
                                   </tr>
                                </table>
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
