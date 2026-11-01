<?php
include_once('./includes/auth-employee.php');
include_once('./includes/config.php');

$branch = $_SESSION['branch_name'];

// ─── Product dropdown options ───
$products = [
    "Airtel Dongle", "APC", "ARRAY NETWORK", "ATM Battery", "ATS", "Branch Battery",
    "Canon 2900 Laser", "Canon 6230 Laser", "Canon 6755", "Canon CR120 (CTS)",
    "Canon Lide 300", "Canon MF 241 AIO", "CCTV Camera", "CCTV DVR",
    "CCTV Monitor", "CISCO ROUTER", "CS D-link Switch", "CS HPE Server",
    "CS HPE Storage", "CS Rack", "Data Cartridge", "Dry Battery",
    "EPSON L3110 (Color)", "External HDD", "Firepro Router", "Fortigate 200F",
    "HP Scanjet 200", "IBM Blade Center Chassis", "IBM Blade Server",
    "IBM LTO TAP DRIVE", "IBM SAN Storage", "Idea Dongle", "Jio Dongle",
    "JUNIPER NETWORK", "KVM", "Laptop Dell", "Laptop HP", "Laptop Lenovo",
    "Lenovo AIO", "Lenovo CPU", "Lenovo Keyboard", "Lenovo Monitor 19.5\"",
    "Lenovo Mouse", "Mantra Device", "Microtik Firewall / Router", "Modem",
    "New Tower Server", "NUMERIC 1 KVA UPS", "NUMERIC 2 KVA UPS",
    "Old Lenovo CPU", "Old Lenovo Monitor", "Old Server",
    "Olivitee Passbook Printer", "Other CPU", "Other Keyboard",
    "Other Monitor", "Other Mouse", "Other Printer", "Other Scanner",
    "Other UPS", "Perto ATM Machine", "Projector", "RACK SEVER",
    "Realtime Biometric Device", "Sarswat Micro ATM", "STABILIZER",
    "Switch", "Thermal Printer NB TEK", "TVS WEBCAM",
    "Vodafone Dongle", "Voltage Stabilizer"
];

$categories = [
    "CPU", "Monitor", "Keyboard", "Mouse", "Aio Desktop",
    "Projector", "Printer", "Scanner", "Laptop",
    "CCTV DVR", "CCTV Camera", "N/W Devices", "UPS",
    "Tower Server", "ATM Machine", "Biometric", "Micro ATM"
];

$statuses = [
    "Available", "Not Available", "Send To Repair",
    "Request to delete", "Replaced", "Scrap"
];

// ─── Fetch existing record (only if it belongs to this branch) ───
$asset = null;
$error = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-it-assets.php");
    exit();
}

$assetId = (int)$_GET['id'];

$stmtFetch = mysqli_prepare($con, "SELECT * FROM it_assets WHERE id = ? AND branch_name = ? LIMIT 1");
mysqli_stmt_bind_param($stmtFetch, "is", $assetId, $branch);
mysqli_stmt_execute($stmtFetch);
$result = mysqli_stmt_get_result($stmtFetch);
$asset = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmtFetch);

if (!$asset) {
    header("Location: manage-it-assets.php");
    exit();
}

// ─── Handle update ───
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category  = trim($_POST['category'] ?? '');
    $product   = trim($_POST['product'] ?? '');
    $serial_id = trim($_POST['serial_id'] ?? '');
    $label     = trim($_POST['label_name'] ?? '');
    $status    = trim($_POST['status'] ?? '');

    if (empty($category) || empty($product) || empty($status)) {
        $error = "Please fill in all required fields (Category, Product, Status).";
    } else {
        $stmtUpdate = mysqli_prepare($con, "UPDATE it_assets SET category=?, product=?, serial_id=?, label_name=?, status=? WHERE id=? AND branch_name=?");
        mysqli_stmt_bind_param($stmtUpdate, "sssssis", $category, $product, $serial_id, $label, $status, $assetId, $branch);

        if (mysqli_stmt_execute($stmtUpdate)) {
            mysqli_stmt_close($stmtUpdate);
            header("Location: manage-it-assets.php?msg=updated");
            exit();
        } else {
            $error = "Database error: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmtUpdate);
    }

    // Refresh asset data for form re-display
    $asset['category']   = $category;
    $asset['product']    = $product;
    $asset['serial_id']  = $serial_id;
    $asset['label_name'] = $label;
    $asset['status']     = $status;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Edit IT Asset | MPSC Bank Portal</title>
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/it-assets.css?v=2" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">

    <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background:#099c78;">
        <a class="navbar-brand ps-3" href="entry-forms.php">SDCC</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="navbar-nav ms-auto me-3 me-lg-4 align-items-center">
            <li class="nav-item me-3 text-white">
                <?php echo htmlspecialchars($_SESSION['branch_name']); ?>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['login']); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <?php include_once('./includes/entry-sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-3">
                    <div class="it-assets-page">
                        <div class="it-assets-panel">

                            <div class="ita-header">
                                <h1 class="ita-title"><i class="fas fa-edit"></i> EDIT ASSET</h1>
                                <a href="manage-it-assets.php" class="ita-btn ita-btn-outline">
                                    <i class="fas fa-arrow-left"></i> Back to Assets
                                </a>
                            </div>

                            <?php if (!empty($error)) { ?>
                                <div class="ita-alert-error">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php } ?>

                            <div class="ita-form-card">
                                <div class="ita-form-card-header edit">
                                    <i class="fas fa-pen-square"></i> Update Asset #<?php echo $assetId; ?>
                                </div>
                                <div class="ita-form-card-body">
                                    <form method="POST" action="edit-it-asset.php?id=<?php echo $assetId; ?>" id="editAssetForm">

                                        <div class="ita-form-grid">
                                            <!-- Branch (read only) -->
                                            <div class="ita-form-group">
                                                <label for="branch_name">
                                                    <i class="fas fa-building"></i> Branch
                                                    <span class="ita-required">*</span>
                                                </label>
                                                <input type="text" id="branch_name" name="branch_name"
                                                    value="<?php echo htmlspecialchars($branch); ?>"
                                                    readonly class="ita-input ita-input-readonly" />
                                            </div>

                                            <!-- Category -->
                                            <div class="ita-form-group">
                                                <label for="category">
                                                    <i class="fas fa-th-large"></i> Category
                                                    <span class="ita-required">*</span>
                                                </label>
                                                <select id="category" name="category" class="ita-input" required>
                                                    <option value="">-- Select Category --</option>
                                                    <?php foreach ($categories as $cat) { ?>
                                                        <option value="<?php echo htmlspecialchars($cat); ?>"
                                                            <?php echo ($asset['category'] === $cat) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($cat); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <!-- Product -->
                                            <div class="ita-form-group">
                                                <label for="product">
                                                    <i class="fas fa-box-open"></i> Product
                                                    <span class="ita-required">*</span>
                                                </label>
                                                <select id="product" name="product" class="ita-input" required>
                                                    <option value="">-- Select Product --</option>
                                                    <?php foreach ($products as $prod) { ?>
                                                        <option value="<?php echo htmlspecialchars($prod); ?>"
                                                            <?php echo ($asset['product'] === $prod) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($prod); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <!-- Status -->
                                            <div class="ita-form-group">
                                                <label for="status">
                                                    <i class="fas fa-info-circle"></i> Status
                                                    <span class="ita-required">*</span>
                                                </label>
                                                <select id="status" name="status" class="ita-input" required>
                                                    <option value="">-- Select Status --</option>
                                                    <?php foreach ($statuses as $st) { ?>
                                                        <option value="<?php echo htmlspecialchars($st); ?>"
                                                            <?php echo ($asset['status'] === $st) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($st); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <!-- Serial ID -->
                                            <div class="ita-form-group">
                                                <label for="serial_id">
                                                    <i class="fas fa-barcode"></i> Serial ID
                                                </label>
                                                <input type="text" id="serial_id" name="serial_id"
                                                    value="<?php echo htmlspecialchars($asset['serial_id'] ?? ''); ?>"
                                                    class="ita-input" placeholder="Enter serial number" />
                                            </div>

                                            <!-- Label -->
                                            <div class="ita-form-group">
                                                <label for="label_name">
                                                    <i class="fas fa-tag"></i> Label
                                                </label>
                                                <input type="text" id="label_name" name="label_name"
                                                    value="<?php echo htmlspecialchars($asset['label_name'] ?? ''); ?>"
                                                    class="ita-input" placeholder="Enter label name" />
                                            </div>
                                        </div>

                                        <div class="ita-form-actions">
                                            <button type="submit" class="ita-btn ita-btn-primary" id="btnUpdateAsset">
                                                <i class="fas fa-check-circle"></i> UPDATE
                                            </button>
                                            <a href="manage-it-assets.php" class="ita-btn ita-btn-cancel">
                                                <i class="fas fa-times"></i> CANCEL
                                            </a>
                                        </div>

                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-3 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="small text-muted">MPSC Bank Portal</div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="./js/scripts.js"></script>
</body>

</html>
