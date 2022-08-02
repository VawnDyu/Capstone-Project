<?php
require_once('../secclass.php');
$sessionData = $payroll->getSessionSecretaryData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
$fullname = $sessionData['fullname'];
$empid=$_GET['empId'];
$sql="SELECT * FROM employee INNER JOIN schedule ON employee.empId = schedule.empId WHERE employee.empId = ?";
$stmt = $payroll->con()->prepare($sql);
$stmt->execute([$empid]);
$users = $stmt->fetch();
$countRow = $stmt->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Deductions</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../seccss/main.css">
</head>
<body>
    <div class="main-containter">
        <div class="modal">
            <form method="post" class="modal__form">
                <div class="modal__form__header1">
                    <h1>View Employee</h1>
                </div>
                <div class="modal__form__content">
                    <div class="modal__form__content__spaces">
                        <label for="" class="user">Name :</label>
                        <input type="text" disabled placeholder="<?php echo $users->firstname ." ". $users->lastname ?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="" class="email">Email:</label>
                        <input type="text" disabled placeholder="<?php echo $users->email ?>">
                    </div>

                    <div class="modal__form__content__spaces">
                         <label for="" class="phone">Contact:</label>
                         <input type="text" disabled placeholder="<?php echo $users->cpnumber ?>">
                     </div>
                    
                    <div class="modal__form__content__spaces">
                        <label for="" class="location">Address:</label>
                        <input type="text" disabled placeholder="<?php echo $users->address ?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="" class="position">Position:</label>
                        <input type="text" disabled placeholder="<?php echo $users->position ?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="" class="time">Schedule:</label>
                        <input type="text" disabled placeholder="<?php echo $users->scheduleTimeIn .' - '.$users->scheduleTimeOut ?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="" class="building">Company:</label>
                        <input type="text" disabled placeholder="<?php echo $users->company ?>">
                    </div>
                    <br>

                
                        <button class="btn_cancel" type = "submit" name ="back">
                            Back
                        </button>
                   
                    <?php 
                        if(isset($_POST['back'])){
                            header('location: employeelist.php');
                            }
                        ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
