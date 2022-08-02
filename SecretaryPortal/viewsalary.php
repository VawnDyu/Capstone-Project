<?php
require_once('../secclass.php');
$sessionData = $payroll->getSessionSecretaryData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
$id = $_GET['logid'];
$payroll->generatemanualpdf($id)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
* {
  box-sizing: border-box;
}
.viewautomatedsalary-logo {
    position: absolute;
    right: 50px;
    top: 50px;
    height: 100px;
}
body{
            background:#F2F2F2;
        }

.row {
  margin-left:-5px;
  margin-right:-5px;
}
  
.column {
  float: left;
  width: 50%;
  padding: 5px;
}

/* Clearfix (clear floats) */
.row::after {
  content: "";
  clear: both;
  display: table;
}

table {
  border-collapse: collapse;
  border-spacing: 0;
  width: 100%;
  border: 2px solid #ddd;
}

th, td {
  text-align: left;
  padding: 10px;
}

tr:nth-child(even) {
  background-color: #f8f9f9;
}

/* Responsive layout - makes the two columns stack on top of each other instead of next to each other on screens that are smaller than 600 px */
@media screen and (max-width: 600px) {
  .column {
    width: 100%;
  }
}
</style>
</head>
<body>
    <?php
    $sql = "SELECT *
    FROM generated_salary
    INNER JOIN employee ON generated_salary.emp_id = employee.empId
    WHERE generated_salary.log = ?;";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute([$id]);
    $rows = $stmt->fetch();
    $absentrate = $rows->day_absent * $rows->hours_duty;
    $absentprice = $absentrate * $rows->rate_hour;
    $lateprice = $rows->rate_hour * $rows->hrs_late;
    ?>
    <form method="post"><button type="submit" name="download">DOWNLOAD PDF</button></form>
    <a href="manualpayroll.php">BACK</a><form>
    <center><h2>JTDV SECURITY AGENCY</h2>
<p><u>400 Gem Bldg.,Gen T De Leon Ave.</br>Barangay Gen T. De Leon, Valenzuela City</u></p></center>

<object data="../img/icon.png" type="" class="viewautomatedsalary-logo"></object>

<div class="row">
  <div class="column">
  <label for="empid">Employee ID: </label><?php echo $rows->empId; ?></br>
 <label for="empname">Employee Name: </label><?php echo $rows->firstname ." ". $rows->lastname; ?></br>
 <label for="location">Location: </label><?php echo $rows->location; ?>
    <table>
            <tr>
                <th>Earnings</th>
                <th>Hours</th>
                <th>Rate</th>
                <th>&nbsp;</th>
            </tr>
            <?php echo "<tr>
            <td>Basic Pay</td>
            <td>$rows->total_hours</td>
            <td>$rows->rate_hour</td>
            <td>",number_format($rows->regular_pay),"</td>
            </tr>
            <tr>
            <td>Overtime</td>
            <td></td>
            <td></td>
            <td></td>
            </tr>
            <tr>
            <td>Regular Holiday </td>
            <td>$rows->regular_holiday</td>
            <td></td>
            <td>",number_format($rows->regular_holiday_pay),"</td>
            </tr>
            </tr>
            <tr>
            <td>Special Holiday</td>
            <td>$rows->special_holiday </td>
            <td></td>
            <td>",number_format($rows->special_holiday_pay)," </td>
            </tr>
            <tr>
            <td>13Month</td>
            <td></td>
            <td> </td>
            <td>",number_format($rows->thirteenmonth)," </td>
            </tr>
            <tr>
            <td>&emsp;</td>
            <td> </td>
            <td> </td>
            <td> </td>
            </tr>
            <tr>
            <td>&emsp;</td>
            <td> </td>
            <td> </td>
            <td></td>
            </tr>
            <tr>
            <td>Total Gross</td>
            <td> </td>
            <td> </td>
            <td>",number_format($rows->total_gross),"</td>
            </tr>
            ";?>
    </table>
    <h3><u>Total Netpay: <?php echo number_format($rows->total_netpay);?></h3></u>
</div>
<div class="column">
<label for="email">Email: </label><?php echo $rows->email;?></br>
<label for="contact">Contact: </label><?php echo "0".$rows->cpnumber;   ?></br>
<label for="date">Date: </label><?php echo date('F j, Y',$rows->date).' - '.$rows->dateandtime_created;?>
    <table>
            <tr>
                <th>Deductions</th>
                <th>No. of</th>
                <th>Rate</th>
                <th>&nbsp;</th>
            </tr>
            <?php echo "
            <tr>
            <td>Late </td>
            <td>$rows->hrs_late</td>
            <td>59.523</td>
            <td>$lateprice</td>
            </tr>
            <tr>
            <td>SSS </td>
            <td> </td>
            <td> </td>
            <td>$rows->sss </td>
            </tr>
            <tr>
            <td>Pag-ibig Fund </td>
            <td> </td>
            <td> </td>
            <td>$rows->pagibig</td>
            </tr>
            <tr>
            <td>Philhealth </td>
            <td> </td>
            <td> </td>
            <td>$rows->philhealth</td>
            </tr>
            <tr>
            <td>Cash Bond </td>
            <td> </td>
            <td> </td>
            <td>$rows->cashbond </td>
            </tr>
            <tr>
            <td>Vale </td>
            <td> </td>
            <td> </td>
            <td>$rows->vale </td>
            </tr>
            <tr>
            <td>Total Deduction</td>
            <td></td>
            <td></td>
            <td>",number_format($rows->total_deduction),"</td>
            </tr>
            ";?>
    </table>
</div>
</div>
</body>
</html>