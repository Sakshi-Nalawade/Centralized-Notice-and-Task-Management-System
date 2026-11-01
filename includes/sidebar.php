<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">

        <div class="sidebar-profile">
            <div class="profile-circle">
                <i class="far fa-user"></i>
            </div>
            <div class="sidebar-profile-info">
                <div class="name"><?php echo htmlspecialchars($_SESSION['login']); ?></div>
                <div class="status">● Online</div>
            </div>
        </div>

        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Main Navigation</div>


                <a class="nav-link <?php echo in_array($current_page, ['manage-circulars.php', 'add-circular.php', 'edit-circular.php']) ? 'active' : ''; ?>"
                    href="manage-circulars.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-upload"></i></div>
                    Upload Circular List
                </a>

                <a class="nav-link <?php echo in_array($current_page, ['manage-news.php', 'add-news.php', 'edit-news.php']) ? 'active' : ''; ?>"
                    href="manage-news.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-newspaper"></i></div>
                    News
                </a>

                <a class="nav-link" href="web-main.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-globe"></i></div>
                    View Portal
                </a>

                <a class="nav-link <?php echo in_array($current_page, ['manage-inward.php', 'add-inward.php', 'edit-inward.php']) ? 'active' : ''; ?>"
                    href="manage-inward.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-sign-in-alt"></i></div>
                    Inward
                </a>

                <a class="nav-link <?php echo in_array($current_page, ['manage-outward.php', 'add-outward.php', 'edit-outward.php']) ? 'active' : ''; ?>"
                    href="manage-outward.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                    Outward
                </a>

                <?php
                    $itAssetPages = ['it-asset-approval.php', 'it-asset-transfer-report.php'];
                    $itAssetOpen = in_array($current_page, $itAssetPages);
                ?>
                <a class="nav-link <?php echo $itAssetOpen ? '' : 'collapsed'; ?>" href="#" data-bs-toggle="collapse"
                    data-bs-target="#collapseItAssets" aria-expanded="<?php echo $itAssetOpen ? 'true' : 'false'; ?>"
                    aria-controls="collapseItAssets">
                    <div class="sb-nav-link-icon"><i class="fas fa-desktop"></i></div>
                    IT Assets
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?php echo $itAssetOpen ? 'show' : ''; ?>" id="collapseItAssets"
                    data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo ($current_page == 'it-asset-approval.php') ? 'active' : ''; ?>"
                            href="it-asset-approval.php">
                            <i class="fas fa-exchange-alt me-2"></i> IT Asset Transfer Approval
                        </a>
                        <a class="nav-link <?php echo ($current_page == 'it-asset-transfer-report.php') ? 'active' : ''; ?>"
                            href="it-asset-transfer-report.php">
                            <i class="fas fa-file-alt me-2"></i> IT Asset Transfer Report
                        </a>
                    </nav>
                </div>

                <?php
                    $fixedAssetPages = ['fixed-asset-approval.php', 'fixed-asset-transfer-report.php'];
                    $fixedAssetOpen = in_array($current_page, $fixedAssetPages);
                ?>
                <a class="nav-link <?php echo $fixedAssetOpen ? '' : 'collapsed'; ?>" href="#" data-bs-toggle="collapse"
                    data-bs-target="#collapseFixedAssets" aria-expanded="<?php echo $fixedAssetOpen ? 'true' : 'false'; ?>"
                    aria-controls="collapseFixedAssets">
                    <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                    Fixed Assets
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?php echo $fixedAssetOpen ? 'show' : ''; ?>" id="collapseFixedAssets"
                    data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo ($current_page == 'fixed-asset-approval.php') ? 'active' : ''; ?>"
                            href="fixed-asset-approval.php">
                            <i class="fas fa-clipboard-check me-2"></i> Fixed Asset Transfer Approval
                        </a>
                        <a class="nav-link <?php echo ($current_page == 'fixed-asset-transfer-report.php') ? 'active' : ''; ?>"
                            href="fixed-asset-transfer-report.php">
                            <i class="fas fa-file-alt me-2"></i> Fixed Asset Transfer Report
                        </a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array($current_page, ['manage-users.php', 'add-profile.php', 'edit-profile.php', 'user-profile.php']) ? 'active' : ''; ?>"
                    href="manage-users.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Manage Users
                </a>

                <a class="nav-link <?php echo in_array($current_page, ['manage-portal-blocks.php', 'edit-portal-blocks.php', 'add-portal-block.php', 'add-portal-block-file.php']) ? 'active' : ''; ?>"
                    href="manage-portal-blocks.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-download"></i></div>
                    Portal Downloads
                </a>

                <a class="nav-link <?php echo in_array($current_page, ['manage-policies.php', 'add-policy-file.php', 'edit-policy.php']) ? 'active' : ''; ?>"
                    href="manage-policies.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-file-pdf"></i></div>
                    Policies
                </a>
            </div>
    </nav>
</div>