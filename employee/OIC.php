<?php 
    require_once('../classemp.php');
    
    $sql = "SELECT status FROM maintenance WHERE module='Guards'";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute();
        
    $row = $stmt->fetchColumn();
        
    if ($row == 1) {
        header('location: ../m_maintenance.php');
    } else {
        $sessionData = $payroll->getSessionOICData();
        $payroll->MobileVerifyUserAccess($sessionData['access'], $sessionData['fullname'], $sessionData['position']);
        $payroll->addFeedback();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="icon" href="../img/icon.png" type="image/png">
    <title>OIC | Dashboard</title>
</head>
<body>
    <div class="main-container">
        <?php $payroll->popupMessage(); ?>
        <div class="nav-bar-left">
            <div class="logo-header">
                <img src="../img/icon.png">
                <header>JTDV</header>
            </div>
        </div>
        <div class="nav-bar-right">
            <div class="navigator">
                <ul>
                    <li><a href="OICProfile.php"><span class="material-icons-outlined">person</span></a></li>
                    <li><a href="OIC.php"><span class="material-icons-outlined">other_houses</span></a></li>
                    <li><a href="OICInbox.php" class='notification'>
                            <span class="material-icons-outlined" style="transform: translateY(6%);">mail</span>
                            <?php $payroll->notificationBadge() ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="welcome-banner">
            <header>Welcome, <?php echo $sessionData['fullname'] ?></header>
        </div>
        <div class="list">
            <ul>
                <a href="#" id='modal-button'><li class="first"><span class="material-icons-outlined">assignment_turned_in</span>Attendance</li></a>
                <a href="OICLeave.php"><li><span class="material-icons-outlined">time_to_leave</span>Apply for Leave</li></a>
                <a href="OICViolations.php"><li><span class="material-icons-outlined">report</span>Violations</li></a>
                <a href="OICAssignGuards.php"><li><span class="material-icons-outlined">person</span>Guards</li></a>
                <a href="#" id='modal-feedback'><li><span class="material-icons-outlined">feedback</span>Feedback</li></a>
                <a href="../m_logout.php"><li class="last"><span class="material-icons-outlined">logout</span>Logout</li></a>
            </ul>
        </div>
    </div>
    <div class="view-modal">
        <div class="modal-box">
            <div class="close-button">
                <a href="#" id="close-button"><span class="close">&times</span></a>
            </div>
            <div class="modal-content">
                <div class="button1">
                    <a href="OICQRLogin.php"><span class="material-icons-outlined">qr_code_2</span>Scan QR</a>
                </div>
                <div class="button2">
                    <a href="OICAttendance.php"><span class="material-icons-outlined">account_circle</span>Attendance</a>
                </div>
            </div>
        </div>
    </div>
    <div class="view-modal-feedback">
        <div class="modal-box">
            <div class="close-button">
                <a href="#" id="close-feedback"><span class="close">&times</span></a>
            </div>
            <div class="modal-content">
                <div class ='modal-header'>
                    <span class='material-icons-outlined'>feedback</span><header>Feedback</header>
                </div>

                <div class='header-message'>
                    <header>Do you have any questions? Tell us</header>
                </div>

                <form method="post">
                        <select name="category" id="category" required>
                            <option value="" selected disabled>Select category</option>
                            <option value="Suggestion">Suggestion</option>
                            <option value="Report Bug">Report Bug</option>
                        </select>
                    <textarea name="comment" id="comment" maxlength='255' placeholder="Please tell us your comment" required></textarea>
                    <input type="submit" name='feedback'>
                </form>
            </div>
        </div>
    </div>

    <div class="view-modal-error">  
        <div class="modal-error">
            <header class='error-header'>Error</header>
            <?php $payroll->getErrorModalMsg() ?>
            <button type="button" id="btnOkay">Okay</button>
        </div>
    </div>

    <div class="view-modal-success">  
        <div class="modal-success">
            <header class='success-header'>Success</header>
            <?php $payroll->getSuccessModalMsg() ?>
            <button type="button" id="btnOkaySuccess">Okay</button>
        </div>
    </div>

    <?php $payroll->showMsgModal() ?>

    <script>
        //For Modal Button
        let a = document.getElementById('modal-button');
        let z = document.getElementsByClassName('view-modal');
        let y = document.getElementById('close-button');
        a.addEventListener("click", function(){
            for (var i=0;i<z.length;i+=1) {
                z[i].style.display = 'block';
            }
        });
        y.addEventListener("click", function() {
            for (var i=0;i<z.length;i+=1) {
                z[i].style.display = 'none';
            }
        });

        //For Modal Feedback

        let aF = document.getElementById('modal-feedback');
        let xF = document.getElementsByClassName('view-modal-feedback');
        let yF = document.getElementById('close-feedback');
        aF.addEventListener("click", function(){
            for (var i=0;i<xF.length;i+=1) {
                xF[i].style.display = 'block';
            }
        });
        yF.addEventListener("click", function() {
            for (var i=0;i<xF.length;i+=1) {
                xF[i].style.display = 'none';
            }
        });


        var popup = document.getElementById("popup-message");

        if (popup) {
            var popupclose = document.getElementById("popup-message-close");
            var span = document.getElementsByClassName("close")[0];

            span.onclick = function() {
                popup.style.display = "none";
                popupclose.style.display = "none";
            }
        }

        // Very simple JS for updating the text when a radio button is clicked
        const INPUTS = document.querySelectorAll('form input[name=smiley]');
        const updateValue = e => document.querySelector('#result').innerHTML = e.target.value;

        INPUTS.forEach(el => el.addEventListener('click', e => updateValue(e)));

        //When press the okay when modal appeared
        var click = document.getElementById("btnOkay");
        var clicksuccess = document.getElementById("btnOkaySuccess");
        var errormodal = document.getElementsByClassName('view-modal-error');
        var successmodal = document.getElementsByClassName('view-modal-success');

        click.addEventListener("click", function() {
            for (var i=0;i<errormodal.length;i+=1) {
                errormodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OIC.php");
            }
        });

        clicksuccess.addEventListener("click", function() {
            for (var i=0;i<successmodal.length;i+=1) {
                successmodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OIC.php");
            }
        });
    </script>
</body>
</html>