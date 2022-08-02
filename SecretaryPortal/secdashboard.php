<?php
require_once('../secclass.php');
$sessionData = $payroll->getSessionSecretaryData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
$fullname = $sessionData['fullname'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Monitoring</title>
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
                        <li class="li__records active">
                            <a href="#" class="active">Attendance</a>
                         </li>
                        <li class="li__user">
                            <a href="../SecretaryPortal/employeelist.php">Employees</a>
                            <ul>
                                <li><a href="../SecretaryPortal/empschedule.php">Schedule</a></li>
                                <li><a href="../SecretaryPortal/deductions.php">Deductions</a></li>
                                <li><a href="../SecretaryPortal/violations.php">Violations</a></li>
                            </ul>
                        </li>
    
                        <li class="li__report">
                            <a href="#">Payroll</a>
                            <ul>
                              
                                <li><a href="../SecretaryPortal/automaticpayroll.php">Salary</a></li>
                            </ul>
                        </li>

                        <li class="li__activities">
                            <a href="#" >Salary Report</a>
                            <ul>
                                <li><a href="../SecretaryPortal/releasedsalary.php" >Released Salary</a></li>
                                <li><a href="../SecretaryPortal/salaryreport.php">Salary Chart</a></li>
                                <li><a href="../SecretaryPortal/thirteen.php">13 Month Pay</a></li>
                                <li><a href="../SecretaryPortal/contributions.php" >Contributions</a></li>
                            </ul>
                         </li>
                         <li class="li__report">
                         <a href="../SecretaryPortal/activitylog.php">Activity log</a>
                         </li>
                         <li><a href="feedback.php">Submit Feedback</a></li>
                    </ul>
                </nav>
                <div class="sidebar__logout">
                    <div class="li li__logout"><a href="../seclogout.php">LOGOUT</a></div>
                </div>
          </div>

          <!--END OF SIDE NAV-->

          <div class="page-info-head">
               Attendance Monitoring
          </div>

        <div class="user-info">
                <a href="editsec.php">[ Edit Account ]</a>
                <p><?php echo $fullname; ?></p>
            <div class="user-profile">
            </div>
        </div>

          <div class="attendance_monitoring">
                    <div class="attendance_monitoring__header">
                    <form method="post">
                        <button type="submit" name="bsearch" style="color:white">Search
                        </button>
                        <input type="search" placeholder="Search" name="search">
                    </form>
                    </div>

               <div class="card">
                    <div class="card__content">
                         <table>
                              <thead>
                                   <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Time-In</th>
                                        <th>Date</th>
                                        <th>Time-Out</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                   </tr>
                              </thead>
                              <tbody>
                                <?php 
                                    if(isset($_POST['print'])){
                                    } else if(isset($_POST['bsearch'])){
                                        $payroll->search();
                                    }
                                        else {
                                        $payroll->displayAttendance(); 
                                    }
                                ?>
                              </tbody>
                         </table>
                    </div>
               </div>
          </div>
     </div>
</body>
</html>