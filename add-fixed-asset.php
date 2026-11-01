<?php
include_once('./includes/auth-employee.php');
include_once('./includes/config.php');

$branch = $_SESSION['branch_name'];

// ─── Dropdown options ───
$categoryOptions = ['Dead Stock', 'Furniture', 'Machinery', 'Vehicle'];
$statusOptions   = ['Available', 'Request to Delete', 'Scrap', 'Request to Sale', 'Request to Repair'];

$productOptions = [
    'Almirah', 'Bench', 'Chair', 'Computer Table', 'Cupboard', 'Desk',
    'Display Board', 'Fan', 'Fire Extinguisher', 'Generator',
    'Iron Safe', 'Locker', 'Money Counting Machine', 'Note Counting Machine',
    'Office Table', 'Rack', 'Sofa', 'Stool', 'Table', 'Typewriter',
    'Water Cooler', 'Water Purifier', 'Wooden Cabinet',
    'Air Conditioner', 'Bike', 'Car', 'Cash Box', 'CCTV System',
    'Drilling Machine', 'Electric Motor', 'Filing Cabinet',
    'Inverter', 'Lamination Machine', 'Paper Shredder',
    'Photocopier', 'Stabilizer', 'Tractor', 'Vacuum Cleaner',
    'Welding Machine', 'Whiteboard'
];

$companyOptions = [
    'Godrej', 'Nilkamal', 'Featherlite', 'Durian', 'Wipro',
    'Bajaj', 'Mahindra', 'Tata', 'Maruti', 'Honda',
    'Blue Star', 'Voltas', 'Eureka Forbes', 'Havells', 'Crompton',
    'Samsung', 'LG', 'Bosch', 'Other'
];

// ─── Handle form submission ───
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date      = trim($_POST['date'] ?? '');
    $category  = trim($_POST['category'] ?? '');
    $product   = trim($_POST['product'] ?? '');
    $company   = trim($_POST['company'] ?? '');
    $rate      = floatval($_POST['rate'] ?? 0);
    $quantity  = intval($_POST['quantity'] ?? 0);
    $amount    = $rate * $quantity;
    $serial_no = trim($_POST['serial_no'] ?? '');
    $label     = trim($_POST['label'] ?? '');
    $status    = trim($_POST['status'] ?? 'Available');

    // Validate required fields
    if (empty($date) || empty($category) || empty($product) || empty($status)) {
        $error = "Please fill in all required fields (Date, Category, Product, Status).";
    } else {
        $stmt = mysqli_prepare($con,
            "INSERT INTO fixed_assets (date, branch, category, product, company, rate, quantity, amount, serial_no, label, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "sssssdiisss",
            $date, $branch, $category, $product, $company,
            $rate, $quantity, $amount, $serial_no, $label, $status
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: fixed-assets.php?msg=added");
            exit();
        } else {
            $error = "Database error: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Add Fixed Asset | MPSC Bank Portal</title>
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/fixed-assets.css?v=1" rel="stylesheet" />
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
                    <div class="fa-page">
                        <div class="fa-panel">

                            <div class="fa-header">
                                <h1 class="fa-title"><i class="fas fa-plus-circle"></i> ADD PRODUCT</h1>
                                <a href="fixed-assets.php" class="fa-btn fa-btn-outline">
                                    <i class="fas fa-arrow-left"></i> Back to Assets
                                </a>
                            </div>

                            <?php if (!empty($error)) { ?>
                                <div class="fa-alert-error">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php } ?>

                            <div class="fa-form-card">
                                <div class="fa-form-card-header">
                                    <i class="fas fa-clipboard-list"></i> New Fixed Asset Details
                                </div>
                                <div class="fa-form-card-body">
                                    <form method="POST" action="add-fixed-asset.php" id="addFixedAssetForm">

                                        <div class="fa-form-grid">
                                            <!-- Date -->
                                            <div class="fa-form-group">
                                                <label for="date">
                                                    <i class="fas fa-calendar-alt"></i> Date
                                                    <span class="fa-required">*</span>
                                                </label>
                                                <input type="date" id="date" name="date"
                                                    value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d')); ?>"
                                                    class="fa-input" required />
                                            </div>

                                            <!-- Branch (auto-filled, read only) -->
                                            <div class="fa-form-group">
                                                <label for="branch">
                                                    <i class="fas fa-building"></i> Branch
                                                    <span class="fa-required">*</span>
                                                </label>
                                                <input type="text" id="branch" name="branch"
                                                    value="<?php echo htmlspecialchars($branch); ?>"
                                                    readonly class="fa-input fa-input-readonly" />
                                            </div>

                                            <!-- Category -->
                                            <div class="fa-form-group">
                                                <label for="category">
                                                    <i class="fas fa-th-large"></i> Category
                                                    <span class="fa-required">*</span>
                                                </label>
                                                <select id="category" name="category" class="fa-input" required>
                                                    <option value="">-- Select Category --</option>
                                                    <?php foreach ($categoryOptions as $cat) { ?>
                                                        <option value="<?php echo htmlspecialchars($cat); ?>"
                                                            <?php echo (isset($_POST['category']) && $_POST['category'] === $cat) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($cat); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <!-- Product -->
                                            <div class="fa-form-group">
                                                <label for="product">
                                                    <i class="fas fa-box-open"></i> Product
                                                    <span class="fa-required">*</span>
                                                </label>
                                                <select id="product" name="product" class="fa-input" required>
                                                    <option value="">-- Select Product --</option>
                                                    <?php foreach ($productOptions as $prod) { ?>
                                                        <option value="<?php echo htmlspecialchars($prod); ?>"
                                                            <?php echo (isset($_POST['product']) && $_POST['product'] === $prod) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($prod); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <!-- Company -->
                                            <div class="fa-form-group">
                                                <label for="company">
                                                    <i class="fas fa-industry"></i> Company
                                                </label>
                                                <select id="company" name="company" class="fa-input">
                                                    <option value="">-- Select Company --</option>
                                                    <?php foreach ($companyOptions as $comp) { ?>
                                                        <option value="<?php echo htmlspecialchars($comp); ?>"
                                                            <?php echo (isset($_POST['company']) && $_POST['company'] === $comp) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($comp); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <!-- Rate -->
                                            <div class="fa-form-group">
                                                <label for="rate">
                                                    <i class="fas fa-rupee-sign"></i> Rate
                                                </label>
                                                <input type="number" id="rate" name="rate" step="0.01" min="0"
                                                    value="<?php echo htmlspecialchars($_POST['rate'] ?? ''); ?>"
                                                    class="fa-input" placeholder="Enter rate" oninput="calcAmount()" />
                                            </div>

                                            <!-- Quantity -->
                                            <div class="fa-form-group">
                                                <label for="quantity">
                                                    <i class="fas fa-sort-numeric-up"></i> Quantity
                                                </label>
                                                <input type="number" id="quantity" name="quantity" min="1"
                                                    value="<?php echo htmlspecialchars($_POST['quantity'] ?? '1'); ?>"
                                                    class="fa-input" placeholder="Enter quantity" oninput="calcAmount()" />
                                            </div>

                                            <!-- Amount (auto-calculated) -->
                                            <div class="fa-form-group">
                                                <label for="amount">
                                                    <i class="fas fa-calculator"></i> Amount
                                                </label>
                                                <input type="text" id="amount" name="amount_display"
                                                    value=""
                                                    class="fa-input fa-input-readonly" readonly placeholder="Auto-calculated" />
                                            </div>

                                            <!-- Serial Number -->
                                            <div class="fa-form-group">
                                                <label for="serial_no">
                                                    <i class="fas fa-barcode"></i> Serial Number
                                                </label>
                                                <input type="text" id="serial_no" name="serial_no"
                                                    value="<?php echo htmlspecialchars($_POST['serial_no'] ?? ''); ?>"
                                                    class="fa-input" placeholder="Enter serial number" />
                                            </div>

                                            <!-- Label -->
                                            <div class="fa-form-group">
                                                <label for="label">
                                                    <i class="fas fa-tag"></i> Label
                                                </label>
                                                <input type="text" id="label" name="label"
                                                    value="<?php echo htmlspecialchars($_POST['label'] ?? ''); ?>"
                                                    class="fa-input" placeholder="Enter label" />
                                            </div>

                                            <!-- Status -->
                                            <div class="fa-form-group">
                                                <label for="status">
                                                    <i class="fas fa-info-circle"></i> Status
                                                    <span class="fa-required">*</span>
                                                </label>
                                                <select id="status" name="status" class="fa-input" required>
                                                    <?php foreach ($statusOptions as $st) { ?>
                                                        <option value="<?php echo htmlspecialchars($st); ?>"
                                                            <?php
                                                            if (isset($_POST['status'])) {
                                                                echo ($_POST['status'] === $st) ? 'selected' : '';
                                                            } else {
                                                                echo ($st === 'Available') ? 'selected' : '';
                                                            }
                                                            ?>>
                                                            <?php echo htmlspecialchars($st); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="fa-form-actions">
                                            <button type="submit" class="fa-btn fa-btn-primary" id="btnSubmitAsset">
                                                <i class="fas fa-save"></i> SAVE
                                            </button>
                                            <a href="fixed-assets.php" class="fa-btn fa-btn-cancel">
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
    <script>
        // Auto-calculate Amount = Rate × Quantity
        function calcAmount() {
            const rate = parseFloat(document.getElementById('rate').value) || 0;
            const qty  = parseInt(document.getElementById('quantity').value) || 0;
            const amt  = (rate * qty).toFixed(2);
            document.getElementById('amount').value = '₹ ' + amt;
        }

        // Calculate on page load
        window.addEventListener('DOMContentLoaded', calcAmount);
    </script>
</body>

</html>
