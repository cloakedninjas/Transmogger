<?php
phpinfo();
exit;

$ref = '4f3646bf7fd96';

writeLog('Starting process');

exec('pgrep Xvfb', $output1);

if (empty($output1)) {
	writeLog('Xvfb not running - starting service');
	exec('Xvfb :1 -screen 0 800x600x24 &');
	sleep(1);
}

exec('pgrep firefox', $output2);

if (empty($output2)) {
	writeLog('Firefox not running - starting service');
	exec('DISPLAY=:1 firefox about:blank &');
	sleep(3);
}

writeLog('Loading Transmogger');
exec('DISPLAY=:1 firefox http://transmogger.info/screengrab/index/?ref=' . $ref . ' &');

sleep(3);

$save_path = '/home/transmog/screengrabber/' . $ref . '.png';

writeLog('Taking screenshot');
exec('DISPLAY=:1 import -window root ' . $save_path . ' &');

writeLog('Cropping');

if (!file_exists($save_path)) {
	writeLog('Screenshot did not exist :S');
	exit;
}

$img = imagecreatefrompng($save_path);

// src image size
$img_width  = imagesx($img);
$img_height = imagesy($img);

// New image size
$width  = 300;
$height = 380;

// Starting point of crop
$tlx = 0;
$tly = 85;

$im = imagecreatetruecolor($width, $height);
imagecopy($im, $img, 0, 0, $tlx, $tly, $width, $height);

// cover the arrows
$bg = imagecolorallocate($im, 24, 24, 24);
imagefilledrectangle($im, 265, 335, 281, 351, $bg);

imagepng($im, $save_path);
imagedestroy($im);
		
// move image to www
rename($save_path, '/home/transmog/public_html/shots/' . $ref . '.png');


function writeLog($msg) {
	$log = '/home/transmog/screengrabber/log.txt';
	$line = date('Y-m-d H:i:s') . ' - ' . $msg . "\n";
	file_put_contents($log , $line, FILE_APPEND);
	echo "<p>$msg</p>";
}