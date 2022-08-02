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

// for error action
$msg2 = '';
if(isset($_GET['message2'])){
    $msg2 = $_GET['message2'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretary</title>
    <link rel="icon" href="../styles/img/icon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../styles/mincss/secretary.min.css">
</head>
<body>
    <?php $payroll->addSecretary($sessionData['id'], $sessionData['fullname']); ?>
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
            <div class="recentaccount">
                <div class="recentaccount-header">
                    <h1>Recent Account Added</h1>
                    <a href="./showAll.php">view all</a>
                </div>
                <div class="recentacount-svg">
                    <?= $payroll->show2Secretary(); ?>
                </div>
            </div>
            <div class="activities">
                <div class="activities-header">
                    <h1>Activities</h1>
                </div>
                <div class="activities-content">
                    <div class="activities-content-header">
                        <h2>Secretary Record</h2>
                    </div>
                    <div class="activities-main-contents">
                        <table>
                            <colgroup>
                                <col span="1" style="width:30%"/>
                                <col span="1" style="width:50%"/>
                                <col span="1" style="width:20%"/>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Action</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?= $payroll->secretaryLogs() ?>
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
            <div class="sidenav">
                <div class="sidenav-header">
                    <h1>Visit</h1>
                </div>
                <div class="sidenav-content">
                    <div>
                        <span></span>
                        <a href="../employee/showEmployees.php">Available Employee</a>
                    </div>
                    <div>
                        <span></span>
                        <a href="../employee/unavailable.php">Unavailable Employee</a>
                    </div>
                    <div>
                        <span></span>
                        <a href="../company/company.php">Company</a>
                    </div>
                </div>
            </div>
            <div class="add-account">
                <div class="add-account-header">
                    <h1>Add Account</h1>
                </div>
                <div class="add-account-container">
                    <div class="add-account-container-header">
                        <h2>Secretary Details</h2>
                    </div>
                    <div class="add-account-container-form">
                        <form method="post">
                            
                            <div>
                                <label for="name">Name</label>
                                <input type="text" name="fullname" id="fullname" autofocus onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' autocomplete="off" required/>
                            </div>
                            <div>
                                <label for="cpnumber">Contact Number</label>
                                <input type="text" name="cpnumber" id='cpnumber' placeholder='09' maxlength="11" onkeypress='validate(event)' autocomplete="off" required/>
                            </div>
                            <div>
                                <label for="name">Email</label>
                                <input type="email" name="email" id="email" autocomplete="off" required/>
                            </div>
                            <div>
                                <label for="name">Gender</label>
                                <select name="gender" id="gender" required>
                                    <option value="Male" selected>Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div>
                                <label for="address">Address</label>
                                <input type="text" name="address" id="address" autocomplete="off" required/>
                            </div>
                            <button type="submit" name="addsecretary" class='btn_primary'>Add</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- for success action -->
    <input type='hidden' id='msg' value='<?= $msg; ?>' /> <!-- success-->
    <input type='hidden' id='msg2' value='<?= $msg2; ?>' /> <!-- error -->

    <?php if(isset($_GET['secId'])){
        $payroll->showSpecificSec($_GET['secId']);
    } ?>

    <script>
    // disable not necessary inputs
    function validate(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
        // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\./;
        if( !regex.test(key) ) {
            theEvent.returnValue = false;
            if(theEvent.preventDefault) theEvent.preventDefault();
        }   
    }

    // success action
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

    // error action
    let msg2 = document.querySelector('#msg2');
        if(msg2.value != ''){
            let errorDiv = document.createElement('div');
            errorDiv.classList.add('error');
            let iconContainerDiv = document.createElement('div');
            iconContainerDiv.classList.add('icon-container');
            let spanIcon = document.createElement('span');
            spanIcon.classList.add('material-icons');
            spanIcon.innerText = 'done';
            let pError = document.createElement('p');
            pError.innerText = msg2.value; // set to $_GET['msg2']
            let closeContainerDiv = document.createElement('div');
            closeContainerDiv.classList.add('closeContainer');
            let spanClose = document.createElement('span');
            spanClose.classList.add('material-icons');
            spanClose.innerText = 'close';

            // destructure
            iconContainerDiv.appendChild(spanIcon);
            closeContainerDiv.appendChild(spanClose);

            errorDiv.appendChild(iconContainerDiv);
            errorDiv.appendChild(pError);
            errorDiv.appendChild(closeContainerDiv);
            document.body.appendChild(errorDiv);

            // remove after 5 mins
            setTimeout(e => errorDiv.remove(), 5000);
        }

        // check if contact number equal to 11
        let btnPrimary = document.querySelector('.btn_primary');
        let mobilePrimary = document.querySelector('#cpnumber');
        let minLength = 11;
        btnPrimary.addEventListener('click', validateMobile);

        function validateMobile(event) {
            if (mobilePrimary.value.length < minLength) {
                event.preventDefault();

                // create error message box
                let errorDiv = document.createElement('div');
                errorDiv.classList.add('error');
                let iconContainerDiv = document.createElement('div');
                iconContainerDiv.classList.add('icon-container');
                let spanIcon = document.createElement('span');
                spanIcon.classList.add('material-icons');
                spanIcon.innerText = 'done';
                let pError = document.createElement('p');
                pError.innerText = 'Contact Number must be ' + minLength + ' digits.'; 
                let closeContainerDiv = document.createElement('div');
                closeContainerDiv.classList.add('closeContainer');
                let spanClose = document.createElement('span');
                spanClose.classList.add('material-icons');
                spanClose.innerText = 'close';

                // destructure
                iconContainerDiv.appendChild(spanIcon);
                closeContainerDiv.appendChild(spanClose);

                errorDiv.appendChild(iconContainerDiv);
                errorDiv.appendChild(pError);
                errorDiv.appendChild(closeContainerDiv);
                document.body.appendChild(errorDiv);

                // remove after 5 mins
                setTimeout(e => errorDiv.remove(), 5000);
            }
        }
    </script>
</body>
</html>