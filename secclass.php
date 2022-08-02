<?php
// required to para makapag send ng email
use PHPMailer\PHPMailer\PHPMailer;
use Dompdf\Dompdf;
use PDFMerger\PDFMerger;
require_once "PHPMailer/PHPMailer.php";
require_once "PHPMailer/SMTP.php";
require_once "PHPMailer/Exception.php";
require_once "vendor/clegginabox/pdf-merger/src/PDFMerger/PDFMerger.php";

require 'vendor/autoload.php';

Class Payroll
{
    private $username = "u359933141_jtdv";
    private $password = "+Y^HLMVV2h";

    private $dns = "mysql:host=localhost;dbname=u359933141_payroll";
    protected $pdo;

    public function con()
    {
        $this->pdo = new PDO($this->dns, $this->username, $this->password,array(PDO::ATTR_PERSISTENT => true));
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return $this->pdo;
    }

    public function close()
    {
        $this->pdo = null;
    }

    // used to set timezone and get date and time
    public function getDateTime()
    {
        date_default_timezone_set('Asia/Manila'); // set default timezone to manila
        $curr_date = date("Y/m/d"); // date
        $curr_time = date("h:i:s A"); // time

        // return date and time in array
        $_SESSION['datetime'] = array('time' => $curr_time, 'date' => $curr_date);
        return $_SESSION['datetime'];
    }
    public function sendEmail($email, $password)
    {
       

        $name = 'JTDV Incorporation';
        $subject = 'subject kunwari';
        $body = "Credentials
                 Your username: $email <br/>
                 Your password: $password
                ";

        if(!empty($email)){

            $mail = new PHPMailer();

            // smtp settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username =  "sicnarfarerreh@gmail.com";  // gmail address
            $mail->Password = "sicnarf123";  // gmail password

            $mail->Port = 587;
            $mail->SMTPSecure = "tls";
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email, $name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ("$email ($subject)");     // headline
            $mail->Body = $body;                        // textarea

            if($mail->send()){
                // $status = "success";
                $response = "Your credentials has been sent to your email";
                echo '<br/>'.$response;
            } else {
                $status = "failed";
                $response = "Something is wrong: <br/>". $mail->ErrorInfo;
                echo '<br/>'.$status."<br/>".$response;
            }
        } 
    }

    public function login()
    {
        if (isset($_POST['login'])) {
            echo 'Incorrect username or password.';
        }
    }


    
    public function formatDateLocked($date)
    {
        $dateArray = explode(" ", $date);

        $dateExpired = date("F j Y", strtotime($dateArray[0])); // date
        $timeExpired = date("h:i:s A", strtotime($dateArray[1])); // time
        return array($dateExpired, $timeExpired);
    }
    
    public function generatedPassword($pword)
    {
        $keyword = "%15@!#Fa4%#@kE";
        $generatedPassword = md5($pword.$keyword);
        return array($generatedPassword, $pword.$keyword);
    }
    
    public function checkAccountTimer($id)
    {
        $sql = "SELECT * FROM super_admin WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            if($users->timer != NULL){
                return true;
            } else {
                return false;
            }
        }

    }


    public function checkEmailExist($email)
    {
        // find email exist in the database
        $sql = "SELECT * FROM super_admin WHERE username = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$email]);
        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();

        // kapag may nadetect
        if($countRow > 0){
            return array(true, $users->password); // yung kaakibat na password, return mo
        } else {
            return array(false, ''); // pag walang nakita, return false and null
        }
    }

    
    public function checkEmailExistEmployee($email)
    {
        // find email exist in the database
        $sql = "SELECT * FROM emp_info WHERE email = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$email]);
        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();

        // kapag may nadetect
        if($countRow > 0){
            return array(true, $users->password); // yung kaakibat na password, return mo
        } else {
            return array(false, ''); // pag walang nakita, return false and null
        }
    }

    public function checkEmailExistSecretary($email)
    {
        // find email exist in the database
        $sql = "SELECT * FROM secretary WHERE email = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$email]);
        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();

        // kapag may nadetect
        if($countRow > 0){
            return array(true, $users->password); // yung kaakibat na password, return mo
        } else {
            return array(false, ''); // pag walang nakita, return false and null
        }
    }
    public function logout()
    {   
        session_start();
        session_destroy();
        $this->pdo = null;
        header('Location: seclogin.php');
    }

    // get login session
    public function getSessionData()
    {
        session_start();
        if($_SESSION['adminDetails']){
            return $_SESSION['adminDetails'];
        }

    }
        // get login session: Secretary
    public function getSessionSecretaryData()
    {
        session_start();
        if($_SESSION['SecretaryDetails']){
            return $_SESSION['SecretaryDetails'];
        }
    }

    public function verifyUserAccess($access, $fullname, $level)
    {
        $message = 'You are not allowed to enter the system';
        if($level == 2){
            $level = '../';
            
            if($access == 'super administrator'){
                return;
            } elseif($access == 'secretary'){
                $sqlm="SELECT * FROM maintenance WHERE module = 'Secretary';";
                $stmtm = $this->con()->prepare($sqlm);
                $stmtm->execute();
                $countrowm = $stmtm->rowCount();
                $usersm=$stmtm->fetch();
                if($usersm->status == 0)
                {

                }else{
                    header('location: '.$level.'secmaintenance.php');
                }
            } else {
                header("Location: ".$level."seclogin.php?message=$message");
            }
        } else {
            if($access == 'super administrator'){
                return;
            } elseif($access == 'secretary'){
                $sqlm="SELECT * FROM maintenance WHERE module = 'Secretary';";
                $stmtm = $this->con()->prepare($sqlm);
                $stmtm->execute();
                $countrowm = $stmtm->rowCount();
                $usersm=$stmtm->fetch();
                if($usersm->status == 0)
                {

                }else{
                    header('location: secmaintenance.php');
                }
            } else {
                header("Location: seclogin.php?message=$message");
            }
        }
    }

    // for secretary table only
    public function checkSecEmailExist($email)
    {
        // find email exist in the database
        $sql = "SELECT * FROM secretary WHERE email = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$email]);
        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();

        // kapag may nadetect
        if($countRow > 0){
            return true; 
        } else {
            return false; 
        }
    }

    // ========================================= RED'S PROPERTY ==============================================

    public function displayAttendance()
    {
            $sql ="SELECT employee.empId, employee.firstname, employee.lastname,emp_attendance.location,emp_attendance.timeIn, emp_attendance.datetimeIn,
            emp_attendance.timeOut, emp_attendance.datetimeOut,
            emp_attendance.status, emp_attendance.empId
            FROM employee
            INNER JOIN emp_attendance ON employee.empId = emp_attendance.empId
            ORDER BY emp_attendance.datetimeIn DESC;";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            while($row = $stmt->fetch()){
            echo "<tr>
            <td>$row->empId</td>
            <td>$row->firstname $row->lastname</td>
            <td>$row->location</td>
            <td>",date('h:i A',strtotime($row->timeIn)),"</td>
            <td>",date('M j, Y', strtotime($row->datetimeIn)),"</td>
            <td>",date('h:i A',strtotime($row->timeOut)),"</td>
            <td>",date('M j, Y', strtotime($row->datetimeOut)),"</td>
            <td>$row->status</td>
            </tr>";   
                                        }
    $this->pdo= null;
    }
    public function displayGeneratedSalary()
    {
        $sql ="SELECT log, generated_salary.emp_id, employee.firstname, employee.lastname, generated_salary.location, generated_salary.date
        FROM generated_salary INNER JOIN employee WHERE generated_salary.emp_id = employee.empId ORDER BY date ASC;";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            while($row = $stmt->fetch()){
            echo "<tr>
            <td>$row->emp_id</td>
            <td>$row->firstname $row->lastname</td>
            <td>$row->location</td>
            <td>$row->date</td>
            <td class='td-action'>
                    <div class='ic ic__add'>
                        <a href='viewsalary.php?logid=$row->log' class='td-view'>
                            <span class='material-icons'>visibility</span>
                        </a>
                    </div>

                    <div class='ic ic__edit'>
                        <a href='updatesalary.php?logid=$row->log' class='td-edit'>
                            <span class='material-icons'>edit</span>
                        </a>
                    </div>

                    <div class='ic ic__delete'>
                        <a href='deletesalary.php?logid=$row->log' class='td-delete'>
                            <span class='material-icons'>delete</span>
                        </a>
                    </div>
            </td>
            </tr>";
            $this->deleteSalary($row->log);
                                        }
    }
    public function deleteSalary($logid)
    {
        if(isset($_POST['delete']))
        {
        $sessionData = $this->getSessionSecretaryData();
        $fullname = $sessionData['fullname'];
        $secid = $sessionData['id'];
        $datetime = $this->getDateTime();
        $time = $datetime['time'];
        $date = $datetime['date'];
        $empid=$logid;
        $sql= "DELETE FROM generated_salary WHERE log = ?;";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$empid]);
        $countrow = $stmt->rowCount();
        if($countrow > 0) 
        {
            $action = "Delete Salary";
            $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                VALUES(?, ?, ?, ?, ?)";
            $stmtSecLog = $this->con()->prepare($sqlSecLog);
            $stmtSecLog->execute([$secid,$fullname, $action, $time, $date]);
            $countRowSecLog = $stmtSecLog->rowCount();
                if($countRowSecLog > 0)
                {
                    echo 'pumasok na sa act log';
                    header('location:manualpayroll.php');
                } else {
                    echo 'di pumasok sa act log';
                    header('location:manualpayroll.php');
                }
        } else {
                echo 'Error in deleting salary!';
                }
        }
        else if(isset($_POST['cancel'])){
            header('location: manualpayroll.php');
        }else{
        }
        $this->pdo= null;
    }
    public function search()
    {
            if(isset($_POST['bsearch']))
            $search = strtolower($_POST['search']);
    
            if(!empty($search))
            {
                $sql ="SELECT employee.empId, employee.firstname, employee.lastname, 
                emp_attendance.location, emp_attendance.timeIn, emp_attendance.datetimeIn, 
                emp_attendance.timeOut, emp_attendance.datetimeOut,
                emp_attendance.status
                FROM employee
                INNER JOIN emp_attendance ON employee.empId = emp_attendance.empId;";
                $found=false;
                $stmt = $this->con()->prepare($sql);
                $stmt->execute();
                $users = $stmt->fetchAll();
                $countRow = $stmt->rowCount();
                foreach($users as $user){
                $lfirstname = strtolower($user->firstname);
                $llastname  = strtolower($user->lastname);
                $lcompany   = strtolower($user->location);
                $lstatus    = strtolower($user->status);
                $timeIn     = strtolower($user->timeIn);
                $timeOut    = strtolower($user->timeOut);
                if(preg_match("/{$search}/i", $lfirstname) || preg_match("/{$search}/i", $llastname) || preg_match("/{$search}/i", $lcompany) || preg_match("/{$search}/i", $lstatus) || preg_match("/{$search}/i", $timeIn) || preg_match("/{$search}/i", $user->datetimeIn) || preg_match("/{$search}/i", $timeOut) ||preg_match("/{$search}/i", $user->datetimeOut)){
                    echo "<tr>
                    <td>&nbsp;$user->empId&nbsp;</td>
                    <td>&nbsp;$user->firstname&nbsp;$user->lastname</td>
                    
                    <td>&nbsp;$user->location&nbsp;</td>
                    <td>&nbsp;$user->timeIn&nbsp;</td>
                    <td>&nbsp;$user->datetimeIn&nbsp;</td>
                    <td>&nbsp;$user->timeOut&nbsp;</td>
                    <td>&nbsp;$user->datetimeOut&nbsp;</td>
                    <td>&nbsp;$user->status&nbsp;</td>
                    <tr/>";
                    $found=true;
                }
                }
                if($found!==true){
                    echo "<tr><td><center>No Result<td><td><td><td><td><td><td></tr>";
                }
            }else{
                $this->displayAttendance();
                }
                $this->pdo= null;
    }

    public function employeeList()
    {
        $sql ="SELECT * FROM employee";
        $stmt = $this->con()->prepare($sql);
                    $stmt->execute();
                    $users = $stmt->fetchall();
                    foreach($users as $user){
                        echo "<tr>
                        <td>$user->empId</td>
                        <td>$user->firstname</td>
                        <td>$user->lastname</td>
                        <td>$user->address</td>
                        <td>$user->cpnumber</td>
                        <td>$user->position</td>
                        <td>$user->availability</td>
                        <td class='td-action'>
                            <div class='ic ic__add'>
                                <a href='viewemp.php?empId=$user->empId' class='td-view'>
                                    <span class='material-icons'>visibility</span>
                                </a>
                            </div>
                        </td>
                        </tr>";
                    }
                    $this->pdo= null;
    }
    public function searchEmployee()
    {
            if(isset($_POST['empsearch'])){
                $search = strtolower($_POST['employeesearch']);
        
                if(!empty($search)){
                    $sql ="SELECT empId, firstname, lastname, address, cpnumber, position, availability
                    FROM employee;";
                    $found=false;
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute();
                    $users = $stmt->fetchAll();
                    $countRow = $stmt->rowCount();
                    foreach($users as $user){
                    $lfirstname = strtolower($user->firstname);
                    $llastname = strtolower($user->lastname);
                    $laddress = strtolower($user->address);
                    $lavailability = strtolower($user->availability);
                    $lposition = strtolower($user->position);
                    if(preg_match("/{$search}/i", $lfirstname) || preg_match("/{$search}/i", $llastname) || preg_match("/{$search}/i", $laddress) || preg_match("/{$search}/i", $lavailability) || preg_match("/{$search}/i", $lposition)){
                        echo "<tr>
                                <td>&nbsp;$user->empId&nbsp;</td>
                                <td>&nbsp;$user->firstname&nbsp;</td>
                                <td>&nbsp;$user->lastname&nbsp;</td>
                                <td>&nbsp;$user->address&nbsp;</td>
                                <td>&nbsp;$user->cpnumber&nbsp;</td>
                                <td>&nbsp;$user->position&nbsp;</td>
                                <td>&nbsp;$user->availability&nbsp;</td>
                                <td class='td-action'>
                                    <div class='ic ic__add'>
                                        <a href='viewemp.php?empId=$user->empId' class='td-view'>
                                            <span class='material-icons'>visibility</span>
                                        </a>
                                    </div>
                                </td>
                             <tr/>";
                        $found=true;
                    }
                    }
                    if($found!==true){
                        echo "<tr><td>No Result<td><td><td><td><td><td><td></tr>";
                    }
                    }else{
                    $this->employeeList();
                    }
        }
        $this->pdo= null;
    }
    public function automaticGenerateSalary($fullname,$id)
    {
        if(isset($_POST['createsalary']))
        {   
            $sql="SELECT * FROM employee;";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            $user = $stmt->fetchall();
            $countRow = $stmt->rowCount();
            foreach($user as $all)
            {                                                       //loop process all employee
                $late=0;
                $empid=$all->empId;
                $sqla="SELECT emp_attendance.*,
                schedule.*,
                employee.*
                FROM emp_attendance 
                LEFT JOIN schedule
                ON emp_attendance.empId = schedule.empId
                LEFT JOIN employee
                ON emp_attendance.empId = employee.empId
                WHERE emp_attendance.empId = '$empid' AND emp_attendance.salary_status != 'paid';";
                $stmta = $this->con()->prepare($sqla);
                $stmta->execute();
                $usera = $stmta->fetchall();
                $countRowa = $stmta->rowCount();
                $sqlsched=" SELECT * FROM schedule WHERE empId = ?";
                $stmtsched = $this->con()->prepare($sqlsched);
                $stmtsched->execute([$empid]);
                $usersched = $stmtsched->fetch();
                $countRowsched = $stmtsched->rowCount();
                if($countRowa > 0) 
                {
                    if($countRowsched>0)
                    {//need may sched para magpush through
                    $sss = 0;
                    $pagibig = 0;
                    $philhealth = 0;
                    $startlog = 0;
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
                    $totallate=0;
                    $totalaccumulated=0;
                    $totalhoursofwork =0;
                    $tothrs=0;
                    $totalovertime=0;
                    $endlog = 0;
                    $noatt = $countRowa;
                    foreach($usera as $att) //attendance
                    {   
                        $endlog = $att->id;
                        $late=0;    
                        $getdateTimeIn = strtotime($att->timeIn); 
                        $getdateTimeOut = strtotime($att->timeOut);
                        $empdatein = date ('F d' , strtotime($att->datetimeIn));
                        $empdateout = date ('F d' , strtotime($att->datetimeOut));
                        $StandardSchedule = date("h:i:s A", strtotime($usersched->scheduleTimeIn) + 8*60*60); 
                        $diff =  $getdateTimeOut - $getdateTimeIn  / (60*60); 
                        
                        $diff2 = $getdateTimeOut - strtotime($StandardSchedule); //compare nilabas sa standard sched
                        $diffAccumulated = strtotime($StandardSchedule) - $getdateTimeIn; //compute total hours pinasok standard
                        $valueAccumulatedTime = $diffAccumulated / (60*60);
                        $totalaccumulated += $valueAccumulatedTime;
                        $rate = $att->ratesperDay;
                        $msc = ($rate * 8) * 30;
                        $ratelate= $rate / 60;
                        $overtimerate =$att->overtime_rate;
                        $diffLate = $getdateTimeIn - strtotime($usersched->scheduleTimeIn);
                        $valueLate = $diffLate / 60;
                        if ($valueLate == 0) 
                        { 
                            } else if($valueLate <=0 )
                            {
                            }else
                            {
                                $totallate += $valueLate;
                            }
                        if ($diff <= 8) {
                            $valueOvertime = 0;
                        } else {
                            $valueOvertime = number_format(floatval($diff2 / (60*60)),2); //int value (Imporante ito kasi ito yung computation para makuha kung ilang hours yung OT)
                            if($valueOvertime < 0){
                                $valueOvertime = 0;
                            }
                            $totalovertime += $valueOvertime;
                        }
                        $regular = "regular holiday";
                        $special = "special holiday";
                        $sql3="SELECT * FROM holidays;";
                        $stmthol3 = $this->con()->prepare($sql3);
                        $stmthol3->execute();
                        $usershol3 = $stmthol3->fetchall();
                        foreach($usershol3 as $holidate)
                        {
                            $holidateto = date('F d',strtotime($holidate->date_holiday));
                            if(preg_match("/{$empdatein}/i", strtolower($holidateto)) OR preg_match("/{$empdateout}/i", strtolower($holidateto)))
                            {   
                                if(preg_match("/{$holidate->type}/i", $regular))
                                {
                                    $regholiday += $valueAccumulatedTime;
                                    $regholidayot += $valueOvertime;
                                }else if(preg_match("/{$holidate->type}/i", $special))              //detect holidays
                                {
                                    $specholiday += $valueAccumulatedTime;
                                    $specholidayot += $valueOvertime;
                                }else {
                                    
                                }
                            }
                        }
                    }

                    $sqlded="SELECT * FROM deductions";
                    $stmtded = $this->con()->prepare($sqlded);
                    $stmtded->execute();
                    $usersded = $stmtded->fetchall();
                    $countRowded =$stmtded->rowCount();                 // deduction process
                    $other = "";
                    $otheramount =0;
                    foreach($usersded as $ded)
                        {
                            if(strtolower(trim($ded->deduction))=="cashbond")
                                {
                                    $cashbond = $ded->amount;
                                }else   
                                {
                                    $other .= $ded->deduction."<br>";
                                    $otheramount+=$ded->amount;
                                }
                        }
                    $sqlsss="SELECT * FROM sss_table;";
                    $stmtsss = $this->con()->prepare($sqlsss);
                    $stmtsss->execute();
                    $usersss = $stmtsss->fetchall();
                    foreach($usersss as $us){
                        if($msc >= $us->salary_from && $msc < $us->salary_to){
                            $sss = ($us->ee / 2);
                        }
                    }
                        $pagibig = 100;
                        $philhealth = ($msc * 0.03) / 2;

                        $sqlvio="SELECT * FROM violationsandremarks WHERE empId = '$empid' AND paid = 'unpaid';";
                        $stmtvio = $this->con()->prepare($sqlvio);
                        $stmtvio->execute();
                        $countRowvio = $stmtvio->rowCount();
                        $usersvio = $stmtvio->fetchall();
                        $violationdeduction=0;
                        if($countRowvio>0)
                        {
                            foreach($usersvio as $countv)
                            {
                                if($countv->description == 'uniform'){
                                    $violationdeduction += $countv->fine;
                                }
                            }
                        }

                    $sql0="SELECT emp_attendance.salary_status, emp_attendance.timeIn, emp_attendance.timeOut, employee.ratesperDay, emp_attendance.datetimeIn, emp_attendance.datetimeOut, employee.position
                    FROM emp_attendance INNER JOIN employee ON emp_attendance.empId = employee.empId WHERE emp_attendance.empId = ? AND emp_attendance.salary_status='unpaid';";
                    $stmt0 = $this->con()->prepare($sql0);
                    $stmt0->execute([$empid]);
                    $users0 = $stmt0->fetch();
                    $countRow0 = $stmt0->rowCount();                 
                    if($countRow0 >= 1)
                    {   
                        $getin=$countRow0;
                        while($countRow0 >= $getin)
                        {
                            $start = $users0->datetimeIn;
                            $getin++;
                        }
                            $end = $start;
                            $users01 = $stmt0->fetchall();                        
                        foreach($users01 as $user0)
                        {
                                $end = $user0->datetimeOut;
                        }

                                $sql1="SELECT * FROM cashadvance WHERE empId = ? AND status IS NULL;";
                                $stmt1 = $this->con()->prepare($sql1);
                                $stmt1->execute([$empid]);
                                $users1 = $stmt1->fetchall();
                                $countRow1 = $stmt1->rowCount();
                                $vale = 0;
                        foreach($users1 as $cadv)
                        {
                                $vale = $vale + $cadv->amount;
                        }
                                $position = $users0->position; //get the position of selected employee

                    }
                    $overtimepay = 0;
                    $standardpay = 0;
                    $thirteenmonth = 0;
                    $laterate = 0;
                    $totalgross = 0;
                    $totaldeduction = 0;
                    $totalnetpay = 0;
                    $startlog = $endlog - $noatt;
                    $total_hours_late = $totallate;
                    if($totalovertime<0){
                        $totalovertime = 0;
                    }
                    $overtimepay = number_format($totalovertime,2) * $overtimerate;
                    if($overtimepay<0){
                        $overtimepay = 0;
                    }
                    $standardpay = number_format($totalaccumulated,2) * $rate;
                    $regholidaybasicpay = $regholiday * $rate;
                    $regholidayotpay = $regholidayot * $overtimerate;
                    $regholidaypay = $regholidaybasicpay + $regholidayotpay;
                    $regholiday =  $regholiday + number_format($regholidayot);

                    $specholidaybasicpay = $specholiday * $rate;
                    $specholidayotpay = $specholidayot * $overtimerate;
                    $specholidaytotal = $specholidaybasicpay + $specholidayotpay;                           //generate salary
                    $specpercent = $specholidaytotal * 0.30;
                    $specholidaypay = $specpercent;
                    $specholiday = number_format($specholiday) + number_format($specholidayot);
                    $thirteenmonth = 0;
                    $laterate = floor($totallate * $ratelate);                                      
                    $totalgross = ($standardpay + $regholidaypay + $specholidaypay + $thirteenmonth + $overtimepay);
                    $totaldeduction = ($sss + $pagibig + $philhealth + $otheramount + $cashbond + $vale + $laterate + $violationdeduction);
                    $totalnetpay = $totalgross - $totaldeduction;
                    $salary_status="for release";
                    date_default_timezone_set('Asia/Manila');
                    $date = date('F j, Y h:i:s A');
                    $sql1="INSERT INTO `automatic_generated_salary`(`emp_id`, `total_hours`,`total_overtime`,`standard_pay`,`overtime_pay`,`regular_holiday`, 
                    `regular_holiday_pay`, `special_holiday`, `special_holiday_pay`, `thirteenmonth`, `sss`,`pagibig`,`philhealth`,`violation`, `cashbond`, `other`,
                    `other_amount`,`vale`, `total_hours_late`,`late_total`, `total_gross`, `total_deduction`, `total_netpay` ,`start`,`end`,`start_id`,`end_id`,`for_release`,`date_created`,`process_by`) 
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
                    $stmt1 = $this->con()->prepare($sql1);
                    $stmt1->execute([$empid,number_format($totalaccumulated,2),$totalovertime,$standardpay,$overtimepay,$regholiday,$regholidaypay,
                    $specholiday,$specholidaypay,$thirteenmonth,$sss,$pagibig,$philhealth,$violationdeduction,$cashbond,$other,
                    $otheramount,$vale,$total_hours_late,$laterate,$totalgross,$totaldeduction,$totalnetpay,$start,
                    $end,$startlog,$endlog,$salary_status,$date,$id]);
                    $CountRow01 = $stmt1 ->rowCount();
                        if($CountRow01 > 0){
                        }
                    }//pag walang attendance
                } //pag walang sched
            }//loop lahat
            $this->pdo = null;
            $this->releaseSalary($fullname,$id); //irerelease niya
        }//isset
        else if(isset($_POST['cancel'])){
            header('location: automaticpayroll.php');
        }
    }
    public function displayAutomaticGeneratedSalary()
    {
        $sql ="SELECT log, automatic_generated_salary.emp_id, automatic_generated_salary.start, automatic_generated_salary.end, employee.firstname, employee.lastname, automatic_generated_salary.date_created
        FROM automatic_generated_salary INNER JOIN employee WHERE automatic_generated_salary.emp_id = employee.empId AND for_release !='released'  ORDER BY date_created DESC;";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            while($row = $stmt->fetch()){
            echo "<tr>
            <td>$row->emp_id</td>
            <td>$row->firstname $row->lastname</td>
            <td>",date('M j, Y',strtotime($row->start)),"</td>
            <td>",date('M j, Y',strtotime($row->end)),"</td>
            <td>",date('M j, Y',strtotime($row->date_created)),"</td>
            <td><a href='viewautomatedsalary.php?logid=$row->log'>View </a><a href='deleteautomatedsalary.php?logid=$row->log'>Delete </a></td>
            </tr>";
            // $this->deleteSalary($row->log);
            }
            $this->pdo= null;
    }
    public function searchAutomaticGeneratedSalary()
    {
            if(isset($_POST['agssearch'])){
                $agssearch = strtolower($_POST['searchsalary']);
                if(!empty($agssearch)){
                    $sql ="SELECT * FROM automatic_generated_salary INNER JOIN employee WHERE for_release = 'released';";
                    $found=false;
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute();
                    $users = $stmt->fetchAll();
                    $countRow = $stmt->rowCount();
                    foreach($users as $user){
                    $lempid=strtolower($user->empId);
                    $lfirstname = strtolower($user->firstname);
                    $llastname = strtolower($user->lastname);
                    if(preg_match("/{$agssearch}/i", $lfirstname) || preg_match("/{$agssearch}/i", $llastname) || preg_match("/{$agssearch}/i", $lempid)){
                        echo "<tr>
                        <td>&nbsp;$user->empId&nbsp;</td>
                        <td>&nbsp;$user->firstname&nbsp;</td>
                        <td>&nbsp;$user->lastname&nbsp;</td>
                        <td>&nbsp;$user->total_gross&nbsp;</td>
                        <td>&nbsp;$user->total_deduction&nbsp;</td>
                        <td>&nbsp;$user->total_netpay&nbsp;</td>
                        <td>&nbsp;$user->date_released&nbsp;</td>
                        <td>&nbsp;<a href='viewemployee.php?empId=$user->empId'>View </a>&nbsp;</td>
                        <tr/>";
                        $found=true;
                    }
                    }
                    if($found!==true){
                    }
                    }else{
                    $this->displayreleasedsalary();
                    }
        }
        $this->pdo= null;
    }
    public function salarychart($empid,$end,$totnetpay){
        $sqlreport="SELECT * FROM salary_report WHERE empId = ?"; //salary report
        $stmtreport = $this->con()->prepare($sqlreport);
        $stmtreport->execute([$empid]);
        $ureport=$stmtreport->fetch();
        $CountRowreport = $stmtreport ->rowCount();
        $jan = 'january';
        $feb = 'february';
        $mar = 'march';
        $apr = 'april';
        $may = 'may';
        $jun = 'june';
        $jul = 'july';
        $aug = 'august';
        $sep = 'september';
        $oct = 'october';
        $nov = 'november';
        $dec = 'december';
        if($CountRowreport > 0)
        {  
            if(preg_match("/{$end}/i", $jan))
            {
                $sqlupdate="UPDATE salary_report SET january = ('january' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $feb)){
                $sqlupdate="UPDATE salary_report SET february = ('february' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $mar)){
                $sqlupdate="UPDATE salary_report SET march = ('march' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $apr)){
                $sqlupdate="UPDATE salary_report SET april = ('april' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $may)){
                $sqlupdate="UPDATE salary_report SET may = ('may' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $jun)){
                $sqlupdate="UPDATE salary_report SET june = ('june' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $jul)){
                $sqlupdate="UPDATE salary_report SET july = ('july' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $aug)){
                $sqlupdate="UPDATE salary_report SET august = ('august' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $sep)){
                $sqlupdate="UPDATE salary_report SET september = ('september' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $oct)){
                $sqlupdate="UPDATE salary_report SET october = ('october' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $nov)){
                $sqlupdate="UPDATE salary_report SET november = ('november' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else if (preg_match("/{$end}/i", $dec)){
                $sqlupdate="UPDATE salary_report SET december = ('december' + $totnetpay) WHERE empId = '$empid'";
                $stmtupdate = $this->con()->prepare($sqlupdate);
                $stmtupdate->execute();
            } else {

            } 
        } else {
            if(preg_match("/{$end}/i", $jan))
            {
                $sqlinsert="INSERT INTO salary_report (empId, $jan) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $feb)){
                $sqlinsert="INSERT INTO salary_report (empId, $feb) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $mar)){
                $sqlinsert="INSERT INTO salary_report (empId, $mar) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $apr)){
                $sqlinsert="INSERT INTO salary_report (empId, $apr) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $may)){
                $sqlinsert="INSERT INTO salary_report (empId, $may) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $jun)){
                $sqlinsert="INSERT INTO salary_report (empId, $jun) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $jul)){
                $sqlinsert="INSERT INTO salary_report (empId, $july) VALUES ('$empid', $jtotnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $aug)){
                $sqlinsert="INSERT INTO salary_report (empId, $aug) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $sep)){
                $sqlinsert="INSERT INTO salary_report (empId, $sep) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $oct)){
                $sqlinsert="INSERT INTO salary_report (empId, $oct) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $nov)){
                $sqlinsert="INSERT INTO salary_report (empId, $nov) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else if (preg_match("/{$end}/i", $dec)){
                $sqlinsert="INSERT INTO salary_report (empId, $dec) VALUES ('$empid', $totnetpay);";
                $stmtinsert = $this->con()->prepare($sqlinsert);
                $stmtinsert->execute();
            } else {
            } 
        }
        $this->pdo= null;
    }
    public function releaseSalary($fullname,$id)
    {
            $sqlall="SELECT * FROM automatic_generated_salary WHERE for_release='for release';";
            $stmtall = $this->con()->prepare($sqlall);
            $stmtall->execute();
            $userall=$stmtall->fetchall();
            $CountRowall = $stmtall ->rowCount();
            $number=0;
            $this->pdo = null;
            if($CountRowall > 0){
            foreach($userall as $all){
                $pdfsarray[]=$all->log;
                $logid=$all->log;
                $sql = "SELECT * FROM automatic_generated_salary WHERE log = ? AND for_release='for release';";
                $stmt = $this->con()->prepare($sql);
                $stmt->execute([$logid]);
                $user=$stmt->fetch();
                $empid = $user->emp_id;
                $end = date('F',strtotime($user->end));
                $totnetpay = $user->total_netpay;
                $currsss = $user->sss;
                $currpagibig =$user->pagibig;
                $currphilhealth =$user->philhealth;
                $currcashbond =$user->cashbond;
                $this->pdo = null;
                if(strtolower($user->for_release)=='**not for release!')
                {
                    echo "Not for release";
                    header('location: automaticpayroll.php');
                }else{
                        $sql1="UPDATE automatic_generated_salary SET for_release = 'for email' WHERE log = $logid;";
                        $stmt1 = $this->con()->prepare($sql1);
                        $stmt1->execute();
                        $CountRow01 = $stmt1 ->rowCount();
                        $this->pdo = null;
                    if($CountRow01>0){
                        $status='unpaid';
                        $sql2="UPDATE emp_attendance SET salary_status = 'paid' WHERE empId = ? AND salary_status = ?;";
                        $stmt2 = $this->con()->prepare($sql2);
                        $stmt2->execute([$user->emp_id,$status]);
                        $CountRow02 = $stmt2 ->rowCount();
                        $this->pdo = null;
                        if($CountRow02>0){
                            $sql3="UPDATE cashadvance SET status = 'paid' WHERE empId = '$user->emp_id' AND status IS NULL;";
                            $stmt3 = $this->con()->prepare($sql3);
                            $stmt3->execute();
                            $this->pdo = null;
                            $sqlcheckifhas="SELECT * FROM contributions WHERE empId = '$user->emp_id';";
                            $stmtcheck = $this->con()->prepare($sqlcheckifhas);
                            $stmtcheck->execute();
                            $CountRowcheck = $stmtcheck ->rowCount();
                            $this->pdo = null;
                            if($CountRowcheck > 0 ){
                                $sql4="UPDATE contributions SET sss = sss + $currsss, pagibig = pagibig + $currpagibig, 
                                philhealth =  philhealth + $currphilhealth , cashbond = cashbond + $currcashbond, 
                                date = CURRENT_TIMESTAMP() WHERE empId = '$user->emp_id';";
                                $stmt4 = $this->con()->prepare($sql4);
                                $stmt4->execute();
                                $this->pdo = null;
                            }elseif($CountRowcheck == 0) {
                                $sql5="INSERT INTO contributions (empId,sss,pagibig,philhealth,cashbond,date) VALUES (?,?,?,?,?,CURRENT_TIMESTAMP());";
                                $stmt5 = $this->con()->prepare($sql5);
                                $stmt5->execute([$user->emp_id,$user->sss,$user->pagibig,$user->philhealth,$user->cashbond]);
                                $this->pdo = null;
                                $sqlvio="UPDATE violationsandremarks SET paid = 'paid' WHERE empId= '$user->emp_id' AND description = 'uniform' AND paid='unpaid'";
                                $stmtvio = $this->con()->prepare($sqlvio);
                                $stmtvio->execute();
                            }else{
                                echo "error";
                            }   
                        }
                    }
                    $this->salarychart($empid,$end,$totnetpay);
                    }
                    $number += 1;
                }//all
                $action = "Generate ".$number." Salary";
                $secdatetime = $this->getDateTime();
                $sectime = $secdatetime['time'];
                $secdate = $secdatetime['date'];
                $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                VALUES(?, ?, ?, ?, ?)";
                $stmtSecLog = $this->con()->prepare($sqlSecLog);
                $stmtSecLog->execute([$id,$fullname, $action, $sectime, $secdate]);
                $countRowSecLog = $stmtSecLog->rowCount();
                if($countRowSecLog > 0){
                } else {
                    echo 'Error in Generating Salary';
                }
                $this->pdo= null;
                ob_start();
                $this->emailpdf();
            }else {
                header('location: automaticpayroll.php?message=There is no salary to compute');
            }
    }
    public function deleteautomatedsalary($logid)
    {
        if(isset($_POST['deleteauto'])){
        $sql = "DELETE FROM automatic_generated_salary WHERE log = ?;";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$logid]);
        $countrow = $stmt->rowCount();
        if($countrow > 0) {
        $action = "Delete Automated Salary";
        $sessionData = $this->getSessionSecretaryData();
        $fullname = $sessionData['fullname'];
        $secid = $sessionData['id'];
        $datetime = $this->getDateTime();
        $time = $datetime['time'];
        $date = $datetime['date'];
            $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                VALUES(?, ?, ?, ?, ?)";
            $stmtSecLog = $this->con()->prepare($sqlSecLog);
            $stmtSecLog->execute([$secid,$fullname, $action, $time, $date]);
            $countRowSecLog = $stmtSecLog->rowCount();
                if($countRowSecLog > 0){
                    echo 'pumasok na sa act log';
                    header('location:automaticpayroll.php');
                } else {
                    echo 'di pumasok sa act log';
                    header('location:automaticpayroll.php');
                }
            } else {
                echo 'Error in deleting !';
            }
        }
        else if(isset($_POST['cancel'])){
            header('location: automaticpayroll.php');
        }else{
        }
        $this->pdo= null;
    }
    public function adddeduction($fullname,$id)
    {
            if(isset($_POST['generatededuction']))
            {   
                $_POST['deduction']="other";
                $countrow = 0;
                $deduction = strtolower($_POST['deduction']);
                $name = $_POST['name'];
                $amount = $_POST['amount'];
                $percentage = (float)$_POST['percentage'];
                $sqlcheck="SELECT * FROM deductions WHERE deduction = ?";
                $stmt = $this->con()->prepare($sqlcheck);
                $stmt->execute([$deduction]);
                $countrowc = $stmt->rowCount();
                $user = $stmt->fetch();
                if ($countrowc > 0)
                {   
                    if($deduction != 'other'){
                        $percentage = (float)$_POST['percentage'];
                        $sql="UPDATE deductions SET deduction = ?, percentage = ? WHERE id = $user->id;";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$deduction,$percentage]);
                        $countrow = $stmt->rowCount();
                        if($countrow>0){
                            echo "Succesfully Updated";
                            header('location: deductions.php');
                        }
                    }
                        if($countrow > 0) {
                        $action = "Edit Deduction";
                        $datetime = $this->getDateTime();
                        $time = $datetime['time'];
                        $date = $datetime['date'];
                        $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                            VALUES(?, ?, ?, ?, ?)";
                        $stmtSecLog = $this->con()->prepare($sqlSecLog);
                        $stmtSecLog->execute([$id,$fullname, $action, $time, $date]);
                        $countRowSecLog = $stmtSecLog->rowCount();
                        if($countRowSecLog > 0){
                            echo 'Succesfully Added';
                        } else {
                            echo 'Error in Adding';
                            header('location:deductions.php');
                        }
                    }
                }
                else
                {
                    if($deduction == 'other')
                    {   
                        $sqlcheck2="SELECT * FROM deductions WHERE deduction = ?";
                        $stmt2 = $this->con()->prepare($sqlcheck2);
                        $stmt2->execute([$name]);
                        $user2 = $stmt2->fetch();
                        $countrow2 = $stmt2->rowCount();
                        if($countrow2 > 0)
                        {
                            $sqla="UPDATE deductions SET deduction = ?, amount = ? WHERE id = $user2->id;";
                            $stmta = $this->con()->prepare($sqla);
                            $stmta->execute([$name,$amount]);
                            $countrowa = $stmta->rowCount();
                            if( $countrowa > 0) {
                                $action = "Edit Deduction";
                                $datetime = $this->getDateTime();
                                $time = $datetime['time'];
                                $date = $datetime['date'];
                                $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                                    VALUES(?, ?, ?, ?, ?)";
                                $stmtSecLog = $this->con()->prepare($sqlSecLog);
                                $stmtSecLog->execute([$id,$fullname, $action, $time, $date]);
                                $countRowSecLog = $stmtSecLog->rowCount();
                                if($countRowSecLog > 0){
                                    header('location:deductions.php');
                                } else {
                                    echo 'di pumasok sa act log';
                                    header('location:deductions.php');
                                }
                            }
                        }else
                        {
                            $name = $_POST['name'];
                            $amount = $_POST['amount'];
                            $sqla="INSERT INTO  deductions (`deduction`,`amount`) VALUES (?,?);";
                            $stmta = $this->con()->prepare($sqla);
                            $stmta->execute([$name,$amount]);
                            $countrowa = $stmta->rowCount();
                            if( $countrowa > 0) {
                                $action = "Add Deduction";
                                $datetime = $this->getDateTime();
                                $time = $datetime['time'];
                                $date = $datetime['date'];
                                $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                                    VALUES(?, ?, ?, ?, ?)";
                                $stmtSecLog = $this->con()->prepare($sqlSecLog);
                                $stmtSecLog->execute([$id,$fullname, $action, $time, $date]);
                                $countRowSecLog = $stmtSecLog->rowCount();
                                if($countRowSecLog > 0){
                                    header('location:deductions.php');
                                } else {
                                    echo 'di pumasok sa act log';
                                    header('location:deductions.php');
                                }
                            }
                        }
                    }
                    else 
                    {
                        $percentage = (float)$_POST['percentage'];
                        $sql="INSERT INTO  deductions (`deduction`,`percentage`) VALUES (?,?);";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$deduction,$percentage]);
                        $countrow = $stmt->rowCount();
                    }
                        if($countrow > 0) {
                        $action = "Add Deduction";
                        $datetime = $this->getDateTime();
                        $time = $datetime['time'];
                        $date = $datetime['date'];
                        $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                            VALUES(?, ?, ?, ?, ?)";
                        $stmtSecLog = $this->con()->prepare($sqlSecLog);
                        $stmtSecLog->execute([$id,$fullname, $action, $time, $date]);
                        $countRowSecLog = $stmtSecLog->rowCount();
                        if($countRowSecLog > 0){
                            header('location:deductions.php');
                        } else {
                            echo 'di pumasok sa act log';
                            header('location:deductions.php');
                        }
                    }
                }
            }//isset
            else if(isset($_POST['cancelded'])){
                header('location: deductions.php');
            }
            $this->pdo= null;
    }
    public function deletededuction($logid)
    {
        if(isset($_POST['deletededuction']))
        {
            $sql = "DELETE FROM deductions WHERE id = ?;";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$logid]);
            $countrow = $stmt->rowCount();
            if($countrow > 0) {
            $action = "Delete Deduction";
            $sessionData = $this->getSessionSecretaryData();
            $fullname = $sessionData['fullname'];
            $secid = $sessionData['id'];
            $datetime = $this->getDateTime();
            $time = $datetime['time'];
            $date = $datetime['date'];
            $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                VALUES(?, ?, ?, ?, ?)";
            $stmtSecLog = $this->con()->prepare($sqlSecLog);
            $stmtSecLog->execute([$secid,$fullname, $action, $time, $date]);
            $countRowSecLog = $stmtSecLog->rowCount();
                if($countRowSecLog > 0){
                    echo 'pumasok na sa act log';
                    header('location:deductions.php');
                } else {
                    echo 'di pumasok sa act log';
                    header('location:deductions.php');
                }
            } else {
                echo 'Error in deleting deduction!';
            }
        }
        else if(isset($_POST['cancel']))
        {
            header('location: deductions.php');
        }else{
        }
        $this->pdo= null;
    }
    public function displaydeduction()
    {
        $sql="SELECT * FROM deductions;";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchall();
        $count=$stmt->rowCount();

        foreach($rows as $row)
        {
            echo "<tr>
            <td>$row->deduction</td>
            <td>$row->amount $row->percentage </td>
            <td></td>
            <td class='td-action'>
                <div class='ic ic__edit'>
                    <a href='editdeduction.php?logid=$row->id' class='td-edit'>
                        <span class='material-icons'>edit</span>
                    </a>
                </div>
                &emsp;&emsp;
                <div class='ic ic__delete'>
                    <a href='deletededuction.php?logid=$row->id' class='td-delete'>
                        <span class='material-icons'>delete</span>
                    </a>
                </div>
            </td>
            </tr>";
            $this->deletededuction($row->id);
        }
        $this->pdo= null;
    }
    public function cashadvance($fullname,$id)
    {
        if(isset($_POST['add'])){
                if(!empty($_POST['amount'])){

                $empid = $_POST['empid'];
                $amount = $_POST['amount'];
                date_default_timezone_set('Asia/Manila');
                $date = date('F j, Y');
                $sql1="SELECT * FROM cashadvance WHERE empId = ?;";
                $stmt1 = $this->con()->prepare($sql1);
                $stmt1->execute([$empid]);
                $users1=$stmt1->fetchall();
                $countrow1=$stmt1->rowCount();
                if($countrow1 > 0)
                {
                        $totadv=0;
                        foreach($users1 as $user){
                        $totadv+= $user->amount;
                                                }
                        $totadv=$totadv + $amount;
                        if($amount <= 3000){
                            if($totadv<=3000){
                                $sql="INSERT INTO cashadvance (`empId`,`date`,`amount`) VALUES (?,?,?);";
                                $stmt = $this->con()->prepare($sql);
                                $stmt->execute([$empid,$date,$amount]);
                                $countrow = $stmt->rowCount();
                                if($countrow > 0) {
                                $action = "Add Cash Advance";
                                $datetime = $this->getDateTime();
                                $time = $datetime['time'];
                                $date = $datetime['date'];
                                $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                                VALUES(?, ?, ?, ?, ?)";
                                $stmtSecLog = $this->con()->prepare($sqlSecLog);
                                $stmtSecLog->execute([$id,$fullname, $action, $time, $date]);
                                $countRowSecLog = $stmtSecLog->rowCount();
                        if($countRowSecLog > 0){
                            echo 'Succesfully Added';
                        } else {
                            echo 'di pumasok sa act log';
                            header('location:deductions.php');
                        }
                    }
                    }else {
                        echo "Maximum Cash Advance: 3,000 only";
                    }
                    } else{
                        echo "Maximum Cash Advance: 3,000 only";
                    }
                }
                else{
                        if($amount <= 3000)
                        {
                            $sql="INSERT INTO cashadvance (`empId`,`date`,`amount`) VALUES (?,?,?);";
                            $stmt = $this->con()->prepare($sql);
                            $stmt->execute([$empid,$date,$amount]);
                            $countrow = $stmt->rowCount();
                            if($countrow > 0) 
                            {
                                $action = "Add Cash Advance";
                                $datetime = $this->getDateTime();
                                $time = $datetime['time'];
                                $date = $datetime['date'];
                                $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                                VALUES(?, ?, ?, ?, ?)";
                                $stmtSecLog = $this->con()->prepare($sqlSecLog);
                                $stmtSecLog->execute([$id,$fullname, $action, $time, $date]);
                                $countRowSecLog = $stmtSecLog->rowCount();
                                if($countRowSecLog > 0)
                                {
                                echo 'Succesfully Added';
                                } else 
                                {
                                echo 'di pumasok sa act log';
                                header('location:deductions.php');
                                }
                            }
                        }
                    }  
                }//empty
        }//isset
        else if(isset($_POST['cancel'])){
            header('location: deductions.php');
        }
        $this->pdo= null;
    }
    public function deletecashadv($logid)
    {
        if(isset($_POST['deletecashadv'])){
        $sql = "DELETE FROM cashadvance WHERE id = ?;";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$logid]);
        $countrow = $stmt->rowCount();
        if($countrow > 0) {
        $action = "Delete Cash Advance";
        $sessionData = $this->getSessionSecretaryData();
        $fullname = $sessionData['fullname'];
        $secid = $sessionData['id'];
        $datetime = $this->getDateTime();
        $time = $datetime['time'];
        $date = $datetime['date'];
            $sqlSecLog = "INSERT INTO secretary_log (sec_id, name, action, time, date)
                                VALUES(?, ?, ?, ?, ?)";
            $stmtSecLog = $this->con()->prepare($sqlSecLog);
            $stmtSecLog->execute([$secid,$fullname, $action, $time, $date]);
            $countRowSecLog = $stmtSecLog->rowCount();
                if($countRowSecLog > 0){
                    echo 'pumasok na sa act log';
                    header('location:deductions.php');
                } else {
                    echo 'di pumasok sa act log';
                    header('location:deductions.php');
                }
            } else {
                echo 'Error in deleting cash advance!';
            }
        }
        else if(isset($_POST['cancel'])){
            header('location: deductions.php');
        }else{
        }
        $this->pdo= null;
    }
    public function displaycashadvance()
    {
        $sql="SELECT cashadvance.id, cashadvance.date, cashadvance.amount, employee.firstname, employee.lastname FROM cashadvance INNER JOIN employee ON cashadvance.empId = employee.empId WHERE status IS NULL;";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        while($row = $stmt->fetch()){
        echo "<tr>
        <td>$row->firstname $row->lastname</td>
        <td>$row->date</td>
        <td>$row->amount</td>
        <td class='td-action'>
                <div class='ic ic__delete'>
                    <a href='deletecashadv.php?logid=$row->id' class='td-delete'>
                        <span class='material-icons'>delete</span>
                    </a>
                </div>
            </td>
        </tr>";
        $this->deletecashadv($row->id);
        }
        $this->pdo= null;
    }
    public function displayschedule()
    {
        $sql="SELECT * FROM schedule INNER JOIN employee WHERE employee.empId = schedule.empId;";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        while($row = $stmt->fetch()){
            echo "<tr>
            <td>$row->empId</td>
            <td>$row->firstname</td>
            <td>$row->lastname</td>
            <td>$row->company</td>
            <td>$row->scheduleTimeIn</td>
            <td>$row->scheduleTimeOut</td>
            <td>$row->expiration_date</td>
            </tr>";
            }
            $this->pdo= null;
    }
    public function searchsched(){
        if(isset($_POST['searchsched'])){
            $ssched = $_POST['sched'];
            $sql="SELECT * FROM schedule INNER JOIN employee WHERE employee.empId = schedule.empId;";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute();
            $found=false;
            while($row = $stmt->fetch()){
                if(preg_match("/{$ssched}/i", $row->empId) || preg_match("/{$ssched}/i", $row->firstname) || preg_match("/{$ssched}/i", $row->lastname) || 
                preg_match("/{$ssched}/i", $row->company) || preg_match("/{$ssched}/i", $row->scheduleTimeIn) || preg_match("/{$ssched}/i", $row->scheduleTimeOut))
                {   
                    $found=true;
                    echo "<tr>
                    <td>$row->empId</td>
                    <td>$row->firstname</td>
                    <td>$row->lastname</td>
                    <td>$row->company</td>
                    <td>$row->scheduleTimeIn</td>
                    <td>$row->scheduleTimeOut</td>
                    <td>$row->expiration_date</td>
                    </tr>";
                }
                }
                if($found==false){
                    echo "<tr><td>No Result<td><td><td><td><td><td></tr>";
                }

        }
    }
    public function displayviolations()
    {
        $sql="SELECT * FROM violationsandremarks INNER JOIN employee WHERE employee.empId = violationsandremarks.empId;";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        while($row = $stmt->fetch()){
            echo "<tr>
            <td>$row->empId</td>
            <td>$row->firstname</td>
            <td>$row->lastname</td>
            <td>$row->violation</td>
            <td>$row->remark</td>
            <td>$row->date_created</td>
            </tr>";
        }
    }
    public function searchviolation(){
        if(isset($_POST['searchvio']))
        {   
            $found=false;
            if(!empty($_POST['svio']))
            {   
                $search=$_POST['svio'];
                $sql="SELECT * FROM violationsandremarks LEFT JOIN employee ON employee.empId = violationsandremarks.empId;";
                $stmt = $this->con()->prepare($sql);
                $stmt->execute();
                $rows=$stmt->fetchall();
                foreach($rows as $row)
                {
                    if(preg_match("/{$search}/i", strtolower($row->firstname)) || preg_match("/{$search}/i", strtolower($row->firstname)) || preg_match("/{$search}/i", date('Y/m/j',strtotime($row->date_created)))){
                        $found = true;
                        echo "<tr>
                        <td>$row->empId</td>
                        <td>$row->firstname</td>
                        <td>$row->lastname</td>
                        <td>$row->violation</td>
                        <td>$row->remark</td>
                        <td>$row->date_created</td>
                        </tr>";
                    }
            

                }
                    if(!$found){
                        echo "<tr><td>No Result<td><td><td><td><td></tr>";
                    }
            } else {
                $this->displayviolations();
            }
        }
    }
    public function displayreleasedsalary()
    {
        $sql="SELECT * FROM automatic_generated_salary INNER JOIN employee WHERE automatic_generated_salary.emp_id = employee.empId 
        AND automatic_generated_salary.for_release = 'released';";
        $stmt=$this->con()->prepare($sql);
        $stmt->execute();
        while($row = $stmt->fetch()){
            
            echo "<tr>
            <td>$row->empId</td>
            <td>$row->firstname</td>
            <td>$row->lastname</td>
            <td>",number_format($row->total_gross),"</td>
            <td>",number_format($row->total_deduction),"</td>
            <td>",number_format($row->total_netpay),"</td>
            <td>".date('M j, Y-h:i A',strtotime($row->date_released))."</td>
            <td><a href='viewautomatedsalary.php?logid=$row->log'>View</td>
            </tr>";

        }
        $this->pdo=null;
    }
    public function searchreleasedsalary()
    {
        if(isset($_POST['searchreleased']))
        {   
            if(!empty($_POST['salary']))
            {
                $salary = strtolower($_POST['salary']);
                $sql="SELECT * FROM automatic_generated_salary LEFT JOIN employee ON automatic_generated_salary.emp_id = employee.empId WHERE automatic_generated_salary.emp_id = employee.empId 
                AND automatic_generated_salary.for_release = 'released';";
                $stmt=$this->con()->prepare($sql);
                $stmt->execute();
                $user=$stmt->fetchall();
                $found = false;
                foreach($user as $all)
                {
                    if(preg_match("/{$salary}/i", $all->firstname)||preg_match("/{$salary}/i", $all->lastname)||
                       preg_match("/{$salary}/i", $all->empId)||preg_match("/{$salary}/i", $all->date_released))
                    {
                        $found=true;
                        echo "<tr>
                        <td>$all->empId</td>
                        <td>$all->firstname</td>
                        <td>$all->lastname</td>
                        <td>",number_format($all->total_gross),"</td>
                        <td>",number_format($all->total_deduction),"</td>
                        <td>",number_format($all->total_netpay),"</td>
                        <td>$all->date_released</td>
                        <td><a href='viewautomatedsalary.php?logid=$all->log'>View</td>
                        </tr>";            
                    } else{
                       
                    }
                }
                    if(!$found)
                    {
                        echo "<tr><td>No Record Found!<td><td><td><td><td><td></tr>";
                    }
            }else
            {
                $this->displayreleasedsalary();
            }
        }
    }
    public function salaryreport()
    {
        $sql="SELECT * FROM salary_report INNER JOIN employee WHERE salary_report.empId = employee.empId; AND status!='paid'";
        $stmt=$this->con()->prepare($sql);
        $stmt->execute();
        while($row = $stmt->fetch()){
           if($row->january < 0){
               $row->january = 0;
           }
            if($row->february < 0){
                $row->february = 0;
            }
            if($row->march<0){
                $row->march = 0;
            }
            if($row->april<0){
                $row->april = 0;
            }
            if($row->may<0){
                $row->may=0;
            }
            if($row->june<0){
                $row->june=0;
            }
            if($row->july<0){
                $row->july=0;
            }
            if($row->august<0){
                $row->august=0;
            }
            if($row->september<0){
                $row->september=0;
            }
            if($row->october<0){
                $row->october=0;
            }
            if($row->november<0){
                $row->november=0;
            }
            if($row->december<0){
                $row->december=0;
            }
            echo "<tr>
                <td>$row->firstname $row->lastname</td>
                <td>".number_format($row->january)."</td>
                <td>".number_format($row->february)."</td>
                <td>".number_format($row->march)."</td>
                <td>".number_format($row->april)."</td>
                <td>".number_format($row->may)."</td>
                <td>".number_format($row->june)."</td>
                <td>".number_format($row->july)."</td>
                <td>".number_format($row->august)."</td>
                <td>".number_format($row->september)."</td>
                <td>".number_format($row->october)."</td>
                <td>".number_format($row->november)."</td>
                <td>".number_format($row->december)."</td>
                </tr>";
        }
    }
    public function displaycontributions()
    {
        $sql="SELECT * FROM contributions INNER JOIN employee WHERE contributions.empId = employee.empId;";
        $stmt=$this->con()->prepare($sql);
        $stmt->execute();

        while($row = $stmt->fetch()){
            echo "<tr>
            <td>$row->firstname $row->lastname</td>
            <td>$row->sss</td>
            <td>$row->pagibig</td>
            <td>$row->philhealth</td>
            <td>$row->cashbond</td>
            </tr>";
        }
    }
    public function searchcontribution()
    {
        if(isset($_POST['searchcon']) && !empty($_POST['emp']))
        {   
            $found=false;
            $emp=$_POST['emp'];
            $sql="SELECT * FROM contributions INNER JOIN employee ON contributions.empId = employee.empId;";
            $stmt=$this->con()->prepare($sql);
            $stmt->execute();
            while($users=$stmt->fetch())
            {
                if(preg_match("/{$emp}/i", $users->empId) || preg_match("/{$emp}/i", $users->firstname) ||
                preg_match("/{$emp}/i", $users->lastname))
                {
                    $found=true;
                    $sqls="SELECT * FROM contributions INNER JOIN employee ON contributions.empId = employee.empId WHERE contributions.empId = $users->empId;";
                    $stmts=$this->con()->prepare($sqls);
                    $stmts->execute();
                    $userss=$stmts->fetchall();
                    foreach($userss as $row){
                        echo "<tr>
                        <td>$row->firstname $row->lastname</td>
                        <td>$row->sss</td>
                        <td>$row->pagibig</td>
                        <td>$row->philhealth</td>
                        <td>$row->cashbond</td>
                        </tr>";
                    }
                    if($found){
                        echo $users->firstname;
                    }

                }

            }
            if($found == false){
                echo "<tr><td>No Record Found!<td><td><td><td></tr>";
            }

        } else {
            $this->displaycontributions();
        }
    }
    public function displayempattendance($fullname,$id){
        $sql="SELECT * FROM employee;";
        $stmt=$this->con()->prepare($sql);
        $stmt->execute();
            while($user=$stmt->fetch())
            {   
                $has=false;
                $tothrs=0;              // ex may 4 attendance siya
                $OTtohrs=0;
                $sql1="SELECT 
                        ea.*,
                        s.scheduleTimeIn
                    FROM emp_attendance ea 
                    
                    LEFT JOIN schedule s
                    ON ea.empId = s.empId
                    
                    WHERE ea.empId = '$user->empId' AND ea.salary_status != 'paid';";
                $stmt1=$this->con()->prepare($sql1);
                $stmt1->execute();
                $countattendance=$stmt1->rowCount();
                if($countattendance > 0){
                    $has=true;
                }else{
                    $has=false;
                }
                    $users1=$stmt1->fetchall();
                    foreach($users1 as $users) {
                        $timein= strtotime($users->timeIn);
                        $timeout= strtotime($users->timeOut);
                        $StandardSchedule = strtotime($users->scheduleTimeIn) + 8*60*60;

                        $tothrs += ($timeout - $timein) / (60*60) ; //important
                        $OTtohrs += ($timeout - $StandardSchedule) / (60*60); //important
                        $Standardtohrs = $tothrs - $OTtohrs; //important
                        $StandardRate = $user->ratesperDay; //Important
                        $OvertimeRate = $user->overtime_rate; //Important
                        $StandardPay = $Standardtohrs * $StandardRate; //Important
                        $OvertimePay = $OTtohrs * $OvertimeRate; //Important
                        $GrossPay = $StandardPay + $OvertimePay; //Important
                    }
                    if($has){
                        $OTtohrsShow = sprintf('%02d hrs, %02d min/s', (int) $OTtohrs, fmod($OTtohrs   , 1) * 60);
                        $tothrsShow = sprintf('%02d hrs, %02d min/s', (int) $tothrs, fmod($tothrs   , 1) * 60);
                        $StandardtohrsShow = sprintf('%02d hrs, %02d min/s', (int) $Standardtohrs, fmod($Standardtohrs   , 1) * 60);
                        $StandardPayShow = number_format($StandardPay, 2);
                        $OvertimePayShow = number_format($OvertimePay, 2);
                        $GrossPayShow = number_format($GrossPay, 2);

                        // echo $OTtohrs.'<br>';

                        echo "<tr>
                                <td>$user->empId</td>
                                <td>$user->firstname $user->lastname</td>
                                <td>$countattendance</td>
                            </tr>";
                           
                    }
            }
    }
    // public function searchempatt($fullname,$id){  //generateauto
    //     if(isset($_POST['searchempatt']) && !empty($_POST['emp']))
    //     {   
    //         $found=false;
    //         $emp=$_POST['emp'];
    //         $sqls="SELECT * FROM employee;";
    //         $stmts=$this->con()->prepare($sqls);
    //         $stmts->execute();
    //         while($users=$stmts->fetch())
    //         {            // ex may 4 attendance siya
    //             if(preg_match("/{$emp}/i", $users->empId) || preg_match("/{$emp}/i", $users->firstname) ||
    //             preg_match("/{$emp}/i", $users->lastname))
    //             {   
    //                 $found=true;
    //                 $tothrss=0;
    //                 $foundempid=$users->empId;
    //                 $foundname= $users->firstname." ".$users->lastname;
    //                 $sql1s="SELECT * FROM emp_attendance WHERE empId = $users->empId AND salary_status != 'paid';";
    //                 $stmt1s=$this->con()->prepare($sql1s);
    //                 $stmt1s->execute();
    //                 $countattendances=$stmt1s->rowCount();
    //                 $users1s=$stmt1s->fetchall();
    //                 foreach($users1s as $userss)
    //                 {
    //                     $timeins= date('H:i:s',strtotime($userss->timeIn));
    //                     $timeouts= date('H:i:s',strtotime($userss->timeOut));
    //                     $tothrss += abs(strtotime($timeins) - strtotime($timeouts)) / 3600 ;
    //                 }
    //             }
    //         }   

                
    //             if($found){
    //                 $tothrss=number_format($tothrss,2);
    //                 $tothrss = sprintf('%02d:%02d', (int) $tothrss, fmod($tothrss   , 1) * 60);
    //             echo "<tr>
    //             <td>$foundempid</td>
    //             <td>$foundname</td>
    //             <td>$countattendances</td>
    //             <td>$tothrss</td>
    //             <td> <a href='createsalary.php?empid=$foundempid'>Generate</a></td>
    //             </tr>";
    //             }else if($found==false){
    //                 echo "No Record Found!";
    //                 $this->displayempattendance($fullname,$id);
    //             }
    //     }else{
    //         $this->displayempattendance($fullname,$id);
    //     }
    // }
    public function generatepdf($id){
        if(isset($_POST['download'])){
        $sql = "SELECT *
        FROM automatic_generated_salary
        INNER JOIN employee ON automatic_generated_salary.emp_id = employee.empId
        WHERE automatic_generated_salary.log = ?;";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $rows = $stmt->fetch();

        $dompdf = new Dompdf();
        $path = '../img/icon.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $payslip = "<!DOCTYPE html>
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
          
        .column {
          float: left;
          width: 48.5%;
          padding: 5px;
        }
        .row::after {
          content: '';
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
        <body><img src='$base64' type='' class='viewautomatedsalary-logo' width='100' height='100'
        style='float:right; margin-left:-200px; margin-right: 70px; margin-top: 20px'></img>
        <center><h2>JTDV SECURITY AGENCY</h2>
        <p><u>400 Gem Bldg.,Gen T De Leon Ave.<br/>Barangay Gen T. De Leon, Valenzuela City</u></p></center>

        <div class='row'>
          <div class='column'>
            Employee ID: $rows->empId <br/>
            Employee Name: $rows->firstname  $rows->lastname <br/>
            Position: $rows->position
            <table>
              <tr>
                <th>Earnings</th>
                <th>Hours</th>
                <th>Rate</th>
                <th>&nbsp;</th>
              </tr>
              <tr>
                <td>Basic Pay</td>
                <td>".$rows->total_hours."</td>
                <td>$rows->ratesperDay</td>
                <td>".number_format($rows->standard_pay,2)."</td>
              </tr>
              <tr>
              <td>Overtime</td>
              <td>".$rows->total_overtime."</td>
              <td>$rows->overtime_rate</td>
              <td>".number_format($rows->overtime_pay,2)."</td>
              </tr>
              <tr>
                <td>Regular Holiday</td>
                <td></td>
                <td></td>
                <td>".number_format($rows->regular_holiday_pay,2)."</td>
              </tr>
              <tr>
                <td>Special Holiday</td>
                <td></td>
                <td></td>
                <td>$rows->special_holiday_pay</td>
              </tr>
              <tr>
                <td>13Month</td>
                <td>$rows->thirteenmonth</td>
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
                <td>".number_format($rows->total_gross,2)."</td>
              </tr>
            </table>
            <h3><u>Total Netpay: ".number_format($rows->total_netpay)."</u></h3>
          </div>
          <div class='column'>
              Email: $rows->email<br/>
              Contact: $rows->cpnumber<br/>
              Date: $rows->date_created
            <table>
              <tr>
                <th>Deductions</th>
                <th>Mins</th>
                <th>Rate</th>
                <th>&nbsp;</th>
              </tr>
              <tr>
                <td>Late</td>
                <td>$rows->total_hours_late</td>
                <td>".number_format($rows->ratesperDay / 60,3)."</td>
                <td>$rows->late_total</td>
              </tr>
              <tr>
                <td>SSS</td>
                <td></td>
                <td></td>
                <td>$rows->sss</td>
              </tr>
              <tr>
                <td>Pagibig</td>
                <td></td>
                <td></td>
                <td>$rows->pagibig</td>
              </tr>
              <tr>
                <td>Philhealth</td>
                <td></td>
                <td></td>
                <td>$rows->philhealth</td>
              </tr>
              <tr>
                <td>Cash Bond</td>
                <td></td>
                <td></td>
                <td>$rows->cashbond</td>
              </tr>";
              $payslip.=
              ($rows->other_amount > 0)?
                "<tr>
                <td>$rows->other</td>
                <td></td>
                <td></td>
                <td>$rows->other_amount</td>
                <td></td>
              </tr>":"";
              $payslip.=
              ($rows->vale > 0)?
              "<tr>
              <td>Cash Advance</td>
              <td></td>
              <td></td>
              <td>".number_format($rows->vale)."</td>
              </tr>":"";
              $payslip.=
              ($rows->violation > 0)?
              "<tr>
              <td>Violations</td>
              <td></td>
              <td></td>
              <td>".number_format($rows->violation)."</td>
              </tr>
              <tr>":"";
              $payslip.="
              <tr>
                <td>Total Deduction</td>
                <td></td>
                <td></td>
                <td>".number_format($rows->total_deduction,2)."</td>
              </tr>
            </table>
            <h3>Salary From: $rows->start  - $rows->end </h3>
          </div>
        </div>
        </body>
        </html>
        ";
        $pdfname = $rows->firstname .' '. $rows->lastname;
        $dompdf->loadHtml($payslip);
        $dompdf->set_option('isRemoteEnabled', TRUE);
        // (Optional) Setup the paper size and orientation
        $customPaper = array(0,0,1000,600);
        $dompdf->set_paper($customPaper);
        
        $dompdf->render();
        ob_end_clean();
        $file = $dompdf->output();
        $dompdf->stream($pdfname);
        }
        $this->pdo= null;
    }
    public function generatemanualpdf($id){
        if(isset($_POST['download'])){
            $sql = "SELECT *
            FROM generated_salary
            INNER JOIN employee ON generated_salary.emp_id = employee.empId
            WHERE generated_salary.log = ?;";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$id]);
            $rows = $stmt->fetch();
    
            $dompdf = new Dompdf();
            $path = '../img/icon.png';
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $payslip = "<!DOCTYPE html>
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
              
            .column {
              float: left;
              width: 48.5%;
              padding: 5px;
            }
            .row::after {
              content: '';
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
            <body><img src='$base64' type='' class='viewautomatedsalary-logo' width='100' height='100'
            style='float:right; margin-left:-200px; margin-right: 70px; margin-top: 20px'></img>
            <center><h2>JTDV SECURITY AGENCY</h2>
            <p><u>400 Gem Bldg.,Gen T De Leon Ave.<br/>Barangay Gen T. De Leon, Valenzuela City</u></p></center>
    
            <div class='row'>
              <div class='column'>
                Employee ID: $rows->empId <br/>
                Employee Name: $rows->firstname  $rows->lastname <br/>
                Position: $rows->position
                <table>
                  <tr>
                    <th>Earnings</th>
                    <th>Hours</th>
                    <th>Rate</th>
                    <th>&nbsp;</th>
                  </tr>
                  <tr>
                    <td>Basic Pay</td>
                    <td>".number_format($rows->total_hours)."</td>
                    <td>$rows->ratesperDay</td>
                    <td>".number_format($rows->standard_pay)."</td>
                  </tr>
                  <tr>
                  <td>Overtime</td>
                  <td></td>
                  <td></td>
                  <td></td>
                  </tr>
                  <tr>
                    <td>Regular Holiday</td>
                    <td>$rows->regular_holiday</td>
                    <td></td>
                    <td>".number_format($rows->regular_holiday_pay)."</td>
                  </tr>
                  <tr>
                    <td>Special Holiday</td>
                    <td>$rows->special_holiday</td>
                    <td></td>
                    <td>".number_format($rows->special_holiday_pay)."</td>
                  </tr>
                  <tr>
                    <td>13Month</td>
                    <td>$rows->thirteenmonth</td>
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
                    <td>".number_format($rows->total_gross)."</td>
                  </tr>
                </table>
                <h3><u>Total Netpay: ".number_format($rows->total_netpay)."</u></h3>
              </div>
              <div class='column'>
                  Email: $rows->email<br/>
                  Contact: $rows->cpnumber<br/>
                  Date: $rows->date_created
                <table>
                  <tr>
                    <th>Deductions</th>
                    <th>Mins</th>
                    <th>Rate</th>
                    <th>&nbsp;</th>
                  </tr>
                  <tr>
                    <td>Late</td>
                    <td>$rows->total_hours_late</td>
                    <td>59.523</td>
                    <td></td>
                  </tr>
                  <tr>
                    <td>SSS</td>
                    <td></td>
                    <td></td>
                    <td>$rows->sss</td>
                  </tr>
                  <tr>
                    <td>Pagibig</td>
                    <td></td>
                    <td></td>
                    <td>$rows->pagibig</td>
                  </tr>
                  <tr>
                    <td>Philhealth</td>
                    <td></td>
                    <td></td>
                    <td>$rows->philhealth</td>
                  </tr>
                  <tr>
                    <td>Cash Bond</td>
                    <td></td>
                    <td></td>
                    <td>$rows->cashbond</td>
                  </tr>";
                  $payslip.=
                  ($rows->other_amount > 0)?
                    "<tr>
                    <td>$rows->other</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>$rows->other_amount</td>
                  </tr>":"";
                  $payslip.="<tr>
                    <td>Cash Advance</td>
                    <td></td>
                    <td></td>
                    <td>".number_format($rows->vale)."</td>
                  </tr>
                  <tr>
                    <td>Total Deduction</td>
                    <td></td>
                    <td></td>
                    <td>".number_format($rows->total_deduction)."</td>
                  </tr>
                  
                </table>
                <h3>Salary From: $rows->start  - $rows->end </h3>
              </div>
            </div>
            </body>
            </html>
            ";
            $pdfname = $rows->firstname .' '. $rows->lastname;
            $dompdf->loadHtml($payslip);
            $dompdf->set_option('isRemoteEnabled', TRUE);
            // (Optional) Setup the paper size and orientation
            $customPaper = array(0,0,1000,600);
            $dompdf->set_paper($customPaper);
            
            $dompdf->render();
            ob_end_clean();
            $file = $dompdf->output();
            $dompdf->stream($pdfname);
            }
    }
    public function emailpdf()
    {
        $sql = "SELECT *
        FROM automatic_generated_salary
        INNER JOIN employee ON automatic_generated_salary.emp_id = employee.empId
        WHERE automatic_generated_salary.for_release = 'for email';";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        $rowss = $stmt->fetchall();
        foreach($rowss as $rows){
        $pdfsarray[]=$rows->log;
        $logid=$rows->log;
        $dompdf = new Dompdf();
        $path = '../img/icon.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $payslip = "<!DOCTYPE html>
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
          
        .column {
          float: left;
          width: 48.5%;
          padding: 5px;
        }
        .row::after {
          content: '';
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
        <body><img src='$base64' type='' class='viewautomatedsalary-logo' width='100' height='100'
        style='float:right; margin-left:-200px; margin-right: 70px; margin-top: 20px'></img>
        <center><h2>JTDV SECURITY AGENCY</h2>
        <p><u>400 Gem Bldg.,Gen T De Leon Ave.<br/>Barangay Gen T. De Leon, Valenzuela City</u></p></center>

        <div class='row'>
          <div class='column'>
            Employee ID: $rows->empId <br/>
            Employee Name: $rows->firstname  $rows->lastname <br/>
            Position: $rows->position
            <table>
              <tr>
                <th>Earnings</th>
                <th>Hours</th>
                <th>Rate</th>
                <th>&nbsp;</th>
              </tr>
              <tr>
                <td>Basic Pay</td>
                <td>".$rows->total_hours."</td>
                <td>$rows->ratesperDay</td>
                <td>".number_format($rows->standard_pay,2)."</td>
              </tr>
              <tr>
              <td>Overtime</td>
              <td>".$rows->total_overtime."</td>
              <td>$rows->overtime_rate</td>
              <td>".number_format($rows->overtime_pay,2)."</td>
              </tr>
              <tr>
                <td>Regular Holiday</td>
                <td></td>
                <td></td>
                <td>".number_format($rows->regular_holiday_pay,2)."</td>
              </tr>
              <tr>
                <td>Special Holiday</td>
                <td></td>
                <td></td>
                <td>$rows->special_holiday_pay</td>
              </tr>
              <tr>
                <td>13Month</td>
                <td>$rows->thirteenmonth</td>
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
                <td>".number_format($rows->total_gross,2)."</td>
              </tr>
            </table>
            <h3><u>Total Netpay: ".number_format($rows->total_netpay)."</u></h3>
          </div>
          <div class='column'>
              Email: $rows->email<br/>
              Contact: $rows->cpnumber<br/>
              Date: $rows->date_created
            <table>
              <tr>
                <th>Deductions</th>
                <th>Mins</th>
                <th>Rate</th>
                <th>&nbsp;</th>
              </tr>
              <tr>
                <td>Late</td>
                <td>$rows->total_hours_late</td>
                <td>".number_format($rows->ratesperDay / 60,3)."</td>
                <td>$rows->late_total</td>
              </tr>
              <tr>
                <td>SSS</td>
                <td></td>
                <td></td>
                <td>$rows->sss</td>
              </tr>
              <tr>
                <td>Pagibig</td>
                <td></td>
                <td></td>
                <td>$rows->pagibig</td>
              </tr>
              <tr>
                <td>Philhealth</td>
                <td></td>
                <td></td>
                <td>$rows->philhealth</td>
              </tr>
              <tr>
                <td>Cash Bond</td>
                <td></td>
                <td></td>
                <td>$rows->cashbond</td>
              </tr>";
              $payslip.=
              ($rows->other_amount > 0)?
                "<tr>
                <td>$rows->other</td>
                <td></td>
                <td></td>
                <td>$rows->other_amount</td>
                <td></td>
              </tr>":"";
              $payslip.=
              ($rows->vale > 0)?
              "<tr>
              <td>Cash Advance</td>
              <td></td>
              <td></td>
              <td>".number_format($rows->vale)."</td>
              </tr>":"";
              $payslip.=
              ($rows->violation > 0)?
              "<tr>
              <td>Violations</td>
              <td></td>
              <td></td>
              <td>".number_format($rows->violation)."</td>
              </tr>
              <tr>":"";
              $payslip.="
              <tr>
                <td>Total Deduction</td>
                <td></td>
                <td></td>
                <td>".number_format($rows->total_deduction,2)."</td>
              </tr>
            </table>
            <h3>Salary From: $rows->start  - $rows->end </h3>
          </div>
        </div>
        </body>
        </html>
        ";
        date_default_timezone_set('Asia/Manila');
        $date = date('F d Y');
        $pdfname = '../SecretaryPortal/uploads/'.$rows->firstname .' '. $rows->lastname.', '.$date.'.pdf';
        $empname= $rows->firstname .' '. $rows->lastname;
        $email=$rows->email;
        // $email="redjudecadornigara2@gmail.com";
        $dompdf->loadHtml($payslip);

        // (Optional) Setup the paper size and orientation
        $customPaper = array(0,0,1000,600);
        $dompdf->set_paper($customPaper);
        ob_start();
        $dompdf->render();
        // ob_end_clean();
        $file = $dompdf->output();
        file_put_contents($pdfname,$file);
        ob_end_flush();
        $name = 'JTDV Security Agency';
        $subject = 'PAYSLIP';
        $body = 'Hello '.$empname.' , Here\'s your Payslip for '.$date;
        if(!empty($email)){

            $mail = new PHPMailer();

            // smtp settings
            $mail->isSMTP();
            $mail->Mailer = "smtp";
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "sicnarfarerreh@gmail.com";  // gmail address
            $mail->Password = "sicnarf123";         // gmail password
            $mail->Port = 587;
            $mail->IsHTML(true);
            $mail->SMTPSecure = 'tls';
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => true,
                    'allow_self_signed' => true
                )
            );


            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email,$name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ($subject);     // headline
            $mail->Body = $body;
            $mail->AddAttachment($pdfname);                        // textarea

            if($mail->send()){
            } else {
                $status = "failed";
                $response = "Something is wrong: <br/>". $mail->ErrorInfo ;
                echo $response ;
            }
        }
        $sql1="UPDATE automatic_generated_salary SET for_release = 'released', date_released=CURRENT_TIMESTAMP() WHERE log = $logid;";
        $stmt1 = $this->con()->prepare($sql1);
        $stmt1->execute();
        $CountRow01 = $stmt1 ->rowCount();
        $this->pdo = null;
    }//lahat ieemail foreach
    $this->mergepdf($pdfsarray);
    }
    public function activitylog(){
        $sql="SELECT * FROM secretary_log ORDER BY id DESC ;";
        $stmt=$this->con()->prepare($sql);
        $stmt->execute();
        $users=$stmt->fetchall();

        foreach($users as $user){
        echo "<tr>
        <td>$user->sec_id</td>
        <td>$user->name</td>
        <td>$user->action</td>
        <td>$user->time</td>
        <td>$user->date</td>
        </tr>";
        }
    }
    public function seclogin()
    {
        if(isset($_POST['login']))
        {
            session_start();
            if(!isset($_SESSION['emailattempt'])){
                $_SESSION['emailattempt'] = 5;
            }

            if($_SESSION['emailattempt'] == 2){
                echo "Your Credentials has been emailed to you<br>";
                $this->sendEmail($_SESSION['reservedsecemail'],$_SESSION['reservedsecpassword']);
            }

            $username = $_POST['username'];
            $password = $this->generatedPassword($_POST['password']);
            $sql="SELECT * FROM secretary WHERE email = ?;";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$username]);
            $countrow = $stmt->rowCount();
            $user=$stmt->fetch();
            if($countrow>0)
            {   
                $email=true;
                if(!isset($_SESSION['reservedsecemail'])){
                $_SESSION['reservedsecemail'] = $user->email;
                $_SESSION['reservedsecpassword'] = $user->password;
                }
                            $sqlpass="SELECT * FROM secretary WHERE password = ?";
                            $stmtpass = $this->con()->prepare($sqlpass);
                            $stmtpass->execute([$password[0]]);
                            $countrowpass = $stmtpass->rowCount();
                            $users=$stmtpass->fetch();
                            if($countrowpass<1){
                                $_SESSION['emailattempt'] -= 1;
                                $email=false;
                                echo "Username and password does not match!";
                            }
                            else{   
                                    if($users->isDeleted <=0){
                                    $sqlm="SELECT * FROM maintenance WHERE module = 'Secretary';";
                                    $stmtm = $this->con()->prepare($sqlm);
                                    $stmtm->execute();
                                    $countrowm = $stmtm->rowCount();
                                    $usersm=$stmtm->fetch();
                                    if($usersm->status == 0)
                                    {
                                        $suspendedAccess="suspended";
                                        if($users->access != $suspendedAccess){
                                        $id = $users->id;
                                        $fullname = $users->fullname; // create fullname
                                        $action = "login"; 
                                            
                                        // set timezone and get date and time
                                        $datetime = $this->getDateTime(); 
                                        $time = $datetime['time'];
                                        $date = $datetime['date'];
                        
                                        // insert mo sa activity log ni admin
                                        $actLogSql = "INSERT INTO secretary_log(`sec_id`,`name`, 
                                                                            `action`,
                                                                            `time`,
                                                                            `date`
                                                                            )
                                                    VALUES(?, ?, ?, ?, ?)";
                                        $actLogStmt = $this->con()->prepare($actLogSql);
                                        $actLogStmt->execute([$id,$fullname, $action, $time, $date]);
                        
                                        // // create user details using session
                                        $_SESSION['SecretaryDetails'] = array('fullname' => $fullname,
                                                                            'access' => $users->access,
                                                                            'position' => $users->position,
                                                                            'id' => $users->id,
                                                                            'empId' => $users->id,
                                                                            'scheduleTimeIn' => $users->scheduleTimeIn,
                                                                            'scheduleTimeOut' => $users->scheduleTimeOut,
                                                                            'datetimeIn' => $users->datetimeIn
                                                                            );
                                        header('Location: SecretaryPortal/secdashboard.php'); // redirect to dashboard.php
                                        return $_SESSION['SecretaryDetails']; // after calling the function, return session


                                } else {
                                    $dateExpiredArray = $this->formatDateLocked($users->timer);
                                    $dateExpired = implode(" ", $dateExpiredArray);
                                    echo 'Your account has been locked until</br>'.
                                        'Date: '.$dateExpired;
                                }
                            } else {
                                header('location: secmaintenance.php');
                            }
                        }else{
                            header('location: seclogin.php?message=No account exist');
                        }
                            }
            }else if($countrow<=0)
                {
                        echo "Username does not exist!";
                        session_destroy();
                }
        }
    }
    public function mergepdf($pdfsarray)
    {
            foreach($pdfsarray as $pdfmerge){
            $sql = "SELECT * FROM automatic_generated_salary RIGHT JOIN employee ON automatic_generated_salary.emp_id = employee.empId WHERE for_release = 'released' AND log = ?;";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$pdfmerge]);
            $rows = $stmt->fetch();
        //     $dompdf = new Dompdf();
        //     $path = '../img/icon.png';
        //     $type = pathinfo($path, PATHINFO_EXTENSION);
        //     $data = file_get_contents($path);
        // $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        // $payslip = "<!DOCTYPE html>
        // <html>
        // <head>
        
        // <style>
        // * {
        //   box-sizing: border-box;
        // }
        // body{
        //             background:#F2F2F2;
        //             border: 1px solid black;
        //         }
        
        // .row {
        //   margin-left:-5px;
        //   margin-right:-5px;
        // }
          
        // .column {
        //   float: left;
        //   width: 48.5%;
        //   padding: 5px;
        // }
        // .row::after {
        //   content: '';
        //   clear: both;
        //   display: table;
        // }
        
        // table {
        //   border-collapse: collapse;
        //   border-spacing: 0;
        //   width: 100%;
        //   border: 2px solid #ddd;
        // }
        
        // th, td {
        //   text-align: left;
        //   padding: 10px;
        // }
        
        // tr:nth-child(even) {
        //   background-color: #f8f9f9;
        // }
        
        // /* Responsive layout - makes the two columns stack on top of each other instead of next to each other on screens that are smaller than 600 px */
        // @media screen and (max-width: 600px) {
        //   .column {
        //     width: 100%;
        //   }
        // }
        // </style>
        // </head>
        // <body><img src='$base64' type='' class='viewautomatedsalary-logo' width='100' height='100'
        // style='float:right; margin-left:-200px; margin-right: 70px; margin-top: 20px'></img>
        // <center><h2>JTDV SECURITY AGENCY</h2>
        // <p><u>400 Gem Bldg.,Gen T De Leon Ave.<br/>Barangay Gen T. De Leon, Valenzuela City</u></p></center>

        // <div class='row'>
        //   <div class='column'>
        //     Employee ID: $rows->empId <br/>
        //     Employee Name: $rows->firstname  $rows->lastname <br/>
        //     Position: $rows->position
        //     <table>
        //       <tr>
        //         <th>Earnings</th>
        //         <th>Hours</th>
        //         <th>Rate</th>
        //         <th>&nbsp;</th>
        //       </tr>
        //       <tr>
        //         <td>Basic Pay</td>
        //         <td>".number_format($rows->total_hours)."</td>
        //         <td>$rows->ratesperDay</td>
        //         <td>".number_format($rows->standard_pay)."</td>
        //       </tr>
        //       <tr>
        //       <td>Overtime</td>
        //       <td>".number_format($rows->total_overtime)."</td>
        //       <td>$rows->overtime_rate</td>
        //       <td>".number_format($rows->overtime_pay)."</td>
        //       </tr>
        //       <tr>
        //         <td>Regular Holiday</td>
        //         <td></td>
        //         <td></td>
        //         <td>".number_format($rows->regular_holiday_pay)."</td>
        //       </tr>
        //       <tr>
        //         <td>Special Holiday</td>
        //         <td></td>
        //         <td></td>
        //         <td>$rows->special_holiday_pay</td>
        //       </tr>
        //       <tr>
        //         <td>13Month</td>
        //         <td>$rows->thirteenmonth</td>
        //         <td></td>
        //         <td></td>
        //       </tr>
        //       <tr>
        //         <td>&nbsp;</td>
        //         <td></td>
        //         <td></td>
        //         <td></td>
        //       </tr>
        //       <tr>
        //         <td>&nbsp;</td>
        //         <td></td>
        //         <td></td>
        //         <td></td>
        //       </tr>
        //       <tr>
        //         <td>Total Gross</td>
        //         <td></td>
        //         <td></td>
        //         <td>".number_format($rows->total_gross)."</td>
        //       </tr>
        //     </table>
        //     <h3><u>Total Netpay: ".number_format($rows->total_netpay)."</u></h3>
        //   </div>
        //   <div class='column'>
        //       Email: $rows->email<br/>
        //       Contact: $rows->cpnumber<br/>
        //       Date: $rows->date_created
        //     <table>
        //       <tr>
        //         <th>Deductions</th>
        //         <th>No.of</th>
        //         <th>Rate</th>
        //         <th>&nbsp;</th>
        //       </tr>
        //       <tr>
        //         <td>Late</td>
        //         <td>$rows->total_hours_late</td>
        //         <td>".number_format($rows->ratesperDay / 60,3)."</td>
        //         <td>$rows->late_total</td>
        //       </tr>
        //       <tr>
        //         <td>SSS</td>
        //         <td></td>
        //         <td></td>
        //         <td>$rows->sss</td>
        //       </tr>
        //       <tr>
        //         <td>Pagibig</td>
        //         <td></td>
        //         <td></td>
        //         <td>$rows->pagibig</td>
        //       </tr>
        //       <tr>
        //         <td>Philhealth</td>
        //         <td></td>
        //         <td></td>
        //         <td>$rows->philhealth</td>
        //       </tr>
        //       <tr>
        //         <td>Cash Bond</td>
        //         <td></td>
        //         <td></td>
        //         <td>$rows->cashbond</td>
        //       </tr>";
        //       $payslip.=
        //       ($rows->other_amount > 0)?
        //         "<tr>
        //         <td>$rows->other</td>
        //         <td></td>
        //         <td></td>
        //         <td>$rows->other_amount</td>
        //         <td></td>
        //       </tr>":"";
        //       $payslip.=
        //       ($rows->vale > 0)?
        //       "<tr>
        //       <td>Cash Advance</td>
        //       <td></td>
        //       <td></td>
        //       <td>".number_format($rows->vale)."</td>
        //       </tr>":"";
        //       $payslip.=
        //       ($rows->violation > 0)?
        //       "<tr>
        //       <td>Violations</td>
        //       <td></td>
        //       <td></td>
        //       <td>".number_format($rows->violation)."</td>
        //       </tr>
        //       <tr>":"";
        //       $payslip.="
        //       <tr>
        //         <td>Total Deduction</td>
        //         <td></td>
        //         <td></td>
        //         <td>".number_format($rows->total_deduction)."</td>
        //       </tr>
        //     </table>
        //     <h3>Salary From: $rows->start  - $rows->end </h3>
        //   </div>
        // </div>
        // </body>
        // </html>
        // ";
            ob_start ();
            date_default_timezone_set('Asia/Manila');
            $date = date('F d Y');
            $datef = date('F d Y h-i-s A');
            $pdfname = '../SecretaryPortal/uploads/'.$rows->firstname .' '. $rows->lastname.', '.$date.'.pdf';
            // $dompdf->loadHtml($payslip);
            // $dompdf->set_option('isRemoteEnabled', TRUE);
            // // (Optional) Setup the paper size and orientation
            // $customPaper = array(0,0,1000,600);
            // $dompdf->set_paper($customPaper);
            // $dompdf->render();
            // ob_end_clean();
            // $file = $dompdf->output();
            // file_put_contents($pdfname,$file);
            $pathToPdfs[] = $pdfname;
            }//foreeach
            $pdf = new \Jurosh\PDFMerge\PDFMerger;
            foreach($pathToPdfs as $pathToPdf){
                $pdf->addPDF($pathToPdf, 'all','landscape');
            }
            ob_start ();
            $backup= '../SecretaryPortal/merged/'.$datef.'.pdf';
            $files = $datef.'.pdf';
            $pdf->merge('download', $files);
            $pdf->merge('file',$backup);
    }
    public function changepass($id,$fullname)
    {
        if(isset($_POST['changepass']))
        {
            $oldpass= $this->generatedPassword($_POST['oldpass']);
            $sql="SELECT * FROM secretary WHERE id = ?;";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$id]);
            $user=$stmt->fetch();
            if($oldpass[0] == $user->password)
            {
                $newpass= $this->generatedPassword($_POST['newpass']);
                $confirmpass= $this->generatedPassword($_POST['confirmpass']);
                if($newpass[0] == $confirmpass[0])
                {
                    $sqledit = "UPDATE secretary SET password = ? WHERE id = $id;";
                    $stmtedit = $this->con()->prepare($sqledit);
                    $stmtedit->execute([$newpass[0]]);
                    $countrowedit = $stmtedit->rowCount();
                    if($countrowedit>0){
                        echo "<p>Password Succesfully Change</p>";
                        $datetime = $this->getDateTime();
                        $time = $datetime['time'];
                        $date = $datetime['date'];
                        $action = "Change Password";
                        $actLogSql = "INSERT INTO secretary_log(`sec_id`,`name`, 
                        `action`,
                        `time`,
                        `date`
                        )
                        VALUES(?, ?, ?, ?, ?)";
                        $actLogStmt = $this->con()->prepare($actLogSql);
                        $actLogStmt->execute([$id,$fullname, $action, $time, $date]);
                    }
                } else{
                    echo "Password does not match";
                }
                
            } else {
                echo "Wrong Password";
            }
        }
        $this->pdo= null;
    
    }
    public function displaybonus()
    {
        $sql="SELECT * FROM employee;";
        $stmt=$this->con()->prepare($sql);
        $stmt->execute();
        $rows=$stmt->fetchall();
        foreach($rows as $row){
        $length = 0;
        $found = false;
        $sqlc="SELECT * FROM salary_report WHERE empId = ? AND status IS NULL;";
        $stmtc=$this->con()->prepare($sqlc);
        $stmtc->execute([$row->empId]);
        $countRowc=$stmtc->rowCount();
        if($countRowc > 0){
                $sqlsr="SELECT
                    SUM(case when january >0 then 1 else 0 end) a, 
                    SUM(case when february > 0 then 1 else 0 end) b,
                    SUM(case when march >0 then 1 else 0 end)c,
                    SUM(case when april > 0 then 1 else 0 end)d,
                    SUM(case when may >0 then 1 else 0 end)e,
                    SUM(case when june > 0 then 1 else 0 end)f,
                    SUM(case when july >0 then 1 else 0 end)g,
                    SUM(case when august > 0 then 1 else 0 end)h,
                    SUM(case when september >0 then 1 else 0 end)i,
                    SUM(case when october > 0 then 1 else 0 end)j,
                    SUM(case when november >0 then 1 else 0 end)k,
                    SUM(case when december > 0 then 1 else 0 end)l
                  FROM salary_report WHERE empId= ? and status IS NULL;";
                $stmtsr=$this->con()->prepare($sqlsr);
                $stmtsr->execute([$row->empId]);
                $countRowsr=$stmtsr->rowCount();
                $rowssr=$stmtsr->fetch();
            if($countRowsr >0)
            {
                $found=true;
                $length= $rowssr->a + $rowssr->b + $rowssr->c + $rowssr->d + $rowssr->e + $rowssr->f + $rowssr->g +
                $rowssr->h + $rowssr->i + $rowssr->j + $rowssr->k + $rowssr->l;
                if($length > 1){
                    $month="Months";
                }else{
                    $month="Month";
                }
                echo "<tr>
                <td>$row->firstname $row->lastname</td>
                <td>&emsp;</td>
                <td>$length $month</td>
                    </tr>";
            }
        }//kung may salary report
        }//all
    }
    public function createbonus($id,$fullname)
    {
        if(isset($_POST['bonus'])){
            
            $sql="SELECT * FROM employee;";
            $stmt=$this->con()->prepare($sql);
            $stmt->execute();
            $rows=$stmt->fetchall();
            foreach($rows as $row)
            {
                $total_gross=0;
                $total_late=0;
                $grossjan=0;
                $grossfeb=0;
                $grossmar=0;
                $grossapr=0;
                $grossmay=0;
                $grossjun=0;
                $grossjul=0;
                $grossaug=0;
                $grosssep=0;
                $grossoct=0;
                $grossnov=0;
                $grossdec=0;
                $latejan=0;
                $latefeb=0;
                $latemar=0;
                $lateapr=0;
                $latemay=0;
                $latejun=0;
                $latejul=0;
                $lateaug=0;
                $latesep=0;
                $lateoct=0;
                $latenov=0;
                $latedec=0;
                $found = false;
                $sql1 = "SELECT * FROM automatic_generated_salary WHERE emp_id = ? AND for_release = 'released' AND bonus_status IS NULL;";
                $stmt1=$this->con()->prepare($sql1);
                $stmt1->execute([$row->empId]);
                $countRow1=$stmt1->rowCount();
                $rows1=$stmt1->fetchall();
                if($countRow1 > 0 )
                {   
                    $number = 0;
                    $length=0;
                    foreach($rows1 as $row1){
                    $log=$row1->log;
                    $empid=$row1->emp_id;
                    date_default_timezone_set('Asia/Manila');
                    $curryear = date('Y');
                    $year=date('Y',strtotime($row1->end));
                    // if($year == $curryear)
                    // {   
                        $month=date('F',strtotime($row1->end));
                            if(strtolower($month)=='january')
                            {   
                                $grossjan += $row1->total_gross;
                                $latejan += $row1->late_total;
                                if($grossjan > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='february'){
                                $grossfeb += $row1->total_gross;
                                $latefeb += $row1->late_total;
                                if($grossfeb > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='march'){
                                $grossmar += $row1->total_gross;
                                $latemar += $row1->late_total;
                                if($grossmar > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='april'){
                                $grossapr += $row1->total_gross;
                                $lateapr += $row1->late_total;
                                if($grossapr > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='may'){
                                $grossmay += $row1->total_gross;
                                $latemay += $row1->late_total;
                                if($grossmay > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='june'){
                                $grossjun += $row1->total_gross;
                                $latejun += $row1->late_total;
                                if($grossjun > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='july'){
                                $grossjul += $row1->total_gross;
                                $latejul += $row1->late_total;
                                if($grossjul > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='august'){
                                $grossaug += $row1->total_gross;
                                $lateaug += $row1->late_total;
                                if($grossaug > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='september'){
                                $grosssep += $row1->total_gross;
                                $latesep += $row1->late_total;
                                if($grosssep > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='october'){
                                $grossoct += $row1->total_gross;
                                $lateoct += $row1->late_total;
                                if($grossoct > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='november'){
                                $grossnov += $row1->total_gross;
                                $latenov += $row1->late_total;
                                if($grossnov > 0){
                                    $length+=1;
                                }
                            } else if(strtolower($month)=='december'){
                                $grossdec += $row1->total_gross;
                                $latedec += $row1->late_total;
                                if($grossdec > 0){
                                    $length+=1;
                                }
                            } else {
    
                            }
                            $number+=1;
                    // }// pag di current year
                }// foreach ng salary
                    if($length>=1)
                        {
                            $total_gross = $grossjan + $grossfeb + $grossmar + $grossapr + $grossmay + $grossjun +
                            $grossjul + $grossaug + $grosssep + $grossoct + $grossnov + $grossdec;
                            $total_late = $latejan + $latefeb + $latemar + $lateapr + $latemay + $latejun + $latejul + 
                            $lateaug + $latesep + $lateoct + $latenov + $latedec;
                            $amount = $total_gross / 12;
                            date_default_timezone_set('Asia/Manila');
                            $date = date('F d, Y');
                            $sqlb="INSERT INTO thirteenmonth (empId,january,february,march,april,may,june,july,august,september,october,november,december,total_gross,late,amount,date_created) 
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                            $stmtb=$this->con()->prepare($sqlb);
                            $stmtb->execute([$row->empId,$grossjan,$grossfeb,$grossmar,$grossapr,$grossmay,$grossjun,$grossjul,$grossaug,$grosssep,$grossoct,$grossnov,$grossdec,$total_gross,$total_late,$amount,$date]);
                            $countRowb=$stmtb->rowCount();
                            if($countRowb>0)
                            {
                                $sqlu="UPDATE automatic_generated_salary SET bonus_status = 'paid' WHERE log = ?;";
                                $stmtu=$this->con()->prepare($sqlu);
                                $stmtu->execute([$log]);
                                $sqlu1="UPDATE salary_report SET status = 'paid' WHERE empId = ?;";
                                $stmtu1=$this->con()->prepare($sqlu1);
                                $stmtu1->execute([$empid]);
                                $countRowu=$stmtu->rowCount();
                                if($countRowu>0)
                                {
                                    $datetime = $this->getDateTime();
                                    $time = $datetime['time'];
                                    $date = $datetime['date'];
                                    $action = "Generate 13 Month Pay for ".$number." Employee/s";
                                    $actLogSql = "INSERT INTO secretary_log(`sec_id`,`name`, 
                                    `action`,
                                    `time`,
                                    `date`
                                    )
                                    VALUES(?, ?, ?, ?, ?)";
                                    $actLogStmt = $this->con()->prepare($actLogSql);
                                    $actLogStmt->execute([$id,$fullname, $action, $time, $date]);
                                    header('location: thirteen.php');
                                }
                            }else {
                                echo "";
                            }
                        }
                }//kapag walang salary
            }//all

        }else if(isset($_POST['cancel'])){
            header('location: thirteen.php');
        }
    }
    public function displaythirteen()
    {
        $sql="SELECT * FROM thirteenmonth INNER JOIN employee WHERE thirteenmonth.empId = employee.empId;";
        $stmt=$this->con()->prepare($sql);
        $stmt->execute();
        while($row=$stmt->fetch()){
            echo"<tr>
            <td>$row->firstname $row->lastname</td>
            <td>$row->total_gross</td>
            <td>$row->late</td>
            <td>$row->amount</td>
            <td>$row->date_created</td>
            <td><a href='viewbonus.php?id=$row->log'>View</a></td>
            </tr>";
        }
    }
    public function feedback($id,$fullname)
    {   
        
        if(isset($_POST['feedback'])){
            $comment = $_POST['comment'];
            $position = 'Secretary';

            if(isset($_POST['satisfaction']) || isset($satisfaction))
            {
                $satisfaction = $_POST['satisfaction'];
                date_default_timezone_set('Asia/Manila');
                $date = date('Y/m/d');
                $sql="INSERT INTO feedback (fullname,position,category,comment,date_created) VALUES (?,?,?,?,?)";
                $stmt=$this->con()->prepare($sql);
                $stmt->execute([$fullname,$position,$satisfaction,$comment,$date]);
                $succ=$stmt->rowCount();
                if($succ > 0){
                    echo"Submitted Succesfully, Thank you!";
                }
            }else{
                echo "Please Choose Caategory<br>";
            }
        }
    }
}// End of class

$payroll = new Payroll;

?> 
