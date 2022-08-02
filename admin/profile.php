<?php
require_once('../class.php');
$sessionData = $payroll->getSessionData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'], 2);
$payroll->editAdminProfile($sessionData['id'], $sessionData['fullname'], $sessionData['id']);
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
    <title>Admin Profile</title>
    <link rel="icon" href="../styles/img/icon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../styles/mincss/adminprofile.min.css">
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
                    <li><a href="../activity/activity.php">Activities</a></li>
                </ul>
                <div>
                    <a href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
        <div class="centerbar">
            <div class="header-info">
                <h1>Admin</h1>
            </div>
            <div class="sidenav-profile">
                <div class="sidenav-profile-header">
                    <h1>Profile</h1>
                    <p>Head Manager Information</p>
                </div>
                <div class="sidenav-profile-content">
                    <div class="sidenav-profile-left">
                        <div>
                            <a href="./profile.php">Admin Profile</a>
                        </div>
                        <div>
                            <a href="./feedback.php">Feedback</a>
                        </div>
                        <div>
                            <a href="./passInfo.php">Change Password</a>
                        </div>
                    </div>
                    <div class="sidenav-profile-right">
                        <div class="info-container">
                            <div class="user-photo-container">
                                <div>
                                    <?= $payroll->viewAdminImage($sessionData['id']);?>
                                    <div class="user-name">
                                        <h1></h1>
                                        <p></p>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" id="toggleForm">Edit Info</button>
                                </div>
                            </div>
                            <div class="about-me-container">
                                <h1>Address</h1>
                                <p></p>
                            </div>
                            <div class="mob-email-container">
                                <div>
                                    <h1>Mobile</h1>
                                    <p></p>
                                </div>
                                <div>
                                    <h1>Email</h1>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                        <div class="links-container">
                            <h3>List</h3>
                            <div class="socialmedia-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="rightbar">
            <div class="profile-container">
                <div class="profile-setter">
                    <h3><?= $sessionData['fullname']; ?></h3>
                    <a href="./profile.php">
                        <div class="image-container">
                            <?= $payroll->viewAdminImage($sessionData['id']); ?>
                        </div>
                    </a>
                </div>
            </div>
            <div class="editing-content" id="iAmForm">
                <div class="editing-content-header">
                    <h1>Edit Information</h1>
                </div>
                <div class="editing-content-content">
                    <form method="POST" enctype="multipart/form-data">
                        <div>
                            <div class="img-container">
                                <input type="file" name="image" id="image" onchange="loadFile(event)" style="display: none;" />
                                <label for="image" style="cursor: pointer;" title="Change profile picture"></label>
                                <div>
                                    <?= $payroll->viewAdminImage2($sessionData['id']); ?>
                                </div>
                            </div>
                            <div class="img-name-container">
                                <div>
                                    <label for="firstname">Name</label>
                                </div>
                                <div>
                                    <input type="text" name='firstname' id='firstname' autocomplete="off" onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' placeholder="Firstname" required/>
                                    <input type="text" name='lastname' id='lastname' autocomplete="off" onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' placeholder="Lastname" required/>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="address">Address</label>
                            <textarea name="address" id="address" autocomplete="off" required></textarea>
                        </div>

                        <div>
                            <label for="cpnumber">Mobile</label>
                            <input type="text" name='cpnumber' id='cpnumber' autocomplete="off" maxlength='11' placeholder='09' onkeypress='validate(event)' required/>
                        </div>

                        <div>
                            <label for="email">Email</label>
                            <input type="email" name='email' id='email' autocomplete="off" required/>
                        </div>

                        <!-- social media icons -->
                        <div>
                            <label for="facebook">Facebook</label>
                            <input type="text" name="facebook" id="facebook" placeholder='Paste the link' autocomplete="off"/>
                        </div>
                        <div>
                            <label for="google">Google</label>
                            <input type="text" name="google" id="google" placeholder='Paste the link' autocomplete="off"/>
                        </div>
                        <div>
                            <label for="twitter">Twitter</label>
                            <input type="text" name="twitter" id="twitter" placeholder='Paste the link' autocomplete="off"/>
                        </div>
                        <div>
                            <label for="instagram">Instagram</label>
                            <input type="text" name="instagram" id="instagram" placeholder='Paste the link' autocomplete="off"/>
                        </div>

                        <button type='submit' name='saveChanges' class='btn_primary'>Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- for success action -->
    <input type='hidden' id='msg' value='<?= $msg; ?>' />

<?= $payroll->adminProfile($sessionData['id']); ?>
<script>
    let loadFile = function(event) {
        let image = document.getElementById('output');
        image.src = URL.createObjectURL(event.target.files[0]);
    };

    let toggleForm = document.querySelector('#toggleForm');
    toggleForm.onclick = () => {
        let myForm = document.querySelector('#iAmForm');
        myForm.classList.toggle('form-active');
        toggleForm.classList.toggle('btn-active');
            
        if(toggleForm.innerText == 'Edit Info'){
            toggleForm.innerText = 'Cancel';
        } else {
            toggleForm.innerText = 'Edit Info';
        }

    }

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