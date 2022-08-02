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
    <title>Employee Schedules</title>
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
                            <a href="../SecretaryPortal/secdashboard.php">Attendance</a>
                         </li>
                        <li class="li__user active">
                            <a href="../SecretaryPortal/employeelist.php" class="active">Employees</a>
                            <ul>
                                <li><a href="../SecretaryPortal/empschedule.php" class="active">Schedule</a></li>
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

          <div class="page-info-head">
               Employee Schedule
          </div>

        <div class="user-info">
                <a href="editsec.php">[ Edit Account ]</a>
                <p><?php echo $fullname; ?></p>
            <div class="user-profile">
            </div>
        </div>

          <div class="employee_list">
              <div class="employee_list__header">
                <h1>Schedule Details</h1>
                  <form method="post">
                        <button type="submit" name="searchsched">Search</button>
                        <input type="search" name="sched" placeholder="Search Employee">
                  </form>
              </div>


              <div class="employee_list__content">
                  <table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Firstname</th>
                                <th>Lastname</th>
                                <th>Company</th>
                                <th>Schedule Time-in</th>
                                <th>Schedule Time-out</th>
                                <th>End Date</th>
                            </tr>
                        </thead>
                        <?php if(isset($_POST['searchsched'])){
                        $payroll->searchsched();
                        }else{
                        $payroll->displayschedule();
                        }
                        ?>
                        <tbody>
                            
                        </tbody>
                  </table>
              </div>
          </div>
    </div>
    
</body>
</html>