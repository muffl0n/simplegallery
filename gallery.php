<?php

include('config.php');

/**
 * Simple gallery for PHP.
 *
 * @author   Sven Schliesing <sven@schliesing.de>
 * @link     https://github.com/muffl0n/simplegallery
 */

define('IMAGES_PER_PAGE', 40);
define('THUMBS', 3);

define('THUMB_MAX_WIDTH', 160);
define('THUMB_MAX_HEIGHT', 160);

define('IMAGE_MAX_WIDTH', 1024);
define('IMAGE_MAX_HEIGHT', 768);

function autoRotateImage($image)
{
    $orientation = $image->getImageOrientation(); 

    switch($orientation) { 
        case imagick::ORIENTATION_BOTTOMRIGHT: 
            $image->rotateimage("#000", 180);
        break; 

        case imagick::ORIENTATION_RIGHTTOP: 
            $image->rotateimage("#000", 90); 
        break; 

        case imagick::ORIENTATION_LEFTBOTTOM: 
            $image->rotateimage("#000", -90);
        break; 
    } 
    $image->setImageOrientation(imagick::ORIENTATION_TOPLEFT); 
} 

function pager($pages, $page)
{
    echo "<div id='pager'>";   
    if ($page == 1) {
        echo "<< ";
    } else {
        echo "<a href='?page=" . ($page - 1) . "'><<</a> ";
    }
    $pageCount = count($pages);
    for ($i = 1; $i <= $pageCount; $i++) {
        if ($i == $page) {
            echo "$i ";
        } else {
            echo "<a href='?page=$i'>$i</a> ";
        }
    }
    if ($page == $pageCount) {
        echo ">> ";
    } else {
        echo "<a href='?page=" . ($page + 1) . "'>>></a> ";
    }
    echo "</div>";  
}

function gps($coordinate, $hemisphere) 
{
    if ($coordinate == NULL || $hemisphere == NULL) {
        return NULL;
    }
    for ($i = 0; $i < 3; $i++) {
        $part = explode('/', $coordinate[$i]);
        if (count($part) == 1) {
            $coordinate[$i] = $part[0];
        } else if (count($part) == 2) {
            $coordinate[$i] = floatval($part[0])/floatval($part[1]);
        } else {
            $coordinate[$i] = 0;
        }
    }
    list($degrees, $minutes, $seconds) = $coordinate;
    $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
    return $sign * ($degrees + $minutes/60 + $seconds/3600);
}

if (isset($_GET['thumb']) && strpos($_GET['thumb'], '..') === FALSE
    || isset($_GET['scaled']) && strpos($_GET['scaled'], '..') === FALSE
) {
    if (isset($_GET['thumb'])) {
        $file = $_GET['thumb'];
        $target = '.thumbs/thumb_' . $file;
        $width = THUMB_MAX_WIDTH;
        $height = THUMB_MAX_HEIGHT;
    } else {
        $file = $_GET['scaled'];
        $target = '.thumbs/scaled_' . $file;
        $width = IMAGE_MAX_WIDTH;
        $height = IMAGE_MAX_HEIGHT;
        header('Content-Disposition: inline; filename = "scaled_' . $file . '"');
    }
    header("Content-Type: image/jpeg"); 
    if (file_exists($target) 
        && ($stat = stat($target)) !== FALSE
        && $stat['mtime'] > filemtime($file)) {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
            && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $stat['mtime']
        ) {
            header('Last-Modified: ' . date('r', $stat['mtime']));
            header('HTTP/1.0 304 Not Modified');
        } else {
            header('Last-Modified: ' . date('r', $stat['mtime']));
            header('Content-Length:' . $stat['size']);
            readfile($target);
        }
    } else {
        $img = new Imagick();
        $img->readImage($file);
        $img->resizeImage($width, $height, Imagick::FILTER_BOX, 1, TRUE);
        autoRotateImage($img);
        $img->writeImage($target);
        echo $img;
    }
} else if (isset($_GET['show']) && strpos($_GET['show'], '..') === FALSE) {
    ?>
    <html>
    <head>
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="//code.jquery.com/ui/1.11.0/jquery-ui.min.js"></script>
    <style type="text/css">
        #cursor_hint {
            text-align: center;
            background: lightgreen;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
            padding: 2px;
        }
    </style>
    </head>
    <body>
    <div id="cursor_hint">Use cursors to navigate!</div>
    <?php
    $files = glob("{*.jpg,*.JPG}", GLOB_BRACE);
    $nextlink = "?";
    $fileCount = count($files);
    for ($i = 0; $i < $fileCount; $i++) {
        if ($files[$i] == $_GET['show']) {
            if ($i + 1 < $fileCount) {
                $nextlink = '?show='.$files[$i + 1];
                for ($j = $i + 1; $j <= $i + THUMBS && $j < $fileCount; $j++) {
                    $nextpics []= $files[$j];
                }
            }
            if ($i != 0) {
                for ($j = $i - 1; $j >= $i - THUMBS && $j >= 0; $j--) {
                    $prevpics []= $files[$j];
                }
            }
            $prevpics = array_reverse($prevpics);   
            break;
        }
    }

    $prevpic = $prevpics[count($prevpics) - 1];
    $nextpic = $nextpics[0];

    echo "<script>
        \$(document).on(\"keydown\", function(event) {
            switch( event.keyCode ) {
                case \$.ui.keyCode.LEFT:
                    if ('$prevpic' != '') {
                        window.location = '?show=$prevpic'
                    }
                    break;
                case \$.ui.keyCode.RIGHT:
                    if ('$nextpic' != '') {
                        window.location = '?show=$nextpic'
                    }
                    break;
            }
        });
    </script>";

    $exif = exif_read_data($_GET['show']);
    echo "<p style='text-align:center'>";
    foreach ($prevpics as $pic) {
        echo "<a href='?show=$pic'><img src='?thumb=$pic' style='margin-left: 5px; margin-right: 5px;'></a>";
    }
    echo "<a href='?'>up</a>";
    foreach ($nextpics as $pic) {
        echo "<a href='?show=$pic'><img src='?thumb=$pic' style='margin-left: 5px; margin-right: 5px;'></a>";
    }
    echo "</p>";
    echo "<p style='text-align:center'>";
    echo "<a href='$nextlink'><img src='?scaled=".$_GET['show']."' style='max-width:800px; max-height:600px;'></a><br>";
    echo $exif['DateTime'];
    echo "<br/>";
    echo "<a href='".$_GET['show']."'>show original</a>";
    echo "<br />";
    if (isset($google_maps_api_key)) {
    	$latitude = gps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
    	$longitude = gps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
        if ($latitude != NULL && $longitude != NULL) {
            echo '<iframe
                width="600"
                height="450"
                frameborder="0" style="border:0"
                src="https://www.google.com/maps/embed/v1/place?key='.$google_maps_api_key.'&q='.$latitude.', '.$longitude.'" allowfullscreen>
                </iframe>';
        }
    }
    echo "</p>";
    ?>
    </body>
    </html>
    <?php
} else {
    ?>
    <html>
    <head>
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="//code.jquery.com/ui/1.11.0/jquery-ui.min.js"></script>
    <style type="text/css">
        #cursor_hint {
            text-align: center;
            background: lightgreen;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
            padding: 2px;
        }
        #pager {
            text-align: center; 
            clear: left;
        }
        div.thumbnail {
            padding: 10px; 
            width: <?= THUMB_MAX_WIDTH ?>; 
            height: <?= THUMB_MAX_HEIGHT ?>; 
            float: left;
        }
    </style>
    </head>
    <body>
    <div id="cursor_hint">Use cursors to navigate!</div>
    <?php
    $files = glob("{*.jpg,*.JPG}", GLOB_BRACE);

    $pages = array_chunk($files, IMAGES_PER_PAGE);

    $options = array('options' => array('min_rage' => 1, 'max_range' => count($pages)+1, 'default' => 1));
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, $options);

    pager($pages, $page);

    echo "<script>
        var page = $page;
        var pages = " . count($pages) . ";
        $(document).keydown(function(e){    
            if (e.keyCode == 37 && page > 1) {
                window.location = '?page=" . ($page-1) . "';
            } 
            if (e.keyCode == 39 && page < pages) {
                window.location = '?page=" . ($page+1) . "';
            }
        });
    </script>";

    $day = null;
    echo "<div style='clear:left'>\n";
    foreach ($pages[$page - 1] as $file) {
        $rel = "";
        if (file_exists($file.".tags")) {
            $rel = file_get_contents($file.".tags");
        }
        $exif = exif_read_data($file);
        if (isset($exif['DateTime'])) {
            $d = DateTime::createFromFormat('Y:m:d H:i:s', $exif['DateTime']);
            $day_file = $d->format('d.m.Y');
        }
        if ($day == null || $day_file != $day) {
            echo "</div><div style='clear:left'><h1>" . $day_file . "</h1>";
            $day = $day_file;
        }
        echo "<div class='thumbnail'><a href='?show=$file'><img src='?thumb=$file' rel='$rel'/></a></div>\n";
    }
    echo "<div>\n";

    pager($pages, $page);

    ?>
    </body>
    </html>
    <?php
}

?>
