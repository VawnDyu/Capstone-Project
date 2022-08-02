<?php 

    require_once('classemp.php');
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
    <title>Remarks</title>
</head>
<body>
    <div>
        <h1>Violations</h1>
        <table>
            <thead>
                <tr>
                    <td>Emp ID</td>
                    <td>Violation</td>
                    <td>Date</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody>
                <?php $payroll->viewListViolation() ?>
            </tbody>
        </table>
    </div>

    <div>
        <h1>List of Remarked Violation</h1>
        <table>
            <thead>
                <tr>
                    <td>Emp ID</td>
                    <td>Name</td>
                    <td>Subject</td>
                    <td>Date</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody>
                <?php $payroll->viewListRemarkedViolation() ?>
            </tbody>
        </table>
    </div>

    <?php $payroll->addModalRemarks() ?>
    <?php $payroll->viewModalViolation() ?>
    <?php $payroll->viewModalListRemarkedViolation() ?>
</body>
</html>