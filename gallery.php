<?php

define('IMAGES_PER_PAGE', 40);

function pager($pages, $page) {
	echo "<div style='text-align: center;'>";	
	if ($page == 1) {
		echo "<< ";
	} else {
		echo "<a href='?page=" . ($page - 1) . "'><<</a> ";
	}
	for ($i = 1; $i <= count($pages); $i++) {
		if ($i == $page) {
			echo "$i ";
		} else {
			echo "<a href='?page=$i'>$i</a> ";
		}
	}
	if ($page == count($pages)) {
		echo ">> ";
	} else {
		echo "<a href='?page=" . ($page + 1) . "'>>></a> ";
	}
	echo "</div>";	
}

if (isset($_GET['thumb']) && strpos($_GET['thumb'], '..') === false || 
	isset($_GET['scaled']) && strpos($_GET['scaled'], '..') === false) {
	if (isset($_GET['thumb'])) {
		$file = $_GET['thumb'];
		$target = '.thumbs/thumb_' . $file;
		$width = 160;
		$height = 160;
	} else {
		$file = $_GET['scaled'];
		$target = '.thumbs/scaled_' . $file;
		$width = 1024;
		$height = 768;
	}
	header("Content-Type: image/jpeg"); 
	if (file_exists($target) && filemtime($target) > filemtime($file)) {
		$stat = @stat($target);
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $stat['mtime']) {
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
		$img->writeImage($target);
		echo $img;
	}
} else if (isset($_GET['show']) && strpos($_GET['show'], '..') === false) {
	?>
	<html>
	<head>
	<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="//code.jquery.com/ui/1.11.0/jquery-ui.min.js"></script>
	</head>
	<body>
	<?php
		$showthumbs = 3;
		$files = glob("{*.jpg,*.JPG}", GLOB_BRACE);
		$nextlink = "?";
		for ($i = 0; $i < sizeof($files); $i++) {
			if ($files[$i] == $_GET['show']) {
				if ($i+1 < sizeof($files)) {
					$nextlink = '?show='.$files[$i + 1];
					for ($j = $i+1; $j < $i+$showthumbs && $j < sizeof($files); $j++) {
						$nextpics []= $files[$j];
					}
				}
				if ($i != 0) {
					for ($j = $i-1; $j > $i-$showthumbs && $j >= 0; $j--) {
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
	</head>
	<body>
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

	foreach ($pages[$page-1] as $file) {
		$rel = "";
		if (file_exists($file.".tags")) {
			$rel = file_get_contents($file.".tags");
		}
		echo "<a href='?show=$file'><img src='?thumb=$file' rel='$rel' border='0' style='margin: 5px;'/></a>\n";
	}

	pager($pages, $page);

	?>
	</body>
	</html>
	<?php
}


?>
