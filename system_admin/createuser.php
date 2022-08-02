<?php 

    require_once('../class.php');

    session_start();

    if (isset($_SESSION['admin_user']) && isset($_SESSION['admin_pass'])) {
        if (isset($_POST['submit'])) {

            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $address = $_POST['address'];
            $cpnumber = $_POST['contact'];
            $username = $_POST['email'];
            $randomGenPW = $payroll->generatedPassword2();
            $password = $payroll->generatedPassword($randomGenPW);
            $access = "administrator";
    
            $sql = "BEGIN;
                        INSERT INTO super_admin (firstname, lastname, address, cpnumber, username, password, access) VALUES (?, ?, ?, ?, ?, ?, ?);
                        INSERT INTO secret_diary (sa_id, secret_key) VALUES (?, ?);
                    COMMIT;";
    
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute([$firstname, $lastname, $address, $cpnumber, $username, $password[0], $access, $username, $randomGenPW]);
    
            $payroll->sendEmail($username, $randomGenPW);
            header('location: ?msg=create_success');
        } else if (isset($_POST['update-view'])) {
    
            $id = $_GET['vid'];
    
            $firstname = $_POST['update-firstname'];
            $lastname = $_POST['update-lastname'];
            $username = $_POST['update-email'];
            $contact = $_POST['update-contact'];
            $address = $_POST['update-address'];
    
            $sql = "UPDATE super_admin SET firstname = ?, lastname = ?, username = ?, cpnumber = ?, address = ? WHERE id = ?";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute([$firstname, $lastname, $username, $contact, $address, $id]);
    
            header('location: ?msg=update_success');
        } else if (isset($_POST['delete'])) {
    
            $id = $_GET['did'];
    
            $sql = "DELETE FROM super_admin WHERE id = ?";
            $stmt = $payroll->con()->prepare($sql);
            $stmt->execute([$id]);
    
            $count = $stmt->rowCount();
    
            if ($count > 0) {
                header('location: ?msg=delete_success');
            } else {
                header('location: ?msg=delete_error');
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
    <title>Create Account</title>
</head>
<body>
    <div class="main-container">
        <div class="left-navigator">
            <div class="logo">
                <img src="img/icon.png">
                <header>JTDV<header>
            </div>
            <div class="link-selected">
                <span class="material-icons-outlined">person</span>
                <a href="#">Head Manager</a>
            </div>
            <div class="link">
                <span class="material-icons-outlined">inventory_2</span>
                <a href="archive_employee.php">Archive</a>
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
                <header>Maintenance</header>
            </div>
            <div class="profile">
                <header>Administrator</header>
            </div>
        </div>

        <div class="create-account">
            <header>Create Account</header>
            <form method="post">
                <label for="firstname">Firstname</label>
                <input type="text" name="firstname" autocomplete="off" required>

                <label for="lastname">Lastname</label>
                <input type="text" name="lastname" autocomplete="off" required>

                <label for="email">Email</label>
                <input type="email" name="email" autocomplete="off" required>

                <label for="contact">Contact No.</label>
                <input type="tel" name="contact" pattern="09[0-9]{9}" autocomplete="off" placeholder="09xxxxxxxx" maxlength="11" required>

                <label for="address">Address</label>
                <input type="text" name="address" autocomplete="off" required>

                <input type="submit" name="submit" value="Submit">
            </form>
        </div>

        <div class="list-of-head-manager">
            <header>List of Head Manager</header>
            <table>
                <thead>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contacts</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    <?php
                        $sql = "SELECT * FROM super_admin";
                        $stmt = $payroll->con()->prepare($sql);

                        if ($stmt->execute()) {
                            while($row = $stmt->fetch()) {
                                echo "<tr>
                                        <td>$row->firstname $row->lastname</td>
                                        <td>$row->username</td>
                                        <td>$row->cpnumber</td>
                                        <td>
                                            <div class='buttons'>
                                                <div class='view'>
                                                    <a href='?vid=$row->id'><span class='material-icons-outlined'>visibility</span></a>
                                                </div>
                                                <div class='delete'>
                                                    <a class='delete' href='?did=$row->id'><span class='material-icons-outlined'>delete_forever</span></a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr>
                                    <td>No data found.</td>
                            </tr>";
                        }
                    ?>
                </tbody>
            </table>
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

        <div class="view-modal-view">
            <form method="post" class="form-modal-view">
                <header>View</header>
                <?php 
                    $id = $_GET['vid'];

                    $sql = "SELECT * FROM super_admin WHERE id = ?";
                    $stmt = $payroll->con()->prepare($sql);
                    $stmt->execute([$id]);

                    $row = $stmt->fetch();
                    $count = $stmt->rowCount();

                    if ($count > 0) {
                        echo "
                            <label for='firstname'>Firstname</label>
                            <input type='text' name='update-firstname' autocomplete='off' value='$row->firstname' required>

                            <label for='lastname'>Lastname</label>
                            <input type='text' name='update-lastname' autocomplete='off' value='$row->lastname' required>

                            <label for='email'>Email</label>
                            <input type='email' name='update-email' autocomplete='off' value='$row->username' required>

                            <label for='contact'>Conctact No.</label>
                            <input type='tel' pattern='09[0-9]{9}'' autocomplete='off' placeholder='09xxxxxxxx' maxlength='11' name='update-contact' value='$row->cpnumber' required>

                            <label for='Address'>Address</label>
                            <input type='text' name='update-address' autocomplete='off' value='$row->address' required>
                        ";
                    }
                ?>

                <div class="buttons-view">
                    <button type="submit" name="update-view" class="update-view" id="update-view"><span class="material-icons-outlined">update</span>Update</button>
                    <button type="button" name="cancel-view" class="cancel-view" id="cancel-view"><span class="material-icons-outlined">cancel</span>Cancel</button>
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
        } else if (isset($_GET['vid'])) {
        
            echo "<script>
                    let viewModal = document.querySelector('.view-modal-view');
                    viewModal.setAttribute('id', 'show-modal');
                </script>";
        }
    ?>
    <script>
        var cancelbtn = document.getElementById("cancel");

        cancelbtn.addEventListener("click", function() {
            window.location.href = "createuser.php";
        });

        var cancelbtn = document.getElementById("cancel-view");

        cancelbtn.addEventListener("click", function() {
            window.location.href = "createuser.php";
        });
    </script>
</body>
</html>