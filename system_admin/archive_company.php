<?php 

    require_once('../class.php');

    session_start();

    if (isset($_SESSION['admin_user']) && isset($_SESSION['admin_pass'])) {
        if (isset($_POST['restore'])) {

            $rid = $_GET['rid'];
            $isDelete = "0";
    
            $sql = "UPDATE company SET isDeleted = ? WHERE id = ?";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute([$isDelete, $rid]);
    
            $countRow = $stmt->rowCount();
    
            if ($countRow > 0) {
                header('location: ?msg=restore_success');
            } else {
                header('location: ?msg=restore_failed');
            }
        } else if (isset($_POST['delete'])) {
            $did = $_GET['did'];
    
            $sql = "DELETE FROM company WHERE id = ?";
            $stmt = $payroll->con()->prepare($sql);
    
            if ($stmt->execute([$did])) {
                header('location: ?msg=delete_success');
            }
        }
    } else {
        header('location: ../a_login.php');
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="img/icon.png" type="image/png">
    <title>Archive - Company</title>
</head>
<body>
    <div class="main-container">
        <div class="left-navigator">
            <div class="logo">
                <img src="img/icon.png">
                <header>JTDV<header>
            </div>
            <div class="link">
                <span class="material-icons-outlined">person</span>
                <a href="createuser.php">Head Manager</a>
            </div>
            <div class="link-selected">
                <span class="material-icons-outlined">inventory_2</span>
                <a href="#">Archive</a>
            </div>
            <div class="link">
                <span class="material-icons-outlined">handyman</span>
                <a href="maintenance.php">Maintenance</a>
            </div>
            <div class="link-logout">
                <span class="material-icons-outlined">logout</span>
                <a href="../a_logout.php">Logout</a>
            </div>
        </div>

        <!-- Top Header -->

        <div class="top-bar">
            <div class="main-header">
                <header>Archive</header>
            </div>
            <div class="profile">
                <header>Administrator</header>
            </div>
        </div>

        <div class="category">
            <a href="archive_employee.php">
                <div>Employee</div>
            </a>
            <a href="archive_secretary.php">
                <div>Secretary</div>
            </a>
            <a href="#">
                <div class="selected">Company</div>
            </a>
        </div>

        <div class="list-of-secretary">
            <header>List of Company</header>

            <table>
                <thead>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Email</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    <?php 
                    
                        $sql = "SELECT * FROM company WHERE isDeleted = '1'";
                        $stmt = $payroll->con()->prepare($sql);
                        $stmt->execute();

                        $count = $stmt->rowCount();

                        if ($count > 0) {
                            while ($row = $stmt->fetch()) {
                            echo "<tr>
                                    <td>$row->company_name</td>
                                    <td>$row->comp_location</td>
                                    <td>$row->email</td>
                                    <td>
                                        <div class='buttons'>
                                            <div class='restore'>
                                                <a href='?rid=$row->id'><span class='material-icons-outlined'>restore</span></a>
                                            </div>
                                            <div class='delete'>
                                                <a class='delete' href='?did=$row->id'><span class='material-icons-outlined'>delete_forever</span></a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                ";
                            }
                        } else {
                            echo "<tr>
                                    <td>No data found</td>
                                </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="archive-count">
            <header>No. of Archived Company</header>

            <div class="count">
                <header>
                    <?php 
                        $sql = "SELECT COUNT(isDeleted) FROM company WHERE isDeleted = '1'";
                        $stmt = $payroll->con()->prepare($sql);
                        $stmt->execute();

                        echo $stmt->fetchColumn();
                    ?>
                </header>
            </div>
        </div>

        <div class="view-modal">
            <form method="post" class="form-modal">
                <header>Note</header>
                <div class="content">Are you sure you want to permanently delete this data?</div>
                <div class="buttons">
                    <button type="submit" name="delete" class="delete"><span class="material-icons-outlined">delete_forever</span>Delete</button>
                    <button type="button" name="cancel" class="cancel" id="cancel"><span class="material-icons-outlined">cancel</span>Cancel</button>
                </div>
            </form>
        </div>

        <div class="view-modal-restore">
            <form method="post" class="form-modal-restore">
                <header>Note</header>
                <div class="content-restore">Are you sure you want to restore this data?</div>
                <div class="buttons-restore">
                    <button type="submit" name="restore" class="restore"><span class="material-icons-outlined">restore</span>Restore</button>
                    <button type="button" name="cancel-restore" class="cancel-restore" id="cancel-restore"><span class="material-icons-outlined">cancel</span>Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php 
        if(isset($_GET['did'])) {
        
            echo "<script>
                    let viewModal = document.querySelector('.view-modal');
                    viewModal.setAttribute('id', 'show-modal');
                </script>";
        } else if (isset($_GET['rid'])) {
        
            echo "<script>
                    let viewModal = document.querySelector('.view-modal-restore');
                    viewModal.setAttribute('id', 'show-modal');
                </script>";
        }
    ?>

    <script>
        var cancelbtn = document.getElementById("cancel");

        cancelbtn.addEventListener("click", function() {
            window.location.href = "archive_company.php";
        });

        var cancelbtn = document.getElementById("cancel-restore");

        cancelbtn.addEventListener("click", function() {
            window.location.href = "archive_company.php";
        });
    </script>
</body>
</html>