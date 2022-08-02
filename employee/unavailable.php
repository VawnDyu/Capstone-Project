<?php
require_once('../class.php');
$sessionData = $payroll->getSessionData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'], 2);
$payroll->deleteUnavailableGuards($sessionData['fullname'], $sessionData['id']);
$payroll->maintenance();

if(isset($_GET['sid'])){
    $expdate = $payroll->getDuration($_GET['sid']);
    $year = $expdate[0];
    $month = $expdate[1];
    $day = $expdate[2];
}

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
    <title>Unavailable Guards</title>
    <link rel="icon" href="../styles/img/icon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../styles/mincss/unavailable.min.css">
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
                            <li><a href="./employee.php">Employee</a></li>
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
                    <li><a href="../activity/activity.php">Activities</a></li>
                </ul>
                <div>
                    <a href="../logout.php">Logout</a>
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
            <div class="table-info">
                <div class="table-header">
                    <h1>Unavailable Employees</h1>
                    <form method="GET">
                        <input type="text" id="search" name="search" placeholder="Search.." autocomplete="off"/>
                        <button type="submit" name="searchbtn"></button>
                    </form>
                </div>
                <div class="table-content">
                    <table>

                        <colgroup>
                            <col span="1" style="width:15%" />
                            <col span="1" style="width:18%" />
                            <col span="1" style="width:22%" />
                            <col span="1" style="width:7%" />
                        </colgroup>

                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Company</th>
                                <th>Address</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if(isset($_GET['search'])){
                                    $payroll->showAllUnavailableEmpActionsSearch($_GET['search']);
                                } else {
                                    $payroll->showAllUnavailableEmpActions();
                                }
                              ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- for success action -->
    <input type='hidden' id='msg' value='<?= $msg; ?>' />

    <!-- editguard modal exit btn -->
    <div class="modal-viewguard">
        <div class="modal-holder">
            <div class="viewguard-header">
                <h1>View Employee</h1>
                <span id="exit-modal-viewguard" class="material-icons">close</span>
            </div>
            <div class="viewguard-content">
                <form method='POST'>
                    <div>
                        <label for='firstname'>Firstname</label>
                        <input type='text' name='firstname' id='firstname' disabled/>
                    </div>
                    <div>
                        <label for='lastname'>Lastname</label>
                        <input type='text' name='lastname' id='lastname' disabled/>
                    </div>
                    <div>
                        <label for="company">Company</label>
                        <input type="text" name="company" id="company" disabled/>
                    </div>
                    <div>
                        <label for="comp_location">Company Location</label>
                        <input type="text" name="comp_location" id="comp_location" disabled/>
                    </div>
                    <div>
                        <h3>Duration</h3>
                        <div>
                            <label for="year">Year</label>
                            <select name="year" id="year" disabled>
                                <option value="<?= $year ?>"><?= $year; ?></option>
                            </select>
            
                            <label for="month">Month</label>
                            <select name="month" id="month" disabled>
                                <option value="<?= $month ?>"><?= $month; ?></option>
                            </select>
            
                            <label for="day">Day</label>
                            <select name="day" id="day" disabled>
                                <option value="<?= $day ?>"><?= $day; ?></option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="position">Position</label>
                        <input type="text" name="position" id='position' disabled/>
                    </div>
                    <div>
                        <label for="price">Rate per hour</label>
                        <input type="text" name="price" id='price' disabled/>
                    </div>
                    <div>
                        <label for="ot">Overtime Rate</label>
                        <input type="text" name="ot" id='ot' disabled/>
                    </div>
                    <div>
                        <label for="empAddress">Employee Address</label>
                        <input type="text" name="empAddress" id="empAddress" disabled/>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" disabled/>
                    </div>
                    <div>
                        <label for="cpnumber">Contact Number</label>
                        <input type="text" name="cpnumber" id="cpnumber" disabled/>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <div class="modal-deleteguard">
            <div class="modal-holder">
                <div class="deleteguard-header">
                    <h1>Delete Employee</h1>
                    <span id="exit-modal-deleteguard" class="material-icons">close</span>
                </div>
                <div class="deleteguard-content">
                    <h1>Are you sure you want to delete this employee?</h1>
                    <form method='post'>
                        <input type='hidden' name='id' id='rEmpId' required/>
                        <button type='submit' name='deleteUnavailable'>Delete</button>
                    </form>
                </div>
            </div>
        </div>


    <?= $payroll->viewModalShow(); ?>
    <?php
        if(isset($_GET['sidDelete'])){
            $payroll->deleteModalShow($_GET['sidDelete']);
        }
    ?>
    <script src="../scripts/unavailable.js"></script>
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