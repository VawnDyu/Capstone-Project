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
    <title>Employee Deductions</title>
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
                            <a href="../SecretaryPortal/employeelist.php" class = "active">Employees</a>
                            <ul>
                                <li><a href="../SecretaryPortal/empschedule.php">Schedule</a></li>
                                <li><a href="../SecretaryPortal/deductions.php" class="active">Deductions</a></li>
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
                            <a href="#">Salary Report</a>
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
               Deductions
          </div>

          <div class="user-info">
               <a href="editsec.php">[ Edit Account ]</a>
               <p><?php echo $fullname; ?></p>
          <div class="user-profile">
          </div>
          </div>

          <div class="generate-deduction-table">
               <div class="generate-deduction-table__header">
                    <h1>Generate Deduction</h1>
               </div>

               <div class="generate-deduction-table__content">
                    <table>
                         <thead>
                              <tr>
                                   <th>Name</th>
                                   <th>Deduction</th>
                                   <th></th>
                                   <th>
                                        <form method="post">
                                             <button type="submit" name="adddeduction">
                                                  <a href="generate-deduction-modal.php">Add
                                                  </a>
                                             </button>
                                        </form>
                                   </th>
                              </tr>
                         </thead>

                         <tbody>
                               <?php $payroll->displaydeduction(); ?>
                         </tbody>
                    </table>
               </div>
          </div>


          <div class="generate-cashadvance-table">
               <div class="generate-cashadvance-table__header">
                    <h1>Generate Cash Advance</h1>
               </div>

               <div class="generate-cashadvance-table__content">
                    <table>
                         <thead>
                              <tr>
                                   <th>Employee ID</th>
                                   <th>Date</th>
                                   <th>Amount</th>
                                   <th>
                                        <form method="post">
                                             <button type="submit" name="addcashadvance">
                                                  <a href="generate-cashadvance-modal.php">Add
                                                  </a>
                                             </button>
                                        </form>
                                   </th>
                              </tr>
                         </thead>

                         <tbody>
                         <?php $payroll->displaycashadvance(); ?>
                         </tbody>

                    </table>
               </div>
          </div>
     </div>
</body>
</html>



