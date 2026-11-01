<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

$error = '';
$message = '';

if (isset($_POST['save'])) {
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    $role = trim($_POST['role']);
    $branch_name = trim($_POST['branch_name']);

    if (empty($username) || empty(trim($_POST['password'])) || empty($role)) {
        $error = "Please fill all required fields.";
    } else {
        // Check for duplicate username
        $checkStmt = mysqli_prepare($con, "SELECT id FROM users WHERE username=?");
        mysqli_stmt_bind_param($checkStmt, "s", $username);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $error = "Username already exists. Please choose a different username.";
        } else {
            $stmt = mysqli_prepare($con, "INSERT INTO users (username, password, role, branch_name) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $username, $password, $role, $branch_name);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_stmt_close($checkStmt);
                header("Location: manage-users.php");
                exit();
            } else {
                $error = "Database error while creating user.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($checkStmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Add User | MPSC Internal Portal</title>
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

                        <h1 class="mt-4">Add New User</h1>

                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php } ?>

                        <div class="card mb-4">
                     <form method="post">
                            <div class="card-body">
                                <table class="table table-bordered">
                                   <tr>
                                    <th>Username <span style="color:red;">*</span></th>
                                       <td><input class="form-control" name="username" type="text" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required /></td>
                                   </tr>
                                   <tr>
                                       <th>Password <span style="color:red;">*</span></th>
                                       <td><input class="form-control" name="password" type="password" value="" required /></td>
                                   </tr>
                                   <tr>
                                       <th>Role <span style="color:red;">*</span></th>
                                       <td>
                                           <select class="form-select" name="role" required>
                                               <option value="">Select Role</option>
                                               <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                               <option value="employee" <?php echo (isset($_POST['role']) && $_POST['role'] == 'employee') ? 'selected' : ''; ?>>Employee</option>
                                           </select>
                                       </td>
                                   </tr>
                                   <tr>
                                       <th>Branch Name</th>
                                       <td><input class="form-control" name="branch_name" type="text" value="<?php echo isset($_POST['branch_name']) ? htmlspecialchars($_POST['branch_name']) : ''; ?>" /></td>
                                   </tr>
                                   <tr>
                                       <td colspan="4" style="text-align:center;"><button type="submit" class="btn btn-primary btn-block" name="save">Save</button></td>
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
