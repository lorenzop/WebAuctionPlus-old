<?php
$username=$_GET['user'];
// The file
$filename = "http://www.minecraft.net/skin/".$username.".png";
// Content type
header("Content-type: image/png");
// Resample
$image_p = imagecreatetruecolor(64, 64);
$image = imagecreatefrompng($filename);
imagecopyresampled($image_p, $image, 0, 0, 8, 8, 64, 64, 8, 8);
// Output
imagepng($image_p);
?>