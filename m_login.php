<?php 
    require_once('classemp.php');
    
    $sql = "SELECT status FROM maintenance WHERE module='Guards'";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute();
        
    $row = $stmt->fetchColumn();
        
    if ($row == 1) {
        header('location: m_maintenance.php');
    } else {
        $payroll->mobile_login();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="img/icon.png" type="image/png">
    <link rel="manifest" href="manifest.json">
    <title>JTDV | Login</title>
</head>
<body>
    <div class="login-main-container">
        <div class="login-logo">
            <img src="img/icon.png">
        </div>
        <div class="login-banner-text">
            <header>JTDV</header>
        </div>
        <div class="login-sub-banner-text">
            <header>Security Agency</header>
        </div>
        <div class="login-greeting-text">
            <header>Sign in your account</header> 
        </div>
        <div class="login-form">
            <form method="post">
                <?php $payroll->login_error_message() ?>
                <div class="email">
                    <label for="login-email">Email</label>
                    <input type="email" name="login-email" id="login-email" placeholder="Enter email" autocomplete="off" required>
                </div>
                <div class="email">
                    <label for="login-password">Password</label>
                    <input type="password" name="login-password" id="login-password" placeholder="Enter password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }
    </script>
</body>
</html>