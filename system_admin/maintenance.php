<?php 

    require_once('../class.php');

    session_start();

    if (isset($_SESSION['admin_user']) && isset($_SESSION['admin_pass'])) {
        if (isset($_POST['activate-a-hm'])) {
            $sql = "UPDATE maintenance SET status = 1 WHERE module = 'Head Manager'";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute();
    
            $count = $stmt->rowCount();
    
            if ($count > 0) {
                header('location: ?msg=hm_activated');
            } else {
                header('location: ?msg=hm_failed');
            }
        } else if (isset($_POST['activate-a-s'])) {
            $sql = "UPDATE maintenance SET status = 1 WHERE module = 'Secretary'";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute();
    
            $count = $stmt->rowCount();
    
            if ($count > 0) {
                header('location: ?msg=s_activated');
            } else {
                header('location: ?msg=s_failed');
            }
        } else if (isset($_POST['activate-a-g'])) {
            $sql = "UPDATE maintenance SET status = 1 WHERE module = 'Guards'";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute();
    
            $count = $stmt->rowCount();
    
            if ($count > 0) {
                header('location: ?msg=g_activated');
            } else {
                header('location: ?msg=g_failed');
            }
        } else if (isset($_POST['deactivate-d-hm'])) {
            $sql = "UPDATE maintenance SET status = 0 WHERE module = 'Head Manager'";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute();
    
            $count = $stmt->rowCount();
    
            if ($count > 0) {
                header('location: ?msg=hm_deactivated');
            } else {
                header('location: ?msg=hm_d_failed');
            }
        } else if (isset($_POST['deactivate-d-s'])) {
            $sql = "UPDATE maintenance SET status = 0 WHERE module = 'Secretary'";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute();
    
            $count = $stmt->rowCount();
    
            if ($count > 0) {
                header('location: ?msg=s_deactivated');
            } else {
                header('location: ?msg=s_d_failed');
            }
        } else if (isset($_POST['deactivate-d-g'])) {
            $sql = "UPDATE maintenance SET status = 0 WHERE module = 'Guards'";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute();
    
            $count = $stmt->rowCount();
    
            if ($count > 0) {
                header('location: ?msg=g_deactivated');
            } else {
                header('location: ?msg=g_d_failed');
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
    <title>Maintenance</title>
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
            <div class="link">
                <span class="material-icons-outlined">inventory_2</span>
                <a href="archive_employee.php">Archive</a>
            </div>
            <div class="link-selected">
                <span class="material-icons-outlined">handyman</span>
                <a href="#">Maintenance</a>
            </div>
            <div class="link-logout">
                <span class="material-icons-outlined">logout</span>
                <a href="../a_logout.php">Logout</a>
            </div>
        </div>

        <!-- Top Header -->

        <div class="top-bar">
            <div class="main-header">
                <header>Maintenance</header>
            </div>
            <div class="profile">
                <header>Administrator</header>
            </div>
        </div>

        <div class='maintenance-header'>
            <header>Activate / Deactivate Maintenance</header>
        </div>

        <div class="maintenance-toggle">
            <div class="devices">
                <span class="material-icons-outlined">computer</span>
                <header>Head Manager</header>  
                <?php 
                    $sql = "SELECT status FROM maintenance WHERE module = 'Head Manager'";
                    $stmt = $payroll->con()->prepare($sql);
                    $stmt->execute();

                    $value = $stmt->fetchColumn();

                    if ($value == 0) {
                        echo "<a href='?mdl=a_submit_hm' class='activate'>Activate</a>";
                    } else {
                        echo "<a href='?mdl=d_submit_hm' class='deactivate'>Deactivate</a>";
                    }
                ?>
            </div>

            <div class="devices">
                <span class="material-icons-outlined">computer</span>
                <header>Secretary</header>
                <?php 
                    $sql = "SELECT status FROM maintenance WHERE module = 'Secretary'";
                    $stmt = $payroll->con()->prepare($sql);
                    $stmt->execute();

                    $value = $stmt->fetchColumn();

                    if ($value == 0) {
                        echo "<a href='?mdl=a_submit_s' class='activate'>Activate</a>";
                    } else {
                        echo "<a href='?mdl=d_submit_s' class='deactivate'>Deactivate</a>";
                    }
                ?>
            </div>

            <div class="devices">
                <span class="material-icons-outlined">smartphone</span>
                <header>Guards</header>
                <?php 
                    $sql = "SELECT status FROM maintenance WHERE module = 'Guards'";
                    $stmt = $payroll->con()->prepare($sql);
                    $stmt->execute();

                    $value = $stmt->fetchColumn();

                    if ($value == 0) {
                        echo "<a href='?mdl=a_submit_g' class='activate'>Activate</a>";
                    } else {
                        echo "<a href='?mdl=d_submit_g' class='deactivate'>Deactivate</a>";
                    }
                ?>
            </div>
        </div>

        <div class='feedback-header'>
            <header>Feedback</header>
        </div>

        <div class="feedback">
            <table>
                <thead>
                    <th>Name</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    <?php 
                    
                        $sql = "SELECT * FROM feedback ORDER BY date_created DESC";
                        $stmt = $payroll->con()->prepare($sql);
                        $stmt->execute();

                        $count = $stmt->rowCount();

                        if ($count > 0) {
                            while ($row = $stmt->fetch()) {
                                echo "<tr>
                                        <td>$row->fullname</td>
                                        <td>$row->position</td>
                                        <td>$row->date_created</td>
                                        <td>
                                            <div class='buttons'>
                                                <div class='view'>
                                                    <a href='?view=$row->id'<span class='material-icons-outlined'>visibility</span></a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr>
                                    <td>No data found</td>
                                </tr>";
                        }

                    ?>
                </tbody>
            </table>
        </div>

        <div class="feedback-view">
            <header>View</header>

            <?php 

                if(isset($_GET['view'])) {

                    $id = $_GET['view'];

                    $sql = "SELECT * FROM feedback WHERE id = ?";
                    $stmt = $payroll->con()->prepare($sql);
                    $stmt->execute([$id]);

                    $row = $stmt->fetch();
                    $count = $stmt->rowCount();

                    if ($count > 0) {
                        echo "<div class='feed'>
                                <div class='name'>
                                    <header>$row->fullname</header>
                                </div>

                                <div class='position'>
                                    <header>$row->position</header>
                                </div>

                                <div class='date'>
                                    <header>$row->date_created</header>
                                </div>

                                <div class='category'>
                                    <header>Category: $row->category</header>
                                </div>

                                <div class='comment'>
                                    <header>$row->comment</header>
                                </div>
                            </div>";
                    } else {
                        echo "<div class='feed'>
                                <header>Click view on the action below to preview feedbacks.</header>
                            </div>
                        ";
                    }
                } else {
                    echo "<div class='no-feed'>
                            <header>Click view on the action below to preview feedbacks.</header>
                        </div>
                    ";
                }
            ?>
        </div>

        <div class="feedback-count">
            <header>No. of submitted feedbacks</header>

            <div class="count">
                <header>
                    <?php 
                        $sql = "SELECT COUNT(*) FROM feedback";
                        $stmt = $payroll->con()->prepare($sql);
                        $stmt->execute();

                        echo $stmt->fetchColumn();
                    ?>
                </header>
            </div>
        </div>

        <div class="view-modal-a-hm">
            <form method="post" class="form-modal-a-hm">
                <header>Note</header>
                <div class="content">This will prevent the <b>Head Manager</b> from using the system while on maintenance.<br><br>Do you want to activate?</div>
                <div class="buttons">
                    <button type="submit" name="activate-a-hm" class="activate-a-hm"><span class="material-icons-outlined">check</span>Activate</button>
                    <button type="button" name="cancel" class="cancel-a-hm" id="cancel-a-hm"><span class="material-icons-outlined">cancel</span>Cancel</button>
                </div>
            </form>
        </div>

        <div class="view-modal-a-s">
            <form method="post" class="form-modal-a-s">
                <header>Note</header>
                <div class="content">This will prevent the <b>Secretary</b> from using the system while on maintenance.<br><br>Do you want to activate?</div>
                <div class="buttons">
                    <button type="submit" name="activate-a-s" class="activate-a-s"><span class="material-icons-outlined">check</span>Activate</button>
                    <button type="button" name="cancel" class="cancel-a-s" id="cancel-a-s"><span class="material-icons-outlined">cancel</span>Cancel</button>
                </div>
            </form>
        </div>

        <div class="view-modal-a-g">
            <form method="post" class="form-modal-a-g">
                <header>Note</header>
                <div class="content">This will prevent the <b>Guards</b> from using the system while on maintenance.<br><br>Do you want to activate?</div>
                <div class="buttons">
                    <button type="submit" name="activate-a-g" class="activate-a-g"><span class="material-icons-outlined">check</span>Activate</button>
                    <button type="button" name="cancel" class="cancel-a-g" id="cancel-a-g"><span class="material-icons-outlined">cancel</span>Cancel</button>
                </div>
            </form>
        </div>

        <div class="view-modal-d-hm">
            <form method="post" class="form-modal-d-hm">
                <header>Note</header>
                <div class="content">Make it sure that all of the problems has been fixed on our system.<br><br>Do you want to deactivate?</div>
                <div class="buttons">
                    <button type="submit" name="deactivate-d-hm" class="deactivate-d-hm"><span class="material-icons-outlined">power_settings_new</span>Deactivate</button>
                    <button type="button" name="cancel" class="cancel-d-hm" id="cancel-d-hm"><span class="material-icons-outlined">cancel</span>Cancel</button>
                </div>
            </form>
        </div>

        <div class="view-modal-d-s">
            <form method="post" class="form-modal-d-s">
                <header>Note</header>
                <div class="content">Make it sure that all of the problems has been fixed on our system.<br><br>Do you want to deactivate?</div>
                <div class="buttons">
                    <button type="submit" name="deactivate-d-s" class="deactivate-d-s"><span class="material-icons-outlined">power_settings_new</span>Deactivate</button>
                    <button type="button" name="cancel" class="cancel-d-s" id="cancel-d-s"><span class="material-icons-outlined">cancel</span>Cancel</button>
                </div>
            </form>
        </div>

        <div class="view-modal-d-g">
            <form method="post" class="form-modal-d-g">
                <header>Note</header>
                <div class="content">Make it sure that all of the problems has been fixed on our system.<br><br>Do you want to deactivate?</div>
                <div class="buttons">
                    <button type="submit" name="deactivate-d-g" class="deactivate-d-g"><span class="material-icons-outlined">power_settings_new</span>Deactivate</button>
                    <button type="button" name="cancel" class="cancel-d-g" id="cancel-d-g"><span class="material-icons-outlined">cancel</span>Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php 
        if(isset($_GET['mdl'])) {

            $id = $_GET['mdl'];

            if ($id == "a_submit_hm") {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-a-hm');
                        viewModal.setAttribute('id', 'show-modal');
                    </script>";
            } else if ($id == "a_submit_s") {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-a-s');
                        viewModal.setAttribute('id', 'show-modal');
                    </script>";
            } else if ($id == "a_submit_g") {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-a-g');
                        viewModal.setAttribute('id', 'show-modal');
                    </script>";
            } else if ($id == "d_submit_hm") {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-d-hm');
                        viewModal.setAttribute('id', 'show-modal');
                    </script>";
            } else if ($id == "d_submit_s") {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-d-s');
                        viewModal.setAttribute('id', 'show-modal');
                    </script>";
            } else if ($id == "d_submit_g") {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-d-g');
                        viewModal.setAttribute('id', 'show-modal');
                    </script>";
            }
        }
    ?>

    <script>
        var cancelbtn_a_hm = document.getElementById("cancel-a-hm");

        cancelbtn_a_hm.addEventListener("click", function() {
            window.location.href = "maintenance.php";
        });

        var cancelbtn_a_s = document.getElementById("cancel-a-s");

        cancelbtn_a_s.addEventListener("click", function() {
            window.location.href = "maintenance.php";
        });

        var cancelbtn_a_g = document.getElementById("cancel-a-g");

        cancelbtn_a_g.addEventListener("click", function() {
            window.location.href = "maintenance.php";
        });

        var cancelbtn_d_hm = document.getElementById("cancel-d-hm");

        cancelbtn_d_hm.addEventListener("click", function() {
            window.location.href = "maintenance.php";
        });

        var cancelbtn_d_s = document.getElementById("cancel-d-s");

        cancelbtn_d_s.addEventListener("click", function() {
            window.location.href = "maintenance.php";
        });

        var cancelbtn_d_g = document.getElementById("cancel-d-g");

        cancelbtn_d_g.addEventListener("click", function() {
            window.location.href = "maintenance.php";
        });
    </script>
</body>
</html>