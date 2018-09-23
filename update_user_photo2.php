<?php

error_reporting(E_ALL);

if (!isset($_FILES['Filedata']['name'])) {
    $result = array('status'=>'400', 'message'=>'Filedata empty');
    echo json_encode($result);
    return;
}


$uploadPath = mb_strtolower(basename( $_FILES['Filedata']['name']));
$imageName = basename(mb_strtolower($_FILES['Filedata']['name']), ".jpg");

$uniqueid = $imageName;

$dir =  './photos/'.$uniqueid.'/';

//echo $dir;
if (!is_dir($dir))
{
	mkdir($dir, 0755);
}

move_uploaded_file($_FILES['Filedata']['tmp_name'], "$dir/$uploadPath");
chmod($uploadPath, 0777);



$config = (include './protected/config/main.php');
$connectionString = $config['components']['db']['connectionString'];

$startPos = strpos($connectionString,'host=') + 5;
$endPos = strpos($connectionString,';');
$offset = $endPos-$startPos;
$server = substr($connectionString,$startPos,$offset);

$startPos = strpos($connectionString,'dbname=') + 7;
$offset = 30;
$db = substr($connectionString,$startPos,$offset);

if(!ini_set("max_execution_time", "600"))
{
    throw new Exception("Failed to set execution time"); 
}

mysql_connect($server, $config['components']['db']['username'], $config['components']['db']['password']) or die('mysql Connection Error');
mysql_select_db($db) or die('Database Connection Error');

$photo = "http://api.garpie.co/photos/$uniqueid/"."$imageName"."_crop.jpg";
$query  = "UPDATE users SET photo='$photo' WHERE uniqueid='$uniqueid'";
$result = mysql_query($query) or die('Database Search Error');

$imageFile = $dir . $imageName . '.jpg';
$image = @imagecreatefromjpeg($imageFile);

$srcWidth = imagesx($image);
$srcHeight = imagesy($image);
$srcX = 0;
$srcY = 0;
if ($srcWidth > $srcHeight) {
    $srcX = ($srcWidth - $srcHeight) / 2;
    $srcWidth = $srcHeight;
} else if ($srcHeight > $srcWidth) {
    $srcY = ($srcHeight - $srcWidth) / 2;
    $srcHeight = $srcWidth;
}
$dstWidth = 200;
$dstHeight = 200;
$thumb = imagecreatetruecolor($dstWidth, $dstHeight);
imagecopyresampled($thumb, $image, 0, 0, $srcX, $srcY, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
$thumbFile = $dir . '/' . $imageName . '_crop.jpg';
imagejpeg($thumb, $thumbFile);

$result = array('status'=>'200', 'url'=>$photo);
echo json_encode($result);
?>