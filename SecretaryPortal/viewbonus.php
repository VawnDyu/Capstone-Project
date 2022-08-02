<?php
require_once('../secclass.php');
$sessionData = $payroll->getSessionSecretaryData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
$id = $_GET['id'];
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
  position: relative;
  left:350px;
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
<a href="thirteen.php">BACK</a>
<?php
    $sql = "SELECT *
    FROM thirteenmonth
    INNER JOIN employee ON thirteenmonth.empId = employee.empId
    WHERE thirteenmonth.log = ?;";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute([$id]);
    $rows = $stmt->fetch();
    if(isset($_POST['download'])){
      $payroll->generatepdf($id);
    }
    ?>
<center><h2>JTDV SECURITY AGENCY</h2></center>


<object data="../img/icon.png" type="" class="viewautomatedsalary-logo"></object>


<div class="row">
  <div class="column">
    Employee ID: <?php echo " ",$rows->empId;?><br/>
    Employee Name: <?php echo " ",$rows->firstname," ", $rows->lastname;?><br/>
    Position: <?php echo " ",$rows->position;?>
    <table>
      <tr>
        <th>Month</th>
        
        <th></th>
        <th>Gross</th>
      </tr>
      <tr>
        <td>January</td>
   
        <td></td>
        <td><?php echo " ",number_format($rows->january);?></td>
      </tr>
      <tr>
      <td>February</td>
      
        <td></td>
        <td><?php echo " ",number_format($rows->february);?></td>
      </tr>
      <tr>
      <td>March</td>
   
        <td></td>
        <td><?php echo " ",number_format($rows->march);?></td>
      </tr>
      <tr>
      <td>May</td>
       
        <td></td>
        <td><?php echo " ",number_format($rows->may);?></td>
      </tr>
      <tr>
      <td>June</td>
      
        <td></td>
        <td><?php echo " ",number_format($rows->june);?></td>
      </tr>
      <tr>
      <td>July</td>
      
        <td></td>
        <td><?php echo " ",number_format($rows->july);?></td>
      </tr>
      <tr>
      <td>August</td>
       
        <td></td>
        <td><?php echo " ",number_format($rows->august);?></td>
      </tr>
      <tr>
      <td>September</td>
      
        <td></td>
        <td><?php echo " ",number_format($rows->september);?></td>
      </tr>
      <tr>
      <td>October</td>
     
        <td></td>
        <td><?php echo " ",number_format($rows->october);?></td>
      </tr>
      <tr>
      <td>November</td>
        
        <td></td>
        <td><?php echo " ",number_format($rows->november);?></td>
      </tr>
      <tr>
      <td>December</td>
      
        <td></td>
        <td><?php echo " ",number_format($rows->december);?></td>
      </tr>
    </table>
    <center><h3><u>Total: <?php echo " ",number_format($rows->amount);?></u></h3></center>
  </div>   
    </table>
  </div>
</div>
</body>
</html>
