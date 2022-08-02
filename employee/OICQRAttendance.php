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

        if (isset($_POST['done'])) {
            unset($_SESSION['login_session']);
            unset($_SESSION['qremail']);
            unset($_SESSION['qrpass']);
            unset($_SESSION['seed']);
            header('location: OIC.php');
        }
    
        if (isset($_SESSION['qremail']) && isset($_SESSION['qrpass']) && isset($_SESSION['seed'])) {
            $email = $_SESSION['qremail'];
            $password = $_SESSION['qrpass'];
            $seed = $_SESSION['seed'];
            $getSeed = $_GET['seed'];
        }
    
        if (!empty($email) && !empty($password) && !empty($seed)) {
            if ($seed == $getSeed) {
                $sql = "SELECT 
                            s.empId,
                            s.scheduleTimeIn,
                            s.scheduleTimeOut,
                            e.lastname,
                            e.firstname
                        FROM employee e 
                    
                        INNER JOIN schedule s
                        ON e.empId = s.empId
                    
                        WHERE email = ? AND password = ? AND qrcode = ?";
                $stmt = $payroll->con()->prepare($sql);
                $stmt->execute([$email, $password, $seed]);
    
                $users = $stmt->fetch();
                $countRow = $stmt->rowCount();
    
                if ($countRow > 0) {
                    $firstname = $users->firstname;
                    $lastname = $users->lastname;
                }
                $payroll->submitQRAttendance();
            }
        } else {
            header('location: OICScanQR.php');
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
    <title>OIC | QR Attendance</title>
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
        </div>

        <div class="main-header"><?php echo 'Hello! '.$lastname.', '.$firstname ?></div>

        <div class="qr-status">
            <form method='post'>
                <?php 
                    if ($_SESSION['login_session'] == "true") {
                        echo '
                                <span class="material-icons-outlined">task_alt</span>
                                <header>You have successfully timed-in.</header>
                                <button type="submit" class="done" name="done">Done</button>
                            ';
                    } else if ($_SESSION['login_session'] == "already"){
                        echo '
                            <span class="material-icons-outlined" style="color:rgb(255, 101, 83)">cancel</span>
                            <header>You have already timed-in.</header>
                            <button type="submit" class="done" name="done" style="background:rgb(255, 101, 83)">Back</button>
                        ';
                    } else {
                        echo '
                        <span class="material-icons-outlined" style="color:rgb(255, 101, 83)">cancel</span>
                        <header>You can only time-in (1 hour) before time schedule</header>
                        <button type="submit" class="done" name="done" style="background:rgb(255, 101, 83)">Back</button>
                    ';
                    }
                ?>
            </form>
        </div>
    </div>
</body>
</html>