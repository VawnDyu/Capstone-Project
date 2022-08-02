<?php
require_once('../secclass.php');
$sessionData = $payroll->getSessionSecretaryData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
$fullname = $sessionData['fullname'];
$access = $sessionData['access'];
$id = $sessionData['id'];
$log=$_GET['logid'];
$payroll->updateSalary($id,$fullname);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Salary</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
</head>
<body>
    <div class="main-container">
        <div class="modal">
            <form action="" method="post" class="modal__form">
                <div class="modal__form__header1">
                    <h1>Update Salary</h1>
                </div>

                <div class="modal__form__content">

                    <div class="modal__form__content__spaces">
                        <h1 class="subheader">Details</h1>
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="empid" class="user">Employee ID :</label>
                        <?php $sql ="SELECT * FROM employee INNER JOIN generated_salary ON employee.empId = generated_salary.emp_id WHERE generated_salary.log = ?;";
                        $stmt = $payroll->con()->prepare($sql); $stmt->execute([$log]); 
                        $rows = $stmt->fetch(); 
                        echo "<select id= select-state name=empid placeholder= Pick a state...><option value=$rows->empId> $rows->empId $rows->firstname $rows->lastname</option></select>";?>
                    </div>
                    
                    <div class="modal__form__content__spaces">
                        <label for="location" class="location">Location: </label>
                        <input type="text" name="location" id="location" value="<?php echo $rows->location;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="regholiday" class="document">Regular Holiday: </label>
                        <input type="text" name="regholiday" id="regholiday" value="<?php echo $rows->regular_holiday;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="specialholiday" class="document">Special Holiday: </label>
                        <input type="text" name="specialholiday" id="specialholiday" value="<?php echo $rows->special_holiday;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="rate" class="time">Rate/Hour: </label>
                        <input type="text" name="rate" id="rate" step="any" value="<?php echo $rows->rate_hour;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="noofdayswork" class="document">No. of days work: </label>
                        <input type="text" name="noofdayswork" id="noofdayswork" value="<?php echo $rows->no_of_work;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="hrsduty" class="time">Hours Duty: </label>
                        <select name="hrsduty" id="hrsduty">
                            <option value="8">8 hours</option>
                            <option value="12">12 hours</option>
                        </select>
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="hrslate" class="time">Total Hours Late: </label>
                        <input type="number" name="hrslate" id="hrslate" value="<?php echo $rows->hrs_late;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <h1>Deductions</h1>
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="daylate" class="time">No. of late:  </label>
                        <input type="number" name="daylate" id="daylate" value="<?php echo $rows->day_late;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="hrslate" class="time">Total hours late:  </label>
                        <input type="number" name="hrslate" id="hrslate" value="<?php echo $rows->hrs_late;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="sss" class="document">SSS: </label>
                        <input type="number" name="sss" id="sss" value="<?php echo $rows->sss;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="pagibig" class="document">Pagibig: </label>
                        <input type="number" name="pagibig" id="pagibig" value="<?php echo $rows->pagibig;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="philhealth" class="document">Philhealth: </label>
                        <input type="number" name="philhealth" id="philhealth" value="<?php echo $rows->philhealth;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="cashbond" class="document">Cash Bond: </label>
                        <input type="number" name="cashbond" id="cashbond" value="<?php echo $rows->cashbond;?>">
                    </div>

                    <div class="modal__form__content__spaces">
                        <label for="cvale" class="document">Vale: </label>
                        <input type="number" name="cvale" id="cvale" value="<?php echo $rows->vale;?>">
                    </div>

                    <div class="modal__form__content__spaces"><br>
                        <button class="btn_success" type="submit" >
                            Update
                        </button>
                    </div>

                    <div class="modal__form__content__spaces">
                        <button class="cancel" type="submit">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>