<?php
require_once('secclass.php');
$secpayroll->login();

// if not allowed to login get the message
if(isset($_GET['message'])){
    echo $_GET['message'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form method="POST">
        <div>
            <label for="secusername">Username</label>
            <input type="email" id="secusername" name="secusername" placeholder="Enter username" required/>
        </div>
        <div>
            <label for="secpassword">Password</label>
            <input type="password" id="secpassword" name="secpassword" placeholder="Enter password" required/>
        </div>
        <button type="submit" name="seclogin">Login</button>
    </form>

</body>
</html>