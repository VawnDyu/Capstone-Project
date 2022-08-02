<?php 

    require_once('classemp.php');

    date_default_timezone_set('Asia/Manila');
    $timedatenow = date("Y-m-d H:i:s");
    $timenow = date("h:i:s A");
    $date = date("F j, Y");
    $page = $_SERVER['PHP_SELF'];
    $sec = "1";

    $sql = "SELECT * FROM do_event WHERE execute_at <= ?";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute([$timedatenow]);

    $users = $stmt->fetch();
    $countRow = $stmt->rowCount();

    if ($countRow > 0) {
        $function = $users->do_function;

        $sqlDo = $function;
        $stmtDo = $payroll->con()->query($sqlDo);
    }

    $sqlFind = "SELECT
                s.empId,
                s.scheduleTimeIn,
                s.scheduleTimeOut,
                att.datetimeIn,
                att.login_session
            FROM schedule s
            LEFT JOIN emp_attendance att
            ON s.empId = att.empId AND att.login_session = 'true'
            INNER JOIN employee e
            ON s.empId = e.empId
            WHERE e.availability = 'Unavailable'
            
            ORDER BY s.empId ASC";

            $stmtFind = $payroll->con()->prepare($sqlFind);
            $stmtFind->execute();

            date_default_timezone_set("Asia/Manila");
            $timeNow = time();
                

            while($row = $stmtFind->fetch()) {

                if ($row->login_session != 'true' && $timeNow >= (strtotime($row->scheduleTimeIn) + 60*60) && $timeNow <= (strtotime($row->scheduleTimeOut))) {
                    $dateNow = date("Y/m/d");
                    $violation = "Absent Without Official Leave (AWOL)";
                    $availability = "Absent";
                    $status = "Absent";
                    $login_session = "false";
                    $getId = $row->empId;

                    //For Event only
                    $strReplace = str_replace('-', '_', $getId);
                    $event_name = "Absent_".$strReplace;
                    $nextDay = date('Y-m-d H:i:s', strtotime(date("Y/m/d").' 1 DAY'));
                    $doFunction = "UPDATE `employee` SET `availability` = 'Unavailable' WHERE `empId` = '$getId'; DELETE FROM `do_event` WHERE `event_name` = '$event_name'";
        
                    $sqlUpdate = "BEGIN;
                                    INSERT INTO emp_attendance (empId, datetimeIn, datetimeOut, status, login_session) VALUES (?, ?, ?, ?, ?);
                                    INSERT INTO violationsandremarks (empId, violation, date_created) VALUES (?, ?, ?);
                                    UPDATE employee SET availability = ? WHERE empId = ?;
                                    INSERT INTO do_event (event_name, execute_at, do_function) VALUES (?, ?, ?); 
                                COMMIT;";
        
                    $stmtUpdate = $payroll->con()->prepare($sqlUpdate);
                    $stmtUpdate->execute([$getId, $dateNow, $dateNow, $status, $login_session, $getId, $violation, $dateNow, $availability, $getId, $event_name, $nextDay, $doFunction]);

                    $sqlFind = "SELECT * FROM employee WHERE empId = ?";
                    $stmtFind = $payroll->con()->prepare($sqlFind);
                    $stmtFind->execute([$getId]);
                    
                    $usersFind = $stmtFind->fetch();
                    $countRowFind = $stmtFind->rowCount();
                    
                    if ($countRowFind > 0) {
                        $email = $usersFind->email;
                        $lastname = $usersFind->lastname;
                        
                        $empId = $getId;
                        $subject = "Marked as Absent";
                        $body = "You will not be able to time-in for this day for not attending to the establishment for more than 1 hour.
                        
If you think that it is just a mistake, you may comply to our agency to solve this issue.";
                        $date_created = date("Y/m/d h:i:s A");
                        $status = "Unread";
                        $generateId = uniqid();
                        
                        $sqlInbox = "INSERT INTO inbox (id, empId, subject, body, date_created, status) VALUES (?,?,?,?,?,?)";
                        $stmtInbox = $payroll->con()->prepare($sqlInbox);
                        $stmtInbox->execute([$generateId, $empId, $subject, $body, $date_created, $status]);
                        
                        // $countRowInbox = $stmtInbox->rowCount();
                        
                        // if ($countRowInbox > 0) {
                        //     $payroll->sendEmailNewMessage($email, $lastname);
                        // }
                    }
                }
            }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
    <link rel="icon" href="img/transparent.png" type="image/png">
    <title>JTDV Clock</title>
</head>

<style>
    body {
        background: black;
    }

    .clock {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translateX(-50%) translateY(-50%);
        color: #17D4FE;
        letter-spacing: 7px;
        text-align: center;
        font-family: "Poppins", Sans-Serif;
        font-weight: 600;
        font-size: 6rem;
        width: 100vw;
    }

    .clock > .dateclock {
        font-weight: 500;
        font-size: 3rem;
    }
</style>

<body>
    
    <div class="clock"><?php echo $timenow?><div class="dateclock"><?php echo $date?></div></div>
</body>
</html>
