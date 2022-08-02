<?php 
    require_once('../classemp.php');
    
    $sqlM = "SELECT status FROM maintenance WHERE module='Guards'";
    $stmtM = $payroll->con()->prepare($sqlM);
    $stmtM->execute();
        
    $rowM = $stmtM->fetchColumn();
        
    if ($rowM == 1) {
        header('location: ../m_maintenance.php');
    } else {
        $sessionData = $payroll->getSessionOICData();
        $payroll->MobileVerifyUserAccess($sessionData['access'], $sessionData['fullname'], $sessionData['position']);
    
        if (isset($_GET['id'])) {
            $empId = $_GET['id']; 
    
            $sql = "SELECT
                        *
                    FROM employee e
        
                    INNER JOIN schedule s
                    ON e.empId = s.empId
        
                    WHERE e.empId = ?";
        
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute([$empId]);
        
            $users = $stmt->fetch();
        } else {
            header('location: OICAssignGuards.php');
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
    <title><?php echo $users->firstname.' '. $users->lastname ?> | Profile</title>
</head>
<body>
    <div class="main-container">

        <div class='popup-message-view'>
            <a href ="OICAssignGuards.php">
                <p><span class='material-icons-outlined'>visibility</span>You are in the viewing mode right now.</p>
            </a>
            <div class='back'>
                <a href="OICAssignGuards.php"><span class="material-icons-outlined">arrow_back</span></a>
            </div>

        </div>

        <div class="nav-bar-left">
            <div class="logo-header">
                <img src="../img/icon.png">
                <header>JTDV</header>
            </div>
        </div>
        <div class="nav-bar-right">
            <div class="navigator">
                <ul>
                </ul>
            </div>
        </div>

        <div class="invisible-block">
        </div>
        <div class="user-profile">
            <div class="user-name">
                <header><?php echo $users->firstname.' '. $users->lastname ?></header>
            </div>
            <div class="user-position">
                <header>(<?php echo $users->position ?>)</header>
            </div>
            <div class="manage-account">

            </div>
            <div class="user-status">
                <header>Status</header>
                <?php
                    $login_session = 'true';

                    $sqlCheck = "SELECT * 
                            FROM emp_attendance 
                            WHERE empId = ? AND login_session = ?";
                    $stmtCheck = $payroll->con()->prepare($sqlCheck);
                    $stmtCheck->execute([$empId, $login_session]);

                    $usersCheck = $stmtCheck->fetch();
                    $countRowCheck = $stmtCheck->rowCount();

                    if ($countRowCheck > 0) {
                        echo "<span class='material-icons' style='color:rgb(25, 199, 115)'>circle</span>
                            <span>On-duty |&nbsp</span>
                            <span>$usersCheck->timeIn</span>";
                    } else {
                        echo "<span class='material-icons' style='color:#af1f1f'>circle</span>
                            <span>Off-duty</span>";
                    }
                
                ?>
            </div>
            <div class="user-info">
                <header>Information</header>
            </div>
            <div class="user-info-content">
                <header>Employee ID: <?php echo $users->empId ?></header>
                <header>Company: <?php echo $users->company?></header>
                <header>Schedule: <?php echo date("H:i", strtotime($users->scheduleTimeIn)) ?> - <?php echo date("H:i", strtotime($users->scheduleTimeOut)) ?></header>
                <header>Shift: <?php echo $users->shift ?></header>
            </div>

            <div class="user-contact">
                <header>Contact Me</header>

                <div class="profile-edit">

                </div>
            </div>

            <div class="user-contact-content">
                <header>Address: <?php echo $users->address ?></header>
                <header>Contact #: <?php echo $users->cpnumber ?><a href="tel:<?php echo $users->cpnumber ?>">Call</a></header>
                <header>Email: <?php echo $users->email ?></header>
            </div>
        </div>
    </div>
</body>
</html>