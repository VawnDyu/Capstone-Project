<?php
    require_once('../classemp.php');

    $sql = "SELECT status FROM maintenance WHERE module='Guards'";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute();
        
    $row = $stmt->fetchColumn();
        
    if ($row == 1) {
        header('location: ../m_maintenance.php');
    } else {
        session_start();
        $email = $_SESSION['qremail'];
        $password = $_SESSION['qrpass'];
        $seed = $_SESSION['seed'];
    
        if (empty($email) && empty($password) && empty($seed)) {
            header('location: OICQRLogin.php');
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
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="icon" href="../img/icon.png" type="image/png">
    <title>OIC | Scan QR</title>
</head>
<body>
    <div class="main-container">
        <div class="nav-bar-left">
            <div class="logo-header">
                <img src="../img/icon.png">
                <header>JTDV</header>
            </div>
        </div>
        <div class="nav-bar-right">
            <div class="navigator">
                <ul>
                    <li><a href="OICProfile.php"><span class="material-icons-outlined">person</span></a></li>
                    <li><a href="OIC.php"><span class="material-icons-outlined">other_houses</span></a></li>
                    <li><a href="OICInbox.php" class='notification'>
                            <span class="material-icons-outlined" style="transform: translateY(6%);">mail</span>
                            <?php $payroll->notificationBadge() ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="qr-navigator">
            <a href="#" class="a-active">Scan QR</a>
            <a href="qrcode/index.php?seed=<?php echo $seed?>" class="a-inactive">Get QR</a>
        </div>

        <div class="qr-scanner" id="qr-scanner">
                    
            <?php if(isset($_SESSION['qrerror'])) {
                $qrerror = $_SESSION['qrerror'];

                echo '<div class="qr-error-message">'.$qrerror.'</div>';
                }
            ?>

            <div id="qr-reader"></div>
        </div>
    </div>
</body>
</html>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function docReady(fn) {
        // see if DOM is already available
        if (document.readyState === "complete"
            || document.readyState === "interactive") {
            // call on next available tick
            setTimeout(fn, 1);
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }

    docReady(function () {
        var lastResult, countResults = 0;
        function onScanSuccess(decodedText, decodedResult) {
            if (decodedText !== lastResult) {
                ++countResults;
                lastResult = decodedText;
                // Handle on success condition with the decoded message.
                console.log(`Scan result ${decodedText}`, decodedResult);

                var qrdb = '<?php echo $_GET['seed'] ?>';
                var qrdiv = document.getElementById("qr-reader-results");
                var redirect = "https://jtdv.tech/employee/OICQRAttendance.php?seed=" + qrdb;
                // var redirect = "http://localhost:8080/backup/employee/OICQRAttendance.php?seed=" + qrdb; //For debugging


                if (qrdiv) {
                    qrdiv.parentNode.removeChild(qrdiv);
                    
                    if (decodedText == qrdb) {
                        document.location.href = redirect;
                    } else {
                        var div = document.createElement('div');
                        div.id = 'qr-reader-results';
                        div.innerHTML = 'Invalid QR Code';
                        document.getElementById("qr-scanner").prepend(div);
                    }

                } else {

                    if (decodedText == qrdb) {
                        document.location.href = redirect;
                    } else {
                        var div = document.createElement('div');
                        div.id = 'qr-reader-results';
                        div.innerHTML = 'Invalid QR Code';
                        document.getElementById("qr-scanner").prepend(div);
                    }

                }
            }
        }

        var html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
    });
</script>
</head>
</html>
