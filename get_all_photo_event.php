<?php
    include_once('./includes/config.php');
    $limit = 10;  // Number of records per page
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    
    $stmt = mysqli_prepare($con, "SELECT * FROM photo_event WHERE pe_visible=1 LIMIT ?, ?");
    mysqli_stmt_bind_param($stmt, "ii", $start, $limit);
    mysqli_stmt_execute($stmt);
    $ret = mysqli_stmt_get_result($stmt);
    $sr = 0;                                
    while($row = mysqli_fetch_assoc($ret)){
        echo '<div class="ribbon"> &nbsp; &nbsp;' . htmlspecialchars($row['pe_title']) . ' &nbsp; &nbsp; </div> <div class="clearfix"></div>';
        $dir = "assets/photo_gallery/" . (int)$row["idphoto_event"] . "/";
        
        // Open a directory, and read its contents
        if (is_dir($dir)){
          if ($dh = opendir($dir)){
            
            while (($file = readdir($dh)) !== false){
                if($file != '.' && $file != '..'){   
                  echo '<div class="col-md-3 mb-3 col-12">';
                  echo '<img src="' . htmlspecialchars('admin/' . $dir . $file) . '" class="img-fluid gallery-img" data-index="' . $sr++ . '">';
                  echo '</div>';
                }
            }
            closedir($dh);
            echo '<div class="clearfix"></div>';
          }
        }
    }
    mysqli_stmt_close($stmt);
