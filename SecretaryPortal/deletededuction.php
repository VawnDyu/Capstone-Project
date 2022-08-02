<?php
require_once('../secclass.php');
$id=$_GET['logid'];
$payroll->deletededuction($id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Deductions</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../seccss/main.css">
    <title>Delete Deduction</title>
</head>
<body>
<div class="main-containter">
    <div class="modal">
        <form method="post" class="modal__delete">
            <div class="modal__delete__header1">
                <h1>Delete Deduction</h1>
            </div>

            <div class="modal__delete__content">
                <h1>Are you sure you want to delete this Deduction?</h1>
                <button class="btn_danger" type="submit" name="deletededuction">
                    Delete
                </button>
            </div>

            <div class="modal__delete__content">
                <button class="cancel" type="submit" name="cancel">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
