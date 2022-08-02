<?php
    require_once('../secclass.php');
    $sessionData = $payroll->getSessionSecretaryData();
    $payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
    $fullname = $sessionData['fullname'];
    $access = $sessionData['access'];
    $id = $sessionData['id'];
?>
<!DOCTYPE html>
<html lang="en">
     <head>
          <meta charset="UTF-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>User Profile</title>
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
                                        <li><a href="../SecretaryPortal/editsec.php" class="active">View Profile</a></li>
                                        <li><a href="../SecretaryPortal/editsecpass.php" class="">Edit Password</a></li>
                                   </ul>
                              </li>
                         </ul>
                    </nav>
               <div class="sidebar__logout">
                   <div class="li li__logout"><a href="#">LOGOUT</a></div>
               </div>
          </div>

          <div class="page-info-head">
               Profile
          </div>

          <div class="user_edit_profile_header">
               <h1>User Profile Details</h1>
          </div>

          <div class="edit-profile-card">
               <div class="changeprofile">
                    <object data="../SVG/userprofilepic.svg" type=""></object>
                    <input type="file">Change Profile Photo</input>
               </div>
               <?php
               $sql="SELECT * FROM secretary WHERE id = ?";
               $stmt = $payroll->con()->prepare($sql);
               $stmt->execute([$id]);
               $user = $stmt->fetch();
               ?>
               <div class="edit-profile-card__form">
                    <label for="" >Name: </label>
                    <input type="text" placeholder= "<?php echo $user->fullname;?>" disabled>

                    <label for="">Contact Number : </label>
                    <input type="number" name="contact" placeholder= "<?php echo $user->cpnumber;?>"disabled>

                    <label for="" >Address: </label>
                    <input type="text" name="address" placeholder= "<?php echo $user->address;?>"disabled>

                    <label for="email" >Email: </label>
                    <input type="text" id ="email" name="email" placeholder= "<?php echo $user->email;?>"disabled>

                    <button class="btn_primary">
                         <span class="material-icons"> description</span>
                         Save Changes
                    </button>
               </div>
              <div class="edit-profile-card__svg">
                   <object data="../SVG/edit_profile_svg.svg" type=""></object>
              </div>
          </div>
     </div>
</body>
</html>