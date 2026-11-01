<?php
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

$error = '';
$success = '';

// Available color classes for blocks
$colorOptions = [
    'block-soft-lavender'  => 'Lavender',
    'block-soft-sand'      => 'Sand',
    'block-soft-peach'     => 'Peach',
    'block-soft-mint'      => 'Mint',
    'block-soft-sky'       => 'Sky',
    'block-soft-rose'      => 'Rose',
    'block-soft-olive'     => 'Olive',
    'block-soft-cyan'      => 'Cyan',
    'block-soft-apricot'   => 'Apricot',
    'block-soft-lilac'     => 'Lilac',
    'block-soft-green'     => 'Green',
    'block-soft-beige'     => 'Beige',
    'block-soft-blue'      => 'Blue',
    'block-soft-coral'     => 'Coral',
    'block-soft-sage'      => 'Sage',
    'block-soft-gold'      => 'Gold',
];

// Get next display_order
$orderResult = mysqli_query($con, "SELECT IFNULL(MAX(display_order),0)+1 AS next_order FROM portal_blocks");
$nextOrder = mysqli_fetch_assoc($orderResult)['next_order'];

if (isset($_POST['submit'])) {
    $blockTitle  = trim($_POST['block_title'] ?? '');
    $fileType    = trim($_POST['file_type'] ?? 'pdf');
    $colorClass  = trim($_POST['color_class'] ?? '');
    $displayOrder = (int)($_POST['display_order'] ?? $nextOrder);

    if (empty($blockTitle)) {
        $error = "Block title is required.";
    } else {
        $fileName = null;

        // Handle optional file upload
        if (isset($_FILES['block_file']) && $_FILES['block_file']['error'] == 0) {
            $uploadDir = __DIR__ . '/uploads/portal-blocks/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFileName = time() . '_' . basename($_FILES['block_file']['name']);
            $targetPath = $uploadDir . $newFileName;
            $ext = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

            $allowed = ['pdf', 'xls', 'xlsx'];
            if (!in_array($ext, $allowed)) {
                $error = "Only PDF, XLS, XLSX files are allowed.";
            } else {
                if (move_uploaded_file($_FILES['block_file']['tmp_name'], $targetPath)) {
                    $fileName = $newFileName;
                } else {
                    $error = "Failed to upload file.";
                }
            }
        }

        if (empty($error)) {
            $stmt = mysqli_prepare($con, "INSERT INTO portal_blocks (block_title, file_type, file_name, color_class, display_order, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            mysqli_stmt_bind_param($stmt, "ssssi", $blockTitle, $fileType, $fileName, $colorClass, $displayOrder);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: manage-portal-blocks.php");
                exit();
            } else {
                $error = "Database error: " . mysqli_error($con);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Add New Block | MPSC Internal Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/portal-blocks.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include_once('./includes/navbar.php'); ?>

<div id="layoutSidenav">
    <?php include_once('./includes/sidebar.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-3">
                <div class="portal-blocks-page">
                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="portal-blocks-panel">
                        <div class="portal-blocks-panel-body">

                            <div class="portal-blocks-top-row">
                                <h2 class="portal-blocks-title">Add New Download Block</h2>
                                <a href="manage-portal-blocks.php" class="pb-action-btn">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>

                            <form method="post" enctype="multipart/form-data">
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <label class="pb-form-label"><span class="req">*</span> Block Title</label>
                                        <input type="text" name="block_title" class="form-control" placeholder="e.g. वार्षिक अहवाल" required
                                            value="<?php echo isset($_POST['block_title']) ? htmlspecialchars($_POST['block_title']) : ''; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="pb-form-label"><span class="req">*</span> File Type</label>
                                        <select name="file_type" class="form-select" required>
                                            <option value="pdf" <?php echo (isset($_POST['file_type']) && $_POST['file_type'] == 'pdf') ? 'selected' : ''; ?>>PDF</option>
                                            <option value="excel" <?php echo (isset($_POST['file_type']) && $_POST['file_type'] == 'excel') ? 'selected' : ''; ?>>EXCEL</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label class="pb-form-label"><span class="req">*</span> Block Color</label>
                                        <select name="color_class" class="form-select" required id="colorSelect">
                                            <?php foreach ($colorOptions as $class => $label) { ?>
                                                <option value="<?php echo $class; ?>" <?php echo (isset($_POST['color_class']) && $_POST['color_class'] == $class) ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <div id="colorPreview" style="margin-top:8px; height:30px; border-radius:6px;"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="pb-form-label">Display Order</label>
                                        <input type="number" name="display_order" class="form-control" min="1"
                                            value="<?php echo isset($_POST['display_order']) ? (int)$_POST['display_order'] : $nextOrder; ?>">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <label class="pb-form-label">Upload File <small style="color:#94a3b8;">(optional — you can add later)</small></label>
                                        <input type="file" name="block_file" class="form-control" accept=".pdf,.xls,.xlsx">
                                    </div>
                                </div>

                                <button type="submit" name="submit" class="pb-save-btn">
                                    <i class="fas fa-plus"></i> Create Block
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include_once('./includes/footer.php'); ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="./js/scripts.js"></script>
<script>
// Color preview
const colorMap = {
    'block-soft-lavender': 'linear-gradient(135deg, #7c83c4, #a5aee0)',
    'block-soft-sand': 'linear-gradient(135deg, #c8a274, #d8b78f)',
    'block-soft-peach': 'linear-gradient(135deg, #e08a5e, #f0a77f)',
    'block-soft-mint': 'linear-gradient(135deg, #5cb89a, #8fd0b8)',
    'block-soft-sky': 'linear-gradient(135deg, #5ba4c0, #8fc4d9)',
    'block-soft-rose': 'linear-gradient(135deg, #c0809a, #d7a3b2)',
    'block-soft-olive': 'linear-gradient(135deg, #98ab6a, #b5c48c)',
    'block-soft-cyan': 'linear-gradient(135deg, #5fb8b8, #89cfd0)',
    'block-soft-apricot': 'linear-gradient(135deg, #d49468, #e6b08c)',
    'block-soft-lilac': 'linear-gradient(135deg, #9580c4, #b4a0d8)',
    'block-soft-green': 'linear-gradient(135deg, #6bab82, #95c8a4)',
    'block-soft-beige': 'linear-gradient(135deg, #b09a78, #ccb89a)',
    'block-soft-blue': 'linear-gradient(135deg, #7a9cc4, #9db7d8)',
    'block-soft-coral': 'linear-gradient(135deg, #cc8068, #e1a08d)',
    'block-soft-sage': 'linear-gradient(135deg, #85a67e, #a8c0a0)',
    'block-soft-gold': 'linear-gradient(135deg, #d4a017, #efb400)',
};
const sel = document.getElementById('colorSelect');
const preview = document.getElementById('colorPreview');
function updatePreview() {
    preview.style.background = colorMap[sel.value] || '#eee';
}
sel.addEventListener('change', updatePreview);
updatePreview();
</script>
</body>
</html>
