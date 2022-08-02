<?php
require_once('../class.php');
$sessionData = $payroll->getSessionData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'], 2);
$payroll->maintenance();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>
    <link rel="icon" href="../styles/img/icon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../styles/mincss/activity.min.css">
</head>
<body>
    <div class="main-container">
        <div class="leftbar">
            <div class="logo-container">
                <div class="logo"></div>
                <h1>JTDV</h1>
            </div>
            <div class="links-container">
                <ul>
                    <li><a href="../dashboard.php">Dashboard</a></li>
                    <li>Records
                        <ul>
                            <li><a href="../employee/employee.php">Employee</a></li>
                            <li><a href="../company/company.php">Company</a></li>
                            <li><a href="../secretary/secretary.php">Secretary</a></li>
                        </ul>
                    </li>
                    <li>Manage Report
                        <ul>
                            <li><a href="../leave/leave.php">Leave</a></li>
                            <li><a href="../remarks/remarks.php">Remarks</a></li>
                        </ul>
                    </li>
                    <li class="active-parent"><a href="./activity.php">Activities</a></li>
                </ul>
                <div>
                    <a href="./logout.php">Logout</a>
                </div>
            </div>
        </div>
        <div class="centerbar">
            <div class="header-info">
                <h1>Employee</h1>
                <div class="profile-container">
                    <div class="profile-setter">
                        <h3><?= $sessionData['fullname']; ?></h3>
                        <a href="../admin/profile.php">
                            <div class="image-container">
                                <?= $payroll->viewAdminImage($sessionData['id']); ?>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- tables -->
            <!-- employee logs -->
            <div class="remarks-table">
                <div class="table-header">
                    <h1>Employee Logs</h1>
                </div>
                <table>
                    <colgroup>
                        <col span="1" style="width:29%" />
                        <col span="1" style="width:12%" />
                        <col span="1" style="width:31%" />
                        <col span="1" style="width:0%" />
                        <col span="1" style="width:0%" />
                    </colgroup>

                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th>
                            <th>From</th>
                            <th>Time</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= $payroll->employeeLogs(); ?>
                    </tbody>
                </table>
            </div>

            <!-- company logs  -->
            <div class="remarks-table">
                <div class="table-header">
                    <h1>Company Logs</h1>
                </div>
                <table>
                    <colgroup>
                        <col span="1" style="width:29%" />
                        <col span="1" style="width:12%" />
                        <col span="1" style="width:31%" />
                        <col span="1" style="width:0%" />
                        <col span="1" style="width:0%" />
                    </colgroup>

                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th>
                            <th>From</th>
                            <th>Time</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= $payroll->companyLogs(); ?>
                    </tbody>
                </table>
            </div>

            <!-- secretary logs  -->
            <div class="remarks-table">
                <div class="table-header">
                    <h1>Secretary Logs</h1>
                </div>
                <table>
                    <colgroup>
                        <col span="1" style="width:29%" />
                        <col span="1" style="width:12%" />
                        <col span="1" style="width:31%" />
                        <col span="1" style="width:0%" />
                        <col span="1" style="width:0%" />
                    </colgroup>

                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th>
                            <th>From</th>
                            <th>Time</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= $payroll->secretaryLogs2(); ?>
                    </tbody>
                </table>
            </div>

            <!-- leave logs  -->
            <div class="remarks-table">
                <div class="table-header">
                    <h1>Leave Logs</h1>
                </div>
                <table>
                    <colgroup>
                        <col span="1" style="width:29%" />
                        <col span="1" style="width:12%" />
                        <col span="1" style="width:31%" />
                        <col span="1" style="width:0%" />
                        <col span="1" style="width:0%" />
                    </colgroup>

                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th>
                            <th>From</th>
                            <th>Time</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= $payroll->leaveLogs(); ?>
                    </tbody>
                </table>
            </div>

            <!-- remarks logs  -->
            <div class="remarks-table">
                <div class="table-header">
                    <h1>Remarks Logs</h1>
                </div>
                <table>
                    <colgroup>
                        <col span="1" style="width:29%" />
                        <col span="1" style="width:12%" />
                        <col span="1" style="width:31%" />
                        <col span="1" style="width:0%" />
                        <col span="1" style="width:0%" />
                    </colgroup>

                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th>
                            <th>From</th>
                            <th>Time</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= $payroll->remarksLogs(); ?>
                    </tbody>
                </table>
            </div>

            <!-- admin logs  -->
            <div class="remarks-table">
                <div class="table-header">
                    <h1>Admin Logs</h1>
                </div>
                <table>
                    <colgroup>
                        <col span="1" style="width:29%" />
                        <col span="1" style="width:12%" />
                        <col span="1" style="width:31%" />
                        <col span="1" style="width:0%" />
                        <col span="1" style="width:0%" />
                    </colgroup>

                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th>
                            <th>From</th>
                            <th>Time</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= $payroll->adminLogs(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>