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
    
        if (isset($_POST['submit'])) {
            
            $email = $_POST['email'];
            $password = $payroll->generatedPassword($_POST['password']);
    
            $sql = "SELECT * FROM employee wHERE email = ? AND password = ?";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute([$email, $password[0]]);
    
            $users = $stmt->fetch();
            $countRow = $stmt->rowCount();
    
            if ($countRow > 0) {
                session_start();
                $_SESSION['qremail'] = $email;
                $_SESSION['qrpass'] = $password[0];
                $seed = $users->qrcode;
                $_SESSION['seed'] = $seed;
                unset($_SESSION['message']);
                header("location: OICScanQR.php?seed=$seed");
            } else {
                $_SESSION['message'] = "Incorrect username or password";
            }
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
    <title>Scan QR | Login</title>
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

        <div class="qr-login-label">
            <span class="material-icons-outlined">login</span>Login your account
        </div>

        <div class="qr-login-form">
            <form method="post">
                
                <?php 
                    if(isset($_SESSION['message'])) {
                        $messageError = $_SESSION['message'];
                        echo "<div class='qr-error-message'>$messageError</div>";
                    }
                ?>

                <div class="email">
                    <label for="email" class="password">Email</label>
                    <input type="email" name="email" id="email" placeholder="Enter your email" autocomplete="off" required>
                </div>
                <div class="password">
                    <label for="password" class="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                </div>
                <input type="submit" name="submit" class="submit" value="Login">
            </form>
        </div>


    </div>
</body>
</html>