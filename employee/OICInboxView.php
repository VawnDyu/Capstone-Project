<?php 

    require_once('../classemp.php');
    
    $sqlM = "SELECT status FROM maintenance WHERE module='Guards'";
    $stmtM = $payroll->con()->prepare($sqlM);
    $stmtM->execute();
        
    $rowM = $stmtM->fetchColumn();
        
    if ($rowM == 1) {
        header('location: ../m_maintenance.php');
    } else {
        $sessionData = $payroll->getSessionOICData();
        $payroll->MobileVerifyUserAccess($sessionData['access'], $sessionData['fullname'], $sessionData['position']);
    
        $id = $_GET['id'];
        $empId = $sessionData['empId'];
    
        $sql = "SELECT * FROM inbox WHERE id = ? AND empId = ?";
        $stmt = $payroll->con()->prepare($sql);
        $stmt->execute([$id, $empId]);
    
        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();
    
        if ($countRow > 0) {
            $status = 'Read';
    
            $sqlUpd = "UPDATE inbox SET status = ? WHERE id = ?";
            $stmtUpd = $payroll->con()->prepare($sqlUpd);
            $stmtUpd->execute([$status, $id]);
        } else {
            header('location: OIC.php');
        }
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
    <title>View</title>
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

        
        <div class="main-header">
            <?php echo $users->subject?>
        </div>

        <div class="view-date">
            <?php echo $users->date_created ?>
        </div>

        <div class="view-body">
            <textarea id='body'><?php echo $users->body ?></textarea>
                    
            <?php 
                if ($users->filename && $users->filenewname) {
                    echo "<a href='../inbox/$users->filenewname' download='$users->filename'>
                            <div class='attachment'>
                                <span class='material-icons-outlined'>file_download</span> Download - $users->filename
                            </div>
                        </a>
                        ";
                } else {
                    false;
                }
            ?>
        </div>

    </div>

    <script>
        document.getElementById('body').onmousedown = function(e) {
            e.preventDefault();
        }

        var tx = document.getElementsByTagName('textarea');

        for (let i = 0; i < tx.length; i++) {
            tx[i].setAttribute('style', 'height:' + (tx[i].scrollHeight) + 'px;overflow-y:hidden;');
            tx[i].addEventListener('input', OnInput, false);
        }

        function OnInput() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        }
    </script>
</body>
</html>