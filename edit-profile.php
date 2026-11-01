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
$error = '';

// Fetch user data
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

// Handle update
if (isset($_POST['update'])) {
    $username = trim($_POST['username']);
    $role = trim($_POST['role']);
    $branch_name = trim($_POST['branch_name']);

    if (empty($username) || empty($role)) {
        $error = "Please fill all required fields.";
    } else {
        // Check duplicate username (exclude current user)
        $checkStmt = mysqli_prepare($con, "SELECT id FROM users WHERE username=? AND id!=?");
        mysqli_stmt_bind_param($checkStmt, "si", $username, $userid);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $error = "Username already taken by another user.";
        } else {
            $updateStmt = mysqli_prepare($con, "UPDATE users SET username=?, role=?, branch_name=? WHERE id=?");
            mysqli_stmt_bind_param($updateStmt, "sssi", $username, $role, $branch_name, $userid);

            if (mysqli_stmt_execute($updateStmt)) {
                mysqli_stmt_close($updateStmt);
                mysqli_stmt_close($checkStmt);
                header("Location: manage-users.php");
                exit();
            } else {
                $error = "Failed to update profile.";
            }
            mysqli_stmt_close($updateStmt);
        }
        mysqli_stmt_close($checkStmt);
    }

    // Refresh data for display after validation error
    $result['username'] = $username;
    $result['role'] = $role;
    $result['branch_name'] = $branch_name;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Edit Profile | MPSC Internal Portal</title>
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

                        <h1 class="mt-4">Edit User Profile</h1>

                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php } ?>

                        <div class="card mb-4">
                     <form method="post" action="edit-profile.php?uid=<?php echo $userid; ?>">
                            <div class="card-body">
                                <table class="table table-bordered">
                                   <tr>
                                    <th>Username <span style="color:red;">*</span></th>
                                       <td><input class="form-control" name="username" type="text" value="<?php echo htmlspecialchars($result['username']);?>" required /></td>
                                   </tr>
                                   <tr>
                                       <th>Role <span style="color:red;">*</span></th>
                                       <td>
                                           <select class="form-select" name="role" required>
                                               <option value="">Select Role</option>
                                               <option value="admin" <?php echo ($result['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                               <option value="employee" <?php echo ($result['role'] == 'employee') ? 'selected' : ''; ?>>Employee</option>
                                           </select>
                                       </td>
                                   </tr>
                                   <tr>
                                       <th>Branch Name</th>
                                       <td><input class="form-control" name="branch_name" type="text" value="<?php echo htmlspecialchars($result['branch_name'] ?? '');?>" /></td>
                                   </tr>
                                   <tr>
                                       <td colspan="4" style="text-align:center;">
                                           <button type="submit" class="btn btn-primary btn-block" name="update">Update</button>
                                           <a href="manage-users.php" class="btn btn-secondary">Cancel</a>
                                       </td>
                                   </tr>
                                </table>
                            </div>
                            </form>
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
