<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');
include_once('./includes/security.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

$message = '';
$error = '';

// Handle password change
if (isset($_POST['update'])) {
    $rawOldPassword = trim($_POST['currentpassword']);
    $rawNewPassword = trim($_POST['newpassword']);
    $adminid = $_SESSION['adminid'];

    if (strlen($rawNewPassword) < 4) {
        $error = "New password must be at least 4 characters long.";
    } else {
        // Fetch stored password hash
        $stmt = mysqli_prepare($con, "SELECT password FROM users WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $adminid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $verified = false;
        if ($row) {
            // Check bcrypt first, then MD5 for backward compatibility
            if (password_verify($rawOldPassword, $row['password'])) {
                $verified = true;
            } elseif ($row['password'] === md5($rawOldPassword)) {
                $verified = true;
            }
        }

        if ($verified) {
            $newHash = password_hash($rawNewPassword, PASSWORD_BCRYPT);
            $updateStmt = mysqli_prepare($con, "UPDATE users SET password=? WHERE id=?");
            mysqli_stmt_bind_param($updateStmt, "si", $newHash, $adminid);

            if (mysqli_stmt_execute($updateStmt)) {
                $message = "Password changed successfully!";
            } else {
                $error = "Failed to update password. Please try again.";
            }
            mysqli_stmt_close($updateStmt);
        } else {
            $error = "Old password does not match!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Change Password | MPSC Internal Portal</title>
        <link href="./css/styles.css" rel="stylesheet" />
        <link href="./css/custom.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
        <script>
        function valid() {
            if (document.changepassword.newpassword.value !== document.changepassword.confirmpassword.value) {
                alert("Password and Confirm Password do not match!");
                document.changepassword.confirmpassword.focus();
                return false;
            }
            return true;
        }
        </script>
    </head>
    <body class="sb-nav-fixed">
      <?php include_once('includes/navbar.php');?>
        <div id="layoutSidenav">
          <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">

                        <h1 class="mt-4">Change Password</h1>

                        <?php if (!empty($message)) { ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                        <?php } ?>

                        <?php if (!empty($error)) { ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php } ?>

                        <div class="card mb-4">
                     <form method="post" name="changepassword" onSubmit="return valid();">
                            <div class="card-body">
                                <table class="table table-bordered">
                                   <tr>
                                    <th>Current Password</th>
                                       <td><input class="form-control" id="currentpassword" name="currentpassword" type="password" value="" required /></td>
                                   </tr>
                                   <tr>
                                       <th>New Password</th>
                                       <td><input class="form-control" id="newpassword" name="newpassword" type="password" value="" required /></td>
                                   </tr>
                                   <tr>
                                       <th>Confirm Password</th>
                                       <td colspan="3"><input class="form-control" id="confirmpassword" name="confirmpassword" type="password" required /></td>
                                   </tr>
                                   <tr>
                                       <td colspan="4" style="text-align:center;"><button type="submit" class="btn btn-primary btn-block" name="update">Change</button></td>
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
