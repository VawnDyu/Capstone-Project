<?php
require_once('../class.php');
$sessionData = $payroll->getSessionData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'], 2);
$payroll->sendFeedback($sessionData['fullname']);
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
    <title>Send Feedback</title>
    <link rel="icon" href="../styles/img/icon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../styles/mincss/feedback.min.css">
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
                                    <button type="button" id="toggleForm">Send Feedback</button>
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
                    <h1>Send Feedback</h1>
                </div>
                <div class="editing-content-content">
                    <form method="POST">

                        <div>
                            <label for="category">Category</label>
                            <select name="category" id="category" required>
                                <option value="">Select Category</option>
                                <option value="Suggestion">Suggestion</option>
                                <option value="Report Bug">Report Bug</option>
                            </select>
                        </div>
                        <div>
                            <label for="comment">Comment</label>
                            <textarea name="comment" id="comment" cols="30" rows="10" required></textarea>
                        </div>

                        <button type='submit' name='sendFeedbackbtn'>Send Feedback</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- for success action -->
    <input type='hidden' id='msg' value='<?= $msg; ?>' />

    <?= $payroll->adminFeedback($sessionData['id']); ?>
    <script>
        let loadFile = function(event) {
            let image = document.getElementById('output');
            image.src = URL.createObjectURL(event.target.files[0]);
        };

        // to open modal
        let toggleForm = document.querySelector('#toggleForm');
        toggleForm.onclick = () => {
            let myForm = document.querySelector('#iAmForm');
            myForm.classList.toggle('form-active');
            toggleForm.classList.toggle('btn-active');
            
            if(toggleForm.innerText == 'Send Feedback'){
                toggleForm.innerText = 'Cancel';
            } else {
                toggleForm.innerText = 'Send Feedback';
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
    </script>
</body>
</html>