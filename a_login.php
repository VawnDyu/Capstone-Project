<?php 
    require_once('class.php');

    session_start();

    if (isset($_POST['login'])) {
        
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM system_admin WHERE username = ? AND password = ?";
        $stmt = $payroll->con()->prepare($sql);
        $stmt->execute([$username, $password]);

        $count = $stmt->rowCount();

        if ($count > 0) {
            $_SESSION['admin_user'] = $username;
            $_SESSION['admin_pass'] = $password;
            unset($_SESSION['message']);
            header('location: system_admin/createuser.php');
        } else {
            $_SESSION['message'] = "Incorrect username of password";
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
    <link rel="stylesheet" href="system_admin/css/styles.css">
    <link rel="icon" href="system_admin/img/icon.png" type="image/png">
    <title>Login</title>
</head>
<body>
<div class="main-container">
        <div class="company-banner">
            <div class="banner">
                <img src='system_admin/img/icon.png'>
                <header class='company'>JTDV</header>
                <header class='info'>Security Agency</header>
            </div>
        </div>
        <div class="login-form">
        <form method="POST">
            <div class="header">
                <header class="main">Welcome back</header>
                <header class="sub">Sign in your account</header>
            </div>
                <?php 
                    if(isset($_SESSION['message'])) {
                        $messageError = $_SESSION['message'];
                        echo "<div class='message'>$messageError</div>";
                    }
                ?>
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" autocomplete="off" required/>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required/>
            </div>
            <button type="submit" name="login">Login</button>
        </form>        
        </div>
    </div>
</body>
</html>