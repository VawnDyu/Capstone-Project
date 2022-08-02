<?php
// required to para makapag send ng email
use PHPMailer\PHPMailer\PHPMailer;
require_once "PHPMailer/PHPMailer.php";
require_once "PHPMailer/SMTP.php";
require_once "PHPMailer/Exception.php";


Class Payroll
{
    private $username = "u359933141_jtdv";
    private $password = "+Y^HLMVV2h";

    private $dns = "mysql:host=localhost;dbname=u359933141_payroll";
    protected $pdo;

    public function con()
    {
        $this->pdo = new PDO($this->dns, $this->username, $this->password);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return $this->pdo;
    }

    // used to set timezone and get date and time
    public function getDateTime()
    {
        date_default_timezone_set('Asia/Manila'); // set default timezone to manila
        $curr_date = date("Y/m/d"); // date
        $curr_time = date("h:i:sa"); // time

        // return date and time in array
        $_SESSION['datetime'] = array('time' => $curr_time, 'date' => $curr_date);
        return $_SESSION['datetime'];
    }

    // check password
    public function generatedPassword($pword)
    {
        $keyword = "%15@!#Fa4%#@kE";
        $generatedPassword = md5($pword.$keyword);
        return array($generatedPassword, $pword.$keyword);
    }

    // create password
    public function generatedPassword2(){
        $pword = "abcdefghijklmnopqrstuvwxyz0123456789@#$%^&*()_+";
        $pword = str_shuffle($pword);
        $pword = substr($pword, 0, 8); // length of pass
        return $pword;
    }

// =================================================Von's Territory====================================================

    public function mobile_login() {
        session_start();
        
        date_default_timezone_set("Asia/Manila");
        $dateNow = date('Y/m/d'); 
        $timedateNow = date('Y-m-d h:i:s A');

        if (isset($_POST['login'])) {

            if(!isset($_SESSION['attempts'])) {
                $_SESSION['attempts'] = 5; //Set the attempts to 5 tries
            }

            if ($_SESSION['attempts'] == 3) {
                $sqlFindEmail = "SELECT 
                                    e.lastname,
                                    sd.e_id,
                                    sd.secret_key
                                            
                                FROM employee e
                                            
                                INNER JOIN secret_diarye sd
                                ON e.email = sd.e_id
                                            
                                WHERE sd.e_id = ?";
                $stmtFindEmail = $this->con()->prepare($sqlFindEmail);
                $stmtFindEmail->execute([$_POST['login-email']]);

                $usersFindEmail = $stmtFindEmail->fetch();
                $countRowFindEmail = $stmtFindEmail->rowCount();

                if($countRowFindEmail > 0) {
                    $GetEmail = $usersFindEmail->e_id;
                    $GetLastname = $usersFindEmail->lastname;
                    $GetPassword = $usersFindEmail->secret_key;

                    $this->MobileSendEmail($GetEmail, $GetLastname, $GetPassword);
                    header('location: m_login.php?msg=incorrect_password');
                }
            }

            if ($_SESSION['attempts'] == 1) {
                $timer = date('Y/m/d h:i:s A', strtotime('+5 minutes'));
                
                $email = $_POST['login-email'];

                $sqlLock = "UPDATE employee SET timer = ? WHERE email = ?";
                $stmtLock = $this->con()->prepare($sqlLock);
                $stmtLock->execute([$timer, $_POST['login-email']]);

                $_SESSION['attempts'] = 5;
            }

            $email = $_POST['login-email']; //Get the email from the login form
            $password = $this->generatedPassword($_POST['login-password']); //Get the password from login form

            $sql = "SELECT
                        e.*,
                        s.company,
                        s.scheduleTimeIn,
                        s.scheduleTimeOut,
                        s.shift_span,
                        s.shift,
                        s.expiration_date,
                        lr.leave_start,
                        lr.leave_end,
                        lr.status,
                        lr.substitute_by,
                        lrx.leave_start AS 'Start'
                    FROM employee e
                    
                    LEFT JOIN schedule s
                    ON e.empId = s.empId

                    LEFT JOIN leave_request lr
                    ON e.empId = lr.empId
                    
                    LEFT JOIN leave_request lrx
                    ON e.empId = lrx.substitute_by
                    
                    WHERE e.email = ? AND e.password = ? ORDER BY lr.leave_start DESC LIMIT 1;";

            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$email, $password[0]]);

            $users = $stmt->fetch();
            $countRow = $stmt->rowCount();

            if ($countRow > 0) {
                
                if (strtotime($timedateNow) >= strtotime($users->timer) && $users->timer != NULL) {
                    $_SESSION['login-error-message'] = 'Timer has completely done. <br> Please log-in again to proceed';
                    $_SESSION['attempts'] = 5;
                    $timerset = NULL;

                    $sqlToNull = "UPDATE employee SET timer = ? WHERE email = ?";
                    $stmtToNull = $this->con()->prepare($sqlToNull);
                    $stmtToNull->execute([$timerset, $email]);

                } else if ($users->timer != NULL) {
                    $_SESSION['login-error-message'] = 'Your account is temporarily locked until <br>'.date('M d, Y - h:i:s A', strtotime($users->timer));
                    $_SESSION['attempts'] = 5;

                } else if ($users->availability == 'Leave' && strtotime($timedateNow) >= strtotime($users->leave_end) && $users->leave_end != NULL && $users->status == 'approved') {
                    $_SESSION['login-error-message'] = 'Leave has been expired. <br> Please log-in again to continue.';
                    $_SESSION['attempts'] = 5;
                    $availabilityUnavail = "Unavailable";
                    $availabilityAvail = "Available";

                    $sqlLeaveDone = "BEGIN;
                                        UPDATE employee SET availability = ? WHERE empId = ?;
                                        UPDATE employee SET availability = ? WHERE empId = ?;
                                    COMMIT;";
                    $stmtLeaveDone = $this->con()->prepare($sqlLeaveDone);
                    $stmtLeaveDone->execute([$availabilityAvail, $users->substitute_by, $availabilityUnavail, $users->empId]);

                } else if ($users->availability == 'Leave' && strtotime($timedateNow) >= strtotime($users->leave_start) && strtotime($timedateNow) <= strtotime($users->leave_end) && $users->status == 'approved') {
                    $_SESSION['login-error-message'] = 'You are on the process of leave <br>'.date("M d, Y", strtotime($users->leave_start)).' - '.date("M d, Y", strtotime($users->leave_end));
                    $_SESSION['attempts'] = 5;
                    
                    // echo strtotime($timedateNow) .'<br>'. strtotime($users->leave_start) . '<br>'. strtotime($timedateNow) . '<br>'. strtotime($users->leave_end) .'<br>'.$users->status.'<br>'.$users->availability;

                } else if ($users->availability == 'Available' && $users->scheduleTimeIn == NULL) {
                    $_SESSION['login-error-message'] = "You do not have a schedule yet.";
                    $_SESSION['attempts'] = 5;

                } else if ($users->availability == 'Unavailable' && strtotime($users->Start) >= strtotime($timedateNow)) {
                    $_SESSION['login-error-message'] = "It is not yet your schedule for work.";
                    $_SESSION['attempts'] = 5;
                    
                } else if (strtotime($dateNow) >= strtotime($users->expiration_date)) {
                    $_SESSION['login-error-message'] = "Your account has been expired.";
                    $_SESSION['attempts'] = 5;
                    $availability = 'Available';

                    $sqlexpire = "BEGIN;
                                    DELETE FROM schedule wHERE empId = ? AND expiration_date = ?;
                                    UPDATE employee SET availability = ? WHERE empId = ?;
                                COMMIT;";
                    $stmtexpire = $this->con()->prepare($sqlexpire);
                    $stmtexpire->execute([$users->empId, $users->expiration_date, $availability, $users->empId]);

                } else {

                    if ($users->position == 'Officer in Charge') {
                        $fullname = $users->firstname.' '. $users->lastname;
                        $_SESSION['OICDetails'] = array('fullname' => $fullname,
                                                        'access' => $users->access,
                                                        'position' => $users->position,
                                                        'id' => $users->id,
                                                        'empId' => $users->empId,
                                                        'company' => $users->company,
                                                        'scheduleTimeIn' => $users->scheduleTimeIn,
                                                        'scheduleTimeOut' => $users->scheduleTimeOut,
                                                        'availability' => $users->availability,
                                                        'email' => $users->email,
                                                        'contact' => $users->cpnumber,
                                                        'shift' => $users->shift,
                                                        'shift_span' => $users->shift_span
                                                        );

                        
                        $sqlAddLog = "INSERT INTO emp_log (empId, action, date_created) VALUES (?, ?, ?)";
                        $stmtAddLog = $this->con()->prepare($sqlAddLog);
                        $stmtAddLog->execute([$users->empId, 'Login', $timedateNow]);

                        header('Location: employee/OIC.php'); // redirect to dashboard.php
                        return $_SESSION['OICDetails']; // after calling the function, return session

                    } else if ($users->position != 'Officer in Charge') {
                        $fullname = $users->firstname.' '. $users->lastname;
                        $_SESSION['GuardsDetails'] = array('fullname' => $fullname,
                                                        'access' => $users->access,
                                                        'position' => $users->position,
                                                        'id' => $users->id,
                                                        'empId' => $users->empId,
                                                        'company' => $users->company,
                                                        'scheduleTimeIn' => $users->scheduleTimeIn,
                                                        'scheduleTimeOut' => $users->scheduleTimeOut,
                                                        'availability' => $users->availability,
                                                        'email' => $users->email,
                                                        'contact' => $users->cpnumber,
                                                        'shift' => $users->shift,
                                                        'shift_span' => $users->shift_span
                                                        );

                        $sqlAddLog = "INSERT INTO emp_log (empId, action, date_created) VALUES (?, ?, ?)";
                        $stmtAddLog = $this->con()->prepare($sqlAddLog);
                        $stmtAddLog->execute([$users->empId, 'Login', $timedateNow]);

                        header('Location: employee/Guards.php'); // redirect to dashboard.php
                        return $_SESSION['GuardsDetails']; // after calling the function, return session
                    }
                    unset($_SESSION['attempts']); //Removes the session in attempts
                    unset($_SESSION['login-error-message']);
                }

            } else {
                $sql = "SELECT * FROM employee WHERE email = ?";
                $stmt = $this->con()->prepare($sql);
                $stmt->execute([$email]);

                $users = $stmt->fetch();
                $countRow = $stmt->rowCount();
                
                if ($countRow > 0) {
                    
                    if ($users->timer != NULL) {
                        $_SESSION['login-error-message'] = 'Your account is temporarily locked until <br>'.date('M d, Y - h:i:s A', strtotime($users->timer));
                        $_SESSION['attempts'] = 5;
                    } else {
                        $_SESSION['login-error-message'] = "Incorrect email or password";
                        $_SESSION['attempts'] -= 1;
                    }
                } else {
                    $_SESSION['login-error-message'] = "Email does not exist";
                    $_SESSION['attempts'] = 5;
                }
                
            }
        }
    }

    public function mobile_logout() {

        session_start();

        date_default_timezone_set('Asia/Manila');
        $timedateNow = date("Y/m/d h:i:s A");

        if (isset($_SESSION['GuardsDetails'])) {
            $empId = $_SESSION['GuardsDetails']['empId'];
        }
        else {
            $empId = $_SESSION['OICDetails']['empId'];
        }

        $sqlAddLog = "INSERT INTO emp_log (empId, action, date_created) VALUES (?, ?, ?)";
        $stmtAddLog = $this->con()->prepare($sqlAddLog);
        $stmtAddLog->execute([$empId, 'Logout', $timedateNow]);

        $this->pdo =null;
        session_destroy();
        header('Location: m_login.php');
    }

    public function login_error_message() {
        if(isset($_SESSION['login-error-message'])) {
            echo
            '<div class="error-message">
                <span>
                    '.$_SESSION["login-error-message"].'
                </span>
            </div>';
        }
    }

    // get login session: Employee: OIC
    public function getSessionOICData() {
        session_start();
        if($_SESSION['OICDetails']){
            return $_SESSION['OICDetails'];
        }
    }

        // get login session: Employee: OIC
    public function getSessionGuardsData() {
        session_start();
        if($_SESSION['GuardsDetails']){
            return $_SESSION['GuardsDetails'];
        }
    }

    public function MobileVerifyUserAccess($access, $fullname, $position) {
        $message = 'You are not allowed to enter the system';

        if ($access == 'employee' && $position == 'Officer in Charge') {
            $position = $_SESSION['OICDetails']['position'];
            $scheduleTimeIn = $_SESSION['OICDetails']['scheduleTimeIn'];
            $scheduleTimeOut = $_SESSION['OICDetails']['scheduleTimeOut'];
        } else if ($access == 'employee' && $position != 'Officer in Charge') {
            $gposition = $_SESSION['GuardsDetails']['position'];
            $gscheduleTimeIn = $_SESSION['GuardsDetails']['scheduleTimeIn'];
            $gscheduleTimeOut = $_SESSION['GuardsDetails']['scheduleTimeOut'];
        } else {
            header("Location: ../m_login.php?message=$message");
        }
    }

    public function MobileSendEmail($email, $lastname, $password) {
        $name = 'JTDV Security Agency';
        $subject = 'Trouble logging in';

        if(!empty($email)){

            $mail = new PHPMailer();

            // smtp settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "DammiDoe123@gmail.com";  // gmail address
            $mail->Password = "dammiedoe123456789";         // gmail password
            $mail->Port = 587;
            $mail->SMTPSecure = "tls";

            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email, $name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ("$email ($subject)");     // headline
            $email_template = 'mail_template_password.html';

            $message = file_get_contents($email_template);
            $message = str_replace('%lastname%', $lastname, $message);
            $message = str_replace('%password%', $password, $message);

            $mail->MsgHTML($message);

            if($mail->send()){
                $status = "success";
                $response = "Email is sent!";
                echo '<br/>'.$status."<br/>".$response;
            } else {
                $status = "failed";
                $response = "Something is wrong: <br/>". $mail->ErrorInfo;
                echo '<br/>'.$status."<br/>".$response;
            }
        } 
    }

    public function sendEmailSchedule($email, $lastname, $timeIn, $timeOut) {
        
        $name = 'JTDV Security Agency';
        $subject = 'Schedule';

        if(!empty($email)){

            $mail = new PHPMailer();

            // smtp settings
            // $mail->SMTPDebug = 2;
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "DammiDoe123@gmail.com";  // gmail address
            $mail->Password = "dammiedoe123456789";         // gmail password
            $mail->Port = 587;
            $mail->SMTPSecure = "tls";

            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email, $name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ("$email ($subject)");     // headline
            $email_template = '../mail_template.html';

            $message = file_get_contents($email_template);
            $message = str_replace('%lastname%', $lastname, $message);
            $message = str_replace('%timeIn%', $timeIn, $message);
            $message = str_replace('%timeOut%', $timeOut, $message);

            $mail->MsgHTML($message);

            if($mail->send()){
                $status = "success";
                $response = "Email is sent!";
                echo '<br/>'.$status."<br/>".$response;
            } else {
                $status = "failed";
                $response = "Something is wrong: <br/>". $mail->ErrorInfo;
                echo '<br/>'.$status."<br/>".$response;
                echo !extension_loaded('openssl')?"Not Available":"Available";
            }
        } 
    }
    
    public function sendEmailNewMessage($email, $lastname) {
        
        $name = 'JTDV Security Agency';
        $subject = 'You have a new message!';

        if(!empty($email)){

            $mail = new PHPMailer();

            // smtp settings
            // $mail->SMTPDebug = 2;
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "DammiDoe123@gmail.com";  // gmail address
            $mail->Password = "dammiedoe123456789";         // gmail password
            $mail->Port = 587;
            $mail->SMTPSecure = "tls";

            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email, $name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ("$email ($subject)");     // headline
            $email_template = '../mail_template_message.html';

            $message = file_get_contents($email_template);
            $message = str_replace('%lastname%', $lastname, $message);

            $mail->MsgHTML($message);

            if($mail->send()){
                $status = "success";
                $response = "Email is sent!";
                echo '<br/>'.$status."<br/>".$response;
            } else {
                $status = "failed";
                $response = "Something is wrong: <br/>". $mail->ErrorInfo;
                echo '<br/>'.$status."<br/>".$response;
                echo !extension_loaded('openssl')?"Not Available":"Available";
            }
        } 
    }

    //OIC Attendance Functions
    public function submitOICAttendance() {

        if(isset($_POST['timeIn'])) {

            if (isset($_SESSION['GuardsDetails'])) {
                $getSessionEmpId = $_SESSION['GuardsDetails']['empId'];
                $getScheduleTimeIn = $_SESSION['GuardsDetails']['scheduleTimeIn'];
                $getScheduleTimeOut = $_SESSION['GuardsDetails']['scheduleTimeOut'];
                $getShiftSpan = $_SESSION['GuardsDetails']['shift_span'];
            } else {
                $getSessionEmpId = $_SESSION['OICDetails']['empId'];
                $getScheduleTimeIn = $_SESSION['OICDetails']['scheduleTimeIn'];
                $getScheduleTimeOut = $_SESSION['OICDetails']['scheduleTimeOut'];
                $getShiftSpan = $_SESSION['OICDetails']['shift_span'];
            }

            date_default_timezone_set("Asia/Manila");            
            $timenow = strtotime($_POST['timenow']);
            $timein = strtotime($getScheduleTimeIn) - 60*60;
            $timeout = strtotime($getScheduleTimeIn) + 60*60*$getShiftSpan - 60*60;

            if ($timenow >= $timein && $timenow <= $timeout) {
                $this->TimeInValidate();
            } else {
                if (isset($_SESSION['GuardsDetails'])) {
                    $_SESSION['errmsg'] = 'You can only time-in (1 hour) before time schedule';  
                    header('Location: GuardsAttendance.php?msg=time_in_error'); 
                } else {
                    $_SESSION['errmsg'] = 'You can only time-in (1 hour) before time schedule';  
                    header('Location: OICAttendance.php?msg=time_in_error'); 
                }
            }
        }
    }

    public function TimeInValidate() {

        if (isset($_SESSION['GuardsDetails'])) {
            $getSessionEmpId = $_SESSION['GuardsDetails']['empId'];
            $getScheduleTimeIn = $_SESSION['GuardsDetails']['scheduleTimeIn'];
            $getScheduleTimeOut = $_SESSION['GuardsDetails']['scheduleTimeOut'];
            // $getdateIn = $_SESSION['OICDetails']['datetimeIn'];
            $getid = $_SESSION['GuardsDetails']['id'];
        } else {
            $getSessionEmpId = $_SESSION['OICDetails']['empId'];
            $getScheduleTimeIn = $_SESSION['OICDetails']['scheduleTimeIn'];
            $getScheduleTimeOut = $_SESSION['OICDetails']['scheduleTimeOut'];
            // $getdateIn = $_SESSION['OICDetails']['datetimeIn'];
            $getid = $_SESSION['OICDetails']['id'];
        }

            $empId = $getSessionEmpId;
            $timenow = date("h:i:s A", strtotime($_POST['timenow']));
            $datenow = $_POST['datenow'];
            $location = $_POST['location'];
            $login_session = 'true';
            $formatTimeOut = date("h:i:s A", strtotime($getScheduleTimeOut));

            $newScheduleTimeIn = new dateTime($getScheduleTimeIn);
            $newScheduleTimeOut = new dateTime($getScheduleTimeOut);
            $newTimeNow = new dateTime($timenow);

                if($newTimeNow < $newScheduleTimeIn) {
                    $TimeInsert = date("h:i:s A", strtotime($getScheduleTimeIn));
                } else {
                    $TimeInsert = $timenow;
                }


                if ($newScheduleTimeIn <= $newTimeNow) {
                    $status = 'Late';
                } else {
                    $status = 'Good';
                }
    
                $sqlgetLoginSession = "SELECT login_session FROM emp_attendance WHERE login_session = ? AND empId = ?";
                $stmtLoginSession = $this->con()->prepare($sqlgetLoginSession);
                $stmtLoginSession->execute([$login_session, $empId]);
    
                $verify = $stmtLoginSession->fetch();
    
                if ($row = $verify) {
                    echo 'You can only login once.';
                } else {    
                    $getHours = abs(strtotime($getScheduleTimeIn) - strtotime($getScheduleTimeOut)) / 3600;
                    $ConcatTimeDate = strtotime($getScheduleTimeIn." ".$datenow."+ ".$getHours." HOURS");
                    $ConvertToDate = date("Y/m/d", $ConcatTimeDate);
                    $ConvertToDateEventName = date("Y_m_d", $ConcatTimeDate);
                    $ConvertToSched = date("Y-m-d H:i:s", $ConcatTimeDate);
                    $NewEmpId = str_replace('-', '_', $getSessionEmpId);
                    $salary_status = "Unpaid";

                    $customEventname = "time_in_$NewEmpId";

                    $sql = "INSERT INTO emp_attendance(empId, timeIn, datetimeIn, datetimeOut, location, login_session, status) VALUES(?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute([$empId, $TimeInsert, $datenow, $ConvertToDate, $location, $login_session, $status]);
        
                    $users = $stmt->fetch();
                    $countRow = $stmt->rowCount();

                    if($countRow > 0) {
                        $doFunction = "UPDATE `emp_attendance` SET `login_session` = 'false', `timeOut` = '$formatTimeOut', `datetimeOut` = '$ConvertToDate', `salary_status` = '$salary_status' WHERE `empid` = '$empId'; DELETE FROM `do_event` WHERE `event_name` = '$customEventname'";
                        $sqlInsertEvent = "INSERT INTO do_event (event_name, execute_at, do_function) VALUES (?, ?, ?)";
                        $InsertEventStmt = $this->con()->prepare($sqlInsertEvent);
                        $InsertEventStmt->execute([$customEventname, $ConvertToSched, $doFunction]);
                        $CountRowEvent = $InsertEventStmt->rowCount();

                        if ($CountRowEvent > 0) {
                            $_SESSION['successmsg'] = 'Time-in successfully';

                            if (isset($_SESSION['GuardsDetails'])) {
                                header('Location: GuardsAttendance.php?msg=time_in_success');
                            } else {
                                header('Location: OICAttendance.php?msg=time_in_success');
                            }

                        } else {
                            echo 'Time-in error';
                        }

                    }
                }
    }

    public function TimeOutAttendance() {
        if(isset($_POST['timeOut'])) {

            if (isset($_SESSION['GuardsDetails'])) {
                $empId = $_SESSION['GuardsDetails']['empId'];
                $scheduleTimeOut = $_SESSION['GuardsDetails']['scheduleTimeOut'];
            } else {
                $empId = $_SESSION['OICDetails']['empId'];
                $scheduleTimeOut = $_SESSION['OICDetails']['scheduleTimeOut'];
            }
            $timenow = $_POST['timenow'];
            $login_session = 'true';

            $NewTimeNow = new dateTime($timenow);
            $NewSchedTimeOutNoInterval = new dateTime($scheduleTimeOut);
            $NewSchedTimeOut = new dateTime($scheduleTimeOut);
            $NewSchedTimeOut->sub(new DateInterval('PT1H'));

            $sql = "SELECT * FROM emp_attendance WHERE empId = ? AND login_session = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$empId, $login_session]);

            $users = $stmt->fetch();
            $countRow = $stmt->rowCount();

            if ($countRow > 0) {
                if ($NewTimeNow <= $NewSchedTimeOut) {
                    if ($NewTimeNow >= $NewSchedTimeOut && $NewTimeNow <= $NewSchedTimeOutNoInterval) {
                        $this->TimeOutUpdate();
                    } else {
                        $_SESSION['errmsg'] = 'You can only time-out 1 hour before your time out schedule.';
                        if (isset($_SESSION['GuardsDetails'])) {
                            header('Location: GuardsAttendance.php?msg=time_out_error');
                        } else {
                            header('Location: OICAttendance.php?msg=time_out_error');
                        } 
                    }
                } else if ($NewTimeNow >= $NewSchedTimeOut) {
                    if ($NewTimeNow >= $NewSchedTimeOut && $NewTimeNow <= $NewSchedTimeOutNoInterval) {
                        $this->TimeOutUpdate();
                    } else if ($NewTimeNow <= $NewSchedTimeOut && $NewTimeNow <= $NewSchedTimeOutNoInterval) {
                        $this->TimeOutUpdate();
                    } else {
                        $_SESSION['errmsg'] = 'You can only time-out 1 hour before your time out schedule.';  
                        if (isset($_SESSION['GuardsDetails'])) {
                            header('Location: GuardsAttendance.php?msg=time_out_error');
                        } else {
                            header('Location: OICAttendance.php?msg=time_out_error');
                        } 
                    }
                }
            } else {
                echo 'User not found.';
            }
        }
    }

    public function TimeOutUpdate() {

        if (isset($_SESSION['GuardsDetails'])) {
            $getempId = $_SESSION['GuardsDetails']['empId'];
        } else {
            $getempId = $_SESSION['OICDetails']['empId'];
        } 

        $strReplace = str_replace('-', '_', $getempId);
        $textformat = "time_in_".$strReplace;
        $login_session = 'false';
        $timeOut = $_POST['timenow'];
        $salary_status = "Unpaid";

        $sqlTimeOutUpdate = "UPDATE `emp_attendance` SET `login_session` = ?, `timeOut` = ?, `salary_status` = ? WHERE empId = ?; DELETE FROM do_event WHERE event_name = '$textformat'";
        $stmtTimeOutUpdate = $this->con()->prepare($sqlTimeOutUpdate);
        $stmtTimeOutUpdate->execute([$login_session, $timeOut, $salary_status, $getempId]);

        $verify = $stmtTimeOutUpdate->fetch();
        $countRowUpdate = $stmtTimeOutUpdate->rowCount();
        
        $_SESSION['successmsg'] = 'Time-out successfully';

        if (isset($_SESSION['GuardsDetails'])) {
            header('location: GuardsAttendance.php?msg=time_out_success');
            echo $_SESSION['successmsg'];
        } else {
            header('location: OICAttendance.php?msg=time_out_success');
        }
    }

    public function getLocation($company) {
        $sqlLoc = "SELECT * FROM company WHERE company_name = ?";
        $stmtLoc = $this->con()->prepare($sqlLoc);
        $stmtLoc->execute([$company]);

        $usersLoc = $stmtLoc->fetch();
        $countRowLoc = $stmtLoc->rowCount();

        if ($countRowLoc > 0) {
            $_SESSION['Location'] = array('longitude' => $usersLoc->longitude,
                                          'latitude' => $usersLoc->latitude,
                                          'boundary' => $usersLoc->boundary_size,
                                          'comp_location' => $usersLoc->comp_location
                                        );
            return $_SESSION['Location'];
        }
    }

    public function alreadyLogin() {
        
        if (isset($_SESSION['GuardsDetails'])) {
            $getSessionEmpId = $_SESSION['GuardsDetails']['empId'];
        } else {
            $getSessionEmpId = $_SESSION['OICDetails']['empId'];
        }

        $empId = $getSessionEmpId;
        $login_session = 'true';


        $sqlgetLoginSession = "SELECT login_session FROM emp_attendance WHERE login_session = ? AND empId = ?";
        $stmtLoginSession = $this->con()->prepare($sqlgetLoginSession);
        $stmtLoginSession->execute([$login_session, $empId]);

        $verify = $stmtLoginSession->fetch();

        if ($row = $verify) {
            echo '<button type="submit" class="timeOut" name="timeOut">Time-Out</button>';
        } else {
            echo '<button type="submit" class="timeIn" name="timeIn" id="time-in-button" disabled style="opacity: 0.6">Time-in</button>';
        }

    }

    public function getErrorModalMsg() {
        if (isset($_SESSION['errmsg'])) {
            echo '<header class="modal-message">'.$_SESSION['errmsg'].'</header>';
        }
    }

    public function getSuccessModalMsg() {
        if (isset($_SESSION['successmsg'])) {
            echo '<header class="modal-message">'.$_SESSION['successmsg'].'</header>';
        }
    }

    public function showMsgModal() { //ShowErrorModal to dati
        if(isset($_GET['msg'])) {
            $getmsg = $_GET['msg'];

            if ($getmsg == 'request_denied') {
                echo "<script>
                        var x = document.getElementsByClassName('view-modal-error');
                        for (var i=0;i<x.length;i+=1) {
                            x[i].style.display = 'block';
                        }
                    </script>";
            } else if ($getmsg == 'time_out_error') {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-error');
                        viewModal.setAttribute('msg', 'show-modal-error');

                        var x = document.getElementsByClassName('view-modal-error');
                        for (var i=0;i<x.length;i+=1) {
                            x[i].style.display = 'block';
                        }
                    </script>";
            } else if ($getmsg == 'time_in_error') {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-error');
                        viewModal.setAttribute('msg', 'show-modal-error');

                        var x = document.getElementsByClassName('view-modal-error');
                        for (var i=0;i<x.length;i+=1) {
                            x[i].style.display = 'block';
                        }
                    </script>";
            } else if ($getmsg == 'time_in_success') {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-success');
                        viewModal.setAttribute('msg', 'show-modal-success');

                        var x = document.getElementsByClassName('view-modal-success');
                        for (var i=0;i<x.length;i+=1) {
                            x[i].style.display = 'block';
                        }
                    </script>"; 
            } else if ($getmsg == 'time_out_success') {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-success');
                        viewModal.setAttribute('msg', 'show-modal-success');

                        var x = document.getElementsByClassName('view-modal-success');
                        for (var i=0;i<x.length;i+=1) {
                            x[i].style.display = 'block';
                        }
                    </script>"; 
            } else if ($getmsg == 'leave_success') {
                echo "<script>
                        let viewModal = document.querySelector('.view-modal-success');
                        viewModal.setAttribute('msg', 'show-modal-success');

                        var x = document.getElementsByClassName('view-modal-success');
                        for (var i=0;i<x.length;i+=1) {
                            x[i].style.display = 'block';
                        }
                    </script>"; 
            } else if ($getmsg == 'violation_success'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-success');
                viewModal.setAttribute('msg', 'show-modal-success');

                var x = document.getElementsByClassName('view-modal-success');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'update_violation_success'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-success');
                viewModal.setAttribute('msg', 'show-modal-success');

                var x = document.getElementsByClassName('view-modal-success');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'you_did_not_change_anything'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-error');
                viewModal.setAttribute('msg', 'show-modal-error');

                var x = document.getElementsByClassName('view-modal-error');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'delete_violation_success'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-success');
                viewModal.setAttribute('msg', 'show-modal-success');

                var x = document.getElementsByClassName('view-modal-success');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'delete_violation_error'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-error');
                viewModal.setAttribute('msg', 'show-modal-error');

                var x = document.getElementsByClassName('view-modal-error');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'guard_schedule_updated'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-success');
                viewModal.setAttribute('msg', 'show-modal-success');

                var x = document.getElementsByClassName('view-modal-success');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'query_schedule_error'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-error');
                viewModal.setAttribute('msg', 'show-modal-error');

                var x = document.getElementsByClassName('view-modal-error');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'change_info_success'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-success');
                viewModal.setAttribute('msg', 'show-modal-success');

                var x = document.getElementsByClassName('view-modal-success');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'change_info_not_changed'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-error');
                viewModal.setAttribute('msg', 'show-modal-error');

                var x = document.getElementsByClassName('view-modal-error');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'account_incorrect_password'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-error');
                viewModal.setAttribute('msg', 'show-modal-error');

                var x = document.getElementsByClassName('view-modal-error');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'password_not_match'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-error');
                viewModal.setAttribute('msg', 'show-modal-error');

                var x = document.getElementsByClassName('view-modal-error');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'password_length_error'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-error');
                viewModal.setAttribute('msg', 'show-modal-error');

                var x = document.getElementsByClassName('view-modal-error');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'incorrect_current_password'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-error');
                viewModal.setAttribute('msg', 'show-modal-error');

                var x = document.getElementsByClassName('view-modal-error');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'change_password_successfully'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-success');
                viewModal.setAttribute('msg', 'show-modal-success');

                var x = document.getElementsByClassName('view-modal-success');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'mark_absent_request_denied'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-error');
                viewModal.setAttribute('msg', 'show-modal-error');

                var x = document.getElementsByClassName('view-modal-error');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'mark_absent_success'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-success');
                viewModal.setAttribute('msg', 'show-modal-success');

                var x = document.getElementsByClassName('view-modal-success');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'feedback_already_committed'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-error');
                viewModal.setAttribute('msg', 'show-modal-error');

                var x = document.getElementsByClassName('view-modal-error');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            } else if ($getmsg == 'feedback_success'){
                echo "<script>
                let viewModal = document.querySelector('.view-modal-success');
                viewModal.setAttribute('msg', 'show-modal-success');

                var x = document.getElementsByClassName('view-modal-success');
                for (var i=0;i<x.length;i+=1) {
                    x[i].style.display = 'block';
                }
            </script>"; 
            }
        }
    }

    //Show Attendance Function

    public function showyourattendance() {

        if (isset($_SESSION['GuardsDetails'])) {
            $getempId = $_SESSION['GuardsDetails']['empId'];
        } else {
            $getempId = $_SESSION['OICDetails']['empId'];
        }

        $sqlselect = "SELECT * FROM emp_attendance WHERE empId = ? ORDER BY id DESC";
        $stmtselect = $this->con()->prepare($sqlselect);
        $stmtselect->execute([$getempId]);

        while ($users = $stmtselect->fetch()) {

            $timeIn = $users->datetimeIn.'<br>'. date("H:i:s", strtotime($users->timeIn));
            $status = $users->status;
            $timeOutCheck = $users->timeOut;
            if ($timeOutCheck === NULL && $status != 'Absent') {
                $timeOut = "Waiting for time-out";
            } else {
                $timeOut = $users->datetimeOut.'<br>'.date("H:i:s", strtotime($users->timeOut));
            }
        
                echo "<tr>
                        <td>$timeIn</td>
                        <td>$timeOut</td>
                        <td>$status</td>
                    </tr>";
        }
    }

    //Leave Function

    public function submitLeave() {

        if(isset($_POST['submit'])) {

                $typeOfLeave = $_POST['type'];

                if (isset($_SESSION['GuardsDetails'])) {
                    $getId = $_SESSION['GuardsDetails']['empId'];
                } else {
                    $getId = $_SESSION['OICDetails']['empId'];
                }
                $statuscheck = 'Pending';
    
                $sqlfind = "SELECT * FROM leave_request WHERE empId = ? AND status = ?";
                $stmtfind = $this->con()->prepare($sqlfind);
                $stmtfind->execute([$getId, $statuscheck]);
    
                $countRowfind = $stmtfind->rowCount();
    
                if ($countRowfind > 0) {        
                    $_SESSION['errmsg'] = 'You already have a pending request. Please wait until it approves.';
                    if (isset($_SESSION['GuardsDetails'])) {
                        header('Location: GuardsLeave.php?msg=request_denied');
                    } else {
                        header('Location: OICLeave.php?msg=request_denied');
                    }
                } else {

                    if ($typeOfLeave == "Sick Leave" || $typeOfLeave == "Emergency Leave") {
                        $this->insertLeave();
                    } else {
                        $sqlCountLeave = "SELECT COUNT(typeOfLeave) 
                        FROM leave_request 
                        WHERE empId = ? 
                        AND typeOfLeave 
                        IN ('Maternity Leave', 'Paternity Leave', 'Vacation Leave')";

                        $stmtCountLeave = $this->con()->prepare($sqlCountLeave);
                        $stmtCountLeave->execute([$getId]);

                        $getCount = $stmtCountLeave->fetchColumn();

                        if ($getCount > 3) {
                            $_SESSION['errmsg'] = 'You have already reached the maximum leave request for a year. (4 times)';
                            if (isset($_SESSION['GuardsDetails'])) {
                                header('Location: GuardsLeave.php?msg=request_denied');
                            } else {
                                header('Location: OICLeave.php?msg=request_denied');
                            }
                        } else {
                            $this->insertLeave();
                        }
                    }
                }
        }
    }

    public function showyourleave() {

        if (isset($_SESSION['GuardsDetails'])) {
            $getempId = $_SESSION['GuardsDetails']['empId'];
        } else {
            $getempId = $_SESSION['OICDetails']['empId'];
        }

        $sqlselect = "SELECT * FROM leave_request WHERE empId = ? ORDER BY id DESC";
        $stmtselect = $this->con()->prepare($sqlselect);
        $stmtselect->execute([$getempId]);

        while ($users = $stmtselect->fetch()) {
        
            $parse_leave_end = strtotime($users->leave_end);
            $getdate = $this->getDateTime();
            $parse_date_now = strtotime("now". ' '.'Asia/Manila');

            if ($parse_date_now >= $parse_leave_end && $users->status == 'approved') {
                $status = 'Completed';
            } else {
                $status = ucfirst($users->status);
            }
        
            if (isset($_SESSION['GuardsDetails'])) {
                echo "<tr>
                        <td>$users->date_created</td>
                        <td class='table_status'>$status</td>
                        <td><a href='GuardsLeave.php?id=$users->id' id='myBtn'>View</a>
                        </td>
                    </tr>";
            } else {
                echo "<tr>
                        <td>$users->date_created</td>
                        <td class='table_status'>$status</td>
                        <td><a href='OICLeave.php?id=$users->id' id='myBtn'>View</a>
                        </td>
                    </tr>";
            }
        }
    }

    public function viewLeave() {
        if(isset($_GET['id'])){
            $id = $_GET['id'];

            if (isset($_SESSION['GuardsDetails'])) {
                $getempId = $_SESSION['GuardsDetails']['empId'];
            } else {
                $getempId = $_SESSION['OICDetails']['empId'];
            }

            $sql = "SELECT * FROM leave_request WHERE empId = ? AND id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$getempId, $id]);

            $getUserId = $stmt->fetch();

            $countRow = $stmt->rowCount();

            if($countRow > 0){
                $parse_leave_end = strtotime($getUserId->leave_end);
                $getdate = $this->getDateTime();
                $parse_date_now = strtotime("now". ' '.'Asia/Manila');
    
                if ($parse_date_now >= $parse_leave_end && $getUserId->status == 'approved') {
                    $status = 'Completed';
                } else {
                    $status = ucfirst($getUserId->status);
                }
                $type = $getUserId->typeOfLeave;
                $leave_start = $getUserId->leave_start;
                $leave_end = $getUserId->leave_end;
                $showReason = $getUserId->reason;
                $date_created = $getUserId->date_created;

                echo "<script>
                        let viewModal = document.querySelector('.view-modal');
                        viewModal.setAttribute('id', 'show-modal');
                        
                        let status = document.querySelector('#showStatus').value = '$status';
                        let typeOfLeave = document.querySelector('#showType').value = '$type';
                        let leave_start = document.querySelector('#showInputFrom').value = '$leave_start';
                        let leave_end = document.querySelector('#showInputTo').value = '$leave_end';
                        let showReason = document.querySelector('#showReason').value = '$showReason';
                        let date_created = document.querySelector('#showDateCreated').value = '$date_created';
                    </script>";
            }
        }
    }

    public function insertLeave() {
        $getDateTime = $this->getDateTime();

        $dateFrom = $_POST['inputFrom'];
        $dateTo = $_POST['inputTo'];
        $reason = $_POST['reason'];

        date_default_timezone_set('Asia/Manila');
        $timedateNow = date('Y/m/d h:i:s A');

        if (isset($_SESSION['GuardsDetails'])) {
            $getId = $_SESSION['GuardsDetails']['empId'];
        } else {
            $getId = $_SESSION['OICDetails']['empId'];
        }

        $typeOfLeave = $_POST['type'];
        $status = 'Pending';
        $getDateNow = $getDateTime['date'];
        
        $strFrom = $dateFrom;
        $strTo = $dateTo;

        $days = abs(strtotime($dateFrom) - strtotime($dateTo)) / (60 * 60 * 24);

        $sql = "INSERT INTO leave_request (empId, days, leave_start, leave_end, typeOfLeave, reason, status, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$getId, $days, $strFrom, $strTo, $typeOfLeave, $reason, $status, $getDateNow]);

        $countRow = $stmt->rowCount();
        
        if ($countRow > 0) {
            $_SESSION['successmsg'] = 'Request added successfully'; 

            $sqlAddLog = "INSERT INTO emp_log (empId, action, date_created) VALUES (?, ?, ?)";
            $stmtAddLog = $this->con()->prepare($sqlAddLog);
            $stmtAddLog->execute([$getId, 'Request Leave', $timedateNow]);

            if (isset($_SESSION['GuardsDetails'])) {
                header('Location: GuardsLeave.php?msg=leave_success');
            } else {
                header('Location: OICLeave.php?msg=leave_success');
            }
        } else {
            echo 'Error: Please contact the company for this issue.';
        }
    }

    //Violations Function
    public function submitViolation() {
        if(isset($_POST['submit'])) {

            $fine = NULL;

            isset($_POST['overseacup']) ? $fine += 100 : NULL;
            isset($_POST['namecloth']) ? $fine += 100 : NULL;
            isset($_POST['agencynamecloth']) ? $fine += 100 : NULL;
            isset($_POST['belt']) ? $fine += 100 : NULL;
            isset($_POST['buckle']) ? $fine += 100 : NULL;
            isset($_POST['holster']) ? $fine += 100 : NULL;
            isset($_POST['badge']) ? $fine += 100 : NULL;
            isset($_POST['blackshoes']) ? $fine += 100 : NULL;
            isset($_POST['blacksock']) ? $fine += 100 : NULL;
            isset($_POST['nightstick']) ? $fine += 100 : NULL;
            isset($_POST['flashlight']) ? $fine += 100 : NULL;
            isset($_POST['whistle']) ? $fine += 100 : NULL;

            $empId = $_POST['empId'];
            $violation = $_POST['violation'];
            $paid = "Unpaid";

            if (isset($_SESSION['GuardsDetails'])) {
                $getId = $_SESSION['GuardsDetails']['empId'];
            } else {
                $getId = $_SESSION['OICDetails']['empId'];
            }

            date_default_timezone_set('Asia/Manila');
            $timedateNow = date('Y/m/d h:i:s A');

            $getdate = $this->getDateTime();
            $datenow = $getdate['date'];
            
            $paid >= 100 ? $description = "uniform" : $description = NULL;
            
            $sql = "INSERT INTO violationsandremarks (empId, violation, fine, date_created, paid, description) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$empId, $violation, $fine, $datenow, $paid, $description]);

            $users = $stmt->fetch();

            $countRow = $stmt->rowCount();

            if ($countRow > 0) {
                $_SESSION['successmsg'] = 'Violation added successfully';

                $sqlAddLog = "INSERT INTO emp_log (empId, action, date_created) VALUES (?, ?, ?)";
                $stmtAddLog = $this->con()->prepare($sqlAddLog);
                $stmtAddLog->execute([$getId, 'Added Violation to '.$empId, $timedateNow]);

                header('Location: OICViolations.php?msg=violation_success');
            } else {
                echo 'Error!';
            }
        }
    }

    public function SelectGuardsToSet() {
        $company = $_SESSION['OICDetails']['company']; //(❁´◡`❁))
        $empId = $_SESSION['OICDetails']['empId'];

        $sql = "SELECT
                    e.firstname,
                    e.lastname,
                    s.id,
                    s.empId
                FROM schedule s
                INNER JOIN employee e
                ON s.empId = e.empId
                WHERE s.company = ? AND s.empId != ?
                ORDER BY empId ASC";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$company, $empId]);

        while($row = $stmt->fetch()) {
            $fullname = $row->lastname.', '.$row->firstname;
            $empId = $row->empId;

            echo "<tr>
                    <td>$empId</td>
                    <td>$fullname</td>
                    <td><a href='OICViolations.php?id=$row->id'>Set</a>
                    </td>
                </tr>";
        }
    }

    public function showViolations() {

        if (isset($_SESSION['GuardsDetails'])) {

            $getEmpId = $_SESSION['GuardsDetails']['empId'];
            $sqlselect = "SELECT
                            v.id,
                            v.violation,
                            v.date_created,
                            e.firstname,
                            e.lastname,
                            e.email,
                            e.empId
                        FROM violationsandremarks v
                        INNER JOIN employee e 
                        ON v.empId = e.empId
                        WHERE v.empId = ? 
                        ORDER BY id DESC";
            $stmtselect = $this->con()->prepare($sqlselect);
            $stmtselect->execute([$getEmpId]);

            while ($usersSelect = $stmtselect->fetch()) {
                // $fullname = $usersSelect->lastname.', '.$usersSelect->firstname;
                echo "<tr>
                        <td>$usersSelect->date_created</td>
                        <td>$usersSelect->violation</td>
                        <td><a href='GuardsViolations.php?vid=$usersSelect->id'>View</a></td>
                    </tr>";
            }
        } else {
                $sqlselect = "SELECT
                            v.id,
                            v.violation,
                            e.firstname,
                            e.lastname,
                            e.email,
                            e.empId
                        FROM violationsandremarks v
                        INNER JOIN employee e 
                        ON v.empId = e.empId
                        ORDER BY id DESC";
            $stmtselect = $this->con()->query($sqlselect);

            while ($usersSelect = $stmtselect->fetch()) {
                // $fullname = $usersSelect->lastname.', '.$usersSelect->firstname;
                echo "<tr>
                        <td>$usersSelect->empId</td>
                        <td>$usersSelect->violation</td>
                        <td><a href='OICViewViolations.php?vid=$usersSelect->id'>View</a></td>
                    </tr>";
            }
        }
    }

    public function setEmployeeId() {
        if(isset($_GET['id'])){
            $id = $_GET['id'];

            $sql = "SELECT * FROM schedule WHERE id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$id]);

            $getUserId = $stmt->fetch();

            $countRow = $stmt->rowCount();

            if($countRow > 0){
                $getEmpId = $getUserId->empId;

                echo "<script>
                         let empId = document.querySelector('#empId').value = '$getEmpId';
                      </script>";
            }
        }
    }

    //View Violations Function

    public function UpdateViolation() {

        if(isset($_POST['update'])) {
            $getvid = $_GET['vid'];
            $updateviolation = $_POST['showViolation'];

            $sql = "UPDATE violationsandremarks SET violation = ? WHERE id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$updateviolation, $getvid]);

            $users = $stmt->fetch();

            $countRow = $stmt->rowCount();

            if ($countRow > 0) {
                $_SESSION['successmsg'] = 'Violation updated successfully'; 
                header('location: OICViewViolations.php?msg=update_violation_success');
            } else {
                $_SESSION['errmsg'] = 'You did not change anything';
                header('location: OICViewViolations.php?msg=you_did_not_change_anything');
            }
        }

    }

    public function DeleteViolation() {
        if(isset($_POST['delete'])) {
            $getvid = $_GET['vid'];

            $sql = "DELETE FROM violationsandremarks WHERE id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$getvid]);

            $user = $stmt->fetch();
            $countRow = $stmt->rowCount();

            if ($countRow > 0) {
                $_SESSION['successmsg'] = 'Violation deleted successfully'; 
                header('location: OICViewViolations.php?msg=delete_violation_success');
            } else {
                $_SESSION['successmsg'] = 'There is an error to the system'; 
                header('location: OICViewViolations.php?msg=delete_violation_error');
            }
        } 
    }

    public function showModalViolation() {
        if(isset($_GET['vid'])){
            $vid = $_GET['vid'];

            $sql = "SELECT v.*,
                            i.body
                    FROM violationsandremarks v 

                    LEFT JOIN inbox i
                    ON v.remark = i.id

                    WHERE v.id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$vid]);

            $getUserId = $stmt->fetch();

            $countRow = $stmt->rowCount();

            if($countRow > 0){
                $empId = $getUserId->empId;
                $violation = $getUserId->violation;
                $violationfine = $getUserId->fine;
                $remark = $getUserId->body;
                $date_created = $getUserId->date_created;

                echo "<script>
                        let viewModal = document.querySelector('.view-modal');
                        viewModal.setAttribute('id', 'show-modal');
                        
                        let showempId = document.querySelector('#showempId').value = '$empId';
                        let showViolation = document.querySelector('#showViolation').value = '$violation';
                        let showViolationFine = document.querySelector('#showViolationFine').value = '$violationfine';
                        let showRemark = document.querySelector('#showRemark').value = `$remark`;
                        let date_created = document.querySelector('#showDateCreated').value = '$date_created';
                    </script>";
            }
        }
    }

    //Assign Guards Function

    public function updateGuards() {
        if(isset($_POST['updateGuards'])) {
            $getId = $_GET['vid'];
            $timeIn = $_POST['timeIn'].':00';
            $timeOut = $_POST['timeOut'];
            $shiftSpan = $_POST['shiftSpan'];
            $shift = $_POST['shift'];
    
            $newtimeIn = strtotime($timeIn);
            $newtimeOut = strtotime($timeOut);
    
            $strTimeIn = date('h:i:s A', $newtimeIn);
            $strTimeOut = date('h:i:s A', $newtimeOut);

            $sql = "UPDATE schedule
                    SET scheduleTimeIn = ?,
                        scheduleTimeOut = ?,
                        shift_span = ?,
                        shift = ?
                    WHERE id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$strTimeIn, $strTimeOut, $shiftSpan, $shift, $getId]);

            $countRow = $stmt->rowCount();

            if ($countRow > 0) {

                $avail = 'New';
                $postempId = $_POST['employeeId'];

                $strReplace = str_replace('-', '_', $postempId);
                $event_name = "Sched_".$strReplace;
                $nextDay = date('Y-m-d H:i:s', strtotime(date("Y/m/d").' 1 DAY'));
                $doFunction = "UPDATE `employee` SET `availability` = 'Unavailable' WHERE `empId` = '$postempId'; DELETE FROM `do_event` WHERE `event_name` = '$event_name'";

                $sqlavail = "BEGIN;
                                UPDATE employee SET availability = ? WHERE empId = ?;
                                INSERT INTO do_event (event_name, execute_at, do_function) VALUES (?, ?, ?);
                            COMMIT;";
                $stmtavail = $this->con()->prepare($sqlavail);
                $stmtavail->execute([$avail, $postempId, $event_name, $nextDay, $doFunction]);

                $countRowavail = $stmtavail->rowCount();

                echo $avail .'<br>'. $postempId .'<br>'. $event_name .'<br>'. $nextDay .'<br>'. $doFunction;

                if ($countRowavail > 0) {

                    $this->ScheduleEmailGuards();
                } else {
                    $this->ScheduleEmailGuards();

                }
            } else {
                $_SESSION['errmsg'] = 'You did not change anything';
                header('location: OICAssignGuards.php?msg=query_schedule_error');
            }

        }
    }

    public function ShowAssignGuards() {
        $company = $_SESSION['OICDetails']['company']; //(❁´◡`❁))
        $empId = $_SESSION['OICDetails']['empId'];

        $sql = "SELECT
                    e.firstname,
                    e.lastname,
                    s.scheduleTimeOut,
                    s.scheduleTimeIn,
                    s.empId,
                    s.id
                FROM schedule s
                INNER JOIN employee e
                ON s.empId = e.empId
                WHERE s.company = ? AND s.empId != ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$company, $empId]);

        while($row = $stmt->fetch()) {
            $fullname = $row->lastname.', '.$row->firstname;
            
            if ($row->scheduleTimeIn === NULL || $row->scheduleTimeOut === NULL) {
                $scheduled = 'No';
            } else {
                $scheduled = 'Yes';
            }

            echo "<tr>
                    <td><a href='OICViewProfile.php?id=$row->empId'>$fullname</a></td>
                    <td class='schedule_table'>$scheduled</td>
                    <td><a href='OICAssignGuards.php?vid=$row->id'>View</a>
                    </td>
                </tr>";
        }
    }

    public function CountAssignGuards() {

        $empId = $_SESSION['OICDetails']['empId'];
        $company = $_SESSION['OICDetails']['company'];

        $sql = "SELECT COuNT(*) FROM schedule WHERE company = ? AND empId != ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$company, $empId]);

        $count = $stmt->fetchColumn();

        echo $count;
    }

    public function CountScheduledGuards() {

        $empId = $_SESSION['OICDetails']['empId'];
        $company = $_SESSION['OICDetails']['company'];

        $sql = "SELECT COUNT(*)
                FROM schedule 
                WHERE company = ?
                AND empId != ? 
                AND scheduleTimeIn 
                AND scheduleTimeOut IS NOT NULL";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$company, $empId]);

        $count = $stmt->fetchColumn();

        echo $count;
    }

    public function CountDayShiftGuards() {

        $empId = $_SESSION['OICDetails']['empId'];
        $company = $_SESSION['OICDetails']['company'];
        $shift = 'First Shift';
        $sql = "SELECT COUNT(*)
                FROM schedule 
                WHERE company = ?
                AND empId != ? 
                AND shift = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$company, $empId, $shift]);

        $count = $stmt->fetchColumn();

        echo $count;
    }

    public function CountNightShiftGuards() {

        $empId = $_SESSION['OICDetails']['empId'];
        $company = $_SESSION['OICDetails']['company'];
        $shift = 'Second Shift';
        $sql = "SELECT COUNT(*)
                FROM schedule 
                WHERE company = ?
                AND empId != ? 
                AND shift = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$company, $empId, $shift]);

        $count = $stmt->fetchColumn();

        echo $count;
    }

    public function ShowSpecificGuards() {

        if(isset($_GET['vid'])){
            $id = $_GET['vid'];
            $company = $_SESSION['OICDetails']['company']; //(❁´◡`❁))

            $sql = "SELECT * FROM schedule WHERE company = ? AND id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$company, $id]);

            $getUserId = $stmt->fetch();

            $countRow = $stmt->rowCount();

            if($countRow > 0) {
                $companyName = $getUserId->company;
                $employeeId = $getUserId->empId;

                if ($getUserId->scheduleTimeOut === NULL) {
                    $timeOut = NULL;
                } else {
                    $timeOut = date("H:i", strtotime($getUserId->scheduleTimeOut));
                }

                if ($getUserId->scheduleTimeIn === NULL) {
                    $timeIn = NULL;
                } else {                
                    $originalTimeIn = $getUserId->scheduleTimeIn;
                    $parseToTimeDate = strtotime($originalTimeIn);
                    $timeIn = date("H", $parseToTimeDate);
                }

                if ($getUserId->shift_span === NULL) {
                    $shift_span = NULL;
                } else {                
                    $shift_span = $getUserId->shift_span;
                }

                if ($getUserId->shift === NULL) {
                    $shift = NULL;
                } else {                
                    $shift = $getUserId->shift;
                }

                echo "<script>
                        let viewModal = document.querySelector('.view-modal');
                        viewModal.setAttribute('id', 'show-modal');

                        let companyName = document.querySelector('#companyName').value = '$companyName';
                        let employeeId = document.querySelector('#employeeId').value = '$employeeId';
                        let timeIn = document.querySelector('#timeIn').value = '$timeIn';
                        let timeOut = document.querySelector('#timeOut').value = '$timeOut';
                        let shift_span = document.querySelector('#shiftSpan').value = '$shift_span';
                        let shift = document.querySelector('#shift').value = '$shift';

                    </script>";
            }
        }
    }

    public function ScheduleEmailGuards() {
        $timeIn = $_POST['timeIn'].':00';
        $timeOut = $_POST['timeOut'];
        $shiftSpan = $_POST['shiftSpan'];
        $shift = $_POST['shift'];

        $newtimeIn = strtotime($timeIn);
        $newtimeOut = strtotime($timeOut);

        $strTimeIn = date('h:i:s A', $newtimeIn);
        $strTimeOut = date('h:i:s A', $newtimeOut);

        $postempId = $_POST['employeeId'];
        $sqlGetInfo = "SELECT * FROM employee WHERE empId = ?";
        $stmtGetInfo = $this->con()->prepare($sqlGetInfo);
        $stmtGetInfo->execute([$postempId]);

        $usersGetInfo = $stmtGetInfo->fetch();
        $countRowGetInfo = $stmtGetInfo->rowCount();

        if ($countRowGetInfo > 0) {
            $email = $usersGetInfo->email;
            $lastname = $usersGetInfo->lastname;

            $this->sendEmailSchedule($email, $lastname, $strTimeIn, $strTimeOut);
            $_SESSION['successmsg'] = 'Schedule updated successfully';
            header('location: OICAssignGuards.php?msg=guard_schedule_updated');
        }
    }

    //Monitor Guards Function

    public function ShowAbsentModal() {
        if(isset($_GET['aid'])) {
            $aid = $_GET['aid'];
    
            echo "<script>
                    let viewModal = document.querySelector('.view-modal');
                    viewModal.setAttribute('id', 'show-modal');
                </script>";
        }
    }

    public function ShowVoidModal() {
        if(isset($_GET['void'])) {
            $aid = $_GET['void'];
    
            echo "<script>
                    let viewModal = document.querySelector('.view-modal-void');
                    viewModal.setAttribute('id', 'show-modal');
                </script>";
        }
    }

    public function MarkAsAbsent() {

        if (isset($_POST['proceed'])) {

            $getId = $_GET['aid'];
            $sqlCheck = "SELECT * FROM schedule WHERE empId = ?";
            $stmtCheck = $this->con()->prepare($sqlCheck);
            $stmtCheck->execute([$getId]);
        
            $usersCheck = $stmtCheck->fetch();
            $countRowCheck = $stmtCheck->rowCount();
        
            if ($countRowCheck > 0) {
                $hours = $usersCheck->shift_span;

                date_default_timezone_set('Asia/Manila');
                $timeIn = strtotime($usersCheck->scheduleTimeIn);
                $timeOut = strtotime($usersCheck->scheduleTimeIn) + 60*60*$hours;
                $timeNow = strtotime(date("h:i:s A"));
                $strtimeNow = date("h:i:s A");
                $dateNow = strtotime(date("Y/m/d"));

                if ($timeNow >= $timeIn && $timeNow <= $timeOut) {
                    $dateNow = date("Y/m/d");
                    $violation = "Absent Without Official Leave (AWOL)";
                    $availability = "Absent";
                    $status = "Absent";
                    $login_session = "false";

                    //For Event only
                    $strReplace = str_replace('-', '_', $getId);
                    $event_name = "Absent_".$strReplace;
                    $nextDay = date('Y-m-d H:i:s', strtotime(date("Y/m/d").' 1 DAY'));
                    $doFunction = "UPDATE `employee` SET `availability` = 'Unavailable' WHERE `empId` = '$getId'; DELETE FROM `do_event` WHERE `event_name` = '$event_name'";
        
                    $sqlUpdate = "BEGIN;
                                    INSERT INTO emp_attendance (empId, timeIn, timeOut, datetimeIn, datetimeOut, status, login_session) VALUES (?, ?, ?, ?, ?, ?, ?);
                                    INSERT INTO violationsandremarks (empId, violation, date_created) VALUES (?, ?, ?);
                                    UPDATE employee SET availability = ? WHERE empId = ?;
                                    INSERT INTO do_event (event_name, execute_at, do_function) VALUES (?, ?, ?); 
                                COMMIT;";
        
                    $stmtUpdate = $this->con()->prepare($sqlUpdate);
                    $stmtUpdate->execute([$getId, $strtimeNow, $strtimeNow, $dateNow, $dateNow, $status, $login_session, $getId, $violation, $dateNow, $availability, $getId, $event_name, $nextDay, $doFunction]);
        
                    $sqlFind = "SELECT * FROM employee WHERE empId = ?";
                    $stmtFind = $this->con()->prepare($sqlFind);
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
                        $generateId = date("dmy") . time();
                        
                        $sqlInbox = "INSERT INTO inbox (id, empId, subject, body, date_created, status) VALUES (?,?,?,?,?,?)";
                        $stmtInbox = $this->con()->prepare($sqlInbox);
                        $stmtInbox->execute([$generateId, $empId, $subject, $body, $date_created, $status]);
                        
                        $countRowInbox = $stmtInbox->rowCount();
                        
                        if ($countRowInbox > 0) {
                            $this->sendEmailNewMessage($email, $lastname);
                            $_SESSION['successmsg'] = "The selected guard has been successfully marked as absent and not be able to login to our system for the day."; 
                             header("location: OICMonitorGuards.php?msg=mark_absent_success");
                        } else {
                            //It must be an error;
                        }
                    }
                } else {
                    $_SESSION['errmsg'] = "You can't mark this as Absent Without Official Leave (AWOL) when this guards' schedule isn't started yet"; 
                    header("location: OICMonitorGuards.php?msg=mark_absent_request_denied");
                }
            }
        }
    }

    public function MarkAsVoid() {
        if (isset($_POST['proceed-void'])) {

            $getId = $_GET['void'];
            $sqlCheck = "SELECT * FROM schedule WHERE empId = ?";
            $stmtCheck = $this->con()->prepare($sqlCheck);
            $stmtCheck->execute([$getId]);
        
            $usersCheck = $stmtCheck->fetch();
            $countRowCheck = $stmtCheck->rowCount();
        
            if ($countRowCheck > 0) {
                date_default_timezone_set('Asia/Manila');
                $dateNow = date("Y/m/d");
                $violation = "Absent Without Official Leave (AWOL)";
                $availability = "Absent";
                $status = "Absent";
                $login_session = "false";
                $strReplace = str_replace('-', '_', $getId);
                $event_name = "time_in_".$strReplace;
                $strtimeNow = date("h:i:s A");

                //New event name
                $newEventName = "Absent_".$strReplace;
                $nextDay = date('Y-m-d H:i:s', strtotime(date("Y/m/d").' 1 DAY'));
                $doFunction = "UPDATE `employee` SET `availability` = 'Unavailable' WHERE `empId` = '$getId'; DELETE FROM `do_event` WHERE `event_name` = '$newEventName'";



                $sqlUpdate = "BEGIN;
                                INSERT INTO do_event (event_name, execute_at, do_function) VALUES (?, ?, ?); 
                                INSERT INTO emp_attendance (empId, timeIn, timeOut, datetimeIn, datetimeOut, status, login_session) VALUES (?, ?, ?, ?, ?, ?, ?);
                                INSERT INTO violationsandremarks (empId, violation, date_created) VALUES (?, ?, ?);
                                UPDATE employee SET availability = ? WHERE empId = ?;
                                DELETE FROM do_event WHERE event_name = ?;
                                DELETE FROM emp_attendance WHERE datetimeIn = ? AND empId = ? AND status != ?;
                            COMMIT;";
        
                $stmtUpdate = $this->con()->prepare($sqlUpdate);
                $stmtUpdate->execute([$newEventName, $nextDay, $doFunction, $getId, $strtimeNow, $strtimeNow, $dateNow, $dateNow, $status, $login_session, $getId, $violation, $dateNow, $availability, $getId, $event_name, $dateNow, $getId, $status]);
                
                $sqlFind = "SELECT * FROM employee WHERE empId = ?";
                $stmtFind = $this->con()->prepare($sqlFind);
                $stmtFind->execute([$getId]);
                    
                $usersFind = $stmtFind->fetch();
                $countRowFind = $stmtFind->rowCount();
                
                if ($countRowFind > 0) {
                    $email = $usersFind->email;
                    $lastname = $usersFind->lastname;
                        
                    $empId = $getId;
                    $subject = "Marked as Absent";
                    $body = "It seems like you are not in your post for less than hour while you are timed-in. We void your attenendance and you may not be able to time-in for this day.

If you think that it is just a mistake, you may comply to our agency to solve this issue.";
                    $date_created = date("Y/m/d h:i:s A");
                    $status = "Unread";
                    $generateId = date("dmy") . time();
                        
                    $sqlInbox = "INSERT INTO inbox (id, empId, subject, body, date_created, status) VALUES (?,?,?,?,?,?)";
                    $stmtInbox = $this->con()->prepare($sqlInbox);
                    $stmtInbox->execute([$generateId, $empId, $subject, $body, $date_created, $status]);
                        
                    $countRowInbox = $stmtInbox->rowCount();
                    
                    if ($countRowInbox > 0) {
                        $_SESSION['successmsg'] = "The selected guard has been successfully marked as absent and not be able to login to our system for the day."; 
                        header("location: OICMonitorGuards.php?msg=mark_absent_success");
                    } else {
                        //It must be an error;
                    }
                    
                }
            }
        }
    }

    public function ShowMonitoringGuards() {
        $company = $_SESSION['OICDetails']['company']; //(❁´◡`❁))
        $empId = $_SESSION['OICDetails']['empId'];

        $sql = "SELECT
                    s.id,
                    s.empId,
                    e.firstname,
                    e.lastname,
                    att.timeIn,
                    att.login_session
                FROM schedule s
                LEFT JOIN emp_attendance att
                ON s.empId = att.empId AND att.login_session = 'true'
                INNER JOIN employee e
                ON s.empId = e.empId
                WHERE s.company = ? AND s.empId != ? AND e.availability = 'Unavailable'";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$company, $empId]);

        while($row = $stmt->fetch()) {
            $fullname = $row->lastname.', '.$row->firstname;
            $dateNow = date("Y/m/d");

            if ($row->login_session == 'true') {
                
                $timeIn = 'Yes';
    
                echo "<tr>
                        <td>$fullname</td>
                        <td class='schedule_table'>$timeIn</td>
                        <td><a href='OICMonitorGuards.php?void=$row->empId'>Void</td>
                    </tr>";
            } else {
                $timeIn = 'No';

                echo "<tr>
                        <td>$fullname</td>
                        <td class='schedule_table'>$timeIn</td>
                        <td></td>
                    </tr>";
            }
        }
    }

    //OIC Profile Function

    public function checkStatusProfile() {

        if (isset($_SESSION['GuardsDetails'])) {
            $getEmpId = $_SESSION['GuardsDetails']['empId'];
        } else {
            $getEmpId = $_SESSION['OICDetails']['empId'];
        }
        $login_session = 'true';

        $sql = "SELECT * 
                FROM emp_attendance 
                WHERE empId = ? AND login_session = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$getEmpId, $login_session]);

        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();
        
        if ($users = $stmt->fetch()) {
            $timeIn = date('H:i:s', strtotime($users->timeIn));
        } else {
            $timeIn = NULL;
        }
        // $timeIn = date('H:i:s', strtotime($users->timeIn));
        //Balik ako

        if ($countRow > 0) {
            echo "<span class='material-icons' style='color:rgb(25, 199, 115)'>circle</span>
                  <span>On-duty |&nbsp</span>
                  <span>$timeIn</span>";
        } else {
            echo "<span class='material-icons' style='color:#af1f1f'>circle</span>
                  <span>Off-duty</span>";
        }

    }

    //Manage Account Function

    public function changeInfo() {
        isset($_SESSION['GuardsDetails']) ? $empId = $_SESSION['GuardsDetails']['empId'] : $empId = $_SESSION['OICDetails']['empId'];

        if(isset($_POST['submit'])) {
            $contact = $_POST['contact'];
            $address = $_POST['address'];

            $sql = "UPDATE employee SET `cpnumber` = ?, `address` = ? WHERE `empId` = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$contact, $address, $empId]);

            $countRow = $stmt->rowCount();

            if ($countRow > 0) {
                $_SESSION['successmsg'] = "Your personal information updated successfully.";
                header('location: ?msg=change_info_success');
            } else {
                $_SESSION['errmsg'] = "You did not change anything";
                header('location: ?msg=you_did_not_change_anything');
            }
        }
    }

    public function changePasswordValidate() {
        if (isset($_POST['submit'])) {

            if (isset($_SESSION['GuardsDetails'])) {
                $empId = $_SESSION['GuardsDetails']['empId'];
            } else {
                $empId = $_SESSION['OICDetails']['empId'];
            }

            $hash_password = $this->generatedPassword($_POST['password']);
            $text_password = $_POST['newpassword'];
            $new_password = $this->generatedPassword($_POST['newpassword']);
            $confirm_password = $this->generatedPassword($_POST['confirmpassword']);

            $sql = "SELECT * FROM employee WHERE empId = ? AND password = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$empId, $hash_password[0]]);

            $users = $stmt->fetch();
            $countRow = $stmt->rowCount();

            if ($countRow > 0) {

                if ($new_password[0] !== $confirm_password[0]) {
                    $_SESSION['errmsg'] = "Your new and confirm password does not match";
                    if (isset($_SESSION['GuardsDetails'])) {
                        header('location: GuardsChangePassword.php?msg=password_not_match');
                    } else {
                        header('location: OICChangePassword.php?msg=password_not_match');
                    }
                } else if (strlen($text_password) < 8) {
                    $_SESSION['errmsg'] = "Password must be greater than 7 alphanumeric characters.";
                    if (isset($_SESSION['GuardsDetails'])) {
                        header('location: GuardsChangePassword.php?msg=password_length_error');
                    } else {
                        header('location: OICChangePassword.php?msg=password_length_error');
                    }
                } else {

                    $sqlUpdate = "BEGIN;
                                    UPDATE employee SET password = ? WHERE empId = ?;
                                    UPDATE secret_diarye SET secret_key = ? WHERE e_id = ?;
                                COMMIT;";
                    $stmtUpdate = $this->con()->prepare($sqlUpdate);
                    $stmtUpdate->execute([$new_password[0], $empId, $text_password, $users->email]);

                    $usersUpdate = $stmtUpdate->fetch();
                    $countRow = $stmtUpdate->rowCount();
{
                        $_SESSION['successmsg'] = "Password was changed successfully";
                        if (isset($_SESSION['GuardsDetails'])) {
                            header('location: GuardsChangePassword.php?msg=change_password_successfully');
                        } else {
                            header('location: OICChangePassword.php?msg=change_password_successfully');
                        }
                    }
                }

            } else {
                $_SESSION['errmsg'] = "You entered an incorrect current password";
                if (isset($_SESSION['GuardsDetails'])) {
                    header('location: GuardsChangePassword.php?msg=incorrect_current_password');
                } else {
                    header('location: OICChangePassword.php?msg=incorrect_current_password');
                }
            }
        }
    }

    //QR Attendance Function

    public function QRTimeInValidate() {

        if (isset($_SESSION['qremail']) && isset($_SESSION['qrpass']) && isset($_SESSION['seed'])) {
            $email = $_SESSION['qremail'];
            $password = $_SESSION['qrpass'];
            $seed = $_SESSION['seed'];
            $getSeed = $_GET['seed'];
        }
        
        date_default_timezone_set('Asia/Manila');

        $sql = "SELECT 
                s.empId,
                s.scheduleTimeIn,
                s.scheduleTimeOut,
                s.company,
                c.comp_location,
                e.lastname
            FROM employee e 
        
            INNER JOIN schedule s
            ON e.empId = s.empId

            LEFT JOIN company c
            ON s.company = c.company_name
        
            WHERE e.email = ? AND e.password = ? AND e.qrcode = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$email, $password, $seed]);

        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if ($countRow > 0) {
            $lastname = $users->lastname;
            $getSessionEmpId = $users->empId;
            $getScheduleTimeIn = $users->scheduleTimeIn;
            $getScheduleTimeOut = $users->scheduleTimeOut;
            $empId = $users->empId;
            $timenow = date("h:i:s A");
            $datenow = date("Y/m/d");
            $location = $users->comp_location;
            $login_session = 'true';

            $newScheduleTimeIn = new dateTime($getScheduleTimeIn);
            $newScheduleTimeOut = new dateTime($getScheduleTimeOut);
            $newTimeNow = new dateTime($timenow);

            if($newTimeNow < $newScheduleTimeIn) {
                    $TimeInsert = $getScheduleTimeIn;
            } else {
                $TimeInsert = $timenow;
            }


            if ($newScheduleTimeIn <= $newTimeNow) {
                $status = 'Late';
            } else {
                $status = 'Good';
            }

            $sqlgetLoginSession = "SELECT login_session FROM emp_attendance WHERE login_session = ? AND empId = ?";
            $stmtLoginSession = $this->con()->prepare($sqlgetLoginSession);
            $stmtLoginSession->execute([$login_session, $empId]);
    
            $verify = $stmtLoginSession->fetch();
            $countRowVerify = $stmtLoginSession->rowCount();

            if ($countRowVerify > 0) {
                $_SESSION['login_session'] = "already";
            } else {
                    
                $getHours = abs(strtotime($getScheduleTimeIn) - strtotime($getScheduleTimeOut)) / 3600;
                $ConcatTimeDate = strtotime($getScheduleTimeIn." ".$datenow."+ ".$getHours." HOURS");
                $ConvertToDate = date("Y/m/d", $ConcatTimeDate);
                $ConvertToDateEventName = date("Y_m_d", $ConcatTimeDate);
                $ConvertToSched = date("Y-m-d H:i:s", $ConcatTimeDate);
                $NewEmpId = str_replace('-', '_', $getSessionEmpId);
                $salary_status = "Unpaid";

                $customEventname = "time_in_$NewEmpId";

                $sql = "INSERT INTO emp_attendance(empId, timeIn, datetimeIn, datetimeOut, location, login_session, status) VALUES(?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->con()->prepare($sql);
                $stmt->execute([$empId, $TimeInsert, $datenow, $ConvertToDate, $location, $login_session, $status]);
        
                $users = $stmt->fetch();
                $countRow = $stmt->rowCount();
                    
                if($countRow > 0) {
                    $doFunction = "UPDATE `emp_attendance` SET `login_session` = 'false', `timeOut` = '$getScheduleTimeOut', `datetimeOut` = '$ConvertToDate', `salary_status` = '$salary_status' WHERE `empid` = '$empId'; DELETE FROM `do_event` WHERE `event_name` = '$customEventname'";
                    $sqlInsertEvent = "INSERT INTO do_event (event_name, execute_at, do_function) VALUES (?, ?, ?)";
                    $InsertEventStmt = $this->con()->prepare($sqlInsertEvent);
                    $InsertEventStmt->execute([$customEventname, $ConvertToSched, $doFunction]);
                    $CountRowEvent = $InsertEventStmt->rowCount();

                    if ($CountRowEvent > 0) {
                        $_SESSION['login_session'] = "true";
                    }
                }
            }
        }
    }

    public function submitQRAttendance() {

        if (isset($_SESSION['qremail']) && isset($_SESSION['qrpass']) && isset($_SESSION['seed'])) {
            $email = $_SESSION['qremail'];
            $password = $_SESSION['qrpass'];
            $seed = $_SESSION['seed'];
            $getSeed = $_GET['seed'];
        }

        $sql = "SELECT 
                s.empId,
                s.scheduleTimeIn,
                s.shift_span,
                e.lastname
            FROM employee e 
        
            INNER JOIN schedule s
            ON e.empId = s.empId
        
            WHERE email = ? AND password = ? AND qrcode = ?";
        
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$email, $password, $seed]);

        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if ($countRow > 0) {
            $lastname = $users->lastname;
            $getSessionEmpId = $users->empId;
            $getScheduleTimeIn = $users->scheduleTimeIn;
            $getShiftSpan = $users->shift_span;
            $empId = $users->empId;

            date_default_timezone_set("Asia/Manila");
            $timenow = strtotime(date("h:i:s A"));
            $timein = strtotime($getScheduleTimeIn) - 60*60;
            $timeout = strtotime($getScheduleTimeIn) + 60*60*$getShiftSpan - 60*60;

            if ($timenow >= $timein && $timenow <= $timeout) {
                $this->QRTimeInValidate();
            } else {
                $_SESSION['login_session'] = "false";
            }
        }
    }
    
    public function popupMessage() {

        isset($_SESSION['GuardsDetails']) ? $empId = $_SESSION['GuardsDetails']['empId'] : $empId = $_SESSION['OICDetails']['empId'];
        isset($_SESSION['GuardsDetails']) ? $link = 'GuardsInbox.php' : $link = 'OICInbox.php';
        $status = "Unread";

        $sql = "SELECT * FROM inbox WHERE empId = ? AND status = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$empId, $status]);
        
        $users = $stmt->rowCount();

        if ($users > 0) {
            echo "<div class='popup-message' id='popup-message'>
                    <a href =$link>
                        <p><span class='material-icons-outlined'>email</span>You have $users unread message! Please check your inbox.</p>
                    </a>
                </div>
                <div class='popup-message-close' id='popup-message-close'>
                    <span class='close'>&times;</span>
                </div>";
        }
    }

    public function notificationBadge() {

        isset($_SESSION['GuardsDetails']) ? $empId = $_SESSION['GuardsDetails']['empId'] : $empId = $_SESSION['OICDetails']['empId'];
        isset($_SESSION['GuardsDetails']) ? $link = 'GuardsInbox.php' : $link = 'OICInbox.php';
        $status = "Unread";

        $sql = "SELECT * FROM inbox WHERE empId = ? AND status = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$empId, $status]);
        
        $users = $stmt->rowCount();

        if ($users > 0) {
            echo "<span class='material-icons'>circle</span>";
        }
    }

    public function getInbox() {

        if (isset($_POST['submit'])) {

            $search = '%'.$_POST['search'].'%';

            isset($_SESSION['GuardsDetails']) ? $empId = $_SESSION['GuardsDetails']['empId'] : $empId = $_SESSION['OICDetails']['empId'];
            isset($_SESSION['GuardsDetails']) ? $link = 'GuardsInboxView.php' : $link = 'OICInboxView.php';
    
            $sql = "SELECT * FROM inbox WHERE empId = ? AND subject LIKE ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$empId, $search]);
    
            while ($row = $stmt->fetch()) {

                $date = date("Y/m/d", strtotime($row->date_created));
                echo "<tr>
                        <td>$date</td>
                        <td>$row->subject</td>
                        <td class='table_status'>$row->status</td>
                        <td><a href='$link?id=$row->id'>View</a></td>
                    </tr>";
            }


        } else {
            isset($_SESSION['GuardsDetails']) ? $empId = $_SESSION['GuardsDetails']['empId'] : $empId = $_SESSION['OICDetails']['empId'];
            isset($_SESSION['GuardsDetails']) ? $link = 'GuardsInboxView.php' : $link = 'OICInboxView.php';
    
            $sql = "SELECT * FROM inbox WHERE empId = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$empId]);
            while ($row = $stmt->fetch()) {
    
                $date = date("Y/m/d", strtotime($row->date_created));
    
                echo "<tr>
                        <td>$date</td>
                        <td>$row->subject</td>
                        <td class='table_status'>$row->status</td>
                        <td><a href='$link?id=$row->id'>View</a></td>
                    </tr>";
            }
        }
    }

    //Code sa WEB VERSION
    public function viewListViolation() {
        $sql = "SELECT * FROM violationsandremarks WHERE remark IS NULL";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
    
        while ($row = $stmt->fetch()) {
            echo "<tr>
                    <td>$row->empId</td>
                    <td>$row->violation</td>
                    <td>$row->date_created</td>
                    <td><a href='?id=$row->id'>View</a> <a href='?rid=$row->id'>Remark</a></td>
                </tr>";
        }
    }
    
    public function viewListRemarkedViolation() {
        $sql = "SELECT 
                    v.remark,
                    v.empId,
                    i.*,
                    e.firstname,
                    e.lastname
                FROM violationsandremarks v
    
                INNER JOIN inbox i
                ON v.remark = i.id
    
                INNER JOIN employee e
                ON v.empId = e.empId
    
                WHERE v.remark IS NOT NULL";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
    
        while ($row = $stmt->fetch()) {
            echo "<tr>
                    <td>$row->empId</td>
                    <td>$row->firstname $row->lastname</td>
                    <td>$row->subject</td>
                    <td>$row->date_created</td>
                    <td><a href='?lrid=$row->id'>View</a></td>
                </tr>";
        }
    }
    
    public function viewModalListRemarkedViolation() {
        if (isset($_GET['lrid'])) {
            $lrid = $_GET['lrid'];
                
            $sqlView = "SELECT
                            i.*,
                            e.firstname,
                            e.lastname
                        FROM inbox i
    
                        INNER JOIN employee e
                        ON i.empId = e.empId
                            
                        WHERE i.id = ?";
            $stmtView = $this->con()->prepare($sqlView);
            $stmtView->execute([$lrid]);
    
            $usersView = $stmtView->fetch();
            $countRowView = $stmtView->rowCount();
    
            if ($countRowView > 0) {
                echo "<div>
                        <h1>View</h1>
    
                        <div>
                            <label for='showName'>Name</label>
                            <input type='text' id='showName' value='$usersView->firstname $usersView->lastname' readonly>
                        </div>
    
                        <div>
                            <label for='showEmpID'>Employee ID</label>
                            <input type='text' id='showEmpID' value='$usersView->empId' readonly>
                        </div>
                    
                        <div>
                            <label for='showSubject'>Subject</label>
                            <input type='text' id='showSubject' value='$usersView->subject' readonly>
                        </div>
                    
                        <div>
                            <label for='showBody'>Remark</label>
                            <br>
                            <textarea style='resize: none; width: 30%; padding: 6px 20px; height: 100px;' id='showBody' readonly>$usersView->body</textarea>
                        </div>
                    
                        <div>
                            <label for='showDate'>Date</label>
                            <input type='text' id='showDate' value='$usersView->date_created' readonly>
                        </div>
                    </div>";
            }
        }
    }
    
    public function viewModalViolation() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
                
            $sqlView = "SELECT v.*,
                                e.*
                        FROM violationsandremarks v
    
                        INNER JOIN employee e
                        ON v.empId = e.empId
                            
                        WHERE v.id = ? AND v.remark IS NULL";
            $stmtView = $this->con()->prepare($sqlView);
            $stmtView->execute([$id]);
    
            $usersView = $stmtView->fetch();
            $countRowView = $stmtView->rowCount();
    
            if ($countRowView > 0) {
                echo "<div>
                        <h1>View</h1>
    
                        <div>
                            <label for='showName'>Name</label>
                            <input type='text' id='showName' value='$usersView->firstname $usersView->lastname' readonly>
                        </div>
    
                        <div>
                            <label for='showEmpID'>Employee ID</label>
                            <input type='text' id='showEmpID' value='$usersView->empId' readonly>
                        </div>
                    
                        <div>
                            <label for='showViolation'>Violation</label>
                            <input type='text' id='showViolation' value='$usersView->violation' readonly>
                        </div>
                    
                        <div>
                            <label for='showFine'>Fine</label>
                            <input type='text' id='showFine' value='$usersView->fine' placeholder='No fine' readonly>
                        </div>
                    
                        <div>
                            <label for='showDate'>Date</label>
                            <input type='text' id='showDate' value='$usersView->date_created' readonly>
                        </div>
                    </div>";
            }
        }
    }
    
    public function addModalRemarks() {
    
        if (isset($_GET['rid'])) {
            $rid = $_GET['rid'];
                
            $sqlRem = "SELECT v.*,
                                e.*
                        FROM violationsandremarks v
    
                        INNER JOIN employee e
                        ON v.empId = e.empId
                            
                        WHERE v.id = ? AND v.remark IS NULL";
            $stmtRem = $this->con()->prepare($sqlRem);
            $stmtRem->execute([$rid]);
    
            $usersRem = $stmtRem->fetch();
            $countRowRem = $stmtRem->rowCount();
    
            if ($countRowRem > 0) {
    
                if ($usersRem->fine != NULL) {          
                    $autoRemark = "Violation: $usersRem->violation&#013;&#013;Fine: $usersRem->fine.00&#013;&#013;(Insert message here)";
                } else {
                    $autoRemark = "Violation: $usersRem->violation&#013;&#013;(Insert message)";
                }
    
                echo "<div>
                        <h1>Remark</h1>
                        <form method='post' enctype='multipart/form-data'>
                            <div>
                                <label for='fullname'>Name</label>
                                <input type='text' id='fullname' name='fullname' value='$usersRem->firstname $usersRem->lastname' readonly>
                            </div>
    
                            <div>
                                <label for='empid'>Employee ID</label>
                                <input type='text' id='empid' name='empid' value='$usersRem->empId' readonly>
                            </div>
    
                            <div>
                                <label for='subject'>Subject</label>
                                <input type='text' id='subject' name='subject' placeholder='Enter a subject' required>
                            </div>
    
                            <div>
                                <label for='body'>Remark</label>
                                <br>
                                <textarea style='resize: none; width: 30%; padding: 6px 20px; height: 100px;' id='body' name='body' maxlength='255' placeholder='Max of 255 characters.' required>$autoRemark</textarea>
                            </div>
    
                            <div>
                                <input type='file' name='file' id='file'>
                            </div>
    
                            <input type='submit' name='submit'>
                        </form>
                    </div>";
            }
    
            if (isset($_POST['submit'])) {
                    
                // Declaring Variables
                date_default_timezone_set('Asia/Manila');
                $location = "inbox/";

                if ($_FILES['file']['name'] != NULL) {
                    $file_new_name = date("dmy") . time() . $_FILES["file"]["name"]; // New and unique name of uploaded file
                    $file_name = $_FILES["file"]["name"]; // Get uploaded file name
                } else {
                    $file_new_name = NULL;
                    $file_name = NULL;
                }

                    
                $file_temp = $_FILES["file"]["tmp_name"]; // Get uploaded file temp
                $file_size = $_FILES["file"]["size"]; // Get uploaded file size
    
                //$_POST Variable
    
                $empId = $_POST['empid'];
                $subject = $_POST['subject'];
                $body = $_POST['body'];
                $date_created = date("Y/m/d h:i:s A");
                $status = "Unread";
                $generateId = date("dmy") . time();
    
                /*
                How we can get mb from bytes
                (mb*1024)*1024
    
                In my case i'm 10 mb limit
                (10*1024)*1024
                */
    
                $fileExt = explode('.', $file_name);
                $fileActualExt = strtolower(end($fileExt));
    
                $allowed = array('docx', 'pdf', NULL);
    
                if (in_array($fileActualExt, $allowed)) {
                    if ($file_size > 10485760) { // Check file size 10mb or not
                        echo "Woops! File is too big. Maximum file size allowed for upload 10 MB.";
                    } else {
                        $sql = "INSERT INTO inbox (id, empId, subject, body, filename, filenewname, date_created, status)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$generateId, $empId, $subject, $body, $file_name, $file_new_name, $date_created, $status]);
                
                        $countRow = $stmt->rowCount();
                
                        if ($countRow > 0) {
                            //Update the remark data on the violationsandremarks table
                            $sqlUpdate = "UPDATE violationsandremarks SET remark = ? WHERE id = ?";
                            $stmtUpdate = $this->con()->prepare($sqlUpdate);
                            $stmtUpdate->execute([$generateId, $rid]);
    
                            //This will move the uploaded file to the specific location
                            move_uploaded_file($file_temp, $location . $file_new_name);
                            header('Location: remarks.php?msg=success');
                        }
                    }
                } else {
                    echo 'Cannot upload this type of file.';
                }     
            }
        }
    }
    
    public function addFeedback() {
        if (isset($_POST['feedback'])) {

            isset($_SESSION['GuardsDetails']) ? $fullname = $_SESSION['GuardsDetails']['fullname'] : $fullname = $_SESSION['OICDetails']['fullname'];
            isset($_SESSION['GuardsDetails']) ? $position = $_SESSION['GuardsDetails']['position'] : $position = $_SESSION['OICDetails']['position'];
            $category = $_POST['category'];
            $comment = $_POST['comment'];
            date_default_timezone_set('Asia/Manila');
            $date_created = date("Y/m/d");

            $sqlFind = "SELECT * FROM feedback WHERE fullname = ? ORDER BY date_created DESC LIMIT 1";
            $stmtFind = $this->con()->prepare($sqlFind);
            $stmtFind->execute([$fullname]);

            $usersFind = $stmtFind->fetch();

            $countRowFind = $stmtFind->rowCount();

            if ($countRowFind > 0 || $countRowFind == NULL) {
                if ($usersFind->date_created != $date_created) {
                    $sql = "INSERT INTO feedback (fullname, position, category, comment, date_created) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute([$fullname, $position, $category, $comment, $date_created]);
        
                    $users = $stmt->fetch();
                    $countRow = $stmt->rowCount();
        
                    if ($countRow > 0) {
                        $_SESSION['successmsg'] = "Your request has sent successfully.";
                        header('location: ?msg=feedback_success');
                    } else {
                        header('location: ?msg=feedback_failed');
                    }
                } else {
                    $_SESSION['errmsg'] = "You can only submit feedback once a day.";
                    header('location: ?msg=feedback_already_committed');
                }
            }
        }
    }
}

$payroll = new Payroll();
?>