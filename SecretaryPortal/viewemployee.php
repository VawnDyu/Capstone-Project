<?php
require_once('../secclass.php');
$sessionData = $payroll->getSessionSecretaryData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
$fullname = $sessionData['fullname'];
$empid=$_GET['empId'];
$sql="SELECT * FROM employee INNER JOIN schedule ON employee.empId = schedule.empId WHERE employee.empId = ?";
$stmt = $payroll->con()->prepare($sql);
$stmt->execute([$empid]);
$users = $stmt->fetch();
$countRow = $stmt->rowCount();
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

</body>
<div class="main-container">
        <!--SIDENAV START-->
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
                                <li><a href="../SecretaryPortal/empschedule.php">Schedule</a></li>
                                <li><a href="../SecretaryPortal/deductions.php">Deductions</a></li>
                                <li><a href="../SecretaryPortal/violations.php" >Violations</a></li>
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
                                <li><a href="../SecretaryPortal/contributions.php" class="active">Contributions</a></li>
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
        <div class="user-info">
                <a href="editsec.php">[ Edit Account ]</a>
                <p><?php echo $fullname; ?></p>
            <div class="user-profile">
            </div>
        </div>
        <div class="employee_list">
              <div class="employee_list__header">
                <h1>Employee Details</h1>
              </div>
            <div class="employee_list__content">
                    <table>
                    <tr>
                    <tr><td><h3>&emsp;Name:</td><td><?php echo $users->firstname ." ". $users->lastname ?></td></tr>
                    <tr><td><h3>&emsp;Email:</td><td><?php echo $users->email ?></td></tr>
                    <tr><td><h3>&emsp;Contact:</td><td><?php echo $users->cpnumber ?></td></tr>
                    <tr><td><h3>&emsp;Address:</td><td><?php echo $users->address ?></td></tr>
                    <tr><td><h3>&emsp;Position:</td><td><?php echo $users->position ?></td></tr>
                    <tr><td><h3>&emsp;Schedule:</td><td><?php echo "FROM ".$users->scheduleTimeIn . " TO ".$users->scheduleTimeOut." - Starting " .$users->date_assigned ?></td></tr>
                    <tr><td><h3>&emsp;Company:</td><td><?php echo $users->company ?></td></tr>
                    <tr><td><h3>&emsp;Attendance:</td><td><?php echo $users->firstname . $users->lastname ?></td></tr>
                    </tr>
                    </table>
                    <div class="emppicture">
            
                </div>
            </div>
        </div>
</div>
</html>
