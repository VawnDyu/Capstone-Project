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
    <title>Inbox</title>
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

        <div class="inbox-header">
            <header>Inbox</header>
        </div>

        <div class="inbox-search">
            <form class="example" method="post">
                <input type="search" placeholder="Search" name="search" autocomplete="off">
                <button type="submit" name="submit"><span class='material-icons-outlined'>search</span></button>
            </form>
        </div>

        <div class="inbox-table">
            <div class="table-set">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $payroll->getInbox() ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    //Change color of Read and Unread

    var items = document.getElementsByClassName("table_status");

    for(let i = 0; i<items.length; i++) {
        if(items[i].innerHTML === "Read") {
            items[i].style.color = "#19C773";
        } else {
            items[i].style.color = "#FE7A30";
        }
    }
    </script>
</body>
</html>