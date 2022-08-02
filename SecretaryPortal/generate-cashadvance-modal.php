<?php
    require_once('../secclass.php');
    $sessionData = $payroll->getSessionSecretaryData();
    $payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
    $fullname = $sessionData['fullname'];
    $access = $sessionData['access'];
    $id = $sessionData['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Deduction Modal</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../seccss/main.css">
</head>
<body>
<div class="main-containter">
    <div class="modal">
        <form action="" method="post" class=modal__form>
             <div class="modal__form__header1">
                    <h1>Generate Cash Advance</h1>
             </div>

             <div class="modal__form__content">

                  <div class="modal__form__content__spaces">
                       
                  </div>
                  <div class="modal__form__content__spaces">
                  <?php $payroll->cashadvance($fullname,$id);?>
                       <label for="" class="document">Employee</label>
                       <?php $sql ="SELECT empId,firstname,lastname FROM employee;";$stmt = $payroll->con()->prepare($sql); $stmt->execute(); $row = $stmt->fetchall(); echo "<select id= empid name=empid >"; foreach($row as $rows){echo "<option value=$rows->empId> $rows->empId $rows->firstname $rows->lastname</option>";}; ?><?php echo "</select>"; ?><br/><br/>
                  </div>

                  <div class="modal__form__content__spaces">
                       <label for="" class="payments">Amount</label>
                       <input type="number" name="amount" placeholder="Php">
                  </div>

                  <button class="btn_success" type="submit" name="add">
                    Generate Cashadvance
                  </button>

                  <button class="cancel" type="submit" name="cancel">
                    Back
                  </button>
             </div>
        </form>
    </div>
</div>
</body>
</html>
