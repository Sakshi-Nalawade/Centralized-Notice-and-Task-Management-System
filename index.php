<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
include_once('./includes/config.php');
include_once('./includes/security.php');

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $rawPassword = trim($_POST['password']);

    if ($username === '' || $rawPassword === '') {
        $error = "Please enter username and password.";
    } else {
        $stmt = mysqli_prepare($con, "SELECT id, username, password, role, branch_name FROM users WHERE username=? AND is_active=1 LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user) {
            $storedPassword = $user['password'];
            $authenticated = false;

            // Check if password is stored with password_hash (bcrypt)
            if (password_verify($rawPassword, $storedPassword)) {
                $authenticated = true;
            }
            // Backward compatibility: check MD5 hash
            elseif ($storedPassword === md5($rawPassword)) {
                $authenticated = true;

                // Auto-migrate MD5 to bcrypt
                $newHash = password_hash($rawPassword, PASSWORD_BCRYPT);
                $upgradeStmt = mysqli_prepare($con, "UPDATE users SET password=? WHERE id=?");
                mysqli_stmt_bind_param($upgradeStmt, "si", $newHash, $user['id']);
                mysqli_stmt_execute($upgradeStmt);
                mysqli_stmt_close($upgradeStmt);
            }

            if ($authenticated) {
                $role = trim(strtolower($user['role']));

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                $_SESSION['userid'] = $user['id'];
                $_SESSION['login'] = $user['username'];
                $_SESSION['role'] = $role;
                $_SESSION['branch_name'] = $user['branch_name'];

                if ($role === 'admin') {
                    $_SESSION['adminid'] = $user['id'];
                    header("Location: manage-circulars.php");
                    exit();
                } elseif ($role === 'employee') {
                    unset($_SESSION['adminid']);
                    header("Location: web-main.php");
                    exit();
                } else {
                    $error = "Invalid role assigned to this user.";
                }
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>MPSC Internal Portal Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@600;700&display=swap"
        rel="stylesheet">
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/login.css?v=2" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"
        crossorigin="anonymous"></script>
</head>

<body>
    <div class="portal-login-page">

        <div class="portal-top-banner">
            <div class="portal-logo-box">
                <img src="./assets/logo.jpeg" alt="Portal Logo">
            </div>

            <div class="portal-title-box">
                <h1 class="bank-title">
                    मामासाहेब पवार सत्यविजय सहकारी बँक <br>
                    लि., कुंडल
                </h1>
            </div>
        </div>

        <div class="portal-menu-bar">
            <a href="web-main.php" class="active">होम</a>
            <a href="https://share.google/v0sGI322DcU2pWU9D" target="_blank">वेबसाईट</a>
            <!-- <a href="https://webmail.rediffmailpro.com/action/login/sanglidccb.bank.in" target="_blank">ई-मेल</a> -->
            <!-- <a href="#">संपर्क</a> -->
        </div>

        <div class="portal-main-wrap">
            <div class="portal-content-row">
                <div class="portal-slider-wrap">
                    <div class="portal-slide active">
                        <img src="./assets/slider/slider1.avif" alt="Slide 1">
                    </div>
                    <div class="portal-slide">
                        <img src="./assets/slider/slider2.jpg" alt="Slide 2">
                    </div>
                    <div class="portal-slide">
                        <img src="./assets/slider/slider3.jpg" alt="Slide 3">
                    </div>
                    <div class="portal-slide">
                        <img src="./assets/slider/slider4.jpg" alt="Slide 4">
                    </div>
                </div>

                <div class="portal-login-panel">
                    <div class="portal-login-card-title">Sign In</div>
                    <div class="portal-login-divider"></div>

                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <form method="post">
                        <label>Username</label>
                        <input class="form-control" name="username" type="text" required>

                        <label>Password</label>
                        <input class="form-control" id="passwordField" name="password" type="password" required>

                        <div class="portal-show-pass">
                            <input type="checkbox" id="showPasswordCheck">
                            <label for="showPasswordCheck" style="margin:0; font-weight:500;">Show password</label>
                        </div>

                        <div class="portal-login-actions">
                            <button class="portal-signin-btn" name="login" type="submit">SIGN IN</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="portal-footer">
            <div>कॉपीराइट २०२६ © मामासाहेब पवार सत्यविजय सहकारी बँक लि., कुंडल. सर्व हक्क राखीव.</div>
            <!-- <div>Design and Developed By: Shreya Patil</div> -->
        </div>
    </div>

    <script>
        document.getElementById('showPasswordCheck').addEventListener('change', function () {
            const passwordField = document.getElementById('passwordField');
            passwordField.type = this.checked ? 'text' : 'password';
        });

        const slides = document.querySelectorAll('.portal-slide');
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) {
                    slide.classList.add('active');
                }
            });
        }

        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 3000);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
</body>

</html>