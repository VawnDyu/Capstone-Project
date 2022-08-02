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
        
        // $payroll->deleteGuards();
        $payroll->updateGuards();
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
    <title>OIC | Assign Guards</title>
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
            <a href="OICAssignGuards.php" class="a-active">Assign</a>
            <a href="OICMonitorGuards.php" class="a-inactive">Monitor</a>
        </div>
        <div class="main-header">
            <header>Guards</header>
        </div>

        <div class="assign-guard-title">
            <span class="material-icons-outlined">groups</span>
            Assign Guards
        </div>
        <div class="assign-guard-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Scheduled?</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $payroll->ShowAssignGuards() ?>
                </tbody>
            </table>
        </div>
        <div class="information-title">
            <span class="material-icons-outlined">info</span>
            Information
        </div>

        <div class="information-section">
            <div class="total-guards-box">
            <span class="material-icons-outlined">account_circle</span>
                Total number of guards:
                <div class="number"><?= $payroll->CountAssignGuards() ?></div>   
            </div>
            <div class="scheduled-guards-box">
            <span class="material-icons-outlined">today</span>
                Scheduled:
                <div class="number"><?= $payroll->CountScheduledGuards() ?></div>   
            </div>
            <div class="day-shift-guards-box">
            <span class="material-icons-outlined">light_mode</span>
                First Shift:
                <div class="number"><?= $payroll->CountDayShiftGuards() ?></div>   
            </div>
            <div class="night-shift-guards-box">
                <span class="material-icons-outlined">dark_mode</span>
                Second Shift:
                <div class="number"><?= $payroll->CountNightShiftGuards() ?></div>   
            </div>
        </div>
    </div>
    <div class="view-modal">
            <form method="post" class="form-modal">
            <a href="OICAssignGuards.php?msg=cancel"><span class="close">&times</span></a>
                <header>Set Schedule</header>
                <div>
                    <label for="companyName">Company Name: </label>
                    <input type="text" name="companyName" id="companyName" readonly required>
                </div>
                <div>
                    <label for="employeeId">Employee ID: </label>
                    <input type="text" name="employeeId" id="employeeId" readonly required>
                </div>

                <div class="timeInBlock">
                    <label for="timeIn">Time In</label>
                    <select name="timeIn" class="timeIn" id="timeIn" onchange="calculateTimeIn(this.value)" required>
                        <option value="" disabled selected>Select Time</option>
                        <option value="06">6:00</option>
                        <option value="07">7:00</option>
                        <option value="08">8:00</option>
                        <option value="09">9:00</option>
                        <option value="10">10:00</option>
                        <option value="11">11:00</option>
                        <option value="12">12:00</option>
                        <option value="13">13:00</option>
                        <option value="14">14:00</option>
                    </select>
                </div>
                <div class="timeOutBlock">
                    <label for="timeOut">Time Out</label>
                    <input type="text" id="timeOut" name="timeOut" class="timeOut" readonly required>
                </div>
                <div>
                    <label for="shiftSpan">Shift Span</label>
                    <select name="shiftSpan" id="shiftSpan" onchange="calculateShiftSpan(this.value)" required>
                        <option value="" disabled selected>Select Hours</option>
                        <option value="8">8 Hours</option>
                        <option value="12">12 Hours</option>
                    </select>
                </div>
                <div>
                    <label for="shift">Shift</label>
                    <input type="text" id="shift" name="shift" readonly required>
                    <button type="submit" class="update" name="updateGuards"><span class="material-icons-outlined">update</span>Update</button>
                    <!-- <button type="submit" class="delete" name="delete"><span class="material-icons-outlined">delete</span>Delete</button> -->
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
<!-- 
    <div>
        <h1>Edit Modal :)</h1>
    </div>

    <div>
        <form method="post">
            <div>
                <label for="companyName">Company Name: </label>
                <input type="text" name="companyName" id="companyName" readonly required>
            </div>
            <div>
                <label for="employeeId">Employee ID: </label>
                <input type="text" name="employeeId" id="employeeId" readonly required>
            </div>

            <div>
                <label for="timeIn">Time In</label>
                <select name="timeIn" id="timeIn" onchange="calculateTimeIn(this.value)" required>
                    <option value="" disabled selected>Select Time</option>
                    <option value="1">1:00</option>
                    <option value="2">2:00</option>
                    <option value="3">3:00</option>
                    <option value="4">4:00</option>
                    <option value="5">5:00</option>
                    <option value="6">6:00</option>
                    <option value="7">7:00</option>
                    <option value="8">8:00</option>
                    <option value="9">9:00</option>
                    <option value="10">10:00</option>
                    <option value="11">11:00</option>
                    <option value="12">12:00</option>
                    <option value="13">13:00</option>
                    <option value="14">14:00</option>
                    <option value="15">15:00</option>
                    <option value="16">16:00</option>
                    <option value="17">17:00</option>
                    <option value="18">18:00</option>
                    <option value="19">19:00</option>
                    <option value="20">20:00</option>
                    <option value="21">21:00</option>
                    <option value="22">22:00</option>
                    <option value="23">23:00</option>
                    <option value="24">24:00</option>
                </select>
            </div>
            <div>
                <label for="timeOut">Time Out</label>
                <input type="text" id="timeOut" name="timeOut" readonly required>
            </div>
            <div>
                <label for="shiftSpan">Shift Span</label>
                <select name="shiftSpan" id="shiftSpan" onchange="calculateShiftSpan(this.value)" required>
                    <option value="" disabled selected>Select Hours</option>
                    <option value="8">8 Hours</option>
                    <option value="12">12 Hours</option>
                </select>
            </div>
            <div>
                <label for="shift">Shift</label>
                <input type="text" id="shift" name="shift" readonly required>
            </div>
            <button type="submit" name="updateGuards">Update</button>
        </form>
    </div>

    <div>
        <h1>Delete Modal :)</h1>
    </div>
    <div>
        <form method="post">
            <header>Are you sure you want to delete this user?</header>
            <button type="submit" name="deleteGuards">Yes</button>
            <button type="button">Cancel</button>
        </form> 
    </div> -->

    <?php $payroll->ShowSpecificGuards() ?>
    <?php $payroll->showMsgModal() ?>
    <!-- Script! :) -->
    <script>
            function calculateTimeIn(val) {
                var shiftSpan = document.getElementById("shiftSpan");
                var shiftSpanValue = parseInt(shiftSpan.value);
                var sum = parseInt(val) + shiftSpanValue;
                var shift;

                if (sum > 24) {
                    sum -= 24;
                }

                if (val >= 1 && val <= 13) {
                    shift = 'First Shift';
                } else {
                    shift = 'Second Shift';
                }

                var total;

                if (sum) {
                    total = sum += ":00";
                } else {
                    total = "";
                }

                // var total = sum += ":00";
                /*display the result*/
                var timeOut = document.getElementById('timeOut');
                timeOut.value = total;

                var shiftshow = document.getElementById('shift');
                shiftshow.value = shift;

                console.log(sum);
            }

            function calculateShiftSpan(val) {
                var timeIn = document.getElementById("timeIn");
                var timeInValue = parseInt(timeIn.value);
                var sum = parseInt(val) + timeInValue;

                if (sum > 24) {
                    sum -= 24;
                }
                
                var total;
                
                if (sum) {
                    total = sum += ":00";
                } else {
                    total = "";
                }

                // var total = sum += ":00";
                /*display the result*/
                var timeOut = document.getElementById('timeOut');
                
                timeOut.value = total;

                console.log(sum);
            }

        var click = document.getElementById("btnOkay");
        var clicksuccess = document.getElementById("btnOkaySuccess");
        var errormodal = document.getElementsByClassName('view-modal-error');
        var successmodal = document.getElementsByClassName('view-modal-success');

        click.addEventListener("click", function() {
            for (var i=0;i<errormodal.length;i+=1) {
                errormodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OICAssignGuards.php");
            }
        });

        clicksuccess.addEventListener("click", function() {
            for (var i=0;i<successmodal.length;i+=1) {
                successmodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OICAssignGuards.php");
            }
        });

        var items = document.getElementsByClassName("schedule_table");

        for(let i = 0; i<items.length; i++) {
            if(items[i].innerHTML === "Yes") {
                items[i].style.color = "#19C773";
            } else {
                items[i].style.color = "red";
            }
        }
    </script>

</body>
</html>