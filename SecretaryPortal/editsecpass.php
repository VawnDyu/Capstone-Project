<?php
    require_once('../secclass.php');
    $sessionData = $payroll->getSessionSecretaryData();
    $payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
    $fullname = $sessionData['fullname'];
    $access = $sessionData['access'];
    $id = $sessionData['id'];
?>


<html lang="en">
     <head>
          <meta charset="UTF-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Change Password</title>
          <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
          <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
          <link rel="stylesheet" type="text/css" href="../seccss/main.css">
      </head>
<body>
     <div class="main-container">

          <div class="sidebar">
               <div class="sidebar__logo">
                   <div class="logo"></div>
                   <h3>JDTV</h3>
               </div>
                    <nav>
                         <ul>
                              <li class="li__records">
                                   <a href="../SecretaryPortal/secdashboard.php" class="">Dashboard</a>
                              </li>
                              <li class="li__user">
                                   <a href="" class="active">User Profile</a>
                                   <ul>
                                        <li><a href="../SecretaryPortal/editsec.php" class="">View Profile</a></li>
                                        <li><a href="../SecretaryPortal/editsecpass.php" class="active">Edit Password</a></li>
                                   </ul>
                              </li>
                         </ul>
                    </nav>
               <div class="sidebar__logout">
                   <div class="li li__logout"><a href="#">LOGOUT</a></div>
               </div>
          </div>

          <div class="page-info-head">
               User Password
          </div>

          <div class="user-info">
                <p><?php echo $fullname; ?></p>
               <div class="user-profile">
               </div>
          </div>

          <div class="user_edit_profile_header">
               <h1>Change Password</h1>
          </div>
          <div class="changepassword-card">
          <form method="post">
               <div class="changepassword-card__form">
               
                    <label for="pass" name="">Old Password : </label>
                    <input type="password" id="pass" name="oldpass">

                    <label for="npass">New Password : </label>
                    <input type="password" id="npass" name="newpass" pattern=".{8,}" title="Eight or more characters">

                    <label for="cpass" >Confirm Password : </label>
                    <input type="password" id="cpass" name="confirmpass"pattern=".{8,}" title="Eight or more characters">

                    <?php
                    if(isset($_POST['changepass'])){
                         $payroll->changepass($id,$fullname);
                    }else{
                    echo "<p>Password must contains atleast 6 to 8 characters.</p>";
                    }
                    ?>
                    <button class="btn_primary" name="changepass">
                         <span class="material-icons"> description</span>
                         Save Password
                    </button>
                    </form>
               </div>
              <div class="changepassword-card__svg">
                   <object data="../SVG/changepass-svg.svg" type=""></object>
              </div>
              
          </div>
     </div>
</body>
</html>