<?php
include_once('./includes/auth-portal.php');
include_once('./includes/config.php');

$dept = isset($_GET['dept']) ? trim($_GET['dept']) : 'all';
$year = isset($_GET['year']) ? trim($_GET['year']) : 'all';

$deptQuery = mysqli_query($con, "SELECT department_name FROM departments WHERE is_active=1 ORDER BY department_name ASC");
$yearQuery = mysqli_query($con, "SELECT DISTINCT YEAR(publish_date) AS yr FROM circulars WHERE is_active=1 ORDER BY yr DESC");

$sql = "SELECT id, title, file_name, department, publish_date
        FROM circulars
        WHERE is_active=1";
$params = [];
$types = '';

if ($dept !== 'all' && $dept !== '') {
    $sql .= " AND department=?";
    $params[] = $dept;
    $types .= 's';
}

if ($year !== 'all' && $year !== '') {
    $sql .= " AND YEAR(publish_date)=?";
    $params[] = (int)$year;
    $types .= 'i';
}

$sql .= " ORDER BY publish_date DESC, id DESC";

$stmt = mysqli_prepare($con, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

function formatDisplayDate($date) {
    return date('M d, Y', strtotime($date));
}

function getPdfSizeKB($fileName) {
    $path = __DIR__ . '/uploads/circulars/' . $fileName;
    if (!empty($fileName) && file_exists($path)) {
        return round(filesize($path) / 1024) . ' KB';
    }
    return '';
}

function buildFilterLink($dept, $year) {
    return 'portal-circulars.php?dept=' . urlencode($dept) . '&year=' . urlencode($year);
}
?>
<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>परिपत्रक | MPSC Bank Portal</title>
    <link href="./css/portal.css?v=7" rel="stylesheet" />
</head>
<body>
<div class="portal-page">

    <div class="portal-top-banner">
        <div class="portal-logo-box">
            <img src="./assets/logo.jpeg" alt="Bank Logo">
        </div>

        <div class="portal-title-box">
            <h1 class="bank-title">
                मामासाहेब पवार सत्यविजय सहकारी बँक<br>
                लि., कुंडल
            </h1>
        </div>
    </div>

    <div class="portal-menu-bar">
        <a href="web-main.php">होम</a>
        <a href="https://share.google/v0sGI322DcU2pWU9D" target="_blank">वेबसाईट</a>
        <a href="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'manage-circulars.php' : 'entry-forms.php'; ?>">एंट्री फॉर्म / रिपोर्ट</a>
        <a href="logout.php" class="portal-menu-right">लॉग आउट</a>
    </div>

    <div class="portal-main">
        <div class="portal-circular-layout">

            <!-- LEFT -->
            <div class="portal-left-box">
                <div class="portal-simple-card">
                    <ul class="portal-left-only-nav">
                        <li><a href="web-main.php">होम</a></li>
                    </ul>
                </div>
            </div>

            <!-- CENTER -->
            <div class="portal-center-box">
                <div class="portal-circulars-main-card">
                    <?php
                    $lastDate = '';
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $currentDate = $row['publish_date'];

                            if ($lastDate !== $currentDate) {
                                if ($lastDate !== '') {
                                    echo '</div>';
                                }
                                echo '<div class="portal-date-block">';
                                echo '<div class="portal-date-head">' . htmlspecialchars(formatDisplayDate($currentDate)) . '</div>';
                                $lastDate = $currentDate;
                            }

                            $pdfSize = getPdfSizeKB($row['file_name']);
                            ?>
                            <div class="portal-circular-item">
                                <div class="portal-circular-item-title">
                                    <a href="./uploads/circulars/<?php echo rawurlencode($row['file_name']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </a>
                                </div>
                                <div class="portal-circular-item-file">
                                    <span class="portal-pdf-icon">📄</span>
                                    <span><?php echo htmlspecialchars($pdfSize); ?></span>
                                </div>
                            </div>
                            <?php
                        }
                        echo '</div>';
                    } else {
                        echo '<div class="portal-no-circulars">No circulars available.</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="portal-right-box">
                <div class="portal-filter-card">
                    <div class="portal-filter-title">विभाग</div>
                    <ul class="portal-filter-list">
                        <li>
                            <a href="<?php echo buildFilterLink('all', $year); ?>" class="<?php echo ($dept === 'all') ? 'active' : ''; ?>">
                                सर्व विभाग
                            </a>
                        </li>

                        <?php if ($deptQuery && mysqli_num_rows($deptQuery) > 0) { ?>
                            <?php while ($drow = mysqli_fetch_assoc($deptQuery)) { ?>
                                <li>
                                    <a href="<?php echo buildFilterLink($drow['department_name'], $year); ?>"
                                       class="<?php echo ($dept === $drow['department_name']) ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($drow['department_name']); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>

                <div class="portal-filter-card">
                    <div class="portal-filter-title">वर्ष</div>
                    <ul class="portal-filter-list">
                        <li>
                            <a href="<?php echo buildFilterLink($dept, 'all'); ?>" class="<?php echo ($year === 'all') ? 'active' : ''; ?>">
                                सर्व वर्ष
                            </a>
                        </li>

                        <?php if ($yearQuery && mysqli_num_rows($yearQuery) > 0) { ?>
                            <?php while ($yrow = mysqli_fetch_assoc($yearQuery)) { ?>
                                <li>
                                    <a href="<?php echo buildFilterLink($dept, $yrow['yr']); ?>"
                                       class="<?php echo ($year == $yrow['yr']) ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($yrow['yr']); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <div class="portal-footer">
        <div>कॉपीराइट २०२६ © मामासाहेब पवार सत्यविजय सहकारी बँक लि., कुंडल. सर्व हक्क राखीव.</div>
        <div>Design and Developed By: Shreya Patil</div>
    </div>
</div>
</body>
</html>