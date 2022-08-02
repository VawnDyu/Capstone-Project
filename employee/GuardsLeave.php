<?php

    require_once('../classemp.php');
    
    $sql = "SELECT status FROM maintenance WHERE module='Guards'";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute();
        
    $row = $stmt->fetchColumn();
        
    if ($row == 1) {
        header('location: ../m_maintenance.php');
    } else {
        $sessionData = $payroll->getSessionGuardsData();
        $getDateTime = $payroll->getDateTime();
    
        $payroll->MobileVerifyUserAccess($sessionData['access'], $sessionData['fullname'], $sessionData['position']);
        $payroll->submitLeave();
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
    <title>Apply for Leave</title>
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
                    <li><a href="GuardsProfile.php"><span class="material-icons-outlined">person</span></a></li>
                    <li><a href="Guards.php"><span class="material-icons-outlined">other_houses</span></a></li>
                    <li><a href="GuardsInbox.php" class='notification'>
                            <span class="material-icons-outlined" style="transform: translateY(6%);">mail</span>
                            <?php $payroll->notificationBadge() ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="main-header">
            <header>Apply for Leave</header>
        </div>

        <div class="first-block-title">
            <span class="material-icons-outlined">calendar_month</span>
            Leave History
        </div>

        <div class="history-table">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $payroll->showyourleave() ?>
                </tbody>
            </table>
        </div>

        <div class="second-block-title">
            <span class="material-icons-outlined">schedule</span>
            Set Leave
        </div>

        <div class="set-leave">
            <form method="post">
                <div class="type">
                    <label for="type">Type: </label>
                    <select name="type" id="typeCategory" class="type" onchange="getType(this)" required>
                        <option value="" disabled selected>Select type of leave</option>
                        <option value="Emergency Leave">Emergency Leave</option>
                        <option value="Maternity Leave">Maternity Leave</option>
                        <option value="Paternity Leave">Paternity Leave</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Vacation Leave">Vacation Leave</option>
                    </select>
                </div>
                <div class="leave-from">
                    <label for="inputFrom">From: </label>
                    <input type="date" class="inputFrom" name="inputFrom" id="inputFrom" onkeydown="return false" required>
                </div>
                <div class="leave-to">
                    <label for="inputTo">To: </label>
                    <input type="date" name="inputTo" id="inputTo" onkeydown="return false" required disabled>
                </div>

                <div class="reason-header">
                    <span class="material-icons-outlined">note_alt</span>
                    Reason
                </div>

                <div class="reason-form">
                    <textarea maxlength="255" name="reason" id="reason" placeholder="Max of 255 characters." required></textarea>
                    <button type="submit" name="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <div class="view-modal">  
        <form method="post" class="form-modal">
            <a href="GuardsLeave.php?msg=cancel"><span class="close">&times</span></a>
            <header>View History</header>
            <div>
                <label for="showStatus"><span class="material-icons-outlined">pending</span>Status</label>
                <input type="text" class="showStatus" name="showStatus" id="showStatus" autocomplete="off" readonly/>
            </div>
            <div>
                <label for="showType"><span class="material-icons-outlined">format_list_bulleted</span>Type of Leave</label>
                <input type="text" name="showType" id="showType" autocomplete="off" readonly/>
            </div>
            <div>
                <label for="showInputFrom"><span class="material-icons-outlined">schedule</span>From</label>
                <input type="text" name="showInputFrom" id="showInputFrom" autocomplete="off" readonly/>
            </div>
            <div>
                <label for="showInputTo"><span class="material-icons-outlined">schedule</span>To</label>
                <input type="text" name="showInputTo" id="showInputTo" autocomplete="off" readonly/>
            </div>
            <div>
                <label for="showReason"><span class="material-icons-outlined">note_alt</span>Reason</label>
                <textarea type="text" name="showReason" id="showReason" autocomplete="off" readonly> </textarea>
            </div>
            <div>
                <label for="showDateCreated"><span class="material-icons-outlined">today</span>Date Created</label>
                <input type="text" name="showDateCreated" id="showDateCreated" autocomplete="off" readonly/>
            </div>
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
    
    <?php $payroll->showMsgModal() ?>
    <?php $payroll->viewLeave() ?>
    <script>
    let a = document.querySelector('.inputFrom');

    var type = document.getElementById('typeCategory');

    function getType(selectObject) {
    
        var option = selectObject.value;
        var inputFrom = document.getElementById("inputFrom");
        var inputTo = document.getElementById("inputTo");
        var dateNow = new Date();
        var newDateNow = dateNow.toISOString().split('T')[0]; //Split in Javascript


        if (option == "Sick Leave" || option == "Emergency Leave") {
            inputFrom.value = "";
            inputTo.value = "";
            inputFrom.min = "2021-01-01";
            inputTo.disabled = true;


            a.addEventListener("change", function() { // Sick Leave and Emergency Leave has no restriction in dates
                document.getElementById("inputTo").disabled = false;
                inputTo.min = this.value;
            });

            console.log(option);

        } else if (option == "Maternity Leave" || option == "Paternity Leave" || option == "Vacation Leave") {

            inputFrom.value = "";
            inputFrom.min = newDateNow;
            inputTo.value = "";
            inputTo.disabled = true;

            a.addEventListener("change", function() { 
                document.getElementById("inputTo").disabled = false;
                inputTo.min = inputFrom.value;
            });
            console.log(option);
        }
    }

        var items = document.getElementsByClassName("table_status");

        for(let i = 0; i<items.length; i++) {
            if(items[i].innerHTML === "Approved") {
                items[i].style.color = "#19C773";
            } else if (items[i].innerHTML === "Rejected") {
                items[i].style.color = "red";
            } else if (items[i].innerHTML === "Completed") {
                items[i].style.color = "#464646";
            } else {
                items[i].style.color = "#FE7A30";
            }
        }

        // let showStatus = document.getElementsByClassName("showStatus");
        const getStatus = document.querySelector('.showStatus');
        const val = document.querySelector('.showStatus').value;

        if (val === "Approved") {
            getStatus.style.color = "#19C773";
        } else if (val === "Rejected") {
            getStatus.style.color = "red";
        } else if (val === "Completed") {
            getStatus.style.color = "#464646";
        } else {
            getStatus.style.color = "#FE7A30";
        }

        var click = document.getElementById("btnOkay");
        var clicksuccess = document.getElementById("btnOkaySuccess");
        var errormodal = document.getElementsByClassName('view-modal-error');
        var successmodal = document.getElementsByClassName('view-modal-success');

        click.addEventListener("click", function() {
            for (var i=0;i<errormodal.length;i+=1) {
                errormodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "GuardsLeave.php");
            }
        });

        clicksuccess.addEventListener("click", function() {
            for (var i=0;i<successmodal.length;i+=1) {
                successmodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "GuardsLeave.php");
            }
        });



    </script>
</body>
</html>