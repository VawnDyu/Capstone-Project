<?php
require_once('secclass.php');
$payroll->seclogin();
// if not allowed to login get the message
if(isset($_GET['message'])){
    echo $_GET['message'];
}
$sqlm="SELECT * FROM maintenance WHERE module = 'Secretary';";
$stmtm = $payroll->con()->prepare($sqlm);
$stmtm->execute();
$countrowm = $stmtm->rowCount();
$usersm=$stmtm->fetch();
if($usersm->status == 0)
{
}else{
    header('location: secmaintenance.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="seccss/styles.css">
    <title>Login</title>
</head>
<body>
    <div class="main-container">
        <div class="company-banner">
            <div class="banner">
                <img src='img/icon.png'>
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
                <input type="email" id="username" name="username" placeholder="Enter username" autocomplete="off" required/>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required/>
            </div>
            <button type="submit" name="login">Login</button>
            <!-- <a href="seclogout.php">Destroy Session</a> -->
        </form>
           
        </div>

    </div>

</body>
</html>