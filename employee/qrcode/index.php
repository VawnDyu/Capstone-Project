<?php 
    require_once('../../classemp.php');
    
    $sql = "SELECT status FROM maintenance WHERE module='Guards'";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute();
        
    $row = $stmt->fetchColumn();
        
    if ($row == 1) {
        header('location: ../../m_maintenance.php');
    } else {
        session_start();
        $qremail = $_SESSION['qremail'];
        $qrpass = $_SESSION['qrpass'];
    
        if(isset($_GET['seed'])) {
            $seed = $_GET['seed'];
    
            $sql = "SELECT * FROM employee WHERE email = ? AND password = ?";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute([$qremail, $qrpass]);
    
            $users = $stmt->fetch();
            $countRow = $stmt->rowCount();
    
            if ($countRow > 0) {
                $verifyseed = $users->qrcode;
                if ($seed != $verifyseed) {
                    header('location: ../OICQRLogin.php');
                    unset($qremail);
                    unset($qrpass);
                }
                
            }
            
            $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        
            $PNG_WEB_DIR = 'temp/';
        
            include "qrlib.php";    
        
            if (!file_exists($PNG_TEMP_DIR))
                mkdir($PNG_TEMP_DIR);
            
            
            $filename = $PNG_TEMP_DIR.$seed.'.png';
            
            $errorCorrectionLevel = 'L';
            $matrixPointSize = 9;
            
            QRcode::png("$verifyseed", $filename, $errorCorrectionLevel, $matrixPointSize, 2);  
            
        }
    }
    
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="icon" href="../../img/icon.png" type="image/png">
    <title>OIC | Get QR</title>
</head>
<body>
    <div class="main-container">
        <div class="nav-bar-left">
            <div class="logo-header">
                <img src="../../img/icon.png">
                <header>JTDV</header>
            </div>
        </div>
        <div class="nav-bar-right">
            <div class="navigator">
                <ul>
                    <li><a href="../OICProfile.php"><span class="material-icons-outlined">person</span></a></li>
                    <li><a href="../OIC.php"><span class="material-icons-outlined">other_houses</span></a></li>
                    <li><a href="../OICInbox.php" class='notification'>
                            <span class="material-icons-outlined" style="transform: translateY(6%);">mail</span>
                            <?php $payroll->notificationBadge() ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="qr-navigator">
            <a href="../OICScanQR.php?seed=<?php echo $seed?>" class="a-inactive">Scan QR</a>
            <a href="qrcode/index.php?seed=<?php echo $seed?>" class="a-active">Get QR</a>
        </div>

        <div class="qr-code">
        <?php 
            echo "<h1>QR Code</h1><hr/>";
            echo '<div class="qr-img"><img src="'.$PNG_WEB_DIR.basename($filename).'" /></div>';
            echo '<hr/><div class="qr-download"><a href="'.$PNG_WEB_DIR.basename($filename).'" download>Download</a></div>';
        ?>
        </div>
    </div>
</body>
</html>