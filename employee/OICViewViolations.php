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
        $payroll->UpdateViolation();
        $payroll->DeleteViolation();
        $payroll->MobileVerifyUserAccess($sessionData['access'], $sessionData['fullname'], $sessionData['position']);
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
    <title>OIC | View Violations and Remarks</title>
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

        <div class="violation-header">
            <header>View Violations</header>
        </div>

        <div class="view-violations">
            <a href="OICViolations.php"><span class="material-icons-outlined">arrow_back</span>Back</a>
        </div>

        <div class="violations-table">
            <div class = "table">
                <table>
                    <thead>
                        <tr>
                            <th>Emp ID</th>
                            <th>Violation</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $payroll->showViolations() ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="view-modal">  
            <form method="post" class="form-modal">
            <a href="OICViewViolations.php?msg=cancel"><span class="close">&times</span></a>
                <header>View Violation</header>
                <div>
                    <label for="showempId"><span class="material-icons-outlined">assignment_ind</span>Emp ID</label>
                    <input type="text" class="showempId" name="showempId" id="showempId" autocomplete="off" readonly/>
                </div>
                <div>
                    <label for="showViolation"><span class="material-icons-outlined">report_problem</span>Violation</label>
                    <textarea type="text" name="showViolation" id="showViolation" autocomplete="off" required></textarea>
                </div>
                <div>
                    <label for="showViolationFine"><span class="material-icons-outlined">payments</span>Violation Fine</label>
                    <input type="text" class="showViolationFine" name="showViolationFine" id="showViolationFine" placeholder="No fine" autocomplete="off" readonly/>
                </div>
                <div>
                    <label for="showRemark"><span class="material-icons-outlined">note_alt</span>Remark</label>
                    <textarea type="text" name="showRemark" id="showRemark" autocomplete="off" placeholder="No remarks yet." readonly></textarea>
                </div>
                <div>
                    <label for="showDateCreated"><span class="material-icons-outlined">today</span>Date Created</label>
                    <input type="text" name="showDateCreated" id="showDateCreated" autocomplete="off" readonly> </input>
                </div>
                <button type="submit" class="update" name="update"><span class="material-icons-outlined">update</span>Update</button>
                <button type="submit" class="delete" name="delete"><span class="material-icons-outlined">delete</span>Delete</button>
            </form>
        </div>

    <div class="view-modal-error">  
        <div class="modal-error">
            <header class='error-header'>Request Denied</header>
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
    <?php $payroll->showModalViolation() ?>
    <?php $payroll->showMsgModal()?>

    <script>
        var click = document.getElementById("btnOkay");
        var clicksuccess = document.getElementById("btnOkaySuccess");
        var errormodal = document.getElementsByClassName('view-modal-error');
        var successmodal = document.getElementsByClassName('view-modal-success');

        click.addEventListener("click", function() {
            for (var i=0;i<errormodal.length;i+=1) {
                errormodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OICViewViolations.php");
            }
        });

        clicksuccess.addEventListener("click", function() {
            for (var i=0;i<successmodal.length;i+=1) {
                successmodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OICViewViolations.php");
            }
        });
    </script>
</body>
</html>