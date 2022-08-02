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
    
        $payroll->MarkAsAbsent();
        $payroll->MarkAsVoid();
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
    <title>OIC | Monitor Guards</title>
</head>
<body>
    <div class="main-container">
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
        <div class="guards-navigator">
            <a href="OICAssignGuards.php" class="a-inactive">Assign</a>
            <a href="OICMonitorGuards.php" class="a-active">Monitor</a>
        </div>
        <div class="main-header">
            <header>Guards</header>
        </div>

        <div class="monitor-guards">
            <span class="material-icons-outlined">monitor</span>
            Monitor Guards
        </div>

        <div class="monitor-guards-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Time-in?</th>
                        <th>Mark</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $payroll->ShowMonitoringGuards() ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="view-modal">
        <form method="post" class="form-modal">
            <header>Note</header>
            <div class="content">This will mark the selected guard as Absent Without Official Leave (AWOL) in the violation and will be removed from the guards panel.</div>
            <div class="buttons">
                <button type="submit" name="proceed" class="proceed"><span class="material-icons-outlined">task_alt</span>Proceed</button>
                <button type="button" name="cancel" class="cancel" id="cancel"><span class="material-icons-outlined">cancel</span>Cancel</button>
            </div>
        </form>
    </div>

    <div class="view-modal-void">
        <form method="post" class="form-modal-void">
            <header>Note</header>
            <div class="content">This selected guard has already timed-in. If the guard is not present in the establishment, you can void their attendance. <br></br> But, this will remove the schedule of the selected guard and will be on process of being "Available Guard" due to Absence Without Official Leave (AWOL).</div>
            <div class="buttons">
                <button type="submit" name="proceed-void" class="proceed"><span class="material-icons-outlined">task_alt</span>Proceed</button>
                <button type="button" name="cancel" class="cancel" id="cancel-void"><span class="material-icons-outlined">cancel</span>Cancel</button>
            </div>
        </form>
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
    <?php $payroll->ShowAbsentModal() ?>
    <?php $payroll->ShowVoidModal() ?>
    <?php $payroll->showMsgModal() ?>
    <script>
        var items = document.getElementsByClassName("schedule_table");

        for(let i = 0; i<items.length; i++) {
            if(items[i].innerHTML === "Yes") {
                items[i].style.color = "#19C773";
            } else {
                items[i].style.color = "red";
            }
        }

        var cancelbtn = document.getElementById("cancel");

        cancelbtn.addEventListener("click", function() {
            window.location.href = "OICMonitorGuards.php";
        });

        var cancelbtn = document.getElementById("cancel-void");

        cancelbtn.addEventListener("click", function() {
            window.location.href = "OICMonitorGuards.php";
        });
        
        var click = document.getElementById("btnOkay");
        var clicksuccess = document.getElementById("btnOkaySuccess");
        var errormodal = document.getElementsByClassName('view-modal-error');
        var successmodal = document.getElementsByClassName('view-modal-success');

        click.addEventListener("click", function() {
            for (var i=0;i<errormodal.length;i+=1) {
                errormodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OICMonitorGuards.php");
            }
        });

        clicksuccess.addEventListener("click", function() {
            for (var i=0;i<successmodal.length;i+=1) {
                successmodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OICMonitorGuards.php");
            }
        });

    </script>
</body>
</html>