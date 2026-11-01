<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="sb-topnav navbar navbar-expand navbar-dark">
    <a class="navbar-brand" href="manage-circulars.php">MPSC</a>

    <button class="btn btn-link btn-sm order-1 order-lg-0 me-2" id="sidebarToggle" href="#!">
        <i class="fas fa-bars"></i>
    </button>

    <a class="nav-link text-white me-auto" href="javascript:void(0);" onclick="toggleFullScreen()">
        <i class="fas fa-arrows-alt"></i>
    </a>

    <div class="topbar-bank-name">
        <i class="far fa-building me-1"></i> MPSC Bank
    </div>

    <ul class="navbar-nav ms-0 me-3">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle topbar-user" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
                <span class="topbar-user-text"><?php echo htmlspecialchars($_SESSION['login']); ?></span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end topbar-user-dropdown" aria-labelledby="navbarDropdown">
                <li>
                    <a class="dropdown-item" href="profile.php">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Sign Out
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>

<script>
function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.log(`Error attempting fullscreen: ${err.message}`);
        });
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}
</script>