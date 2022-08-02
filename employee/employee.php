<?php
require_once('../class.php');
$sessionData = $payroll->getSessionData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'], 2);
$payroll->deleteRecentGuard($sessionData['fullname'], $sessionData['id']);
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
    <title>Employee</title>
    <link rel="icon" href="../styles/img/icon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../styles/mincss/employee.min.css">
</head>
<body>
    <?php $payroll->addEmployee($sessionData['fullname'], $sessionData['id']); ?>
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
            </div>
            <div class="availability-container">
                <div class="available">
                    <object data="../styles/SVG_modified/available.svg" type="image/svg+xml"></object>
                    <div class="svg-info">
                        <h1>Available Employees</h1>
                        <button><a href="./showEmployees.php">View All</a></button>
                    </div>
                </div>
                <div class="unavailable">
                    <object data="../styles/SVG_modified/unavailable.svg" type="image/svg+xml"></object>
                    <div class="svg-info">
                        <h1>Unavailable Employees</h1>
                        <button><a href="./unavailable.php">View All</a></button>
                    </div>
                </div>
                <div class="next">
                    <object data="../styles/SVG_modified/next.svg" type="image/svg+xml"></object>
                    <div class="svg-info">
                        <h1><a href="../company/company.php">Next</a></h1>
                    </div>
                </div>
            </div>
            <div class="employee-record">
                <div class="employee-header">
                    <h2>Employee Records</h2>
                    <form method="GET" action='employee.php'>
                        <div>
                            <input type="text" name="search" id="search" placeholder="Search" autocomplete="off"/>
                        </div>
                        <button type="submit" name="searchEmp"></button>
                    </form>
                </div>
                <div class="employee-content">
                    <table cellspacing="0" cellpadding="0">
                        <colgroup>
                            <col span="1" style="width:30%"/>
                            <col span="1" style="width:18%"/>
                            <col span="1" style="width:15%"/>
                            <col span="1" style="width:10%"/>
                            <col span="1" style="width:10%"/>
                        </colgroup>

                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contacts</th>
                                <th>Availability</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if(isset($_GET['search'])){
                                    $payroll->showAllEmpSearch($_GET['search']);
                                } else {
                                    $payroll->showAllEmp();
                                }
                            ?>
                        </tbody>
                    </table>
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
            <div class="assignedguards-container">
                <div class="assignedguards-header">
                    <h1>Recent Assigned Employees</h1>
                </div>
                <div class="assignedguards-content">
                    <?= $payroll->recentAssignedGuards(); ?>
                </div>
            </div>
            <div class="addemployee-container">
                <div class="addemployee-header">
                    <h1>Add Employee</h1>
                    <a href="./employee.php?addEmployee=true">modal</a>
                </div>
                <div class="addemployee-content">
                    <form method="POST">
                        <div class="form-holder">
                            <div>
                                <label for="firstname">Firstname</label>
                                <input type="text" name="firstname" id="firstname" onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' autofocus autocomplete="off" required/>
                            </div>
                            <div>
                                <label for="lastname">Lastname</label>
                                <input type="text" name="lastname" id="lastname" onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' autocomplete="off" required/>
                            </div>
                            <div>
                                <label for="address">Address</label>
                                <input type="text" name="address" id="address" placeholder="Please include the city" autocomplete="off" required/>
                            </div>
                            <div>
                                <label for="email">Email</label>
                                <input type="text" name="email" id="email" autocomplete="off" required/>
                            </div>
                            <div>
                                <label for="number">Contact Number</label>
                                <input type="text" name="number" id="number" placeholder='09' maxlength="11" onkeypress='validate(event)' autocomplete="off" required/>
                            </div>
                            <div>
                                <label for="qrcode">Qr Code</label>
                                <input type="text" name="qrcode" id="qrcode" autocomplete="off" readonly required/>
                                <div onclick="generatePassword(this)">Generate</div>
                            </div>
                        </div>
                        <button type="submit" name="addemployee" class="btn_primary">
                            <span class="material-icons">security</span>Add
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- for success action -->
    <input type='hidden' id='msg' value='<?= $msg; ?>' /> <!-- success -->
    <input type='hidden' id='msg2' value='<?= $msg2; ?>' /> <!-- error -->

    <?php if(isset($_GET['addEmployee']) && $_GET['addEmployee'] == true){ ?>
        <div class="modal-addguard">
            <div class="modal-holder">
                <div class="addguard-header">
                    <h1>Add Employee</h1>
                    <span id="exit-modal-addguard" class="material-icons">close</span>
                </div>
                <div class="addguard-content">
                    <form method='POST'>
                        <div>
                            <label for='firstname2'>Firstname</label>
                            <input type='text' name='firstname' id='firstname2' onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' autocomplete="off" required/>
                        </div>
                        <div>
                            <label for='lastname2'>Lastname</label>
                            <input type='text' name='lastname' id='lastname2' onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' autocomplete="off" required/>
                        </div>
                        <div>
                            <label for='address2'>Address</label>
                            <input type='text' name='address' id='address2' placeholder='Please include the city' autocomplete="off" required/>
                        </div>
                        <div>
                            <label for='email2'>Email</label>
                            <input type='email' name='email' id='email2' autocomplete="off" required/>
                        </div>
                        <div>
                            <label for='cpnumber2'>Contact Number</label>
                            <input type='text' name='number' id='cpnumber2' placeholder='09' maxlength="11" onkeypress='validate(event)' autocomplete="off" required/>
                        </div>
                        <div>
                            <label for='qrcode2'>QR Code</label>
                            <input type='text' name='qrcode' id='qrcode2' autocomplete="off" readonly required/>
                            <div onclick="generatePassword(this)">Generate</div>
                        </div>
                        <div>
                            <button type='submit' name='addemployeemodal' class='btn_primary2'>Add Employee</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            // addguard modal exit btn
            let exitModalAddGuard = document.querySelector("#exit-modal-addguard")
            exitModalAddGuard.addEventListener('click', e => {
                let addguardModal = document.querySelector('.modal-addguard');
                addguardModal.style.display = "none";
            });

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
        </script>
    <?php } ?>

    <!-- delete modal -->
    <?php if(isset($_GET['idDelete'])){ ?>
        <div class="modal-deleteguard">
            <?php $payroll->deleteRecentGuardModal($_GET['idDelete']);  ?>
        </div>
        <script>
            // deleteguard modal exit btn
            let exitModalDeleteGuard = document.querySelector("#exit-modal-deleteguard")
            exitModalDeleteGuard.addEventListener('click', e => {
                let deleteguardModal = document.querySelector('.modal-deleteguard');
                deleteguardModal.style.display = "none";
            });
        </script>
    <?php } ?>


    <script src="../scripts/employee.js"></script>
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

        // error
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
        let mobilePrimary = document.querySelector('#number');
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



        // check if contact number equal to 11 in MODAL
        let btnPrimary2 = document.querySelector('.btn_primary2');
        let mobilePrimary2 = document.querySelector('#cpnumber2');
        let minLength2 = 11;
        btnPrimary2.addEventListener('click', validateMobileModal);

        function validateMobileModal(event) {
            if (mobilePrimary2.value.length < minLength2) {
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
                pError.innerText = 'Contact Number must be ' + minLength2 + ' digits.'; 
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