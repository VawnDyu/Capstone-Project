<?php
require_once('../class.php');
$sessionData = $payroll->getSessionData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'], 2);
$payroll->maintenance();

// for success action
$msg = '';
if(isset($_GET['message'])){
    $msg = $_GET['message'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Secretary</title>
    <link rel="icon" href="../styles/img/icon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../styles/mincss/showAll.min.css">
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
                    <li class="active-parent">Records
                        <ul>
                            <li><a href="../employee/employee.php">Employee</a></li>
                            <li><a href="../company/company.php">Company</a></li>
                            <li><a href="./secretary.php">Secretary</a></li>
                        </ul>
                    </li>
                    <li>Manage Report
                        <ul>
                            <li><a href="../leave/leave.php">Leave</a></li>
                            <li><a href="../remarks/remarks.php">Remarks</a></li>
                        </ul>
                    </li>
                    <li><a href="../activity/activity.php">Activities</a></li>
                </ul>
                <div>
                    <a href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
        <div class="centerbar">
            <div class="header-info">
                <h1>Secretary</h1>
            </div>
            <div class="table-container">
                <div class="table-header">
                    <h1>Table</h1>
                </div>
                <div class="table-content">
                    <div class="table-content-header">
                        <h1>List of Secretary</h1>
                        <form method="GET">
                            <input type="text" id="search" name="search" placeholder="Search" autocomplete="off"/>
                            <button type="submit" name="searchSec"></button>
                        </form>
                    </div>
                    <div class="table-content-form">
                        <table>
                            <colgroup>
                                <col span="1" style="width:20%" />
                                <col span="1" style="width:10%" />
                                <col span="1" style="width:55%" />
                                <col span="1" style="width:20%" />
                            </colgroup>

                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Email</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    if(isset($_GET['search'])){
                                        $payroll->showAllSecretarySearch($_GET['search']);
                                    } else {
                                        $payroll->showAllSecretary();
                                    }
                                 ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="rightbar">
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
            <div class="relative-links">
                <div class="relative-links-header">
                    <h1>Relative Links</h1>
                </div>
                <div class="top">
                    <div>
                        <a href="../employee/unavailable.php"><span></span></a>
                    </div>
                    <div>
                        <a href="../employee/unavailable.php">Unavailable Employee</a>
                    </div>
                </div>
                <div class="bottom">
                    <div>
                        <p>Available Employee</p>
                        <div>
                            <a href="../employee/showEmployees.php"><span></span></a>
                        </div>
                    </div>
                    <div>
                        <p>Company</p>
                        <div>
                            <a href="../company/company.php"><span></span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- for success action -->
    <input type='hidden' id='msg' value='<?= $msg; ?>' />

    <?php if(isset($_GET['secId']) && !isset($_GET['email'])){
        $payroll->showSpecificSec($_GET['secId']);
    } ?>


    <?php if(isset($_GET['secId']) && isset($_GET['email'])){
        $payroll->editModalShow($_GET['secId']);
        $payroll->editSecretary($_GET['secId'], $_GET['email'], $sessionData['fullname'], $sessionData['id']);
    } ?>

    <?php if(isset($_GET['secIdDelete'])){
        $payroll->deleteModalShowIt($_GET['secIdDelete']);
        $payroll->deleteSecretary($sessionData['fullname'], $sessionData['id']);
    } ?>

    <script>
        let msg = document.querySelector('#msg');
        if(msg.value != ''){
            let successDiv = document.createElement('div');
            successDiv.classList.add('success');
            let iconContainerDiv = document.createElement('div');
            iconContainerDiv.classList.add('icon-container');
            let spanIcon = document.createElement('span');
            spanIcon.classList.add('material-icons');
            spanIcon.innerText = 'done';
            let pSuccess = document.createElement('p');
            pSuccess.innerText = msg.value; // set to $_GET['msg']
            let closeContainerDiv = document.createElement('div');
            closeContainerDiv.classList.add('closeContainer');
            let spanClose = document.createElement('span');
            spanClose.classList.add('material-icons');
            spanClose.innerText = 'close';

            // destructure
            iconContainerDiv.appendChild(spanIcon);
            closeContainerDiv.appendChild(spanClose);

            successDiv.appendChild(iconContainerDiv);
            successDiv.appendChild(pSuccess);
            successDiv.appendChild(closeContainerDiv);
            document.body.appendChild(successDiv);

            // remove after 5 mins
            setTimeout(e => successDiv.remove(), 5000);
        }
    </script>
</body>
</html>