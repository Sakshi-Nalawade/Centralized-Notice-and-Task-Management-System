<?php
include_once('./includes/auth-portal.php');
include_once('./includes/config.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid circular.");
}

$id = (int)$_GET['id'];

$stmt = mysqli_prepare($con, "SELECT * FROM circulars WHERE id=? AND is_active=1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Circular not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Circular</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 30px;">
    <h2><?php echo htmlspecialchars($row['title']); ?></h2>
    <p>This detailed circular page will be built next.</p>
    <p><a href="web-main.php">← Back to Home</a></p>
</body>
</html>