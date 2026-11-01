<?php
$current_entry_page = basename($_SERVER['PHP_SELF']);
$fixedAssetPages = ['fixed-assets.php', 'add-fixed-asset.php', 'edit-fixed-asset.php', 'fixed-asset-transfer.php', 'fixed-asset-transfer-report.php'];
$fixedAssetOpen = in_array($current_entry_page, $fixedAssetPages);

$itAssetPages = ['manage-it-assets.php', 'add-it-asset.php', 'edit-it-asset.php', 'it-asset-transfer.php', 'it-asset-transfer-report.php'];
$itAssetOpen = in_array($current_entry_page, $itAssetPages);
?>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">

        <div class="sb-sidenav-menu">
            <div class="nav">

                <a class="nav-link <?php echo $fixedAssetOpen ? '' : 'collapsed'; ?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseFixedAssetMenu" aria-expanded="<?php echo $fixedAssetOpen ? 'true' : 'false'; ?>" aria-controls="collapseFixedAssetMenu">
                    <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                    Fixed Asset Main Menu
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?php echo $fixedAssetOpen ? 'show' : ''; ?>" id="collapseFixedAssetMenu" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo ($current_entry_page == 'fixed-assets.php') ? 'active' : ''; ?>" href="fixed-assets.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Fixed Assets Dashboard
                        </a>
                        <a class="nav-link <?php echo ($current_entry_page == 'add-fixed-asset.php') ? 'active' : ''; ?>" href="add-fixed-asset.php">
                            <i class="fas fa-plus-circle me-2"></i> Add Fixed Asset
                        </a>
                        <a class="nav-link <?php echo ($current_entry_page == 'fixed-asset-transfer.php') ? 'active' : ''; ?>" href="fixed-asset-transfer.php">
                            <i class="fas fa-exchange-alt me-2"></i> Fixed Asset Transfer
                        </a>
                        <a class="nav-link <?php echo ($current_entry_page == 'fixed-asset-transfer-report.php') ? 'active' : ''; ?>" href="fixed-asset-transfer-report.php">
                            <i class="fas fa-file-alt me-2"></i> Fixed Asset Transfer Report
                        </a>
                    </nav>
                </div>

                <a class="nav-link <?php echo $itAssetOpen ? '' : 'collapsed'; ?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseItAssetMenu" aria-expanded="<?php echo $itAssetOpen ? 'true' : 'false'; ?>" aria-controls="collapseItAssetMenu">
                    <div class="sb-nav-link-icon"><i class="fas fa-desktop"></i></div>
                    IT Asset Main Menu
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?php echo $itAssetOpen ? 'show' : ''; ?>" id="collapseItAssetMenu" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo ($current_entry_page == 'manage-it-assets.php') ? 'active' : ''; ?>" href="manage-it-assets.php">
                            <i class="fas fa-tachometer-alt me-2"></i> IT Assets Dashboard
                        </a>
                        <a class="nav-link <?php echo ($current_entry_page == 'add-it-asset.php') ? 'active' : ''; ?>" href="add-it-asset.php">
                            <i class="fas fa-plus-circle me-2"></i> Add IT Asset
                        </a>
                        <a class="nav-link <?php echo ($current_entry_page == 'it-asset-transfer.php') ? 'active' : ''; ?>" href="it-asset-transfer.php">
                            <i class="fas fa-exchange-alt me-2"></i> IT Asset Transfer
                        </a>
                        <a class="nav-link <?php echo ($current_entry_page == 'it-asset-transfer-report.php') ? 'active' : ''; ?>" href="it-asset-transfer-report.php">
                            <i class="fas fa-file-alt me-2"></i> IT Assets Transfer Report
                        </a>
                    </nav>
                </div>


                <a class="nav-link" href="web-main.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-globe"></i></div>
                    View Portal
                </a>

            </div>
        </div>
    </nav>
</div>