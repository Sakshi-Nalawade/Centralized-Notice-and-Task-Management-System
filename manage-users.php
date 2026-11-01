<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

$message = '';

// Delete user using prepared statement
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Prevent deleting yourself
    if ($id == $_SESSION['adminid']) {
        $message = "You cannot delete your own account.";
    } else {
        $stmt = mysqli_prepare($con, "DELETE FROM users WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "User deleted successfully.";
        }
        mysqli_stmt_close($stmt);
    }
}

$query = mysqli_query($con, "SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Manage Users | MPSC Internal Portal</title>
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
                        <h1 class="mt-4">Manage Users</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="manage-circulars.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manage Users</li>
                        </ol>

                        <?php if (!empty($message)) { ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                        <?php } ?>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Registered User Details

                                <div class="btn btn-success btn-sm pull-right" onclick="window.location='add-profile.php'"> <i class="fas fa-plus"></i> Add</div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                             <th>Sno.</th>
                                             <th>Username</th>
                                             <th>Role</th>
                                             <th>Branch</th>
                                             <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $cnt = 1;
                                        if ($query && mysqli_num_rows($query) > 0) {
                                            while ($row = mysqli_fetch_assoc($query)) { ?>
                                                <tr>
                                                    <td><?php echo $cnt++;?></td>
                                                    <td><?php echo htmlspecialchars($row['username']);?></td>
                                                    <td><?php echo htmlspecialchars($row['role']);?></td>
                                                    <td><?php echo htmlspecialchars($row['branch_name'] ?? '');?></td>
                                                    <td>
                                                        <a href="user-profile.php?uid=<?php echo (int)$row['id'];?>">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="manage-users.php?id=<?php echo (int)$row['id'];?>" onClick="return confirm('Do you really want to delete this user?');">
                                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php }
                                        } else { ?>
                                            <tr><td colspan="5" style="text-align:center;">No users found.</td></tr>
                                        <?php } ?>
                                    </tbody>
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