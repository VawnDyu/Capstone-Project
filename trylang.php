<?php
require_once('secclass.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    table {
        font-family: Arial, Helvetica, sans-serif; 
        border-collapse: collapse; 
        width: 100%;
    }

    table td, th {
        border: 1px solid #ddd;
        padding: 8px;
        font-size: 14px;
        text-align: center;
    }

    table > thead > tr:nth-child(even){background-color: #f2f2f2;}

    table > thead > tr:hover {background-color: #ddd;}

    table > thead > tr > th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: center;
        font-size: 10px;
        background-color: #04AA6D;
        color: white;
    }
</style>
<body>  <form method="post">
        <select name='empid'>
        <?php $sql="SELECT * FROM employee";
         $stmt = $payroll->con()->prepare($sql);
         $stmt->execute();
         $rows = $stmt->fetchall();
         $rowss = $stmt->fetch();
         foreach ($rows as $row)
         {
            echo " 
            <option value='$row->empId'>$row->firstname $row->lastname $row->empId</option>";
        }
        ?>
        </select>
        <select name='status'>
        <option value="paid">Paid</option>
        <option value="unpaid">Unpaid</option>
        </select>
        <button type="submit" name="select">Select</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>Log</th>
                <th>EmpId</th>
                <th>DateTimeIn</th>
                <th>TimeIn</th>
                <th>DateTimeOut</th>
                <th>TimeOut</th>
                <th>Late</th>
                <th>Accumulated Time</th>
                <th>Overtime</th>
                <th>Basic Pay(AccumulatedTime x Rate)</th>
                <th>Overtime Pay(Overtime x Overtime Rate)</th>
                <th>Salary per Day</th>
                <th>Regular Holiday</th>
                <th>Special Holiday</th>
                <th>Total Gross</th>
            </tr>
        </thead>
        <?php
        if(isset($_POST['select'])){
            $totalregularall=0;
            $totalspecialall=0;
            $totdo=0;
            $totalo=0;
            $totald=0;
            $totallate=0;
            $totalovertime=0;
            $totalaccumulatedtime=0;
            $empid=$_POST['empid'];
            $status=$_POST['status'];
                $sqla="SELECT 
                emp_attendance.*,
                schedule.scheduleTimeIn,
                employee.ratesperDay,
                employee.firstname,
                employee.lastname,
                employee.overtime_rate
            FROM emp_attendance 
            LEFT JOIN schedule
            ON emp_attendance.empId = schedule.empId
            LEFT JOIN employee
            ON emp_attendance.empId = employee.empId
            WHERE emp_attendance.empId= ? AND emp_attendance.salary_status = ?;";
    $stmta = $payroll->con()->prepare($sqla);
    $stmta->execute([$empid,$status]);
    $overtime = "No overtime";
    $userss= $stmta->fetchall();
                    $regholidaybasicpay = 0;
                    $regholiday = 0;
                    $regholidayotpay = 0;
                    $regholidayot = 0;
                    $regholidaypay = 0;
                    $specholidaybasicpay = 0;
                    $specholiday = 0;
                    $specholidayotpay = 0;
                    $specholidayot = 0;
                    $specholidaytotal = 0;
                    $totalaccumulated=0;
                    $valueOvertime=0;
    foreach ($userss as $users) {
            $totald1 = 0;
            $totalo1 = 0;
        date_default_timezone_set("Asia/Manila");

            $getdateTimeIn = strtotime($users->timeIn);
            $getdateTimeOut = strtotime($users->timeOut);
            $empdatein = date ('F d' , strtotime($users->datetimeIn));
            $empdateout = date ('F d' , strtotime($users->datetimeOut));

            $diff =  $getdateTimeOut - $getdateTimeIn  / (60*60);
            $interval = $diff; 

                $StandardSchedule = date("h:i:s A", strtotime($users->scheduleTimeIn) + 8*60*60); 
                $diff2 = $getdateTimeOut - strtotime($StandardSchedule);

                    if ($interval <= 8) {
                        $valueOvertime = 0;
                        $overtime = 0;
                        $overtimerate = 0;
                    } else {
                        $valueOvertime = number_format(floatval($diff2 / (60*60)),2);
                        if($valueOvertime <0 )
                        {
                            $valueOvertime =0 ;
                        }   
                        $totalovertime += $valueOvertime;
                        $overtimerate = 0;
                    }

                    if($valueOvertime) {
                        $overtime = $valueOvertime;
                    }
                $diffAccumulated = strtotime($StandardSchedule) - $getdateTimeIn;
                $valueAccumulatedTime = $diffAccumulated / (60*60);

                    if($valueAccumulatedTime) {
                        $accumulatedtime = $valueAccumulatedTime;
                        $totalaccumulatedtime +=  $accumulatedtime;
                    }

                $diffLate = $getdateTimeIn - strtotime($users->scheduleTimeIn);
                $valueLate = $diffLate / 60; 

                    if ($valueLate == 0) { 
                        $late = 0;
                    } else {
                        $late = $diffLate / 60;
                        $totallate += $late;
                    }
                    $regular = "regular holiday";
                    $special = "special holiday";
                    $sql3="SELECT * FROM holidays;";
                    $stmthol3 = $payroll->con()->prepare($sql3);
                    $stmthol3->execute();
                    $usershol3 = $stmthol3->fetchall();
                    $totalregular=0;
                    $totalspecial=0;
                    foreach($usershol3 as $holidate)
                    {
                        $totalspeciald= 0;
                        $holidateto = date('F d',strtotime($holidate->date_holiday));
                        if(preg_match("/{$empdatein}/i", strtolower($holidateto)) OR preg_match("/{$empdateout}/i", strtolower($holidateto)))
                        {   
                            if(preg_match("/{$holidate->type}/i", $regular))
                            {
                                $regholiday += number_format($valueAccumulatedTime);
                                $regholidayot += number_format($valueOvertime);
                                echo "Holiday: $holidate->type $holidate->date_holiday $holidate->name $regholiday $regholidayot<br>";
                                $totalregular = ($accumulatedtime * $users->ratesperDay) + ($valueOvertime* $users->overtime_rate ) ;
                            }else if(preg_match("/{$holidate->type}/i", $special))              //detect holidays
                            {
                                $specholiday += number_format($valueAccumulatedTime);
                                $specholidayot += number_format($valueOvertime);
                                echo "Holiday: $holidate->type $holidate->date_holiday $holidate->name $specholiday $specholidayot<br>";
                                $totalspeciald = ($accumulatedtime * $users->ratesperDay) + ($valueOvertime* $users->overtime_rate ) ;
                                $totalspecial = $totalspeciald * 0.30 ;
                            }else {
                                
                            }
                        }
                    }
            $totald += $accumulatedtime * $users->ratesperDay;
            $totalo += number_format($valueOvertime * $users->overtime_rate);
            $totald1 = $accumulatedtime * $users->ratesperDay;
            $totalo1 = number_format($valueOvertime * $users->overtime_rate);
            $totdo += $totald1 + $totalo1; //total salary without holiday and deduction
            $totalregularall += $totalregular;
            $totalspecialall += $totalspecial;
            echo "<tr>
                    <td>$users->id</td>
                    <td>$users->empId</td>
                    <td>$users->datetimeIn</td>
                    <td>$users->timeIn</td>
                    <td>$users->datetimeOut</td>
                    <td>$users->timeOut</td>
                    <td>$late</td>
                    <td>".number_format($accumulatedtime,2)."</td>
                    <td>".$valueOvertime."</td>
                    <td>".($accumulatedtime * $users->ratesperDay)."</td>
                    <td>".$valueOvertime * $users->overtime_rate."</td>
                    <td>".$totald1 + $totalo1."</td>
                    <td>$totalregular</td>
                    <td>$totalspecial</td>
                    <td></td>
                    
                <tr>";
    }
            echo "<thead><tr>
                <th>TOTAL</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>".number_format($totallate,2)." mins</th>
                <th>".number_format($totalaccumulatedtime,2)." hrs</th>
                <th>".number_format($totalovertime,2)." hrs</th>
                <th>$totald</th>
                <th>$totalo</th>
                <th>$totdo</th>
                </tr>
                <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>".number_format($totallate,2)." mins</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>$totdo</th>
                <th>$totalregularall</th>
                <th>$totalspecialall</th>
                <th>".($totalspecialall  + $totalregularall + $totdo)."</th>
                </tr>
                
                </thead>";
                $stmta->execute([$empid,$status]);
                $rowss=$stmta->fetch();
            echo "<h3>$rowss->firstname $rowss->lastname <br>
            Rate: $rowss->ratesperDay <br>
            Overtime Rate: $rowss->overtime_rate <br></h3>";
            echo "<h1><tr>Computation for Late:&emsp;&emsp;&emsp;&emsp;"  .$totallate."mins x ".$rowss->ratesperDay / 60 ." = ".($totallate * ($rowss->ratesperDay/60))."</tr><br>
            <tr>Computation for Basic Pay:&emsp;&emsp;"   .$totalaccumulatedtime."hrs x ".$rowss->ratesperDay." = ".$totalaccumulatedtime * $rowss->ratesperDay."</tr></br>
            <tr>Computation for Overtime: &emsp;&emsp;".$totalovertime."hrs x ".$rowss->overtime_rate." = ".$totalovertime * $rowss->overtime_rate."</tr>";
}
?>
    </table>
    <!-- <form method="post"><button type="submit" name="merge">Merge</button></form> <?php if(isset($_POST['merge'])){ $payroll->mergepdf(); }?> -->
</body>
</html>