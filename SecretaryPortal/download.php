<?php
$files = basename($_GET['file']);
$file = "../SecretaryPortal/merged/".$files;
$filename = $files;

if(!file_exists($file)){ // file does not exist
    die('file not found');
} else {
    header("Content-Disposition:attachment;filename=$filename");
    header("Content-Type: application/pdf");
    ob_clean(); 
    flush(); 
    // read the file from disk
    readfile($file);
}
?>