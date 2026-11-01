<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}

$data = $errors = $uploadedFiles = array();
$photo_event = null;

if (isset($_POST['save'])) {
    $required = 1;
    $pe_title = trim($_POST['pe_title']);
    $pe_description = trim($_POST['pe_description']);
    $pe_visible = isset($_POST['pe_visible']) ? 1 : 0;

    if ($pe_title == "") {
        $data['pe_title'] = 'This field is required';
        $required = 0;
    }

    if ($required == 1) {
        if (isset($_POST['idphoto_event']) && $_POST['idphoto_event'] != "") {
            // Update using prepared statement
            $eventId = (int)$_POST['idphoto_event'];
            $stmt = mysqli_prepare($con, "UPDATE photo_event SET pe_title=?, pe_description=?, pe_visible=? WHERE idphoto_event=?");
            mysqli_stmt_bind_param($stmt, "ssii", $pe_title, $pe_description, $pe_visible, $eventId);
            $msg = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $last_id = $eventId;
        } else {
            // Insert using prepared statement
            $stmt = mysqli_prepare($con, "INSERT INTO photo_event (pe_title, pe_description, pe_visible) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssi", $pe_title, $pe_description, $pe_visible);
            $msg = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $last_id = mysqli_insert_id($con);
        }

        if ($msg) {
            $uploadDir = "assets/photo_gallery/" . $last_id . "/";

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB limit
            $fileuploadcnt = 0;

            if (isset($_FILES['userfile']) && is_array($_FILES['userfile']['tmp_name'])) {
                foreach ($_FILES['userfile']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['userfile']['name'][$key] != "") {
                        $fileName = $_FILES['userfile']['name'][$key];
                        $fileSize = $_FILES['userfile']['size'][$key];
                        $fileTmp = $_FILES['userfile']['tmp_name'][$key];
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                        $newFileName = "image_" . time() . '_' . ($key + 1) . '.' . $fileExt;

                        if (!in_array($fileExt, $allowedTypes)) {
                            $errors[] = htmlspecialchars($fileName) . " is not a valid image file.";
                            continue;
                        }
                        if ($fileSize > $maxSize) {
                            $errors[] = htmlspecialchars($fileName) . " exceeds the 2MB size limit.";
                            continue;
                        }

                        if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                            $uploadedFiles[] = $newFileName;
                            $fileuploadcnt++;
                        } else {
                            $errors[] = "Failed to upload " . htmlspecialchars($fileName) . ".";
                        }
                    }
                }
            }

            $data['errors'] = $errors;
            $data['uploadedFiles'] = $uploadedFiles;
            $data['uploadedfilecnt'] = $fileuploadcnt;
            $data['msgsuccess'] = "Record saved successfully.";
        }
    }

    // Re-fetch the photo event data for display after save
    if (isset($last_id) && $last_id > 0) {
        $fetchStmt = mysqli_prepare($con, "SELECT * FROM photo_event WHERE idphoto_event=?");
        mysqli_stmt_bind_param($fetchStmt, "i", $last_id);
        mysqli_stmt_execute($fetchStmt);
        $fetchResult = mysqli_stmt_get_result($fetchStmt);
        $photo_event = mysqli_fetch_assoc($fetchResult);
        mysqli_stmt_close($fetchStmt);
    }

} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $eventId = (int)$_GET['id'];

    // Handle image deletion
    if (isset($_GET['image']) && $_GET['image'] != "") {
        $imageName = basename($_GET['image']); // Prevent directory traversal
        $url = './assets/photo_gallery/' . $eventId . '/' . $imageName;
        if (file_exists($url)) {
            unlink($url);
        }
        header("Location: photo_gallery.php?id=" . $eventId);
        exit();
    }

    // Fetch event data using prepared statement
    $stmt = mysqli_prepare($con, "SELECT * FROM photo_event WHERE idphoto_event=?");
    mysqli_stmt_bind_param($stmt, "i", $eventId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $photo_event = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Photo Gallery | MPSC Internal Portal</title>
        <link href="./css/styles.css" rel="stylesheet" />
        <link href="./css/custom.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
      <?php include_once('includes/navbar.php');?>
        <div id="layoutSidenav">
          <?php include_once('includes/sidebar.php');?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Photo Event <?php echo isset($photo_event) ? 'Edit' : 'Add'; ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="manage-circulars.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Photo Event <?php echo isset($photo_event) ? 'Edit' : 'Add'; ?></li>
                        </ol>

                        <div class="card mb-4">
                            <form action="photo_gallery.php<?php echo isset($photo_event) ? '?id=' . (int)$photo_event['idphoto_event'] : ''; ?>" method="post" enctype="multipart/form-data">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                              Photo Gallery
                            </div>
                            <div class="card-body">
                                <div class="row clearfix">
                                    <input type="hidden" name="idphoto_event" value="<?php echo isset($photo_event) ? (int)$photo_event['idphoto_event'] : ''; ?>"/>
                                        <div class="col-md-6">
                                            <label for="pe_title" class="control-label"><span class="text-danger">*</span>Title</label>
                                            <div class="form-group">
                                                <input type="text" name="pe_title" value="<?php echo isset($photo_event) ? htmlspecialchars($photo_event['pe_title']) : ''; ?>" class="form-control" id="pe_title" />
                                                <span class="text-danger"><?php echo isset($data['pe_title']) ? htmlspecialchars($data['pe_title']) : ''; ?></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="pe_visible" class="control-label">Visible</label>
                                            <div class="form-group">
                                                <input type="checkbox" name="pe_visible" value="1" style="width: 25px;height: 25px;" id="pe_visible" <?php echo isset($photo_event) && $photo_event['pe_visible'] == 1 ? 'checked' : ''; ?>  />
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="col-md-6">
                                            <label for="pe_description" class="control-label">Description</label>
                                            <div class="form-group">
                                                <textarea name="pe_description" class="form-control" id="pe_description"><?php echo isset($photo_event) ? htmlspecialchars($photo_event['pe_description']) : ''; ?></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="userfile" class="control-label"><span class="text-danger">*</span>Upload Photos</label>
                                            <div class="form-group">
                                                <input type="file" name="userfile[]" class="form-control" id="userfile" multiple="" />
                                            </div>
                                        </div>

                                        <div class="clearfix" style="margin-bottom: 10px;"></div>

                                        <div class="col-md-12">
                                            <?php if (isset($data['uploadedfilecnt']) && $data['uploadedfilecnt'] > 0) { ?>
                                            <div class="alert alert-success">Total <?php echo (int)$data['uploadedfilecnt']; ?> Image Files uploaded successfully.</div>
                                            <?php } elseif (isset($data['uploadedfilecnt']) && $data['uploadedfilecnt'] == 0 && isset($data['msgsuccess'])) { ?>
                                            <div class="alert alert-success"><?php echo htmlspecialchars($data['msgsuccess']); ?></div>
                                            <?php } ?>
                                        </div>

                                        <div class="col-md-12">
                                            <?php if (isset($data['errors']) && !empty($data['errors'])) {
                                                foreach ($data['errors'] as $err) { ?>
                                            <div class="alert alert-danger"><?php echo $err; ?></div>
                                                <?php }
                                            } ?>
                                        </div>

                                </div>
                            </div>

                                <div class="card-footer">
                                    <button type="submit" name="save" value="save" class="btn btn-success btn-block">
                                        <i class="fa fa-check"></i> Save
                                    </button>

                                    <a href="./photo_event_list.php" class="btn btn-warning btn-block"><i class="fa fa-arrow-left"></i> Back</a>
                                </div>

                            </form>
                        </div>

    <div class="col-md-12">
        <div class="">
            <div class="row gallery">
            <?php
                if (isset($photo_event)) {
                    $dir = "./assets/photo_gallery/" . (int)$photo_event["idphoto_event"] . "/";

                    if (is_dir($dir)) {
                        if ($dh = opendir($dir)) {
                            while (($file = readdir($dh)) !== false) {
                                if ($file != '.' && $file != '..') {
            ?>
            <div class="col-md-3">
                <div class="card ">
                    <div class="card-body">
                        <a href="<?php echo htmlspecialchars($dir . $file); ?>" target="_blank"><img src="<?php echo htmlspecialchars($dir . $file); ?>" class="img-fluid gallery-img" data-index="0"></a>
                    </div>
                    <div class="card-footer">
                        <a href="photo_gallery.php?id=<?php echo (int)$photo_event["idphoto_event"]; ?>&image=<?php echo urlencode($file); ?>" onclick="return confirm('Do you really want to delete this image?');" class="btn btn-danger" style="width: 100%;"><i class="fa fa-trash"></i> Remove Image</a>
                    </div>
                </div>
            </div>

            <?php
                                }
                            }
                            closedir($dh);
                        }
                    }
                }
            ?>
            </div>
        </div>
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
