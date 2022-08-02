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
    <title>Remarks</title>
    <link rel="icon" href="../styles/img/icon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../styles/mincss/remarks.min.css">
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
                    <li class="active-parent">Manage Report
                        <ul>
                            <li><a href="../leave/leave.php">Leave</a></li>
                            <li><a href="./remarks.php">Remarks</a></li>
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
                <h1>Remarks</h1>
            </div>
            <div class="welcome-info">
                <div class="welcome-svg">
                    <object data="../styles/SVG_modified/remarks.svg" type="image/svg+xml"></object>
                </div>
                <div class="welcome-box">
                    <div class="welcome-box-content">
                        <h2>To punish is to inflict penalty for violating rules or intentional wrongdoing.</h2>
                        <p>They must have followed the regulations.</p>
                        <button type='button'><a href='remarks.php?pv=true'>View Violations</a></button>
                    </div>
                </div>
            </div>
            <div class="remarked-violations">
                <div class="remarked-violations-header">
                    <h1>Violations</h1>
                </div>
                <div class="remarked-violations-content">
                    <div class="violations-content-header">
                        <h1>Remarked Violations</h1>
                        <form method="GET">
                            <input type="search" name="search" placeholder="Search" autocomplete="off" id="search">
                            <button type="submit" name="searchBtn"></button>
                        </form>
                    </div>
                    <div class="violations-content-content">
                        <table>
                            <colgroup>
                                <col span="1" style="width:12%"/>
                                <col span="1" style="width:25%"/>
                                <col span="1" style="width:31%"/>
                                <col span="1" style="width:28%"/>
                                <col span="1" style="width:19%"/>
                            </colgroup>

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    if(isset($_GET['search'])){
                                        $payroll->viewListRemarkedViolationSearch($_GET['search']);
                                    } else {
                                        $payroll->viewListRemarkedViolation();
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
            <div class="most-violation">
                <?= $payroll->mostViolation(); ?>
            </div>
        </div>
    </div>

    <!-- for success action -->
    <input type='hidden' id='msg' value='<?= $msg; ?>' />

    <!-- pending violation -->
    <?php if(isset($_GET['pv']) && $_GET['pv'] == true){ ?>
        <div class="pending-violations">
            <div class="modal-holder">
                <div class="pending-violations-header">
                    <h1>Pending Violation</h1>
                    <span class='material-icons' id='exit-modal-pendingviolations'>close</span>
                </div>
                <div class="pending-violations-content">
                    <table>
                        <colgroup>
                            <col span='1' style="width: 11%">
                            <col span='1' style="width: 11%">
                            <col span='1' style="width: 44%">
                            <col span='1' style="width: 12%">
                            <col span='1' style="width: 5%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fine</th>
                                <th>Violation</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $payroll->viewListViolation() ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>
            let exitModalPendingViolations = document.querySelector("#exit-modal-pendingviolations");
            exitModalPendingViolations.addEventListener('click', e => {
                let pendingViolationsModal = document.querySelector('.pending-violations');
                pendingViolationsModal.style.display = "none";
            });
        </script>
    <?php } ?>

    <!-- when remarks clicked -->
    <?php if(isset($_GET['rid'])){
        $payroll->addModalRemarks($sessionData['fullname'], $sessionData['id']); 
    } ?>

    <!-- list of finished violations -->
    <?php if(isset($_GET['lrid'])){
        $payroll->viewModalListRemarkedViolation();
    } ?>

    <!-- modal for most violation -->
    <?php if(isset($_GET['mvId'])){ ?>
        <div class="most-violations">
            <div class="modal-holder">
                <div class="most-violations-header">
                    <h1>All Violations</h1>
                    <span class='material-icons' id='exit-modal-mostviolations'>close</span>
                </div>
                <div class="most-violations-content">
                    <table>
                        <colgroup>
                            <col span='1' style="width: 11%">
                            <col span='1' style="width: 13%">
                            <col span='1' style="width: 46%">
                            <col span='1' style="width: 12%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Violation</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $payroll->viewMostViolation($_GET['mvId']) ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>
            let exitModalMostViolations = document.querySelector("#exit-modal-mostviolations");
            exitModalMostViolations.addEventListener('click', e => {
                let mostViolationsModal = document.querySelector('.most-violations');
                mostViolationsModal.style.display = "none";
            });
        </script>
    <?php } ?>

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