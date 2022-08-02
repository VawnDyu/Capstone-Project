<?php

require_once('../class.php');
$sessionData = $payroll->getSessionData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'], 2);
$payroll->maintenance();

if(isset($_GET['availability']) && $_GET['availability'] == 'Available' ){
    $adminFullname = $sessionData['fullname'];
    $adminId = $sessionData['id'];
    $action = "Print";
    $table_name = "Available Employee QR";
    $admindatetime = $payroll->getDateTime();
    $adminTime = $admindatetime['time'];
    $adminDate = $admindatetime['date'];
                                        
    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
    $stmtAdminLog = $payroll->con()->prepare($sqlAdminLog);
    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                        
    $countRowAdminLog = $stmtAdminLog->rowCount();
}

if(isset($_GET['availability']) && $_GET['availability'] == 'Unavailable' ){
    $adminFullname = $sessionData['fullname'];
    $adminId = $sessionData['id'];
    $action = "Print";
    $table_name = "Unavailable Employee QR";
    $admindatetime = $payroll->getDateTime();
    $adminTime = $admindatetime['time'];
    $adminDate = $admindatetime['date'];
                                        
    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
    $stmtAdminLog = $payroll->con()->prepare($sqlAdminLog);
    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                        
    $countRowAdminLog = $stmtAdminLog->rowCount();
}

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

$writer = new PngWriter();

// Create QR code
$qrCode = QrCode::create($_GET['myqr'])
    ->setEncoding(new Encoding('UTF-8'))
    ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
    ->setSize(300)
    ->setMargin(10)
    ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
    // ->setForegroundColor(new Color(255, 255, 255))
    // ->setBackgroundColor(new Color(152, 74, 254));
    ->setForegroundColor(new Color(0, 0, 0))
    ->setBackgroundColor(new Color(255, 255, 255));

$result = $writer->write($qrCode);

// Directly output the QR code
header('Content-Type: '.$result->getMimeType());
echo $result->getString();

// Save it to a file
$result->saveToFile('./qrcode.png');

// Generate a data URI to include image data inline (i.e. inside an <img> tag)
$dataUri = $result->getDataUri();

?>