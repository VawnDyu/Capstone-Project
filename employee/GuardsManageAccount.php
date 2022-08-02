<?php 
    require_once('../classemp.php');
    
    $sql = "SELECT status FROM maintenance WHERE module='Guards'";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute();
        
    $row = $stmt->fetchColumn();
        
    if ($row == 1) {
        header('location: ../m_maintenance.php');
    } else {
        $sessionData = $payroll->getSessionGuardsData();
        $payroll->MobileVerifyUserAccess($sessionData['access'], $sessionData['fullname'], $sessionData['position']);
    
        $payroll->changeInfo();
    
        isset($_SESSION['GuardsDetails']) ? $empId = $_SESSION['GuardsDetails']['empId'] : $empId = $_SESSION['OICDetails']['empId'];
    
        $sqlFind = "SELECT * FROM employee WHERE empId = ?";
        $stmtFind = $payroll->con()->prepare($sqlFind);
        $stmtFind->execute([$empId]);
    
        $userFind = $stmtFind->fetch();
    
        $contact = $userFind->cpnumber;
        $address = $userFind->address;
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
    <title>About Me</title>
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
                    <li><a href="GuardsProfile.php"><span class="material-icons-outlined">person</span></a></li>
                    <li><a href="Guards.php"><span class="material-icons-outlined">other_houses</span></a></li>
                    <li><a href="GuardsInbox.php" class='notification'>
                            <span class="material-icons-outlined" style="transform: translateY(6%);">mail</span>
                            <?php $payroll->notificationBadge() ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="main-header">
            <header>Manage Account</header>
        </div>

        <div class="account-navigator">
            <a href="GuardsManageAccount.php" class="a-active">About Me</a>
            <a href="GuardsChangePassword.php" class="a-inactive">Password</a>
        </div>

        <div class="first-block-title">
            <span class="material-icons-outlined">person</span> Personal Information
        </div>

        <div class="change-information-form">
            <form method="post">
                <div class="contact">
                    <label for="contact">Contact No.</label>
                    <input type="tel" name="contact" id="contact" value="<?php echo $contact ?>" pattern="09[0-9]{9}" autocomplete="off" maxlength="11" placeholder="09xxxxxxxxx" required>
                </div>
                <div class="address">
                    <label for="address">Address</label>
                    <input type="text" name="address" id="address" value="<?php echo $address ?>" placeholder="Enter your complete address" required>
                </div>
                <button type="submit" name="submit">Submit</button>
            </form>
        </div>
    </div>

    <div class="view-modal-success">  
        <div class="modal-success">
            <header class='success-header'>Success</header>
            <?php $payroll->getSuccessModalMsg() ?>
            <button type="button" id="btnOkaySuccess">Okay</button>
        </div>
    </div>

    <div class="view-modal-error">  
        <div class="modal-error">
            <header class='error-header'>Error</header>
            <?php $payroll->getErrorModalMsg() ?>
            <button type="button" id="btnOkay">Okay</button>
        </div>
    </div>

    <?php $payroll->showMsgModal() ?>

    <script>
        var click = document.getElementById("btnOkay");
        var clicksuccess = document.getElementById("btnOkaySuccess");
        var errormodal = document.getElementsByClassName('view-modal-error');
        var successmodal = document.getElementsByClassName('view-modal-success');

        click.addEventListener("click", function() {
            for (var i=0;i<errormodal.length;i+=1) {
                errormodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "GuardsManageAccount.php");
            }
        });

        clicksuccess.addEventListener("click", function() {
            for (var i=0;i<successmodal.length;i+=1) {
                successmodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "GuardsManageAccount.php");
            }
        });
    </script>
</body>
</html>