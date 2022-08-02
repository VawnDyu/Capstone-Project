<?php 

    session_start();

    unset($_SESSION['admin_user']);
    unset($_SESSION['admin_pass']);
    unset($_SESSION['message']);

    header('location: a_login.php');

?>