<?php
require_once('../secclass.php');
$sessionData = $payroll->getSessionSecretaryData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
$id = $_GET['logid'];
?>
<!DOCTYPE html>
<html>
<head>

<style>
* {
  box-sizing: border-box;
}
body{
            background:#F2F2F2;
            border: 1px solid black;
        }

.row {
  margin-left:-5px;
  margin-right:-5px;
}

.viewautomatedsalary-logo {
    position: absolute;
    right: 50px;
    top: 50px;
    height: 100px;
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
<a href="releasedsalary.php">BACK</a>
<form method="post"><button type="submit" name="download">DOWNLOAD PDF</button></form>
<?php
    $sql = "SELECT *
    FROM automatic_generated_salary
    INNER JOIN employee ON automatic_generated_salary.emp_id = employee.empId
    WHERE automatic_generated_salary.log = ?;";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute([$id]);
    $rows = $stmt->fetch();
    if(isset($_POST['download'])){
      $payroll->generatepdf($id);
    }
    ?>
<center><h2>JTDV SECURITY AGENCY</h2>
<p><u>400 Gem Bldg.,Gen T De Leon Ave.</br>Barangay Gen T. De Leon, Valenzuela City</u></p></center>


<object data="../img/icon.png" type="" class="viewautomatedsalary-logo"></object>


<div class="row">
  <div class="column">
    Employee ID: <?php echo " ",$rows->empId;?><br/>
    Employee Name: <?php echo " ",$rows->firstname," ", $rows->lastname;?><br/>
    Position: <?php echo " ",$rows->position;?>
    <table>
      <tr>
        <th>Earnings</th>
        <th>Hours</th>
        <th>Rate</th>
        <th>&nbsp;</th>
      </tr>
      <tr>
        <td>Basic Pay</td>
        <td><?php echo " ",number_format($rows->total_hours);?></td>
        <td><?php echo " ",$rows->ratesperDay;?></td>
        <td><?php echo " ",number_format($rows->standard_pay);?></td>
      </tr>
      <tr>
        <td>Overtime</td>
        <td><?php echo " ",number_format($rows->total_overtime);?></td>
        <td><?php echo " ",$rows->overtime_rate;?></td>
        <td><?php echo " ",number_format($rows->overtime_pay);?></td>
      </tr>
      <tr>
        <td>Regular Holiday</td>
        <td></td>
        <td></td>
        <td><?php echo " ",number_format($rows->regular_holiday_pay);?></td>
      </tr>
      <tr>
        <td>Special Holiday</td>
        <td></td>
        <td></td>
        <td><?php echo " ",$rows->special_holiday_pay;?></td>
      </tr>
      <tr>
        <td>13Month</td>
        <td><?php echo " ",$rows->thirteenmonth;?></td>
        <td></td>
        <td></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>
        <td>Total Gross</td>
        <td></td>
        <td></td>
        <td><?php echo " ",number_format($rows->total_gross);?></td>
      </tr>
    </table>
    <h3><u>Total Netpay: <?php echo " ",number_format($rows->total_netpay);?></u></h3>
  </div>
  <div class="column">
      Email: <?php echo " ",$rows->email;?><br/>
      Contact: <?php echo " ",$rows->cpnumber;?><br/>
      Date: <?php echo " ",$rows->date_created;?>
    <table>
      <tr>
        <th>Deductions</th>
        <th>No.of</th>
        <th>Rate</th>
        <th>&nbsp;</th>
      </tr>
      <tr>
        <td>Late</td>
        <td><?php echo " ",$rows->total_hours_late." Min/s"?></td>
        <td><?php echo " ",number_format($rows->ratesperDay / 60,3);?></td>
        <td><?php echo " ",$rows->late_total;?></td>
      </tr>
      <tr>
        <td>Sss</td>
        <td></td>
        <td></td>
        <td><?php echo " ",$rows->sss;?></td>
        <td></td>
      </tr>
      <tr>
        <td>Pagibig</td>
        <td></td>
        <td></td>
        <td><?php echo " ",$rows->pagibig;?></td>
        <td></td>
      </tr>
      <tr>
        <td>Philhealth</td>
        <td></td>
        <td></td>
        <td><?php echo " ",$rows->philhealth;?></td>
        <td></td>
      </tr>
      <tr>
        <td>Cash bond</td>
        <td></td>
        <td></td>
        <td><?php echo " ",$rows->cashbond;?></td>
        <td></td>
      </tr>
      <?php
      if($rows->other_amount > 0){
      echo "<tr>
        <td>$rows->other</td>
        <td></td>
        <td></td>
        <td>$rows->other_amount</td>
        <td></td>
      </tr>";
      }
      if($rows->vale > 0){
      echo "<tr>
        <td>Cash Advance</td>
        <td></td>
        <td></td>
        <td>".number_format($rows->vale)."</td>
      </tr>";
      }
      if($rows->violation > 0){
      echo "<tr>
              <td>Violations</td>
              <td></td>
              <td></td>
              <td>".number_format($rows->violation)."</td>
            </tr>
      <tr>";
      }
      ?>
        <td>Total Deduction</td>
        <td></td>
        <td></td>
        <td><?php echo " ",number_format($rows->total_deduction);?></td>
      </tr>
      
    </table>
    <h3>Salary From: <?php echo " ",$rows->start, " - ",$rows->end?></h3>
  </div>
</div>
</body>
</html>
