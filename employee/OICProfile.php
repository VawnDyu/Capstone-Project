<?php 
    require_once('../classemp.php');

    $sql = "SELECT status FROM maintenance WHERE module='Guards'";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute();
        
    $row = $stmt->fetchColumn();
        
    if ($row == 1) {
        header('location: ../m_maintenance.php');
    } else {
        $sessionData = $payroll->getSessionOICData();
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
    <title><?php echo $sessionData['fullname'] ?> | Profile</title>
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

        <div class="invisible-block">
        </div>
        <div class="user-profile">
            <div class="user-name">
                <header><?php echo $sessionData['fullname'] ?></header>
            </div>
            <div class="user-position">
                <header>(<?php echo $sessionData['position'] ?>)</header>
            </div>
            <div class="manage-account">
                <a href="OICManageAccount.php"><span class="material-icons-outlined">manage_accounts</span>Manage Account</a>
            </div>
            <div class="user-status">
                <header>Status</header>
                <?php $payroll->checkStatusProfile() ?>
                <!-- <span class="material-icons-outlined">
                    task_alt
                </span><span>On Duty | 10:30:31 PM</span> -->
            </div>
            <div class="user-info">
                <header>Information</header>
            </div>
            <div class="user-info-content">
                <header>Employee ID: <?php echo $sessionData['empId'] ?></header>
                <header>Company: <?php echo $sessionData['company']?></header>
                <header>Schedule: <?php echo date("H:i", strtotime($sessionData['scheduleTimeIn']))?> - <?php echo date("H:i", strtotime($sessionData['scheduleTimeOut']))?></header>
                <header>Shift: <?php echo ucfirst($sessionData['shift'])?></header>
            </div>

            <div class="user-contact">
                <header>Contact Me</header>

                <div class="profile-edit">
                    <a href="OICManageAccount.php">
                        <span class="material-icons-outlined">edit</span>
                        Edit
                    </a>
                </div>
            </div>

            <div class="user-contact-content">
                <header>Address: <?php echo $address ?></header>
                <header>Contact #: <?php echo $contact ?></header>
                <header>Email: <?php echo $sessionData['email']?></header>
            </div>
        </div>
    </div>
</body>
</html>