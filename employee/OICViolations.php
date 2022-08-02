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

        $payroll->submitViolation();
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
    <title>OIC | Add Violations</title>
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
            <header>Add Violations</header>
        </div>

        <div class="view-violations">
        <a href="OICViewViolations.php"><span class="material-icons-outlined">format_list_bulleted</span>View</a>
        </div>

        <div class="add-violation">
            <form method="post">
                <div>
                    <span class="material-icons-outlined">assignment_ind</span><label for="empId">Employee ID</label>
                    <input type="text" name="empId" id="empId" placeholder="Click 'Set' to enter Employee ID" onkeypress="return false;" required>
                </div>
                <div class="table-set">
                    <table>
                        <thead>
                            <tr>
                                <th>Emp-ID</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $payroll->SelectGuardsToSet() ?>
                        </tbody>
                    </table>
                </div>
                <div>
                    <span class="material-icons-outlined">report_problem</span><label for="violation">Violation</label>
                    <textarea name="violation" id="violation" maxlength="255" placeholder="Max of 255 characters." required></textarea>
                </div>

                <div>
                    <input type="checkbox" name="uniform" id="uniform" onclick="myFunction();">
                    <label for="uniform">No Uniform</label>
                </div>
                <div class="category">
                    <table id="category" style="display: none;" class="category">
                        <tr>
                            <td>
                                <label for="category" class="labelCategory">Category</label>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" class="inputCategory" name="overseacup" id="overseacup" value=" Overseacup" onclick="category();">
                                <label for="overseacup" class="labelOverseacup">Overseacup</label>
                            </td>
                            <td>
                                <input type="checkbox" class="inputCategory" name="namecloth" id="namecloth" value=" Name Cloth" onclick="category();">
                                <label for="namecloth" class="labelNamecloth">Name Cloth</label>
                            </td>
                            <td>
                                <input type="checkbox" class="inputCategory" name="agencynamecloth" id="agencynamecloth" value=" Agency Name Cloth" onclick="category();">
                                <label for="agencynamecloth" class="labelAgencynamecloth">Agency Name Cloth</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" class="inputCategory" name="belt" id="belt" value=" Belt" onclick="category();">
                                <label for="belt">Belt</label>
                            </td>
                            <td>
                                <input type="checkbox" class="inputCategory" name="buckle" id="buckle" value=" Buckle" onclick="category();">
                                <label for="buckle">Buckle</label>
                            </td>
                            <td>
                                <input type="checkbox" class="inputCategory" name="holster" id="holster" value=" Holster" onclick="category();">
                                <label for="holster">Holster</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" class="inputCategory" name="badge" id="badge" value=" Badge" onclick="category();">
                                <label for="badge">Badge</label>
                            </td>
                            <td>
                                <input type="checkbox" class="inputCategory" name="blackshoes" id="blackshoes" value=" Black Shoes" onclick="category();">
                                <label for="blackshoes">Black Shoes</label>
                            </td>
                            <td>
                                <input type="checkbox" class="inputCategory" name="blacksock" id="blacksock" value=" Black Sock" onclick="category();">
                                <label for="blacksock">Black Sock</label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" class="inputCategory" name="nightstick" id="nightstick" value=" Night Stick" onclick="category();">
                                <label for="nightstick">Night Stick</label>
                            </td>
                            <td>
                                <input type="checkbox" class="inputCategory" name="flashlight" id="flashlight" value=" Flash Light" onclick="category();">
                                <label for="flashlight">Flash Light</label>
                            </td>
                            <td>
                                <input type="checkbox" class="inputCategory" name="whistle" id="whistle" value=" Whistle" onclick="category();">
                                <label for="whistle">Whistle</label>
                            </td>
                        </tr>
                    </table>
                </div>
                <button type="submit" name="submit">Submit</button>
            </form>
        </div>
    </div>
    <div class="view-modal-success">  
        <div class="modal-success">
            <header class='success-header'>Success</header>
            <?php $payroll->getSuccessModalMsg() ?>
            <button type="button" id="btnOkaySuccess">Okay</button>
        </div>
    </div>
    <?php $payroll->setEmployeeId() ?>
    <?php $payroll->showMsgModal() ?>

    <script>
        var clicksuccess = document.getElementById("btnOkaySuccess");
        var successmodal = document.getElementsByClassName('view-modal-success');

        clicksuccess.addEventListener("click", function() {
            for (var i=0;i<successmodal.length;i+=1) {
                successmodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OICViolations.php");
            }
        });

        function myFunction() {
            // Get the checkbox
            var checkBox = document.getElementById("uniform");
            var inputType = document.getElementById("violation");
            // Get the output text
            var text = document.getElementById("category");
            let checkboxes = document.querySelectorAll('input[class="inputCategory"]');

            // If the checkbox is checked, display the output text
            if (checkBox.checked == true){
                text.style.display = "";
                inputType.placeholder = "Click on the category.";
                inputType.setAttribute("onkeypress", "return false");
                inputType.value = "";

            } else {
                text.style.display = "none";
                inputType.value = "";
                inputType.placeholder = "Max of 255 characters.";
                inputType.setAttribute("onkeypress", "return true");

                checkboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });
            }
        }

        function category() {
            let checkboxes = document.querySelectorAll('input[class="inputCategory"]:checked');
            var inputType = document.getElementById("violation");
            
            let values = [];
            checkboxes.forEach((checkbox) => {
                values.push(checkbox.value);
            });

            if (values.length > 0) {
                inputType.value = "No" + values;
            } else {
                inputType.value = "";
            }
        }
    </script>
</body>
</html>