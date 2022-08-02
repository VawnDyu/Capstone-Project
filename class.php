<?php

require_once "index2.php";
$credentials = array('e_username' => $e_username, 
                     'e_password' => $e_password);

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

    private $e_username;
    private $e_password;

    public function __construct(){
        global $credentials;

        $this->e_username = &$credentials['e_username'];
        $this->e_password = &$credentials['e_password'];
    }


    public function con()
    {
        $this->pdo = new PDO($this->dns, $this->username, $this->password);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return $this->pdo;
    }

    public function closeCon()
    {
        $this->pdo = null;
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

    // server maintenance
    public function maintenance(){
        $isMaintenance = 1;
        $maintenanceModule = 'Head Manager';
        $sql = "SELECT * FROM maintenance WHERE status = ? AND module = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$isMaintenance, $maintenanceModule]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            header('Location: https://www.jtdv.tech/maintenance.html');
        } else {
            return null;
        }
    }

    public function login()
    {
        // set 5 attempts
        session_start();
        if(!isset($_SESSION['attempts'])){
            $_SESSION['attempts'] = 5;
        }

        // create email and password using session
        if(!isset($_SESSION['reservedEmail']) && !isset($_SESSION['reservedPassword'])){
            $_SESSION['reservedEmail'] = "";
            $_SESSION['reservedPassword'] = "";
        }

        // if attempts hits 2
        if($_SESSION['attempts'] == 2){
            
            if(isset($_POST['login'])){

                $username = $_POST['username'];
                $password = $this->generatedPassword($_POST['password']);

                if(empty($username) && empty($password)){
                    $msg = 'Input field are required to login';
                    echo "<script>window.location.assign('./login.php?errormessage=$msg');</script>";
                } else {
                    
                    // if user input === reservedEmail
                    if($username === $_SESSION['reservedEmail']){

                        $sqlAttempt2 = "SELECT * FROM super_admin WHERE username = ? AND password = ?";
                        $stmtAttempt2 = $this->con()->prepare($sqlAttempt2);
                        $stmtAttempt2->execute([$_SESSION['reservedEmail'], $password[0]]);
                        $usersAttempt2 = $stmtAttempt2->fetch();
                        $countRowAttempt2 = $stmtAttempt2->rowCount();

                        // if no row detected
                        if($countRowAttempt2 < 1){

                            // dito natin gawin
                            $sqlGetPass =  "SELECT 
                                                    sa.username,
                                                    sd.secret_key as secret_key
                                            FROM super_admin sa
                                            INNER JOIN secret_diary sd
                                            ON sa.username = sd.sa_id
                                            WHERE sa.username = '$username'
                                            ";
                            $stmtGetPass = $this->con()->query($sqlGetPass);
                            $userGetPass = $stmtGetPass->fetch();

                            // send user credentials
                            $this->sendEmail($_SESSION['reservedEmail'], $userGetPass->secret_key);
                            $_SESSION['attempts'] -= 1; // decrease 1 attempt to current attempts
                            $msg = 'No of attempts: '.$_SESSION['attempts'];
                            echo "<script>window.location.assign('./login.php?errormessage=$msg');</script>";
                        } else {
                            // if row detected
                            $fullname = $usersAttempt2->firstname." ".$usersAttempt2->lastname;
                            $action = "Login";
                            $table_name = 'Login';

                            // set timezone and get date and time
                            $datetime = $this->getDateTime(); 
                            $time = $datetime['time'];
                            $date = $datetime['date'];


                            // add to admin_log
                            $sqlLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date)
                                       VALUES(?, ?, ?, ?, ?, ?)
                                      ";
                            $stmtLog = $this->con()->prepare($sqlLog);
                            $stmtLog->execute([$usersAttempt2->id, $fullname, $action, $table_name, $time, $date]);
                            $countRowLog = $stmtLog->rowCount();

                            // if insert is successful
                            if($countRowLog > 0){

                                $_SESSION['attempts'] = 5; // reset, back to 5
                                unset($_SESSION['reservedEmail']);
                                unset($_SESSION['reservedPassword']);

                                // create user details using session
                                $_SESSION['adminDetails'] = array('fullname' => $fullname,
                                                                'access' => $usersAttempt2->access,
                                                                'id' => $usersAttempt2->id
                                                                );
                                header('Location: dashboard.php'); // redirect to dashboard.php
                                return $_SESSION['adminDetails']; // after calling the function, return session
                            }
                        }
                    }
                }
            }
        } else if($_SESSION['attempts'] == 0){ // if attempts bring down to 0
            
            // select username na gumamit ng 5 attempts
            $reservedEmail = $_SESSION['reservedEmail'];
            $setTimerSql = "SELECT * FROM super_admin WHERE username = ?";
            $stmtTimer = $this->con()->prepare($setTimerSql);
            $stmtTimer->execute([$reservedEmail]);
            $usersTimer = $stmtTimer->fetch();
            $countRowTimer = $stmtTimer->rowCount();

            // kapag may nadetect na ganong username
            if($countRowTimer > 0){
                // get id of that username
                $userId = $usersTimer->id;
                $userAccess = $usersTimer->access;
                $accessSuspended = "suspended";

                $datetimeGet = $this->getDateTime();
                $finalData = $datetimeGet['date']." ".date('h:i:sa', strtotime('+6 hours'));

                // update column timer set value to DATENOW - 6HRS
                
                $updateTimerSql = "UPDATE super_admin 
                                   SET timer = ?, 
                                       access = ?
                                   WHERE id = ?;
                                  ";
                $updateTimerStmt = $this->con()->prepare($updateTimerSql);
                $updateTimerStmt->execute([$finalData, $accessSuspended, $userId]);
                $updateCountRow = $updateTimerStmt->rowCount();

                // checking if the column was updated already
                if($updateCountRow > 0){
                    session_destroy(); // destroy all the session
                    $msg = 'System has been locked for 6 hrs';
                    echo "<script>window.location.assign('./login.php?errormessage=$msg');</script>";
                } else {
                    session_destroy();
                }
            } else {
                $msg = 'Username is not exists';
                echo "<script>window.location.assign('./login.php?errormessage=$msg');</script>";
            }
        } else {
            // if user hit login button
            if(isset($_POST['login'])){

                // get input data
                $username = $_POST['username'];
                // $password = md5($_POST['password']);
                $password = $this->generatedPassword($_POST['password']);
    
                // if username and password are empty
                if(empty($username) && empty($password[0])){
                    $msg = 'All input fields are required to login.';
                    echo "<script>window.location.assign('./login.php?errormessage=$msg');</script>";
                } else {
                    // check if email is exist using a function
                    $checkEmailArray = $this->checkEmailExist($username); // returns an array(true, cho@gmail.com)
                    $passwordArray = $checkEmailArray[1]; // password ni cho na naka md5

                    // kapag ang unang array ay nag true
                    if($checkEmailArray[0]){

                        $suspendedAccess = 'suspended';
                        
                        // find account that matches the username and password
                        $sql = "SELECT * FROM super_admin WHERE username = ? AND password = ?";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$username, $password[0]]);
                        $users = $stmt->fetch();
                        $countRow = $stmt->rowCount();
        
                        // if account exists
                        if($countRow > 0){

                            if($users->access != $suspendedAccess){
                                $fullname = $users->firstname." ".$users->lastname; // create fullname
                                $action = "Login"; 
                                $table_name = "Login";    

                                // set timezone and get date and time
                                $datetime = $this->getDateTime(); 
                                $time = $datetime['time'];
                                $date = $datetime['date'];
                
                                // insert mo sa activity log ni admin
                                $actLogSql = "INSERT INTO admin_log(`admin_id`,
                                                                    `name`, 
                                                                    `action`,
                                                                    `table_name`,
                                                                    `time`,
                                                                    `date`
                                                                    )
                                              VALUES(?, ?, ?, ?, ?, ?)";
                                $actLogStmt = $this->con()->prepare($actLogSql);
                                $actLogStmt->execute([$users->id, $fullname, $action, $table_name, $time, $date]);
                
                                // create user details using session
                                session_start();
                                $_SESSION['attempts'] = 5;
                                $_SESSION['adminDetails'] = array('fullname' => $fullname,
                                                                  'access' => $users->access,
                                                                  'id' => $users->id
                                                                  );
                                unset($_SESSION['reservedEmail']);
                                unset($_SESSION['reservedPassword']);

                                header('Location: dashboard.php'); // redirect to dashboard.php
                                return $_SESSION['adminDetails']; // after calling the function, return session
                            } else {
                                $dateExpiredArray = $this->formatDateLocked($users->timer);
                                $dateExpired = implode(" ", $dateExpiredArray);

                                // set timezone and get date and time
                                $datetime = $this->getDateTime();
                                $time = $datetime['time'];
                                $date = $datetime['date'];

                                // format current date and time
                                $checkDateTimeNowArray = $this->formatDateLocked($date." ".$time);
                                $checkDateTimeNow = implode(" ", $checkDateTimeNowArray);

                                // check if user->timer date was expired
                                if(strtotime($dateExpired) < strtotime($checkDateTimeNow)){
                                    
                                    // timer end, back to its default state
                                    $varNull = NULL;
                                    $setAccess = 'administrator';
                                    $sqlUpdateTimer = "UPDATE super_admin SET timer = ?, access = ? WHERE id = ?";
                                    $stmtUpdateTimer = $this->con()->prepare($sqlUpdateTimer);
                                    $stmtUpdateTimer->execute([$varNull, $setAccess, $users->id]);

                                    $fullname = $users->firstname." ".$users->lastname; // create fullname
                                    $action = "Login"; 
                                    $table_name = "Login"; 
                                        
                                    // set timezone and get date and time
                                    $datetime = $this->getDateTime(); 
                                    $time = $datetime['time'];
                                    $date = $datetime['date'];
                    
                                    // insert mo sa activity log ni admin
                                    $actLogSql = "INSERT INTO admin_log(`admin_id`,
                                                                        `name`, 
                                                                        `action`,
                                                                        `table_name`,
                                                                        `time`,
                                                                        `date`
                                                                        )
                                                VALUES(?, ?, ?, ?, ?, ?)";
                                    $actLogStmt = $this->con()->prepare($actLogSql);
                                    $actLogStmt->execute([$users->id, $fullname, $action, $table_name, $time, $date]);
                    
                                    // create user details using session
                                    session_start();
                                    $_SESSION['attempts'] = 5;
                                    $_SESSION['adminDetails'] = array('fullname' => $fullname,
                                                                    'access' => $users->access,
                                                                    'id' => $users->id
                                                                    );
                                    unset($_SESSION['reservedEmail']);
                                    unset($_SESSION['reservedPassword']);

                                    header('Location: dashboard.php'); // redirect to dashboard.php
                                    return $_SESSION['adminDetails']; // after calling the function, return session
                                } else {
                                    $msg = 'Your account has been locked until '.
                                           'Date: '.$dateExpired;
                                    echo "<script>window.location.assign('./login.php?errormessage=$msg');</script>";
                                }
                            } 
                        } else {
                            // insert here, pag suspended na tas naglogin ulit same email dapat yung attempt will set to 0
                            
                            $_SESSION['attempts'] -= 1; // decrease 1 attempt to current attempts
                            $msg = 'Username and password are not matched. No of attempts: '.$_SESSION['attempts'];
                            echo "<script>window.location.assign('./login.php?errormessage=$msg');</script>";
                            
                            $_SESSION['reservedEmail'] = $username; // blank to kanina, nagkaron na ng laman
                            $_SESSION['reservedPassword'] = $passwordArray; // blank to kanina, nagkaron na ng laman
                        }
                    } else {
                        $msg = 'Your email is not exist in our system.';
                        echo "<script>window.location.assign('./login.php?errormessage=$msg');</script>";
                    }
                }
            }
        }
    }

    public function formatDateLocked($date)
    {
        $dateArray = explode(" ", $date);

        $dateExpired = date("F j Y", strtotime($dateArray[0])); // date
        $timeExpired = date("h:i:s A", strtotime($dateArray[1])); // time
        return array($dateExpired, $timeExpired);
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

    public function sendEmail($email, $password)
    {
        
        $name = 'JTDV Incorporation';
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
            $mail->Username = $this->e_username;  // gmail address
            $mail->Password = $this->e_password;  // gmail password

            $mail->Port = 465;
            $mail->SMTPSecure = "ssl";

            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email, $name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ("$email");                // headline
            $mail->Body = $body;                        // textarea

            $mail->send();
          
        } 
    }

    public function logout()
    {
        $this->pdo = null;
        session_destroy();
        $this->closeCon();
        header('Location: login.php');
    }

    // vonne
    public function mobile_logout() {
        $this->pdo = null;
        session_start();
        session_destroy();
        header('Location: m_login.php');
    }

    // get login session
    public function getSessionData()
    {
        session_start();
        if($_SESSION['adminDetails']){
            return $_SESSION['adminDetails'];
        }
    }

    public function verifyUserAccess($access, $fullname, $level)
    {
        $message = 'You are not allowed to enter the system';
        if($level == 2){
            $level = '../';
            
            if($access == 'administrator'){
                return;
            } elseif($access == 'secretary'){
                echo 'Welcome '.$fullname.' ('.$access.')';
            } else {
                header("Location: ".$level."login.php?message2=$message");
            }
        } else {
            if($access == 'administrator'){
                return;
            } elseif($access == 'secretary'){
                // red
                echo 'Welcome '.$fullname.' ('.$access.')';
            } else {
                header("Location: login.php?message2=$message");
            }
        }
    }


    // for secretary functionality in admin
    public function addSecretary($id, $fullnameAdmin)
    {
        if(isset($_POST['addsecretary'])){
            $fullname = $_POST['fullname'];
            $cpnumber = $_POST['cpnumber'];
            $email = $_POST['email'];
            $gender = $_POST['gender'];
            $address = $_POST['address'];
            $access = "secretary";
            // generated password
            $realPassword = $this->generatedPassword2();
            $dbPassword = $this->generatedPassword($realPassword);
            $isDeleted = 0;

            $timer = NULL;

            if(empty($fullname) &&
               empty($email) &&
               empty($gender) &&
               empty($address) &&
               empty($realPassword) &&
               empty($dbPassword) &&
               empty($isDeleted)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input fields are required!</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {
                
                // check if secretary fullname and email already exists
                $sqlFindAccNot = "SELECT * FROM secretary WHERE fullname = ? AND email = ? AND isDeleted = 0";
                $stmtFindAccNot = $this->con()->prepare($sqlFindAccNot);
                $stmtFindAccNot->execute([$fullname, $email]);
                $userFindAccNot = $stmtFindAccNot->fetch();
                $countRowFindAccNot = $stmtFindAccNot->rowCount();

                // check if secretary fullname and email already exists
                $sqlFindAcc = "SELECT * FROM secretary WHERE fullname = ? AND email = ? AND isDeleted = 1";
                $stmtFindAcc = $this->con()->prepare($sqlFindAcc);
                $stmtFindAcc->execute([$fullname, $email]);
                $userFindAcc = $stmtFindAcc->fetch();
                $countRowFindAcc = $stmtFindAcc->rowCount();

                if($countRowFindAccNot > 0){ 
                    $msg = "Account Already Exists.";
                    echo "<script>window.location.assign('secretary.php?message2=$msg');</script>";
                } elseif($countRowFindAcc > 0){
                    $msg = "Account Already Exists. Request Restoration.";
                    echo "<script>window.location.assign('secretary.php?message2=$msg');</script>";
                } elseif($this->checkSecEmailExist($email)){
                    $msg = "Email Already Exists";
                    echo "<script>window.location.assign('secretary.php?message2=$msg');</script>";
                } else {

                    // set timezone and get date and time
                    $datetime = $this->getDateTime(); 
                    $time = $datetime['time'];
                    $date = $datetime['date'];

                    $sql = "INSERT INTO secretary(fullname, 
                                                  gender, 
                                                  cpnumber, 
                                                  address, 
                                                  email, 
                                                  password,
                                                  timer, 
                                                  admin_id,
                                                  access,
                                                  isDeleted
                                                  )
                            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute([$fullname, $gender, $cpnumber, $address, $email, $dbPassword[0], $timer, $id, $access, $isDeleted]);
                    $users = $stmt->fetch();
                    $countRow = $stmt->rowCount();

                    if($countRow > 0){

                        // gagamitin pang login sa employee dashboard
                        $sqlSecretKeySecretary = "INSERT INTO secret_diarys(se_id, secret_key)
                                                  VALUES(?, ?)";
                        $stmtSecretKeySecretary = $this->con()->prepare($sqlSecretKeySecretary);
                        $stmtSecretKeySecretary->execute([$email, $realPassword]);
                        // send user credentials
                        $this->sendEmail($email, $realPassword);

                        $action = "Add";
                        $table_name = "Secretary";
                        $admindatetime = $this->getDateTime();
                        $adminTime = $admindatetime['time'];
                        $adminDate = $admindatetime['date'];
                                                            
                        $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                        $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                        $stmtAdminLog->execute([$id, $fullnameAdmin, $action, $table_name, $adminTime, $adminDate]);
                                                            
                        $countRowAdminLog = $stmtAdminLog->rowCount();
                        if($countRowAdminLog > 0){
                            $msg = 'New Data was Added';
                            echo "<script>window.location.assign('./secretary.php?message=$msg');</script>";
                        } else {
                            $msg = "No Data Added";
                            echo "<script>window.location.assign('secretary.php?message2=$msg');</script>";
                        }
                    } else {
                        $msg = "Create Failed";
                        echo "<script>window.location.assign('secretary.php?message2=$msg');</script>";
                    }
                }

            }
        }
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


    // show only 2 record of secretary
    public function show2Secretary()
    {
        $sql = "SELECT fullname, access, gender, id FROM secretary WHERE isDeleted = 0 ORDER BY id DESC LIMIT 2";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        $total = 0;

        if($countRow == 0){
            echo "<div class='left-svg'>
                    <div class='left-svg-headline'>
                        <div class='left-svg-top'>
                            <h2 style='width:170px;'>0 Data in Secretary Account</h2>
                            <button><div class='circle'></div></button>
                        </div>
                        <di class='left-svg-bottom'>
                            <div class='profile'>
                                <object data='../styles/SVG_modified/nosec.svg' type='image/svg+xml'></object>
                            </div>
                            <div class='profile-text'>
                                <h3>No data found</h3>
                                <p>Secretary</p>
                            </div>
                        </di>
                    </div>
                    <div class='left-svg-image'>
                        <object data='../styles/SVG_modified/leftsecretary.svg' type='image/svg+xml'></object>
                    </div>
                  </div>";
        }

        while($row = $stmt->fetch()){
            
            $gender = "";
            if($row->gender == 'Male'){
                $gender = "<object data='../styles/SVG_modified/malesec.svg' type='image/svg+xml'></object>";
            } else {
                $gender = "<object data='../styles/SVG_modified/femalesec.svg' type='image/svg+xml'></object>";
            }
            
            
            $total = $total + 1;
            if($total == 1){
                echo "<div class='left-svg'>
                        <div class='left-svg-headline'>
                            <div class='left-svg-top'>
                                <h2>Role as Secretary</h2>
                                <button><div class='circle'></div> <a href='./secretary.php?secId=$row->id'>View</a></button>
                            </div>
                            <di class='left-svg-bottom'>
                                <div class='profile'>
                                    $gender
                                </div>
                                <div class='profile-text'>
                                    <h3>$row->fullname</h3>
                                    <p>$row->access</p>
                                </div>
                            </di>
                        </div>
                        <div class='left-svg-image'>
                            <object data='../styles/SVG_modified/leftsecretary.svg' type='image/svg+xml'></object>
                        </div>
                      </div>";
            }

            if($total == 2){
                echo "<div class='right-svg'>
                        <div class='right-svg-headline'>
                            <div class='right-svg-top'>
                                <h2>Role as Secretary</h2>
                                <button><div class='circle'></div> <a href='./secretary.php?secId=$row->id'>View</a></button>
                            </div>
                            <div class='right-svg-bottom'>
                                <div class='profile'>
                                    $gender
                                </div>
                                <div class='profile-text'>
                                    <h3>$row->fullname</h3>
                                    <p>$row->access</p>
                                </div>
                            </div>
                        </div>
                        <div class='right-svg-image'>
                            <object data='../styles/SVG_modified/rightsecretary.svg' type='image/svg+xml'></object>
                        </div>
                      </div>";
            }

        }
    }

    public function secretaryLogs()
    {
        $sql = "SELECT sl.*, s.fullname 
                FROM secretary_log sl
                INNER JOIN secretary s
                ON sl.sec_id = s.id
                ORDER BY sl.date DESC";
        $stmt = $this->con()->query($sql);

        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->fullname</td>
                        <td>$row->action</td>
                        <td>$row->date</td>
                      </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }

    public function showAllSecretary()
    {
        $sql = "SELECT * FROM secretary WHERE isDeleted = 0 ORDER BY id DESC";
        $stmt = $this->con()->query($sql);

        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->fullname</td>
                        <td>$row->gender</td>
                        <td>$row->email</td>
                        <td>
                            <div class='buttons'>
                                <a href='showAll.php?secId=$row->id'><span class='material-icons'>visibility</span></a>
                                <a href='showAll.php?secId=$row->id&email=$row->email'><span class='material-icons'>edit</span></a>
                                <a href='showAll.php?secIdDelete=$row->id'><span class='material-icons'>delete</span></a>
                            </div>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }


    public function showAllSecretarySearch($search)
    {
        $sql = "SELECT * FROM secretary 
                WHERE isDeleted = 0 AND fullname LIKE '%$search%' OR
                      isDeleted = 0 AND gender LIKE '%$search%' OR
                      isDeleted = 0 AND email LIKE '%$search%'
                ORDER BY id DESC";
        $stmt = $this->con()->query($sql);

        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->fullname</td>
                        <td>$row->gender</td>
                        <td>$row->email</td>
                        <td>
                            <div class='buttons'>
                                <a href='showAll.php?secId=$row->id'><span class='material-icons'>visibility</span></a>
                                <a href='showAll.php?secId=$row->id&email=$row->email'><span class='material-icons'>edit</span></a>
                                <a href='showAll.php?secIdDelete=$row->id'><span class='material-icons'>delete</span></a>
                            </div>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }

    public function showSpecificSec($id)
    {

        $sql = "SELECT * FROM secretary WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            $fullname = $user->fullname;
            $gender = $user->gender;
            $email = $user->email;
            $cpnumber = $user->cpnumber;
            $address = $user->address;

            echo "  <div class='view-modal'>
                        <div class='modal-holder'>
                            <div class='viewmodal-header'>
                                <h1>View Details</h1>
                                <span class='material-icons' id='viewModalClose'>close</span>
                            </div>
                            <div class='viewmodal-content'>
                                <form method='post'>
                                    <div>
                                        <label for='fullname'>Fullname</label>
                                        <input type='text' name='fullname' id='fullname' value='$fullname' readonly/>
                                    </div>
                                    <div>
                                        <label for='gender'>Gender</label>
                                        <select name='gender' id='gender' required disabled>
                                            <option value='$gender'>$gender</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for='email'>Email</label>
                                        <input type='email' name='email' id='email' value='$email' readonly/>
                                    </div>
                                    <div>
                                        <label for=''>Contact Number</label>
                                        <input type='text' name='cpnumber' id='cpnumber' value='$cpnumber' readonly/>
                                    </div>
                                    <div>
                                        <label for='address'>Address</label>
                                        <input type='text' name='address' id='address' value='$address' readonly/>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <script>
                        // close modal
                        let viewModalClose = document.querySelector('#viewModalClose');
                        viewModalClose.addEventListener('click', () => {
                            let viewModal = document.querySelector('.view-modal');
                            viewModal.style.display = 'none';
                        });
                    </script>";
        }
    }

    public function editModalShow($id)
    {
        $sql = "SELECT * FROM secretary WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            $fullname = $user->fullname;
            $gender = $user->gender;
            $gender2 = $gender == 'Male' ? 'Female' : 'Male';
            $email = $user->email;
            $cpnumber = $user->cpnumber;
            $address = $user->address;

            echo "  <div class='edit-modal'>
                        <div class='modal-holder'>
                            <div class='editmodal-header'>
                                <h1>Edit Details</h1>
                                <span class='material-icons' id='editModalClose'>close</span>
                            </div>
                            <div class='editmodal-content'>
                                <form method='post'>
                                    <div>
                                        <label for='fullname'>Fullname</label>
                                        <input type='text' name='fullname' id='fullname' autofocus value='$fullname' onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' autocomplete='off' required/>
                                    </div>
                                    <div>
                                        <label for='gender'>Gender</label>
                                        <select name='gender' id='gender' required>
                                            <option value='$gender'>$gender</option>
                                            <option value='$gender2'>$gender2</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for='email'>Email</label>
                                        <input type='email' name='email' id='email' value='$email' autocomplete='off' required/>
                                    </div>
                                    <div>
                                        <label for=''>Contact Number</label>
                                        <input type='text' name='cpnumber' id='cpnumber' value='$cpnumber' maxlength='11' placeholder='09' onkeypress='validate(event)' autocomplete='off' required/>
                                    </div>
                                    <div>
                                        <label for='address'>Address</label>
                                        <input type='text' name='address' id='address' value='$address' autocomplete='off' required/>
                                    </div>
                                    <div>
                                        <button type='submit' name='updateSec' id='updateBtn' class='btn_primary'>Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <script>
                        // close modal
                        let editModalClose = document.querySelector('#editModalClose');
                        editModalClose.addEventListener('click', () => {
                            let editModal = document.querySelector('.edit-modal');
                            editModal.style.display = 'none';
                        });
                        // disable not necessary inputs
                        function validate(evt) {
                            var theEvent = evt || window.event;

                            // Handle paste
                            if (theEvent.type === 'paste') {
                                key = event.clipboardData.getData('text/plain');
                            } else {
                            // Handle key press
                                var key = theEvent.keyCode || theEvent.which;
                                key = String.fromCharCode(key);
                            }
                            var regex = /[0-9]|\./;
                            if( !regex.test(key) ) {
                                theEvent.returnValue = false;
                                if(theEvent.preventDefault) theEvent.preventDefault();
                            }
                        }

                        // check if contact number equal to 11
                        let btnPrimary = document.querySelector('.btn_primary');
                        let mobilePrimary = document.querySelector('#cpnumber');
                        let minLength = 11;
                        btnPrimary.addEventListener('click', validateMobile);

                        function validateMobile(event) {
                            if (mobilePrimary.value.length < minLength) {
                                event.preventDefault();

                                // create error message box
                                let errorDiv = document.createElement('div');
                                errorDiv.classList.add('error');
                                let iconContainerDiv = document.createElement('div');
                                iconContainerDiv.classList.add('icon-container');
                                let spanIcon = document.createElement('span');
                                spanIcon.classList.add('material-icons');
                                spanIcon.innerText = 'done';
                                let pError = document.createElement('p');
                                pError.innerText = 'Contact Number must be ' + minLength + ' digits.'; 
                                let closeContainerDiv = document.createElement('div');
                                closeContainerDiv.classList.add('closeContainer');
                                let spanClose = document.createElement('span');
                                spanClose.classList.add('material-icons');
                                spanClose.innerText = 'close';

                                // destructure
                                iconContainerDiv.appendChild(spanIcon);
                                closeContainerDiv.appendChild(spanClose);

                                errorDiv.appendChild(iconContainerDiv);
                                errorDiv.appendChild(pError);
                                errorDiv.appendChild(closeContainerDiv);
                                document.body.appendChild(errorDiv);

                                // remove after 5 mins
                                setTimeout(e => errorDiv.remove(), 5000);
                            }
                        }
                    </script>";
        }
    }


    public function editSecretary($id, $urlEmail, $adminFullname, $adminId)
    {
        if(isset($_POST['updateSec'])){
            $fullname = $_POST['fullname'];
            $gender = $_POST['gender'];
            $email = $_POST['email'];
            $number = $_POST['cpnumber'];
            $address = $_POST['address'];

            // oks lang ket walang number
            if(empty($fullname) &&
            empty($gender) &&
            empty($email) &&
            empty($address)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input fields are required!</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {

                if($email != $urlEmail){
                    
                    if($this->checkSecEmailExist($email)){
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Email Already Exist!</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                    } else {
                    
                        $sql = "UPDATE secretary
                                SET fullname = ?, 
                                    gender = ?,
                                    email = ?,
                                    cpnumber = ?, 
                                    address = ?
                                WHERE id = ?";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$fullname, $gender, $email, $number, $address, $id]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){

                            // after mo maupdate kunin mo yung data
                            $sql2 = "SELECT s.email, 
                                            se.se_id, 
                                            se.secret_key as secret_key
                                     FROM secretary s
                                     INNER JOIN secret_diarys se
                                     ON s.email = se.se_id

                                     WHERE s.email = ?";
                            $stmt2 = $this->con()->prepare($sql2);
                            $stmt2->execute([$email]);
                            $users2 = $stmt2->fetch();
                            $countRow2 = $stmt2->rowCount();

                            if($countRow2 > 0){
                                $this->sendEmail($users2->email, $users2->secret_key);

                                $action = "Edit";
                                $table_name = "Secretary";
                                $admindatetime = $this->getDateTime();
                                $adminTime = $admindatetime['time'];
                                $adminDate = $admindatetime['date'];
                                                                    
                                $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                                    
                                $countRowAdminLog = $stmtAdminLog->rowCount();
                                if($countRowAdminLog > 0){
                                    $msg = 'Update Successfully';
                                    echo "<script>window.location.assign('./showAll.php?message=$msg');</script>";
                                } else {
                                echo "<div class='error'>
                                            <div class='icon-container'>
                                                <span class='material-icons'>close</span>
                                            </div>
                                            <p>Update Failed</p>
                                            <div class='closeContainer'>
                                                <span class='material-icons'>close</span>
                                            </div>
                                        </div>
                                        <script>
                                            let msgErr = document.querySelector('.error');
                                            setTimeout(e => msgErr.remove(), 5000);
                                        </script>";
                                }
                            } else {
                                echo "<div class='error'>
                                        <div class='icon-container'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                        <p>Error Sending Credentials</p>
                                        <div class='closeContainer'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                      </div>
                                      <script>
                                        let msgErr = document.querySelector('.error');
                                        setTimeout(e => msgErr.remove(), 5000);
                                      </script>";
                            }

                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Update Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                        }
                    }
                } else {
                    
                    $sql = "UPDATE secretary
                    SET fullname = ?, 
                        gender = ?,
                        email = ?,
                        cpnumber = ?, 
                        address = ?
                    WHERE id = ?";
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute([$fullname, $gender, $email, $number, $address, $id]);
                    $countRow = $stmt->rowCount();

                    if($countRow > 0){

                        $action = "Edit";
                        $table_name = "Secretary";
                        $admindatetime = $this->getDateTime();
                        $adminTime = $admindatetime['time'];
                        $adminDate = $admindatetime['date'];
                                                            
                        $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                        $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                        $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                            
                        $countRowAdminLog = $stmtAdminLog->rowCount();
                        if($countRowAdminLog > 0){
                            $msg = 'Update Successfully';
                            echo "<script>window.location.assign('./showAll.php?message=$msg');</script>";
                        } else {
                        echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Update Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                </div>
                                <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                </script>";
                        }
                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Update Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                    }
                }
            }
        }
    }


    public function deleteModalShowIt($id)
    {
        $sql = "SELECT * FROM secretary WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();
        if($countRow > 0){
            echo "  <div class='delete-modal'>
                        <div class='modal-holder'>
                            <div class='deletemodal-header'>
                                <h1>Delete Secretary</h1>
                                <span class='material-icons' id='deleteModalClose'>close</span>
                            </div>
                            <div class='deletemodal-content'>
                                <form method='post'>
                                    <h1>Are you sure you want to delete this secretary?</h1>
                                    <div>
                                        <input type='hidden' name='id' value='$user->id' required/>
                                        <button type='submit' name='deleteSec' id='deleteBtn'>Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <script>
                        // close modal
                        let deleteModalClose = document.querySelector('#deleteModalClose');
                        deleteModalClose.addEventListener('click', () => {
                            let deleteModal = document.querySelector('.delete-modal');
                            deleteModal.style.display = 'none';
                        });
                    </script>";
        }
    }

    public function deleteSecretary($adminFullname, $adminId)
    {
        if(isset($_POST['deleteSec'])){
            $id = $_POST['id'];

            if(empty($id)){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>Id is required to delete!</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {
                $sql = "UPDATE secretary SET isDeleted = ? WHERE id = ?";
                $stmt = $this->con()->prepare($sql);
                $stmt->execute([1, $id]);
                $countRow = $stmt->rowCount();

                if($countRow > 0){

                    $action = "Delete";
                    $table_name = "Secretary";
                    $admindatetime = $this->getDateTime();
                    $adminTime = $admindatetime['time'];
                    $adminDate = $admindatetime['date'];
                                                        
                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                        
                    $countRowAdminLog = $stmtAdminLog->rowCount();
                    if($countRowAdminLog > 0){
                        $msg = 'Deleted Successfully';
                        echo "<script>window.location.assign('./showAll.php?message=$msg');</script>";
                    } else {
                    echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Delete Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                            </div>
                            <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                            </script>";
                    }
                } else {
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Update Failed</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                }
            }
        }
    }


    public function recentAssignedGuards()
    {
        $sql = "SELECT * FROM employee 
                WHERE availability = 'Unavailable'
                AND isDeleted = 0
                AND date BETWEEN CURRENT_DATE - 15 
                             AND CURRENT_DATE
                ORDER BY id DESC
                LIMIT 3";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<div class='assignedguard-row'>
                    <div class='assignedguard-row-text'>
                        <p>No Data Found</p>
                        <span><b></b></span>
                    </div>
                    <div class='assignedguard-row-button'>
                        <div>
                            <a><span class='material-icons'></span></a>
                        </div>
                    </div>
                  </div>";
        } else {
            while($users = $stmt->fetch()){
                $fullname = $users->lastname.", ".$users->firstname;
                echo "<div class='assignedguard-row'>
                          <div class='assignedguard-row-text'>
                              <p>$fullname</p>
                              <span>Position to <b>$users->position</b></span>
                          </div>
                          <div class='assignedguard-row-button'>
                              <div class='btn-delete'>
                                  <a href='./employee.php?idDelete=$users->empId' class='btn-delete-icon'>
                                      <span class='material-icons'>delete</span>
                                  </a>
                              </div>
                          </div>
                      </div>";
            }
        }
    }

    public function deleteRecentGuardModal($id)
    {
        $sql = "SELECT * FROM schedule WHERE empId = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            echo "<div class='modal-holder'>
                    <div class='deleteguard-header'>
                        <h1>Delete Employee</h1>
                        <span id='exit-modal-deleteguard' class='material-icons'>close</span>
                    </div>
                    <div class='deleteguard-content'>
                        <h1>Are you sure you want to transfer this employee to available guard?</h1>
                        <form method='post'>
                            <input type='hidden' name='empId' value='$user->empId' required/>
                            <button type='submit' name='deleteRecord'>Delete</button>
                        </form>
                    </div>
                </div>
                  ";
        }
    }
    
    public function deleteRecentGuard($adminFullname, $adminId)
    {
        if(isset($_POST['deleteRecord'])){
            $empId = $_POST['empId'];
            if(empty($empId)){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>Id are required to delete!</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {
                // delete record in leave request
                $sqlLeave = "DELETE FROM leave_request WHERE empId = ?";
                $stmtLeave = $this->con()->prepare($sqlLeave);
                $stmtLeave->execute([$empId]);

                // find company in schedule before deleting it
                $sqlFindCompany = "SELECT * FROM schedule WHERE empId = ?";
                $stmtFindCompany = $this->con()->prepare($sqlFindCompany);
                $stmtFindCompany->execute([$empId]);
                $userFindCompany = $stmtFindCompany->fetch();
                $countRowFindCompany = $stmtFindCompany->rowCount();

                if($countRowFindCompany > 0){
                    // find how many guards in specific company
                    $sqlTotalGuards = "SELECT hired_guards FROM company WHERE company_name = ?";
                    $stmtTotalGuards = $this->con()->prepare($sqlTotalGuards);
                    $stmtTotalGuards->execute([$userFindCompany->company]);
                    $userTotalGuards = $stmtTotalGuards->fetch();
                    $countRowTotalGuards = $stmtTotalGuards->rowCount();

                    $hiredGuards = 0;
                    $intUsersHR = intval($userTotalGuards->hired_guards);

                    if($countRowTotalGuards > 0){
                        if($intUsersHR == 0 || 
                           $intUsersHR == NULL ||
                           $intUsersHR == 'NULL' ||
                           $intUsersHR == ''
                        ){
                           $hiredGuards = intval($intUsersHR) - 1;
                        } else {
                            $hiredGuards = intval($intUsersHR) - 1;
                        }
                    } 

                    // minus 1 in hired_guards inside company table
                    $sqlCompany = "UPDATE company SET hired_guards = ? WHERE company_name = ?";
                    $stmtCompany = $this->con()->prepare($sqlCompany);
                    $stmtCompany->execute([$hiredGuards, $userFindCompany->company]);
                    

                    // delete in schedule
                    $sqlSched = "DELETE FROM schedule WHERE empId = ?";
                    $stmtSched = $this->con()->prepare($sqlSched);
                    $stmtSched->execute([$empId]);

                    // delete in update employee details
                    $makeItNull = NULL;
                    $availability = 'Available';
                    $sqlEmp = "UPDATE employee
                               SET position = ?,
                                   ratesperDay = ?,
                                   overtime_rate = ?,
                                   availability = ?
                               WHERE empId = ?
                               ";
                    $stmtEmp = $this->con()->prepare($sqlEmp);
                    $stmtEmp->execute([$makeItNull, $makeItNull, $makeItNull, $availability, $empId]);

                    $action = "Delete";
                    $table_name = "Unavailable Employee";
                    $admindatetime = $this->getDateTime();
                    $adminTime = $admindatetime['time'];
                    $adminDate = $admindatetime['date'];
                                                        
                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                        
                    $countRowAdminLog = $stmtAdminLog->rowCount();
                    if($countRowAdminLog > 0){
                        $msg = 'Deleted Successfully';
                        echo "<script>window.location.assign('./employee.php?message=$msg');</script>";
                    } else {
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Delete Failed</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                    }
                }
            }
        }
    }




    public function addEmployee($adminFullname, $adminId){
        
        if(isset($_POST['addemployee'])){

            date_default_timezone_set('Asia/Manila'); // set default timezone to manila
            $curr_year = date("Y"); // year

            $empId = $curr_year."-".$this->createEmpId(); // generated empId

            if($this->createEmpId() == NULL || $this->createEmpId() == 0 || $this->createEmpId() == ""){
                $empId = $curr_year."-1";
            }

            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $address = $_POST['address'];
            $email = $_POST['email'];
            $realPassword = $this->generatedPassword2();
            $dbPassword = $this->generatedPassword($realPassword); // md5, pass with keyword
            $qrcode = $_POST['qrcode'];
            $number = $_POST['number'];
            $access = "employee";
            $availability = "Available";

            $fullname = $firstname.$lastname;

            if(empty($firstname) &&
               empty($lastname) &&
               empty($number) &&
               empty($address) &&
               empty($email) &&
               empty($dbPassword) &&
               empty($qrcode) &&
               empty($access) &&
               empty($availability)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input fields are required!</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {

                    // for not deleted account
                    $sqlFindAccNot = "SELECT * FROM employee 
                                   WHERE firstname = ? 
                                   AND lastname = ? 
                                   AND email = ?
                                   AND isDeleted = 0";
                    $stmtFindAccNot = $this->con()->prepare($sqlFindAccNot);
                    $stmtFindAccNot->execute([$firstname, $lastname, $email]);
                    $userFindAccNot = $stmtFindAccNot->fetch();
                    $countRowFindAccNot = $stmtFindAccNot->rowCount();

                    // for deleted account
                    $sqlFindAcc = "SELECT * FROM employee 
                                   WHERE firstname = ? 
                                   AND lastname = ? 
                                   AND email = ?
                                   AND isDeleted = 1";
                    $stmtFindAcc = $this->con()->prepare($sqlFindAcc);
                    $stmtFindAcc->execute([$firstname, $lastname, $email]);
                    $userFindAcc = $stmtFindAcc->fetch();
                    $countRowFindAcc = $stmtFindAcc->rowCount();

                    if($countRowFindAccNot > 0){
                        $msg = 'Account Already Exists.';
                        echo "<script>window.location.assign('employee.php?message2=$msg');</script>";
                    } elseif($countRowFindAcc > 0){
                        $msg = 'Account Already Exists. Request Restoration.';
                        echo "<script>window.location.assign('employee.php?message2=$msg');</script>";
                    } elseif($this->checkEmpEmailExist($email)){
                        $msg = 'Email Already Exist!';
                        echo "<script>window.location.assign('employee.php?message2=$msg');</script>";
                    } else {
                        // set timezone and get date and time
                        $datetime = $this->getDateTime(); 
                        $time = $datetime['time'];
                        $date = $datetime['date'];

                        // add mo na ko
                        $sql = "INSERT INTO employee(empId,
                                                    firstname,
                                                    lastname,
                                                    cpnumber,
                                                    address,
                                                    email,
                                                    password,
                                                    qrcode,
                                                    access,
                                                    availability,
                                                    time,
                                                    date)
                                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$empId, $firstname, $lastname, $number, $address, $email, $dbPassword[0], $qrcode, $access, $availability, $time, $date]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){

                            // gagamitin pang login sa employee dashboard
                            $sqlSecretKeyEmployee = "INSERT INTO secret_diarye(e_id, secret_key)
                                                    VALUES(?, ?)";
                            $stmtSecretKeyEmployee = $this->con()->prepare($sqlSecretKeyEmployee);
                            $stmtSecretKeyEmployee->execute([$email, $realPassword]);
                            // send user credentials
                            $this->sendEmail($email, $realPassword);

                            $action = "Add";
                            $table_name = "Available Employee";
                            $admindatetime = $this->getDateTime();
                            $adminTime = $admindatetime['time'];
                            $adminDate = $admindatetime['date'];
                                                                
                            $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                            $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                            $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                                
                            $countRowAdminLog = $stmtAdminLog->rowCount();
                            if($countRowAdminLog > 0){
                                $msg = 'New Data Added';
                                echo "<script>window.location.assign('./employee.php?message=$msg');</script>";
                            } else {
                                $msg = 'Add Failed.';
                                echo "<script>window.location.assign('employee.php?message2=$msg');</script>";
                            }
                        } else {
                            $msg = 'No Data Added.';
                            echo "<script>window.location.assign('employee.php?message2=$msg');</script>";
                        }
                    }
                
            }
        }

        // for add employee modal
        if(isset($_POST['addemployeemodal'])){

            date_default_timezone_set('Asia/Manila'); // set default timezone to manila
            $curr_year = date("Y"); // year

            $empId = $curr_year."-".$this->createEmpId(); // generated empId

            if($this->createEmpId() == NULL || $this->createEmpId() == 0 || $this->createEmpId() == ""){
                $empId = $curr_year."-1";
            }

            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $address = $_POST['address'];
            $email = $_POST['email'];
            $realPassword = $this->generatedPassword2();
            $dbPassword = $this->generatedPassword($realPassword); // md5, pass with keyword
            $qrcode = $_POST['qrcode'];
            $number = $_POST['number'];
            $access = "employee";
            $availability = "Available";

            $fullname = $firstname.$lastname;

            if(empty($firstname) &&
               empty($lastname) &&
               empty($number) &&
               empty($address) &&
               empty($email) &&
               empty($dbPassword) &&
               empty($qrcode) &&
               empty($access) &&
               empty($availability)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input fields are required!</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {

                    // for not deleted account
                    $sqlFindAccNot = "SELECT * FROM employee 
                                   WHERE firstname = ? 
                                   AND lastname = ? 
                                   AND email = ?
                                   AND isDeleted = 0";
                    $stmtFindAccNot = $this->con()->prepare($sqlFindAccNot);
                    $stmtFindAccNot->execute([$firstname, $lastname, $email]);
                    $userFindAccNot = $stmtFindAccNot->fetch();
                    $countRowFindAccNot = $stmtFindAccNot->rowCount();

                    // for deleted account
                    $sqlFindAcc = "SELECT * FROM employee 
                                   WHERE firstname = ? 
                                   AND lastname = ? 
                                   AND email = ?
                                   AND isDeleted = 1";
                    $stmtFindAcc = $this->con()->prepare($sqlFindAcc);
                    $stmtFindAcc->execute([$firstname, $lastname, $email]);
                    $userFindAcc = $stmtFindAcc->fetch();
                    $countRowFindAcc = $stmtFindAcc->rowCount();

                    if($countRowFindAccNot > 0){
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Account Already Exists</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                    } elseif($countRowFindAcc > 0){
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p style='font-size: 12px !important;'>Account Already Exists.<br/>Request Restoration.</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                            </div>
                            <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                            </script>";
                    } elseif($this->checkEmpEmailExist($email)){
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Email Already Exist!</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                            </div>
                            <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                            </script>";
                    } else {
                        // set timezone and get date and time
                        $datetime = $this->getDateTime(); 
                        $time = $datetime['time'];
                        $date = $datetime['date'];

                        // add mo na ko
                        $sql = "INSERT INTO employee(empId,
                                                    firstname,
                                                    lastname,
                                                    cpnumber,
                                                    address,
                                                    email,
                                                    password,
                                                    qrcode,
                                                    access,
                                                    availability,
                                                    time,
                                                    date)
                                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$empId, $firstname, $lastname, $number, $address, $email, $dbPassword[0], $qrcode, $access, $availability, $time, $date]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){

                            // gagamitin pang login sa employee dashboard
                            $sqlSecretKeyEmployee = "INSERT INTO secret_diarye(e_id, secret_key)
                                                    VALUES(?, ?)";
                            $stmtSecretKeyEmployee = $this->con()->prepare($sqlSecretKeyEmployee);
                            $stmtSecretKeyEmployee->execute([$email, $realPassword]);
                            // send user credentials
                            $this->sendEmail($email, $realPassword);

                            $action = "Add";
                            $table_name = "Available Employee";
                            $admindatetime = $this->getDateTime();
                            $adminTime = $admindatetime['time'];
                            $adminDate = $admindatetime['date'];
                                                                
                            $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                            $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                            $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                                
                            $countRowAdminLog = $stmtAdminLog->rowCount();
                            if($countRowAdminLog > 0){
                                $msg = 'New Data Added';
                                echo "<script>window.location.assign('./employee.php?message=$msg');</script>";
                            } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Add Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                </div>
                                <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                </script>";
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>No Data Added</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                </div>
                                <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                </script>";
                        }
                    }

            }
        }
    }

    public function createEmpId()
    {
        $sql = "SELECT MAX(id) AS id FROM employee";
        $stmt = $this->con()->query($sql);
        $users = $stmt->fetch();
        $getId = $users->id;
        
        return $getId;
    }


    // employee.php      td without actions
    public function showAllEmp(){
        $sql = "SELECT * FROM employee WHERE isDeleted = 0 ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<tr>
                    <td>No data found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        } else {
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->lastname, "."$row->firstname</td>
                        <td>$row->cpnumber</td>
                        <td>$row->availability</td>
                        <td>$row->date</td>
                      </tr>";
            }
        }
    }


    // employee.php      td without actions
    public function showAllEmpSearch($search){
        $sql = "SELECT * FROM employee
                WHERE isDeleted = 0 AND lastname LIKE '%$search%' OR
                      isDeleted = 0 AND firstname LIKE '%$search%' OR
                      isDeleted = 0 AND cpnumber LIKE '%$search%' OR
                      isDeleted = 0 AND availability LIKE '%$search%' OR
                      isDeleted = 0 AND date LIKE '%$search%'
                ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<tr>
                    <td>No data found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>";
        } else {
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->lastname, "."$row->firstname</td>
                        <td>$row->cpnumber</td>
                        <td>$row->availability</td>
                        <td>$row->date</td>
                    </tr>";
            }
        }
    }


    // showEmployees.php      td with actions
    public function showAllEmpActions(){
        $sql = "SELECT * FROM employee WHERE availability = 'Available' AND isDeleted = 0 ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<tr>
                    <td></td>
                    <td style='width:100px;'>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }

        while($row = $stmt->fetch()){

            $fullname = $row->firstname." ".$row->lastname;
            $availability = $row->availability;
            echo "<tr>
                    <td><input type='checkbox' id='c$row->id' onclick='setVal(this, $row->id);'/></td>
                    <td><label for='c$row->id'>$row->lastname, $row->firstname</label></td>
                    <td>$row->email</td>
                    <td>$row->address</td>
                    <td>
                       <div class='buttons'>
                            <div class='buttons-edit'>
                                <a href='showEmployees.php?id=$row->id&email=$row->email&action=edit'>
                                    <span class='material-icons'>edit</span>
                                </a>
                            </div>
                            <div class='buttons-qr'>
                                <a href='./generateqr.php?myqr=$row->qrcode&fullname=$fullname&availability=$availability'>
                                    <span class='material-icons'>qr_code</span>
                                </a>
                            </div>
                            <div class='buttons-delete'>
                                <a href='showEmployees.php?id=$row->id&action=delete'>
                                    <span class='material-icons'>delete</span>
                                </a>
                            </div>
                        </div>
                    </td>
                  </tr>";
        }
    }


    public function showAllEmpActionsSearch($search){
        $sql = "SELECT * FROM employee 
                WHERE isDeleted = 0 AND availability = 'Available' AND lastname LIKE '%$search%' OR
                      isDeleted = 0 AND availability = 'Available' AND firstname LIKE '%$search%' OR
                      isDeleted = 0 AND availability = 'Available' AND email LIKE '%$search%' OR
                      isDeleted = 0 AND availability = 'Available' AND `address` LIKE '%$search%'
                ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<tr>
                    <td></td>
                    <td style='width:100px;'>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }

        while($row = $stmt->fetch()){

            $fullname = $row->firstname." ".$row->lastname;
            $availability = $row->availability;
            echo "<tr>
                    <td><input type='checkbox' id='c$row->id' onclick='setVal(this, $row->id);'/></td>
                    <td><label for='c$row->id'>$row->lastname, $row->firstname</label></td>
                    <td>$row->email</td>
                    <td>$row->address</td>
                    <td>
                       <div class='buttons'>
                            <div class='buttons-edit'>
                                <a href='showEmployees.php?id=$row->id&email=$row->email&action=edit'>
                                    <span class='material-icons'>edit</span>
                                </a>
                            </div>
                            <div class='buttons-qr'>
                                <a href='./generateqr.php?myqr=$row->qrcode&fullname=$fullname&availability=$availability'>
                                    <span class='material-icons'>qr_code</span>
                                </a>
                            </div>
                            <div class='buttons-delete'>
                                <a href='showEmployees.php?id=$row->id&action=delete'>
                                    <span class='material-icons'>delete</span>
                                </a>
                            </div>
                        </div>
                    </td>
                  </tr>";
        }
    }



    // showEmployees.php      td with actions for unavailable
    public function showAllUnavailableEmpActions(){
        $sql = "SELECT 
                       s.id,
                       s.empId,
                       s.company,
                       e.empId,
                       e.firstname AS firstname,
                       e.lastname AS lastname,
                       e.email,
                       e.qrcode AS qrcode,
                       e.availability,
                       e.isDeleted,
                       c.company_name AS companyname,
                       c.comp_location AS location
                FROM schedule s
                INNER JOIN employee e
                ON s.empId = e.empId
                
                INNER JOIN company c
                ON s.company = c.company_name
                
                WHERE e.isDeleted = 0
                AND e.availability = 'Unavailable' OR
                    e.availability = 'Leave'
                ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
             echo "<tr>
                    <td style='width:200px;'>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                   </tr>";
        } else {
            while($row = $stmt->fetch()){
                $fullname = $row->lastname.", ".$row->firstname;
                $fullname2 = $row->firstname." ".$row->lastname;
                $availability = $row->availability;
                $qrcode = $row->qrcode;

                echo "<tr>
                         <td>$fullname</td>
                         <td>$row->companyname</td>
                         <td>$row->location</td>
                         <td>
                            <div class='buttons'>
                                <div class='buttons-view'>
                                    <a href='unavailable.php?sid=$row->id'>
                                        <span class='material-icons'>visibility</span>
                                    </a>
                                </div>
                                <div class='buttons-qr'>
                                    <a href='./generateqr.php?myqr=$qrcode&fullname=$fullname2&availability=$availability'>
                                        <span class='material-icons'>qr_code</span>
                                    </a>
                                </div>
                                <div class='buttons-delete'>
                                    <a href='unavailable.php?sidDelete=$row->id'>
                                        <span class='material-icons'>delete</span>
                                    </a>
                                </div>
                            </div>
                        </td>
                      </tr>";
            }
        }
    }


    public function showAllUnavailableEmpActionsSearch($search){
        $sql = "SELECT 
                       s.id,
                       s.empId,
                       s.company,
                       e.empId,
                       e.firstname AS firstname,
                       e.lastname AS lastname,
                       e.email,
                       e.qrcode AS qrcode,
                       e.availability,
                       e.isDeleted,
                       c.company_name AS companyname,
                       c.comp_location AS location
                FROM schedule s
                INNER JOIN employee e
                ON s.empId = e.empId
                
                INNER JOIN company c
                ON s.company = c.company_name
                
                WHERE e.availability = 'Leave' OR e.availability = 'Unavailable' AND e.isDeleted = 0 AND e.lastname LIKE '%$search%' OR
                      e.availability = 'Leave' OR e.availability = 'Unavailable' AND e.isDeleted = 0 AND e.firstname LIKE '%$search%' OR
                      e.availability = 'Leave' OR e.availability = 'Unavailable' AND e.isDeleted = 0 AND c.company_name LIKE '%$search%' OR
                      e.availability = 'Leave' OR e.availability = 'Unavailable' AND e.isDeleted = 0 AND c.comp_location LIKE '%$search%'
                ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
             echo "<tr>
                    <td style='width:200px;'>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                   </tr>";
        } else {
            while($row = $stmt->fetch()){
                $fullname = $row->lastname.", ".$row->firstname;
                $fullname2 = $row->firstname." ".$row->lastname;
                $availability = $row->availability;
                $qrcode = $row->qrcode;

                echo "<tr>
                         <td>$fullname</td>
                         <td>$row->companyname</td>
                         <td>$row->location</td>
                         <td>
                            <div class='buttons'>
                                <div class='buttons-view'>
                                    <a href='unavailable.php?sid=$row->id'>
                                        <span class='material-icons'>visibility</span>
                                    </a>
                                </div>
                                <div class='buttons-qr'>
                                    <a href='./generateqr.php?myqr=$qrcode&fullname=$fullname2&availability=$availability'>
                                        <span class='material-icons'>qr_code</span>
                                    </a>
                                </div>
                                <div class='buttons-delete'>
                                    <a href='unavailable.php?sidDelete=$row->id'>
                                        <span class='material-icons'>delete</span>
                                    </a>
                                </div>
                            </div>
                        </td>
                      </tr>";
            }
        }
    }



    public function viewModalShow()
    {
        if(isset($_GET['sid'])){
            $id = $_GET['sid'];

            $sql = "SELECT 
                          s.id,
                          s.empId,
                          s.company,
                          s.expiration_date AS expdate,
                          e.empId AS empId,
                          e.firstname AS firstname,
                          e.lastname AS lastname,
                          e.position AS position,
                          e.ratesperDay AS price,
                          e.overtime_rate AS ot,
                          e.address AS address,
                          e.email AS email,
                          e.cpnumber as cpnumber,
                          c.company_name AS companyname,
                          c.comp_location AS location

                    FROM schedule s
                    INNER JOIN employee e
                    ON s.empId = e.empId

                    INNER JOIN company c
                    ON s.company = c.company_name
                    WHERE s.id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            $countRow = $stmt->rowCount();

            if($countRow > 0){
                $expdateArray = explode('-', $user->expdate);
                $year = $expdateArray[0];
                $month = $expdateArray[1];
                $day = $expdateArray[2];
                echo "<script>
                         let viewModal = document.querySelector('.modal-viewguard');
                         viewModal.style.display = 'flex';

                         let firstname = document.querySelector('#firstname').value = '$user->firstname';
                         let lastname = document.querySelector('#lastname').value = '$user->lastname';
                         let company = document.querySelector('#company').value = '$user->companyname';
                         let comp_location = document.querySelector('#comp_location').value = '$user->location';
                         let year = document.querySelector('#year').value = '$year';
                         let month = document.querySelector('#month').value = '$month';
                         let day = document.querySelector('#day').value = '$day';
                         let position = document.querySelector('#position').value = '$user->position';
                         let price = document.querySelector('#price').value = '$user->price';
                         let ot = document.querySelector('#ot').value = '$user->ot';
                         let empAddress = document.querySelector('#empAddress').value = '$user->address';
                         let email = document.querySelector('#email').value = '$user->email';
                         let cpnumber = document.querySelector('#cpnumber').value = '$user->cpnumber';
                      </script>";
            }
        }
    }

    // for unavailable guards
    public function getDuration($id)
    {
        $sql = "SELECT expiration_date FROM schedule WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        $expdate = $user->expiration_date;
        $exdateArray = explode('-', $expdate);
        return $exdateArray;
    }

    public function deleteModalShow($id)
    {
        if(isset($_GET['sidDelete'])){
            $id = $_GET['sidDelete'];

            echo "<script>
                    let viewModal = document.querySelector('.modal-viewguard');
                    if(viewModal.style.display == 'block'){
                        viewModal.style.display = 'none';
                    }
                    
                    let removeModal = document.querySelector('.modal-deleteguard');
                    removeModal.style.display = 'flex';
                    let empId = document.querySelector('#rEmpId');
                    empId.value = '$id';
                  </script>";
        }
    }

    public function deleteUnavailableGuards($adminFullname, $adminId)
    {
        if(isset($_POST['deleteUnavailable'])){
            $id = $_GET['sidDelete'];

            $sql = "SELECT * FROM schedule WHERE id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$id]);
            $users = $stmt->fetch();
            $countRow = $stmt->rowCount();

            if($countRow > 0){
                $empId = $users->empId;
                $company = $users->company;

                $sqlEmployee = "SELECT * FROM employee WHERE empId = ?";
                $stmtEmployee = $this->con()->prepare($sqlEmployee);
                $stmtEmployee->execute([$empId]);
                $usersEmployee = $stmt->fetch();
                $countRowEmployee = $stmt->rowCount();

                if($countRowEmployee > 0){
                    $position = NULL;
                    $price = NULL;
                    $ot = NULL;
                    $availability = 'Available';
                    
                    // delete someone in schedule
                    $sqlUpdateSched = "DELETE FROM schedule WHERE id = ?";
                    $stmtUpdateSched = $this->con()->prepare($sqlUpdateSched);
                    $stmtUpdateSched->execute([$id]);

                    // delete in leave request
                    $sqlUpdateLeave = "DELETE FROM leave_request WHERE empId = ?";
                    $stmtUpdateLeave = $this->con()->prepare($sqlUpdateLeave);
                    $stmtUpdateLeave->execute([$empId]);

                    // delete in violation and remarks

                    // delete in inbox

                    // remove position, price, type and availability 
                    $sqlUpdateEmp = "UPDATE employee
                                     SET position = ?,
                                         ratesperDay = ?,
                                         overtime_rate = ?,
                                         availability = ?
                                     WHERE empId = ?"; 
                    $stmtUpdateEmp = $this->con()->prepare($sqlUpdateEmp);
                    $stmtUpdateEmp->execute([$position, $price, $ot, $availability, $empId]);

                    // get current number of guards in company table
                    $sqlCompany = "SELECT * FROM company WHERE company_name = ?";
                    $stmtCompany = $this->con()->prepare($sqlCompany);
                    $stmtCompany->execute([$company]);
                    $userCompany = $stmtCompany->fetch();
                    $countRowCompany = $stmtCompany->rowCount();
                    $hiredGuards = 0;
                    $intHiredGuards = intval($userCompany->hired_guards);

                    if($countRowCompany > 0){

                        $hiredGuards = intval($intHiredGuards) - 1;

                        // decrease 1 in hiredguards inside company table
                        $sqlUpdateComp = "UPDATE company 
                                          SET hired_guards = ?
                                          WHERE company_name = ?";
                        $stmtUpdateComp = $this->con()->prepare($sqlUpdateComp);
                        $stmtUpdateComp->execute([$hiredGuards, $company]);
                        $countRowUpdateComp = $stmtUpdateComp->rowCount();

                        if($countRowUpdateComp > 0){

                            $action = "Delete";
                            $table_name = "Unavailable Employee";
                            $admindatetime = $this->getDateTime();
                            $adminTime = $admindatetime['time'];
                            $adminDate = $admindatetime['date'];
                                                                
                            $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                            $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                            $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                                
                            $countRowAdminLog = $stmtAdminLog->rowCount();
                            if($countRowAdminLog > 0){
                                $msg = 'Deleted Successfully';
                                echo "<script>window.location.assign('unavailable.php?message=$msg');</script>";
                            } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Delete Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Delete Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                        }
                    }
                }
            }
        }
    }


    public function addNewSelectedGuard()
    {
        $sql = "SELECT * FROM employee WHERE availability = 'Available' AND isDeleted = 0 ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($users = $stmt->fetch()){
                echo "<tr class='doDelete' data-empIdDelete='$users->id'>
                          <td><input type='checkbox' id='c$users->id' onclick='setVal(this, $users->id)' /></td>
                          <td><label for='c$users->id'>$users->firstname $users->lastname</label></td>
                          <td><label for='c$users->id'>$users->email</label></td>
                          <td><label for='c$users->id'>$users->address</label></td>
                      </tr>";
            }
        } else {
            echo "<tr>
                    <td></td>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }


    public function selectguards()
    {
        if(isset($_POST['selectguards']))
        {
            $ids = $_POST['ids'];
            header("Location: selectedGuards.php?ids=$ids");
        }
    }

    public function dropdownCompanyDetails()
    {        
        // select all company
        $sql = "SELECT company_name, comp_location, isDeleted FROM company WHERE isDeleted = 0 ORDER BY company_name ASC";
        $stmt = $this->con()->query($sql);

        $companyArr = array();
        $locArr = array();

        while($row = $stmt->fetch()){
            array_push($companyArr, $row->company_name);
            array_push($locArr, $row->comp_location);
        }

        // put it in length
        for($i = 0; $i < sizeof($companyArr); $i++){

            $posArray = array();
            $priceArray = array();
            $otArray = array();

            $companyGet = $companyArr[$i];
            $locGet = $locArr[$i];

            $sqlPos = "SELECT * FROM positions WHERE company = '$companyGet'";
            $stmtPos = $this->con()->query($sqlPos);

            while($userPos = $stmtPos->fetch()){
                array_push($posArray, $userPos->position_name);
                array_push($priceArray, $userPos->price);
                array_push($otArray, $userPos->overtime_rate);
            }
            
            $posString = implode(',', $posArray);
            $priceString = implode(',', $priceArray);
            $otString = implode(',', $otArray);

            echo "<option data-pos='$posString'
                          data-price='$priceString' 
                          data-ot='$otString' 
                          data-loc='$locGet'
                          value='$companyGet'>$companyGet
                  </option>";

            
            unset($posArray);
        }
    }
    
    public function selectguardsAddCompany($ids)
    {    
        $idArray = explode (",", $ids); 

        $sql = "SELECT * FROM employee WHERE id = ? AND availability = 'Available' AND isDeleted = 0";
        $stmt = $this->con()->prepare($sql);
        
        // set timezone and get date and time
        $datetime = $this->getDateTime(); 
        $date = $datetime['date'];

        $countInputs = sizeof($idArray);
        echo "<input type='hidden' value='$countInputs' name='countInput' required/>";

        for($i = 0; $i < sizeof($idArray); $i++){
            $rowId = $idArray[$i]; 
            $stmt->execute([$idArray[$i]]);
            $user = $stmt->fetch();
            

            if($user->firstname == ''&& 
               $user->firstname == NULL &&
               $user->lastname == '' &&
               $user->lastname == NULL){
                echo "<script>window.location.assign('showEmployees.php');</script>";
            } else {
                $fullname = $user->firstname ." ". $user->lastname;
            }

            echo "<tr>
                      <td><input type='hidden' name='empId$i' value='$user->empId' required/><span>$fullname</span></td>
                      <td>
                         <select onchange='getPrice(this)' class='position' name='position$i' required>
                            <option value=''>Select Position</option>
                         </select>
                         <input type='hidden' class='price' name='price$i' required/>
                         <input type='hidden' class='ot' name='ot$i' required/>
                      </td>
                      <td><input type='hidden' name='email$i' value='$user->email'/>$user->email</td>
                      <td>$date</td>
                      <td>
                          <span data-deleteId='$rowId' onclick='removeMe(this)'class='material-icons'>delete</span>
                      </td>
                  </tr>";
        }
    }

    public function selectguardsAddCompanySearch($ids, $search)
    {    
        $idArray = explode (",", $ids); 

        $sql = "SELECT * FROM employee 
                WHERE id = ? AND firstname LIKE '%$search%' OR
                      id = ? AND lastname LIKE '%$search%'";
        $stmt = $this->con()->prepare($sql);
        
        // set timezone and get date and time
        $datetime = $this->getDateTime(); 
        $date = $datetime['date'];

        $countInputs = sizeof($idArray);
        echo "<input type='hidden' value='$countInputs' name='countInput' required/>";

        for($i = 0; $i < sizeof($idArray); $i++){
            $rowId = $idArray[$i]; 
            $stmt->execute([$idArray[$i]]);
            $user = $stmt->fetch();
            

            if($user->firstname == ''&& 
               $user->firstname == NULL &&
               $user->lastname == '' &&
               $user->lastname == NULL){
                echo "<script>window.location.assign('showEmployees.php');</script>";
            } else {
                $fullname = $user->firstname ." ". $user->lastname;
            }

            echo "<tr>
                      <td><input type='hidden' name='empId$i' value='$user->empId' required/><span>$fullname</span></td>
                      <td>
                         <select onchange='getPrice(this)' class='position' name='position$i' required>
                            <option value=''>Select Position</option>
                         </select>
                         <input type='hidden' class='price' name='price$i' required/>
                         <input type='hidden' class='ot' name='ot$i' required/>
                      </td>
                      <td><input type='hidden' name='email$i' value='$user->email'/>$user->email</td>
                      <td>$date</td>
                      <td>
                          <span data-deleteId='$rowId' onclick='removeMe(this)'class='material-icons'>delete</span>
                      </td>
                  </tr>";
        }
    }

    public function sendEmailForEmployee($email, $empId, $company, $expdate)
    {
        $sqlEmployee = "SELECT * FROM employee WHERE empId = ?";
        $stmtEmployee = $this->con()->prepare($sqlEmployee);
        $stmtEmployee->execute([$empId]);
        $userEmployee = $stmtEmployee->fetch();
            
        $empPosition = $userEmployee->position;
        $empPrice = $userEmployee->ratesperDay;
        $empOt = $userEmployee->overtime_rate;

        if($empPosition == 'Officer in Charge'){
            $sqlCompany = "SELECT * FROM company WHERE company_name = ?";
            $stmtCompany = $this->con()->prepare($sqlCompany);
            $stmtCompany->execute([$company]);
            $userCompany = $stmtCompany->fetch();
            $countRowCompany = $stmtCompany->rowCount();

            if($countRowCompany > 0){
                $empLocation = $userCompany->comp_location;
                $empShiftSpan = $userCompany->shift_span;

                $empShift = $userCompany->shifts;
                $empDayStart = "";
                $empDayEnd = "";

                if($empShift == 'night'){
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                    $empDayEnd = date("h:i a", strtotime($empDayStart." +".$userCompany->shift_span." hours"));
                } else {
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start));
                    $empDayEnd = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                }

                $name = 'JTDV Incorporation';
                $body = "Congratulations! You have been assigned to $company. The company located at $empLocation. <br/>
                         Shift type: $empShift <br/>
                         Your schedule: $empDayStart - $empDayEnd <br/>
                         Position: $empPosition <br/>
                         Rate per hour: $empPrice <br/>
                         Overtime Rate: $empOt <br/>
                         Contract: $expdate
                        ";

                if(!empty($email)){

                    $mail = new PHPMailer();

                    // smtp settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->e_username;  // gmail address
                    $mail->Password = $this->e_password;  // gmail password

                    $mail->Port = 465;
                    $mail->SMTPSecure = "ssl";

                    // email settings
                    $mail->isHTML(true);
                    $mail->setFrom($email, $name);              // Katabi ng user image
                    $mail->addAddress($email);                  // gmail address ng pagsesendan
                    $mail->Subject = ("$email");                // headline
                    $mail->Body = $body;                        // textarea

                    $mail->send();

                }
            } 
        } elseif($empPosition == 'Head Finance'){
            $sqlCompany = "SELECT * FROM company WHERE company_name = ?";
            $stmtCompany = $this->con()->prepare($sqlCompany);
            $stmtCompany->execute([$company]);
            $userCompany = $stmtCompany->fetch();
            $countRowCompany = $stmtCompany->rowCount();

            if($countRowCompany > 0){
                $empLocation = $userCompany->comp_location;
                $empShiftSpan = $userCompany->shift_span;

                $empShift = $userCompany->shifts;
                $empDayStart = "";
                $empDayEnd = "";

                if($empShift == 'night'){
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                    $empDayEnd = date("h:i a", strtotime($empDayStart." +".$userCompany->shift_span." hours"));
                } else {
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start));
                    $empDayEnd = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                }

                $name = 'JTDV Incorporation';
                $body = "Congratulations! You have been assigned to $company. The company located at $empLocation. <br/>
                         Shift type: $empShift <br/>
                         Your schedule: $empDayStart - $empDayEnd <br/>
                         Position: $empPosition <br/>
                         Rate per hour: $empPrice <br/>
                         Overtime Rate: $empOt <br/>
                         Contract: $expdate
                        ";

                if(!empty($email)){

                    $mail = new PHPMailer();

                    // smtp settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->e_username;  // gmail address
                    $mail->Password = $this->e_password;  // gmail password

                    $mail->Port = 465;
                    $mail->SMTPSecure = "ssl";

                    // email settings
                    $mail->isHTML(true);
                    $mail->setFrom($email, $name);              // Katabi ng user image
                    $mail->addAddress($email);                  // gmail address ng pagsesendan
                    $mail->Subject = ("$email");                // headline
                    $mail->Body = $body;                        // textarea

                    $mail->send();

                }
            } 
        } elseif($empPosition == 'Office Clerk'){
            $sqlCompany = "SELECT * FROM company WHERE company_name = ?";
            $stmtCompany = $this->con()->prepare($sqlCompany);
            $stmtCompany->execute([$company]);
            $userCompany = $stmtCompany->fetch();
            $countRowCompany = $stmtCompany->rowCount();

            if($countRowCompany > 0){
                $empLocation = $userCompany->comp_location;
                $empShiftSpan = $userCompany->shift_span;

                $empShift = $userCompany->shifts;
                $empDayStart = "";
                $empDayEnd = "";

                if($empShift == 'night'){
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                    $empDayEnd = date("h:i a", strtotime($empDayStart." +".$userCompany->shift_span." hours"));
                } else {
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start));
                    $empDayEnd = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                }

                $name = 'JTDV Incorporation';
                $body = "Congratulations! You have been assigned to $company. The company located at $empLocation. <br/>
                         Shift type: $empShift <br/>
                         Your schedule: $empDayStart - $empDayEnd <br/>
                         Position: $empPosition <br/>
                         Rate per hour: $empPrice <br/>
                         Overtime Rate: $empOt <br/>
                         Contract: $expdate
                        ";

                if(!empty($email)){

                    $mail = new PHPMailer();

                    // smtp settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->e_username;  // gmail address
                    $mail->Password = $this->e_password;  // gmail password

                    $mail->Port = 465;
                    $mail->SMTPSecure = "ssl";

                    // email settings
                    $mail->isHTML(true);
                    $mail->setFrom($email, $name);              // Katabi ng user image
                    $mail->addAddress($email);                  // gmail address ng pagsesendan
                    $mail->Subject = ("$email");                // headline
                    $mail->Body = $body;                        // textarea

                    $mail->send();

                }
            } 
        } elseif($empPosition == 'Inspector'){
            $sqlCompany = "SELECT * FROM company WHERE company_name = ?";
            $stmtCompany = $this->con()->prepare($sqlCompany);
            $stmtCompany->execute([$company]);
            $userCompany = $stmtCompany->fetch();
            $countRowCompany = $stmtCompany->rowCount();

            if($countRowCompany > 0){
                $empLocation = $userCompany->comp_location;
                $empShiftSpan = $userCompany->shift_span;

                $empShift = $userCompany->shifts;
                $empDayStart = "";
                $empDayEnd = "";

                if($empShift == 'night'){
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                    $empDayEnd = date("h:i a", strtotime($empDayStart." +".$userCompany->shift_span." hours"));
                } else {
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start));
                    $empDayEnd = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                }

                $name = 'JTDV Incorporation';
                $body = "Congratulations! You have been assigned to $company. The company located at $empLocation. <br/>
                         Shift type: $empShift <br/>
                         Your schedule: $empDayStart - $empDayEnd <br/>
                         Position: $empPosition <br/>
                         Rate per hour: $empPrice <br/>
                         Overtime Rate: $empOt <br/>
                         Contract: $expdate
                        ";

                if(!empty($email)){

                    $mail = new PHPMailer();

                    // smtp settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->e_username;  // gmail address
                    $mail->Password = $this->e_password;  // gmail password

                    $mail->Port = 465;
                    $mail->SMTPSecure = "ssl";

                    // email settings
                    $mail->isHTML(true);
                    $mail->setFrom($email, $name);              // Katabi ng user image
                    $mail->addAddress($email);                  // gmail address ng pagsesendan
                    $mail->Subject = ("$email");                // headline
                    $mail->Body = $body;                        // textarea

                    $mail->send();

                }
            } 
        } elseif($empPosition == 'Operation Manager'){
            $sqlCompany = "SELECT * FROM company WHERE company_name = ?";
            $stmtCompany = $this->con()->prepare($sqlCompany);
            $stmtCompany->execute([$company]);
            $userCompany = $stmtCompany->fetch();
            $countRowCompany = $stmtCompany->rowCount();

            if($countRowCompany > 0){
                $empLocation = $userCompany->comp_location;
                $empShiftSpan = $userCompany->shift_span;

                $empShift = $userCompany->shifts;
                $empDayStart = "";
                $empDayEnd = "";

                if($empShift == 'night'){
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                    $empDayEnd = date("h:i a", strtotime($empDayStart." +".$userCompany->shift_span." hours"));
                } else {
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start));
                    $empDayEnd = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                }

                $name = 'JTDV Incorporation';
                $body = "Congratulations! You have been assigned to $company. The company located at $empLocation. <br/>
                         Shift type: $empShift <br/>
                         Your schedule: $empDayStart - $empDayEnd <br/>
                         Position: $empPosition <br/>
                         Rate per hour: $empPrice <br/>
                         Overtime Rate: $empOt <br/>
                         Contract: $expdate
                        ";

                if(!empty($email)){

                    $mail = new PHPMailer();

                    // smtp settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->e_username;  // gmail address
                    $mail->Password = $this->e_password;  // gmail password

                    $mail->Port = 465;
                    $mail->SMTPSecure = "ssl";

                    // email settings
                    $mail->isHTML(true);
                    $mail->setFrom($email, $name);              // Katabi ng user image
                    $mail->addAddress($email);                  // gmail address ng pagsesendan
                    $mail->Subject = ("$email");                // headline
                    $mail->Body = $body;                        // textarea

                    $mail->send();

                }
            } 
        } elseif($empPosition == 'Collector'){
            $sqlCompany = "SELECT * FROM company WHERE company_name = ?";
            $stmtCompany = $this->con()->prepare($sqlCompany);
            $stmtCompany->execute([$company]);
            $userCompany = $stmtCompany->fetch();
            $countRowCompany = $stmtCompany->rowCount();

            if($countRowCompany > 0){
                $empLocation = $userCompany->comp_location;
                $empShiftSpan = $userCompany->shift_span;

                $empShift = $userCompany->shifts;
                $empDayStart = "";
                $empDayEnd = "";

                if($empShift == 'night'){
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                    $empDayEnd = date("h:i a", strtotime($empDayStart." +".$userCompany->shift_span." hours"));
                } else {
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start));
                    $empDayEnd = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                }

                $name = 'JTDV Incorporation';
                $body = "Congratulations! You have been assigned to $company. The company located at $empLocation. <br/>
                         Shift type: $empShift <br/>
                         Your schedule: $empDayStart - $empDayEnd <br/>
                         Position: $empPosition <br/>
                         Rate per hour: $empPrice <br/>
                         Overtime Rate: $empOt <br/>
                         Contract: $expdate
                        ";

                if(!empty($email)){

                    $mail = new PHPMailer();

                    // smtp settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->e_username;  // gmail address
                    $mail->Password = $this->e_password;  // gmail password

                    $mail->Port = 465;
                    $mail->SMTPSecure = "ssl";

                    // email settings
                    $mail->isHTML(true);
                    $mail->setFrom($email, $name);              // Katabi ng user image
                    $mail->addAddress($email);                  // gmail address ng pagsesendan
                    $mail->Subject = ("$email");                // headline
                    $mail->Body = $body;                        // textarea

                    $mail->send();

                }
            } 
        } elseif($empPosition == 'Secretary'){
            $sqlCompany = "SELECT * FROM company WHERE company_name = ?";
            $stmtCompany = $this->con()->prepare($sqlCompany);
            $stmtCompany->execute([$company]);
            $userCompany = $stmtCompany->fetch();
            $countRowCompany = $stmtCompany->rowCount();

            if($countRowCompany > 0){
                $empLocation = $userCompany->comp_location;
                $empShiftSpan = $userCompany->shift_span;

                $empShift = $userCompany->shifts;
                $empDayStart = "";
                $empDayEnd = "";

                if($empShift == 'night'){
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                    $empDayEnd = date("h:i a", strtotime($empDayStart." +".$userCompany->shift_span." hours"));
                } else {
                    $empDayStart = date("h:i a", strtotime($userCompany->day_start));
                    $empDayEnd = date("h:i a", strtotime($userCompany->day_start." +".$userCompany->shift_span." hours"));
                }

                $name = 'JTDV Incorporation';
                $body = "Congratulations! You have been assigned to $company. The company located at $empLocation. <br/>
                         Shift type: $empShift <br/>
                         Your schedule: $empDayStart - $empDayEnd <br/>
                         Position: $empPosition <br/>
                         Rate per hour: $empPrice <br/>
                         Overtime Rate: $empOt <br/>
                         Contract: $expdate
                        ";

                if(!empty($email)){

                    $mail = new PHPMailer();

                    // smtp settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->e_username;  // gmail address
                    $mail->Password = $this->e_password;  // gmail password

                    $mail->Port = 465;
                    $mail->SMTPSecure = "ssl";

                    // email settings
                    $mail->isHTML(true);
                    $mail->setFrom($email, $name);              // Katabi ng user image
                    $mail->addAddress($email);                  // gmail address ng pagsesendan
                    $mail->Subject = ("$email");                // headline
                    $mail->Body = $body;                        // textarea

                    $mail->send();

                }
            } 
        } else {
            // not officer in charge

            $sqlCompany = "SELECT * FROM company WHERE company_name = ?";
            $stmtCompany = $this->con()->prepare($sqlCompany);
            $stmtCompany->execute([$company]);
            $userCompany = $stmtCompany->fetch();
            $countRowCompany = $stmtCompany->rowCount();

            if($countRowCompany > 0){
                $empLocation = $userCompany->comp_location;
                
                $name = 'JTDV Incorporation';
                $body = "Congratulations! You have been assigned to $company. The company located at $empLocation. <br/>
                         Position: $empPosition <br/>
                         Rate per hour: $empPrice <br/>
                         Overtime Rate: $empOt <br/>
                         Contract: $expdate
                        ";

                if(!empty($email)){

                    $mail = new PHPMailer();

                    // smtp settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->e_username;  // gmail address
                    $mail->Password = $this->e_password;  // gmail password

                    $mail->Port = 465;
                    $mail->SMTPSecure = "ssl";

                    // email settings
                    $mail->isHTML(true);
                    $mail->setFrom($email, $name);              // Katabi ng user image
                    $mail->addAddress($email);                  // gmail address ng pagsesendan
                    $mail->Subject = ("$email");     // headline
                    $mail->Body = $body;                        // textarea

                    $mail->send();
                }
            }
        }
    }



    // unavailable guards
    public function setUnavailableGuards($adminFullname, $adminId)
    {
        if(isset($_POST['assignguards']))
        {
            $countInput = $_POST['countInput'];

            if(empty($countInput) || $countInput == '' || $countInput == NULL){
                echo "<script>window.location.assign('showEmployees.php');</script>";
            } else {
                
                // year, month, day
                $year = $_POST['year'];
                
                if(isset($_POST['month']) && isset($_POST['day'])){
                    $month = $_POST['month'];
                    $day = $_POST['day'];
                } else {
                    $month = 0;
                    $day = 0;
                }

                // date now - input fields
                $expiration_date = date('Y-m-d', strtotime("+$year years $month months $day days"));

                $availability = "Unavailable";

                $company = $_POST['companyname'];
                
                $sqlCompany = "SELECT * FROM company WHERE company_name = ?";
                $stmtCompany = $this->con()->prepare($sqlCompany);
                $stmtCompany->execute([$company]);
                $userCompany = $stmtCompany->fetch();
                $countRowCompany = $stmtCompany->rowCount();

                for($i = 0; $i < $countInput; $i++)
                {
                    $empId = $_POST["empId$i"];
                    $position = $_POST["position$i"];
                    $ratesperDay = $_POST["price$i"];
                    $ot = $_POST["ot$i"];
                    $email = $_POST["email$i"];

                    if($position == 'Officer in Charge'){
                        
                        // pag di ka manual meron ka sched
                        $companyShift = $userCompany->shifts;
                        $companyShiftSpan = $userCompany->shift_span;
                        $companyStart = "";

                        if($companyShift == 'night'){
                                $companyStart = date("h:i a", strtotime($userCompany->day_start." +".$companyShiftSpan." hours"));
                        } else {
                            $companyStart = $userCompany->day_start;
                        }

                        $companyEnd = date("h:i a", strtotime($companyStart." +".$companyShiftSpan." hours"));

                        $sql = "INSERT INTO schedule(empId, 
                                                     company, 
                                                     scheduleTimeIn, 
                                                     scheduleTimeOut, 
                                                     shift,
                                                     shift_span,
                                                     expiration_date
                                                    )
                                VALUES(?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$empId, 
                                        $company, 
                                        $companyStart,
                                        $companyEnd,
                                        $companyShift,
                                        $companyShiftSpan,
                                        $expiration_date
                                       ]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){
                            $sqlEmpUpdate = "UPDATE employee
                                             SET position = ?,
                                                 ratesperDay = ?,
                                                 overtime_rate = ?,
                                                 availability = ?
                                             WHERE empId = ?";
                            $stmtEmpUpdate = $this->con()->prepare($sqlEmpUpdate);
                            $stmtEmpUpdate->execute([$position, $ratesperDay, $ot, $availability, $empId]);
                            $countRowEmpUpdate = $stmtEmpUpdate->rowCount();

                            if($countRowEmpUpdate > 0){

                                $sqlHR = "SELECT * FROM company WHERE company_name = ?";
                                $stmtHR = $this->con()->prepare($sqlHR);
                                $stmtHR->execute([$company]);
                                $usersHR = $stmtHR->fetch();
                                $countRowHR = $stmtHR->rowCount();
                                $hiredGuards = 0;

                                $intUsersHR = intval($usersHR->hired_guards);
                                    
                                if($countRowHR > 0){
                                    if($intUsersHR == 0 || 
                                        $intUsersHR == '0' || 
                                        $intUsersHR == NULL ||
                                        $intUsersHR == 'NULL' ||
                                        $intUsersHR == ''
                                    ){
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    } else {
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    }
                                }

                                $sqlHiredGuards = "UPDATE company SET hired_guards = ? WHERE company_name = ?";
                                $stmtHiredGuards = $this->con()->prepare($sqlHiredGuards);
                                $stmtHiredGuards->execute([$hiredGuards, $company]);
                                $countRowHiredGuards = $stmtHiredGuards->rowCount();

                                if($countRowHiredGuards > 0){
                                    $this->sendEmailForEmployee($email, $empId, $company, $expiration_date);

                                    $action = "Assign";
                                    $table_name = "Available Employee";
                                    $admindatetime = $this->getDateTime();
                                    $adminTime = $admindatetime['time'];
                                    $adminDate = $admindatetime['date'];
                                                                        
                                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                }
                                
                                echo "<script>window.location.assign('employee.php');</script>";
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Submit Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                        }
                    } elseif($position == 'Inspector'){
                        // pag di ka manual meron ka sched
                        $companyShift = $userCompany->shifts;
                        $companyShiftSpan = $userCompany->shift_span;
                        $companyStart = "";

                        if($companyShift == 'night'){
                                $companyStart = date("h:i a", strtotime($userCompany->day_start." +".$companyShiftSpan." hours"));
                        } else {
                            $companyStart = $userCompany->day_start;
                        }

                        $companyEnd = date("h:i a", strtotime($companyStart." +".$companyShiftSpan." hours"));

                        $sql = "INSERT INTO schedule(empId, 
                                                     company, 
                                                     scheduleTimeIn, 
                                                     scheduleTimeOut, 
                                                     shift,
                                                     shift_span,
                                                     expiration_date
                                                    )
                                VALUES(?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$empId, 
                                        $company, 
                                        $companyStart,
                                        $companyEnd,
                                        $companyShift,
                                        $companyShiftSpan,
                                        $expiration_date
                                       ]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){
                            $sqlEmpUpdate = "UPDATE employee
                                             SET position = ?,
                                                 ratesperDay = ?,
                                                 overtime_rate = ?,
                                                 availability = ?
                                             WHERE empId = ?";
                            $stmtEmpUpdate = $this->con()->prepare($sqlEmpUpdate);
                            $stmtEmpUpdate->execute([$position, $ratesperDay, $ot, $availability, $empId]);
                            $countRowEmpUpdate = $stmtEmpUpdate->rowCount();

                            if($countRowEmpUpdate > 0){

                                $sqlHR = "SELECT * FROM company WHERE company_name = ?";
                                $stmtHR = $this->con()->prepare($sqlHR);
                                $stmtHR->execute([$company]);
                                $usersHR = $stmtHR->fetch();
                                $countRowHR = $stmtHR->rowCount();
                                $hiredGuards = 0;

                                $intUsersHR = intval($usersHR->hired_guards);
                                    
                                if($countRowHR > 0){
                                    if($intUsersHR == 0 || 
                                        $intUsersHR == '0' || 
                                        $intUsersHR == NULL ||
                                        $intUsersHR == 'NULL' ||
                                        $intUsersHR == ''
                                    ){
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    } else {
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    }
                                }

                                $sqlHiredGuards = "UPDATE company SET hired_guards = ? WHERE company_name = ?";
                                $stmtHiredGuards = $this->con()->prepare($sqlHiredGuards);
                                $stmtHiredGuards->execute([$hiredGuards, $company]);
                                $countRowHiredGuards = $stmtHiredGuards->rowCount();

                                if($countRowHiredGuards > 0){
                                    $this->sendEmailForEmployee($email, $empId, $company, $expiration_date);

                                    $action = "Assign";
                                    $table_name = "Available Employee";
                                    $admindatetime = $this->getDateTime();
                                    $adminTime = $admindatetime['time'];
                                    $adminDate = $admindatetime['date'];
                                                                        
                                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                }
                                
                                echo "<script>window.location.assign('employee.php');</script>";
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Submit Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                        }
                    } elseif($position == 'Office Clerk'){
                        // pag di ka manual meron ka sched
                        $companyShift = $userCompany->shifts;
                        $companyShiftSpan = $userCompany->shift_span;
                        $companyStart = "";

                        if($companyShift == 'night'){
                                $companyStart = date("h:i a", strtotime($userCompany->day_start." +".$companyShiftSpan." hours"));
                        } else {
                            $companyStart = $userCompany->day_start;
                        }

                        $companyEnd = date("h:i a", strtotime($companyStart." +".$companyShiftSpan." hours"));

                        $sql = "INSERT INTO schedule(empId, 
                                                     company, 
                                                     scheduleTimeIn, 
                                                     scheduleTimeOut, 
                                                     shift,
                                                     shift_span,
                                                     expiration_date
                                                    )
                                VALUES(?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$empId, 
                                        $company, 
                                        $companyStart,
                                        $companyEnd,
                                        $companyShift,
                                        $companyShiftSpan,
                                        $expiration_date
                                       ]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){
                            $sqlEmpUpdate = "UPDATE employee
                                             SET position = ?,
                                                 ratesperDay = ?,
                                                 overtime_rate = ?,
                                                 availability = ?
                                             WHERE empId = ?";
                            $stmtEmpUpdate = $this->con()->prepare($sqlEmpUpdate);
                            $stmtEmpUpdate->execute([$position, $ratesperDay, $ot, $availability, $empId]);
                            $countRowEmpUpdate = $stmtEmpUpdate->rowCount();

                            if($countRowEmpUpdate > 0){

                                $sqlHR = "SELECT * FROM company WHERE company_name = ?";
                                $stmtHR = $this->con()->prepare($sqlHR);
                                $stmtHR->execute([$company]);
                                $usersHR = $stmtHR->fetch();
                                $countRowHR = $stmtHR->rowCount();
                                $hiredGuards = 0;

                                $intUsersHR = intval($usersHR->hired_guards);
                                    
                                if($countRowHR > 0){
                                    if($intUsersHR == 0 || 
                                        $intUsersHR == '0' || 
                                        $intUsersHR == NULL ||
                                        $intUsersHR == 'NULL' ||
                                        $intUsersHR == ''
                                    ){
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    } else {
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    }
                                }

                                $sqlHiredGuards = "UPDATE company SET hired_guards = ? WHERE company_name = ?";
                                $stmtHiredGuards = $this->con()->prepare($sqlHiredGuards);
                                $stmtHiredGuards->execute([$hiredGuards, $company]);
                                $countRowHiredGuards = $stmtHiredGuards->rowCount();

                                if($countRowHiredGuards > 0){
                                    $this->sendEmailForEmployee($email, $empId, $company, $expiration_date);

                                    $action = "Assign";
                                    $table_name = "Available Employee";
                                    $admindatetime = $this->getDateTime();
                                    $adminTime = $admindatetime['time'];
                                    $adminDate = $admindatetime['date'];
                                                                        
                                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                }
                                
                                echo "<script>window.location.assign('employee.php');</script>";
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Submit Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                        }
                    } elseif($position == 'Head Finance'){
                        // pag di ka manual meron ka sched
                        $companyShift = $userCompany->shifts;
                        $companyShiftSpan = $userCompany->shift_span;
                        $companyStart = "";

                        if($companyShift == 'night'){
                                $companyStart = date("h:i a", strtotime($userCompany->day_start." +".$companyShiftSpan." hours"));
                        } else {
                            $companyStart = $userCompany->day_start;
                        }

                        $companyEnd = date("h:i a", strtotime($companyStart." +".$companyShiftSpan." hours"));

                        $sql = "INSERT INTO schedule(empId, 
                                                     company, 
                                                     scheduleTimeIn, 
                                                     scheduleTimeOut, 
                                                     shift,
                                                     shift_span,
                                                     expiration_date
                                                    )
                                VALUES(?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$empId, 
                                        $company, 
                                        $companyStart,
                                        $companyEnd,
                                        $companyShift,
                                        $companyShiftSpan,
                                        $expiration_date
                                       ]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){
                            $sqlEmpUpdate = "UPDATE employee
                                             SET position = ?,
                                                 ratesperDay = ?,
                                                 overtime_rate = ?,
                                                 availability = ?
                                             WHERE empId = ?";
                            $stmtEmpUpdate = $this->con()->prepare($sqlEmpUpdate);
                            $stmtEmpUpdate->execute([$position, $ratesperDay, $ot, $availability, $empId]);
                            $countRowEmpUpdate = $stmtEmpUpdate->rowCount();

                            if($countRowEmpUpdate > 0){

                                $sqlHR = "SELECT * FROM company WHERE company_name = ?";
                                $stmtHR = $this->con()->prepare($sqlHR);
                                $stmtHR->execute([$company]);
                                $usersHR = $stmtHR->fetch();
                                $countRowHR = $stmtHR->rowCount();
                                $hiredGuards = 0;

                                $intUsersHR = intval($usersHR->hired_guards);
                                    
                                if($countRowHR > 0){
                                    if($intUsersHR == 0 || 
                                        $intUsersHR == '0' || 
                                        $intUsersHR == NULL ||
                                        $intUsersHR == 'NULL' ||
                                        $intUsersHR == ''
                                    ){
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    } else {
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    }
                                }

                                $sqlHiredGuards = "UPDATE company SET hired_guards = ? WHERE company_name = ?";
                                $stmtHiredGuards = $this->con()->prepare($sqlHiredGuards);
                                $stmtHiredGuards->execute([$hiredGuards, $company]);
                                $countRowHiredGuards = $stmtHiredGuards->rowCount();

                                if($countRowHiredGuards > 0){
                                    $this->sendEmailForEmployee($email, $empId, $company, $expiration_date);

                                    $action = "Assign";
                                    $table_name = "Available Employee";
                                    $admindatetime = $this->getDateTime();
                                    $adminTime = $admindatetime['time'];
                                    $adminDate = $admindatetime['date'];
                                                                        
                                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                }
                                
                                echo "<script>window.location.assign('employee.php');</script>";
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Submit Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                        }
                    } elseif($position == 'Operation Manager'){
                        // pag di ka manual meron ka sched
                        $companyShift = $userCompany->shifts;
                        $companyShiftSpan = $userCompany->shift_span;
                        $companyStart = "";

                        if($companyShift == 'night'){
                                $companyStart = date("h:i a", strtotime($userCompany->day_start." +".$companyShiftSpan." hours"));
                        } else {
                            $companyStart = $userCompany->day_start;
                        }

                        $companyEnd = date("h:i a", strtotime($companyStart." +".$companyShiftSpan." hours"));

                        $sql = "INSERT INTO schedule(empId, 
                                                     company, 
                                                     scheduleTimeIn, 
                                                     scheduleTimeOut, 
                                                     shift,
                                                     shift_span,
                                                     expiration_date
                                                    )
                                VALUES(?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$empId, 
                                        $company, 
                                        $companyStart,
                                        $companyEnd,
                                        $companyShift,
                                        $companyShiftSpan,
                                        $expiration_date
                                       ]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){
                            $sqlEmpUpdate = "UPDATE employee
                                             SET position = ?,
                                                 ratesperDay = ?,
                                                 overtime_rate = ?,
                                                 availability = ?
                                             WHERE empId = ?";
                            $stmtEmpUpdate = $this->con()->prepare($sqlEmpUpdate);
                            $stmtEmpUpdate->execute([$position, $ratesperDay, $ot, $availability, $empId]);
                            $countRowEmpUpdate = $stmtEmpUpdate->rowCount();

                            if($countRowEmpUpdate > 0){

                                $sqlHR = "SELECT * FROM company WHERE company_name = ?";
                                $stmtHR = $this->con()->prepare($sqlHR);
                                $stmtHR->execute([$company]);
                                $usersHR = $stmtHR->fetch();
                                $countRowHR = $stmtHR->rowCount();
                                $hiredGuards = 0;

                                $intUsersHR = intval($usersHR->hired_guards);
                                    
                                if($countRowHR > 0){
                                    if($intUsersHR == 0 || 
                                        $intUsersHR == '0' || 
                                        $intUsersHR == NULL ||
                                        $intUsersHR == 'NULL' ||
                                        $intUsersHR == ''
                                    ){
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    } else {
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    }
                                }

                                $sqlHiredGuards = "UPDATE company SET hired_guards = ? WHERE company_name = ?";
                                $stmtHiredGuards = $this->con()->prepare($sqlHiredGuards);
                                $stmtHiredGuards->execute([$hiredGuards, $company]);
                                $countRowHiredGuards = $stmtHiredGuards->rowCount();

                                if($countRowHiredGuards > 0){
                                    $this->sendEmailForEmployee($email, $empId, $company, $expiration_date);

                                    $action = "Assign";
                                    $table_name = "Available Employee";
                                    $admindatetime = $this->getDateTime();
                                    $adminTime = $admindatetime['time'];
                                    $adminDate = $admindatetime['date'];
                                                                        
                                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                }
                                
                                echo "<script>window.location.assign('employee.php');</script>";
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Submit Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                        }
                    } elseif($position == 'Collector'){
                        // pag di ka manual meron ka sched
                        $companyShift = $userCompany->shifts;
                        $companyShiftSpan = $userCompany->shift_span;
                        $companyStart = "";

                        if($companyShift == 'night'){
                                $companyStart = date("h:i a", strtotime($userCompany->day_start." +".$companyShiftSpan." hours"));
                        } else {
                            $companyStart = $userCompany->day_start;
                        }

                        $companyEnd = date("h:i a", strtotime($companyStart." +".$companyShiftSpan." hours"));

                        $sql = "INSERT INTO schedule(empId, 
                                                     company, 
                                                     scheduleTimeIn, 
                                                     scheduleTimeOut, 
                                                     shift,
                                                     shift_span,
                                                     expiration_date
                                                    )
                                VALUES(?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$empId, 
                                        $company, 
                                        $companyStart,
                                        $companyEnd,
                                        $companyShift,
                                        $companyShiftSpan,
                                        $expiration_date
                                       ]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){
                            $sqlEmpUpdate = "UPDATE employee
                                             SET position = ?,
                                                 ratesperDay = ?,
                                                 overtime_rate = ?,
                                                 availability = ?
                                             WHERE empId = ?";
                            $stmtEmpUpdate = $this->con()->prepare($sqlEmpUpdate);
                            $stmtEmpUpdate->execute([$position, $ratesperDay, $ot, $availability, $empId]);
                            $countRowEmpUpdate = $stmtEmpUpdate->rowCount();

                            if($countRowEmpUpdate > 0){

                                $sqlHR = "SELECT * FROM company WHERE company_name = ?";
                                $stmtHR = $this->con()->prepare($sqlHR);
                                $stmtHR->execute([$company]);
                                $usersHR = $stmtHR->fetch();
                                $countRowHR = $stmtHR->rowCount();
                                $hiredGuards = 0;

                                $intUsersHR = intval($usersHR->hired_guards);
                                    
                                if($countRowHR > 0){
                                    if($intUsersHR == 0 || 
                                        $intUsersHR == '0' || 
                                        $intUsersHR == NULL ||
                                        $intUsersHR == 'NULL' ||
                                        $intUsersHR == ''
                                    ){
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    } else {
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    }
                                }

                                $sqlHiredGuards = "UPDATE company SET hired_guards = ? WHERE company_name = ?";
                                $stmtHiredGuards = $this->con()->prepare($sqlHiredGuards);
                                $stmtHiredGuards->execute([$hiredGuards, $company]);
                                $countRowHiredGuards = $stmtHiredGuards->rowCount();

                                if($countRowHiredGuards > 0){
                                    $this->sendEmailForEmployee($email, $empId, $company, $expiration_date);

                                    $action = "Assign";
                                    $table_name = "Available Employee";
                                    $admindatetime = $this->getDateTime();
                                    $adminTime = $admindatetime['time'];
                                    $adminDate = $admindatetime['date'];
                                                                        
                                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                }
                                
                                echo "<script>window.location.assign('employee.php');</script>";
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Submit Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                        }
                    } elseif($position == 'Secretary'){
                        // pag di ka manual meron ka sched
                        $companyShift = $userCompany->shifts;
                        $companyShiftSpan = $userCompany->shift_span;
                        $companyStart = "";

                        if($companyShift == 'night'){
                                $companyStart = date("h:i a", strtotime($userCompany->day_start." +".$companyShiftSpan." hours"));
                        } else {
                            $companyStart = $userCompany->day_start;
                        }

                        $companyEnd = date("h:i a", strtotime($companyStart." +".$companyShiftSpan." hours"));

                        $sql = "INSERT INTO schedule(empId, 
                                                     company, 
                                                     scheduleTimeIn, 
                                                     scheduleTimeOut, 
                                                     shift,
                                                     shift_span,
                                                     expiration_date
                                                    )
                                VALUES(?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$empId, 
                                        $company, 
                                        $companyStart,
                                        $companyEnd,
                                        $companyShift,
                                        $companyShiftSpan,
                                        $expiration_date
                                       ]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){
                            $sqlEmpUpdate = "UPDATE employee
                                             SET position = ?,
                                                 ratesperDay = ?,
                                                 overtime_rate = ?,
                                                 availability = ?
                                             WHERE empId = ?";
                            $stmtEmpUpdate = $this->con()->prepare($sqlEmpUpdate);
                            $stmtEmpUpdate->execute([$position, $ratesperDay, $ot, $availability, $empId]);
                            $countRowEmpUpdate = $stmtEmpUpdate->rowCount();

                            if($countRowEmpUpdate > 0){

                                $sqlHR = "SELECT * FROM company WHERE company_name = ?";
                                $stmtHR = $this->con()->prepare($sqlHR);
                                $stmtHR->execute([$company]);
                                $usersHR = $stmtHR->fetch();
                                $countRowHR = $stmtHR->rowCount();
                                $hiredGuards = 0;

                                $intUsersHR = intval($usersHR->hired_guards);
                                    
                                if($countRowHR > 0){
                                    if($intUsersHR == 0 || 
                                        $intUsersHR == '0' || 
                                        $intUsersHR == NULL ||
                                        $intUsersHR == 'NULL' ||
                                        $intUsersHR == ''
                                    ){
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    } else {
                                        $hiredGuards = intval($intUsersHR) + 1;
                                    }
                                }

                                $sqlHiredGuards = "UPDATE company SET hired_guards = ? WHERE company_name = ?";
                                $stmtHiredGuards = $this->con()->prepare($sqlHiredGuards);
                                $stmtHiredGuards->execute([$hiredGuards, $company]);
                                $countRowHiredGuards = $stmtHiredGuards->rowCount();

                                if($countRowHiredGuards > 0){
                                    $this->sendEmailForEmployee($email, $empId, $company, $expiration_date);

                                    $action = "Assign";
                                    $table_name = "Available Employee";
                                    $admindatetime = $this->getDateTime();
                                    $adminTime = $admindatetime['time'];
                                    $adminDate = $admindatetime['date'];
                                                                        
                                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                }
                                
                                echo "<script>window.location.assign('employee.php');</script>";
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Submit Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                        }
                    } else {
                        // if not equal to officer in charge do not set schedule
                        $sql = "INSERT INTO schedule(empId, company, expiration_date)
                                VALUES(?, ?, ?)";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$empId, $company, $expiration_date]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){
                            $sqlEmpUpdate = "UPDATE employee
                                             SET position = ?,
                                                 ratesperDay = ?,
                                                 overtime_rate = ?,
                                                 availability = ?
                                             WHERE empId = ?";
                            $stmtEmpUpdate = $this->con()->prepare($sqlEmpUpdate);
                            $stmtEmpUpdate->execute([$position, $ratesperDay, $ot, $availability, $empId]);
                            $countRowEmpUpdate = $stmtEmpUpdate->rowCount();

                            if($countRowEmpUpdate > 0){

                                $sqlHR = "SELECT * FROM company WHERE company_name = ?";
                                $stmtHR = $this->con()->prepare($sqlHR);
                                $stmtHR->execute([$company]);
                                $usersHR = $stmtHR->fetch();
                                $countRowHR = $stmtHR->rowCount();
                                $hiredGuards = 0;

                                $intUsersHR = intval($usersHR->hired_guards);
                                    
                                    if($countRowHR > 0){
                                        if($intUsersHR == 0 || 
                                           $intUsersHR == NULL ||
                                           $intUsersHR == 'NULL' ||
                                           $intUsersHR == ''
                                        ){
                                           $hiredGuards = intval($intUsersHR) + 1;
                                        } else {
                                            $hiredGuards = intval($intUsersHR) + 1;
                                        }
                                    }

                                $sqlHiredGuards = "UPDATE company SET hired_guards = ? WHERE company_name = ?";
                                $stmtHiredGuards = $this->con()->prepare($sqlHiredGuards);
                                $stmtHiredGuards->execute([$hiredGuards, $company]);
                                $countRowHiredGuards = $stmtHiredGuards->rowCount();

                                if($countRowHiredGuards > 0){
                                    $this->sendEmailForEmployee($email, $empId, $company, $expiration_date);

                                    $action = "Assign";
                                    $table_name = "Available Employee";
                                    $admindatetime = $this->getDateTime();
                                    $adminTime = $admindatetime['time'];
                                    $adminDate = $admindatetime['date'];
                                                                        
                                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);

                                }
                                echo "<script>window.location.assign('employee.php');</script>";
                            }
                        }
                    }
                }
            }
        }
    }






    public function updateEmployee($id, $urlEmail, $adminFullname, $adminId)
    {
        if(isset($_POST['editemployee'])){

            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $address = $_POST['address'];
            $email = $_POST['email'];
            $number = $_POST['cpnumber'];

            if(empty($firstname) &&
               empty($lastname) &&
               empty($number) &&
               empty($address) &&
               empty($email)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input fields are required!</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {
                
                if($email != $urlEmail){
                    
                    if($this->checkEmpEmailExist($email)){
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Email is already exist!</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                    } else {
                        
                        // update mo na ko
                        $sql = "UPDATE employee
                                SET firstname = ?, 
                                    lastname = ?,
                                    cpnumber = ?,
                                    address = ?, 
                                    email = ?
                                WHERE id = ?";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$firstname, $lastname, $number, $address, $email, $id]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){

                            // after mo maupdate kunin mo yung data
                            $sql2 = "SELECT e.email, 
                                            de.e_id, 
                                            de.secret_key as secret_key
                                    FROM employee e
                                    INNER JOIN secret_diarye de
                                    ON e.email = de.e_id

                                    WHERE e.email = ?";
                            $stmt2 = $this->con()->prepare($sql2);
                            $stmt2->execute([$email]);
                            $users2 = $stmt2->fetch();
                            $countRow2 = $stmt2->rowCount();

                            if($countRow2 > 0){
                                $this->sendEmail($users2->email, $users2->secret_key);

                                $action = "Edit";
                                $table_name = "Available Employee";
                                $admindatetime = $this->getDateTime();
                                $adminTime = $admindatetime['time'];
                                $adminDate = $admindatetime['date'];
                                                                    
                                $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                                    
                                $countRowAdminLog = $stmtAdminLog->rowCount();
                                if($countRowAdminLog > 0){
                                    $msg = 'Updated Successfully';
                                    echo "<script>window.location.assign('./showEmployees.php?message=$msg');</script>";
                                } else {
                                echo "<div class='error'>
                                        <div class='icon-container'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                        <p>Update Failed</p>
                                        <div class='closeContainer'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                    </div>
                                    <script>
                                        let msgErr = document.querySelector('.error');
                                        setTimeout(e => msgErr.remove(), 5000);
                                    </script>";
                                }
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Update Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                </div>
                                <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                </script>";
                        }
                    }
                } else {
                    
                    // update mo na ko
                    $sql = "UPDATE employee
                            SET firstname = ?, 
                                lastname = ?,
                                cpnumber = ?,
                                address = ?, 
                                email = ?
                            WHERE id = ?";
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute([$firstname, $lastname, $number, $address, $email, $id]);
                    $countRow = $stmt->rowCount();

                    if($countRow > 0){
                        
                        $action = "Edit";
                        $table_name = "Available Employee";
                        $admindatetime = $this->getDateTime();
                        $adminTime = $admindatetime['time'];
                        $adminDate = $admindatetime['date'];
                                                            
                        $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                        $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                        $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                            
                        $countRowAdminLog = $stmtAdminLog->rowCount();
                        if($countRowAdminLog > 0){
                            $msg = 'Updated Successfully';
                            echo "<script>window.location.assign('./showEmployees.php?message=$msg');</script>";
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Update Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                </div>
                                <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                </script>";
                        }
                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Update Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                            </div>
                            <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                            </script>";
                    }
                }
            }
        }
    }


    public function deleteEmployee($id, $adminFullname, $adminId){
        if(isset($_POST['deleteEmployee'])){

            $sqlFind = "SELECT * FROM employee WHERE id = ?";
            $stmt = $this->con()->prepare($sqlFind);
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            $countRow = $stmt->rowCount();

            if($countRow > 0){
                $empEmail = $user->email;

                $sqlEmployee = "UPDATE employee SET isDeleted = 1 WHERE id = ?";
                $stmtEmployee = $this->con()->prepare($sqlEmployee);
                $stmtEmployee->execute([$id]);
                $countRowEmployee = $stmtEmployee->rowCount();
                if($countRowEmployee > 0){

                    $action = "Delete";
                    $table_name = "Available Employee";
                    $admindatetime = $this->getDateTime();
                    $adminTime = $admindatetime['time'];
                    $adminDate = $admindatetime['date'];
                                                            
                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                            
                    $countRowAdminLog = $stmtAdminLog->rowCount();
                    if($countRowAdminLog > 0){
                        $msg = 'Deleted Successfully';
                        echo "<script>window.location.assign('./showEmployees.php?message=$msg');</script>";
                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Delete Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                        }
                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Delete Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                    }
            }
        }
    }



    public function showSpecificEmp()
    {
        if(isset($_GET['id'])){
            $id = $_GET['id'];

            $sql = "SELECT * FROM employee WHERE id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            $countRow = $stmt->rowCount();

            if($countRow > 0){
                $firstname = $user->firstname;
                $lastname = $user->lastname;
                $address = $user->address;
                $email = $user->email;
                $number = $user->cpnumber;

                echo "<script>
                         let viewModal = document.querySelector('.modal-edit');
                         let firstname = document.querySelector('#firstname').value = '$firstname';
                         let lastname = document.querySelector('#lastname').value = '$lastname';
                         let address = document.querySelector('#address').value = '$address';
                         let email = document.querySelector('#email').value = '$email';
                         let number = document.querySelector('#cpnumber').value = '$number';
                      </script>";
            }
        }
    }


    public function checkEmpEmailExist($email)
    {
        // find email exist in the database
        $sql = "SELECT * FROM employee WHERE email = ?";
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

    public function recentactivityleave()
    {
        $sql = "SELECT 
                        l.*,
                        l.date_admin,
                        e.empId,
                        e.firstname as firstname,
                        e.lastname as lastname
                FROM leave_request l
                INNER JOIN employee e
                ON l.empId = e.empId
                WHERE 
                    status != 'pending' 
                AND 
                    date_admin BETWEEN date_sub(curdate(),interval 30 day) AND curdate()
                ORDER BY l.date_admin DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<div class='card'>
                    <div class='card-header'>
                        <div class='card-status'>
                            <div class='circle' style='background-color: gray;'></div>
                            <h2>No recent</h2>
                        </div>
                        <form method='post' class='removeRecent'>
                            <input type='hidden' name='removeDate' value='' required/>
                            <input type='hidden' name='removeId' value='' required/>
                            <button type='submit' name='removeRecentBtn'><span class='material-icons'></span></button>
                        </form>  
                    </div>
                    <div class='card-content'>
                        <p></p>
                        <span style='margin-top: 20px;'><b></b></span>
                    </div>
                  </div>";
        } else {
            while($row = $stmt->fetch()){
                $fullname = $row->lastname . ", " . $row->firstname;
    
                echo "<div class='card'>
                        <div class='card-header'>
                            <div class='card-status'>
                                <div class='circle $row->status'></div>
                                <h2>$row->status</h2>
                            </div>
                            <form method='post' class='removeRecent'>
                                <input type='hidden' name='removeDate' value='$row->date_admin' required/>
                                <input type='hidden' name='removeId' value='$row->id' required/>
                                <button type='submit' name='removeRecentBtn'><span class='material-icons'>close</span></button>
                            </form>  
                        </div>
                        <div class='card-content'>
                            <p>$fullname</p>
                            <span>Date: <b>$row->date_admin</b></span>
                        </div>
                      </div>";
            }
        }
    }

    public function removeRecentFunction()
    {
        if(isset($_POST['removeRecentBtn'])){
            $removeId = $_POST['removeId'];
            $removeDate = $_POST['removeDate'];

            $sql = "UPDATE leave_request 
                    SET date_admin = date_sub(?, interval 31 day)
                    WHERE id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$removeDate, $removeId]);
            $countRow = $stmt->rowCount();

            if($countRow > 0){
                $msg = 'Removed from recent';
                echo "<script>window.location.assign('./leave.php?message=$msg');</script>";
            }
        }
    }


    public function listofleaverequest()
    {
        $sql = "SELECT 
                        l.*, 
                        l.id as id,
                        e.empId,
                        e.firstname as firstname,  
                        e.lastname as lastname
                FROM leave_request l
                INNER JOIN employee e
                ON l.empId = e.empId
                WHERE status = 'pending'
                ORDER BY l.id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<tr>
                    <td>No data found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        } else {
            while($row = $stmt->fetch()){
                $fullname = $row->firstname ." ". $row->lastname;
                echo "<tr>
                        <td>$fullname</td>
                        <td>$row->typeOfLeave</td>
                        <td>$row->reason</td>
                        <td>$row->days</td>
                        <td>$row->leave_start</td>
                        <td>$row->leave_end</td>
                        <td>
                            <div class='buttons'>
                                <a href='leave.php?id=$row->id&act=approve'><span class='material-icons'>done</span></a>
                                <a href='leave.php?id=$row->id&act=reject'><span class='material-icons'>close</span></a>
                            </div>
                        </td>
                      </tr>";
            }
        }
    }

    public function listofleaveapprove()
    {
        $sql = "SELECT 
                        l.*, 
                        l.id as id,
                        e.empId,
                        e.firstname as firstname,  
                        e.lastname as lastname
                FROM leave_request l
                INNER JOIN employee e
                ON l.empId = e.empId
                WHERE status = 'approved'
                ORDER BY l.id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<tr>
                    <td>No data found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        } else {
            while($row = $stmt->fetch()){
                $fullname = $row->firstname ." ". $row->lastname;
                echo "<tr>
                        <td>$fullname</td>
                        <td>$row->typeOfLeave</td>
                        <td>$row->reason</td>
                        <td>$row->days</td>
                        <td>$row->leave_start</td>
                        <td>$row->leave_end</td>
                        <td>$row->date_admin</td>
                      </tr>";
            }
        }
    }

    public function listofleavereject()
    {
        $sql = "SELECT 
                        l.*, 
                        l.id as id,
                        e.empId,
                        e.firstname as firstname,  
                        e.lastname as lastname
                FROM leave_request l
                INNER JOIN employee e
                ON l.empId = e.empId
                WHERE status = 'rejected'
                ORDER BY l.id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<tr>
                    <td>No data found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        } else {
            while($row = $stmt->fetch()){
                $fullname = $row->firstname ." ". $row->lastname;
                echo "<tr>
                        <td>$fullname</td>
                        <td>$row->typeOfLeave</td>
                        <td>$row->reason</td>
                        <td>$row->days</td>
                        <td>$row->leave_start</td>
                        <td>$row->leave_end</td>
                        <td>$row->date_admin</td>
                      </tr>";
            }
        }
    }

    public function listoffreeguard()
    {
        $sql = "SELECT * FROM employee WHERE availability = 'Available' AND isDeleted = 0 ORDER BY date DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                $addressArr = explode(' ', $row->address);
                $fullname = $row->firstname ." ". $row->lastname;
                if(in_array('City', $addressArr)){
                    $cityIndex = array_search('City', $addressArr);
                    $cityName = $cityIndex - 1;
                    $filteredAdd = $addressArr[$cityName] . " City";
                    echo "<option value='$row->empId'>$fullname($filteredAdd)</option>";
                }
            }
        } else {
            echo "<option value=''>No Available Guard</option>";
        }
    }


    public function viewRequest($id)
    {
        $sql = "SELECT * FROM leave_request WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            $empId = $user->empId;

            $sqlFind = "SELECT * FROM employee WHERE empId = ?";
            $stmtFind = $this->con()->prepare($sqlFind);
            $stmtFind->execute([$empId]);
            $userFind = $stmtFind->fetch();
            $countRowFind = $stmtFind->rowCount();

            if($countRowFind > 0){
                $fullname = $userFind->firstname." ".$userFind->lastname;
                $email = $userFind->email;
                $address = $userFind->address;
                $days = $user->days;
                $leave_start = $user->leave_start;
                $leave_end = $user->leave_end;
                $type = $user->typeOfLeave;
                $reason = $user->reason;
                echo "<script>
                        let requestId = document.querySelector('#requestId');
                        let fullname = document.querySelector('#fullname');
                        let email = document.querySelector('#email');
                        let address = document.querySelector('#address');
                        let daysleave = document.querySelector('#daysleave');
                        let leave_start = document.querySelector('#leave_start');
                        let leave_end = document.querySelector('#leave_end');
                        let type = document.querySelector('#type');
                        let reason = document.querySelector('#reason');

                        requestId.value = '$id';

                        fullname.value = '$fullname';
                        fullname.setAttribute('readonly', 'readonly');
                        email.value = '$email';
                        email.setAttribute('readonly', 'readonly');

                        address.value = '$address';
                        address.setAttribute('readonly', 'readonly');

                        let option = document.createElement('option');
                        option.value = '$days';
                        option.innerText = '$days';

                        daysleave.appendChild(option);

                        daysleave.value = '$days';
                        daysleave.setAttribute('readonly', 'readonly');


                        leave_start.value = '$leave_start';
                        leave_start.setAttribute('readonly', 'readonly');
                        leave_end.value = '$leave_end';
                        leave_end.setAttribute('readonly', 'readonly');

                        type.value = '$type';
                        type.setAttribute('readonly', 'readonly');

                        reason.value = '$reason';
                        reason.setAttribute('readonly', 'readonly');

                      </script>";
            } else {
                echo " no user found";
            }
        }
    }

    public function informSubstitute($email, 
                                     $company, 
                                     $comp_address, 
                                     $timeinSched,
                                     $timeoutSched,
                                     $shiftSched,
                                     $shiftSpanSched,
                                     $leaveStart,
                                     $expDateNew,
                                     $substiPosition,
                                     $substiPrice,
                                     $substiOT)
    {
        $name = 'JTDV Incorporation';
        $body = "You have been assigned as a substitute for $company. Located at $comp_address <br/>
                 <br/>
                 <h4>Starting at $leaveStart you may start working on us.</h4> <br/>
                 Shift: $shiftSched <br/>
                 Total hours per day: $shiftSpanSched <br/> 
                 Schedule: $timeinSched to $timeoutSched <br/>
                 Position: $substiPosition <br/>
                 Rate per hour: $substiPrice <br/>
                 Overtime Rate: $substiOT <br/>
                 <br/>
                 End of Contract: $expDateNew <br/>

                ";

        if(!empty($email)){

            $mail = new PHPMailer();

            // smtp settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = $this->e_username;  // gmail address
            $mail->Password = $this->e_password;  // gmail password

            $mail->Port = 465;
            $mail->SMTPSecure = "ssl";

            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email, $name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ("$email");     // headline
            $mail->Body = $body;                        // textarea

            $mail->send();
        }
    }

    public function approveRequest($id, $adminFullname, $adminId)
    {
        if(isset($_POST['approveRequest'])){
            $id = $_POST['requestId'];

            $sqlFind = "SELECT 
                                l.*,
                                l.leave_start as leaveStart,
                                l.leave_end as leaveEnd,
                                l.empId as leaveEmpId,
                                s.empId as empId,
                                s.company as company,
                                s.scheduleTimeIn as timein,
                                s.scheduleTimeOut as timeout,
                                s.shift as shift,
                                s.shift_span as shift_span,
                                s.expiration_date as expdate,

                                e.position as position,
                                e.ratesperDay as price,
                                e.overtime_rate as ot,

                                c.comp_location as c_address
                        FROM leave_request l
                        INNER JOIN schedule s
                        ON l.empId = s.empId

                        INNER JOIN employee e
                        ON l.empId = e.empId

                        INNER JOIN company c
                        ON s.company = c.company_name 
                        WHERE l.id = ?";
            $stmtFind = $this->con()->prepare($sqlFind);
            $stmtFind->execute([$id]);
            $userFind = $stmtFind->fetch();
            $countRowFind = $stmtFind->rowCount();

            if($countRowFind > 0){

                $checkData = $userFind->leaveStart;
                $datetime = $this->getDateTime();
                $dateNow = $datetime['date'];
                
                if(strtotime($checkData) < strtotime($dateNow)){
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>No longer valid. Out of date.</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                } else {
                    $status = 'approved';
                    $substiEmpId = $_POST['substitute'];
                    $expDateNew = $userFind->leaveEnd;

                    $substiPosition = $userFind->position;
                    $substiPrice = $userFind->price;
                    $substiOT = $userFind->ot;

                    $availability = 'Unavailable';

                    // set timezone and get date and time
                    $datetime = $this->getDateTime();
                    $date = $datetime['date'];

                    $sqlSubstiUpdate = "UPDATE employee 
                                        SET position = ?,
                                            ratesperDay = ?,
                                            overtime_rate = ?,
                                            availability = ?
                                        WHERE empId = ?";

                    $stmtSubstiUpdate = $this->con()->prepare($sqlSubstiUpdate);
                    $stmtSubstiUpdate->execute([$substiPosition, $substiPrice, $substiOT, $availability, $substiEmpId]);
                    $countRowSubstiUpdate = $stmtSubstiUpdate->rowCount();
                    if($countRowSubstiUpdate > 0){

                        $sql = "UPDATE leave_request
                                SET substitute_by = ?,
                                    status = ?,
                                    date_admin = ?
                                WHERE id = ?
                                ";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$substiEmpId, $status, $date, $id]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){
                            // to add new schedule
                            $companySched = $userFind->company;
                            $timeinSched = $userFind->timein;
                            $timeoutSched = $userFind->timeout;
                            $shiftSched = $userFind->shift;
                            $shiftSpanSched = $userFind->shift_span;

                            $sqlSched = "INSERT INTO schedule(empId, company, scheduleTimeIn, scheduleTimeOut, shift, shift_span, expiration_date)
                                        VALUES(?, ?, ?, ?, ?, ?, ?)
                                        ";
                            $stmtSched = $this->con()->prepare($sqlSched);
                            $stmtSched->execute([$substiEmpId, $companySched, $timeinSched, $timeoutSched, $shiftSched, $shiftSpanSched, $expDateNew]);
                            $countRowSched = $stmtSched->rowCount();
                            if($countRowSched > 0){
                                $leaveEmpId = $userFind->leaveEmpId;

                                $sqlUpdateAvailability = "UPDATE employee
                                                        SET availability = ?
                                                        WHERE empId = ?";
                                $stmtUpdateAvailability = $this->con()->prepare($sqlUpdateAvailability);
                                $stmtUpdateAvailability->execute(['Leave', $leaveEmpId]);
                                $countRowUpdateAvailability = $stmtUpdateAvailability->rowCount();

                                if($countRowUpdateAvailability > 0){
                                    $comp_address = $userFind->c_address;
                                    $leaveStart = $userFind->leaveStart;

                                    // get email of substitute guard
                                    $sqlFindSubsti = "SELECT * FROM employee WHERE empId = ?";
                                    $stmtFindSubsti = $this->con()->prepare($sqlFindSubsti);
                                    $stmtFindSubsti->execute([$substiEmpId]);
                                    $userFindSubsti = $stmtFindSubsti->fetch();
                                    $countRowFindSubsti = $stmtFindSubsti->rowCount();

                                    if($countRowFindSubsti > 0){
                                        // inform substitute guard
                                        $this->informSubstitute($userFindSubsti->email, 
                                                            $companySched, 
                                                            $comp_address, 
                                                            $timeinSched,
                                                            $timeoutSched,
                                                            $shiftSched,
                                                            $shiftSpanSched,
                                                            $leaveStart,
                                                            $expDateNew,
                                                            $substiPosition,
                                                            $substiPrice,
                                                            $substiOT
                                                            );
                                        
                                        $action = "Approve";
                                        $table_name = "Leave";
                                        $admindatetime = $this->getDateTime();
                                        $adminTime = $admindatetime['time'];
                                        $adminDate = $admindatetime['date'];
                                    
                                        $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                        $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                        $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                    
                                        $countRowAdminLog = $stmtAdminLog->rowCount();
                                        if($countRowAdminLog > 0){
                                            $msg = 'Approved Successfully';
                                            echo "<script>window.location.assign('./leave.php?message=$msg')</script>";
                                        } else {
                                            echo "<div class='error'>
                                                    <div class='icon-container'>
                                                        <span class='material-icons'>close</span>
                                                    </div>
                                                    <p>Approve Failed</p>
                                                    <div class='closeContainer'>
                                                        <span class='material-icons'>close</span>
                                                    </div>
                                                  </div>
                                                  <script>
                                                    let msgErr = document.querySelector('.error');
                                                    setTimeout(e => msgErr.remove(), 5000);
                                                  </script>";
                                        }
                                    } else {
                                        echo "<div class='error'>
                                                <div class='icon-container'>
                                                    <span class='material-icons'>close</span>
                                                </div>
                                                <p>No Available Guard Found</p>
                                                <div class='closeContainer'>
                                                    <span class='material-icons'>close</span>
                                                </div>
                                            </div>
                                            <script>
                                                let msgErr = document.querySelector('.error');
                                                setTimeout(e => msgErr.remove(), 5000);
                                            </script>";
                                    }
                                }
                            } else {
                                echo "<div class='error'>
                                        <div class='icon-container'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                        <p>Error Creating Schedule</p>
                                        <div class='closeContainer'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                    </div>
                                    <script>
                                        let msgErr = document.querySelector('.error');
                                        setTimeout(e => msgErr.remove(), 5000);
                                    </script>";
                            }
                        }
                    }
                }

                
            }
        }
    }

    // for dashboard
    public function approveRequest2($id, $adminFullname, $adminId)
    {
        if(isset($_POST['approveRequest'])){
            $id = $_POST['requestId'];

            $sqlFind = "SELECT 
                                l.*,
                                l.leave_start as leaveStart,
                                l.leave_end as leaveEnd,
                                l.empId as leaveEmpId,
                                s.empId as empId,
                                s.company as company,
                                s.scheduleTimeIn as timein,
                                s.scheduleTimeOut as timeout,
                                s.shift as shift,
                                s.shift_span as shift_span,
                                s.expiration_date as expdate,

                                e.position as position,
                                e.ratesperDay as price,
                                e.overtime_rate as ot,

                                c.comp_location as c_address
                        FROM leave_request l
                        INNER JOIN schedule s
                        ON l.empId = s.empId

                        INNER JOIN employee e
                        ON l.empId = e.empId

                        INNER JOIN company c
                        ON s.company = c.company_name 
                        WHERE l.id = ?";
            $stmtFind = $this->con()->prepare($sqlFind);
            $stmtFind->execute([$id]);
            $userFind = $stmtFind->fetch();
            $countRowFind = $stmtFind->rowCount();

            if($countRowFind > 0){

                $checkData = $userFind->leaveStart;
                $datetime = $this->getDateTime();
                $dateNow = $datetime['date'];
                
                if(strtotime($checkData) < strtotime($dateNow)){
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>No longer valid. Out of date.</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                } else {
                    $status = 'approved';
                    $substiEmpId = $_POST['substitute'];
                    $expDateNew = $userFind->leaveEnd;

                    $substiPosition = $userFind->position;
                    $substiPrice = $userFind->price;
                    $substiOT = $userFind->ot;

                    $availability = 'Unavailable';

                    // set timezone and get date and time
                    $datetime = $this->getDateTime();
                    $date = $datetime['date'];

                    $sqlSubstiUpdate = "UPDATE employee 
                                        SET position = ?,
                                            ratesperDay = ?,
                                            overtime_rate = ?,
                                            availability = ?
                                        WHERE empId = ?";

                    $stmtSubstiUpdate = $this->con()->prepare($sqlSubstiUpdate);
                    $stmtSubstiUpdate->execute([$substiPosition, $substiPrice, $substiOT, $availability, $substiEmpId]);
                    $countRowSubstiUpdate = $stmtSubstiUpdate->rowCount();
                    if($countRowSubstiUpdate > 0){

                        $sql = "UPDATE leave_request
                                SET substitute_by = ?,
                                    status = ?,
                                    date_admin = ?
                                WHERE id = ?
                                ";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$substiEmpId, $status, $date, $id]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){
                            // to add new schedule
                            $companySched = $userFind->company;
                            $timeinSched = $userFind->timein;
                            $timeoutSched = $userFind->timeout;
                            $shiftSched = $userFind->shift;
                            $shiftSpanSched = $userFind->shift_span;

                            $sqlSched = "INSERT INTO schedule(empId, company, scheduleTimeIn, scheduleTimeOut, shift, shift_span, expiration_date)
                                        VALUES(?, ?, ?, ?, ?, ?, ?)
                                        ";
                            $stmtSched = $this->con()->prepare($sqlSched);
                            $stmtSched->execute([$substiEmpId, $companySched, $timeinSched, $timeoutSched, $shiftSched, $shiftSpanSched, $expDateNew]);
                            $countRowSched = $stmtSched->rowCount();
                            if($countRowSched > 0){
                                $leaveEmpId = $userFind->leaveEmpId;

                                $sqlUpdateAvailability = "UPDATE employee
                                                          SET availability = ?
                                                          WHERE empId = ?";
                                $stmtUpdateAvailability = $this->con()->prepare($sqlUpdateAvailability);
                                $stmtUpdateAvailability->execute(['Leave', $leaveEmpId]);
                                $countRowUpdateAvailability = $stmtUpdateAvailability->rowCount();

                                if($countRowUpdateAvailability > 0){
                                    $comp_address = $userFind->c_address;
                                    $leaveStart = $userFind->leaveStart;

                                    // get email of substitute guard
                                    $sqlFindSubsti = "SELECT * FROM employee WHERE empId = ?";
                                    $stmtFindSubsti = $this->con()->prepare($sqlFindSubsti);
                                    $stmtFindSubsti->execute([$substiEmpId]);
                                    $userFindSubsti = $stmtFindSubsti->fetch();
                                    $countRowFindSubsti = $stmtFindSubsti->rowCount();

                                    if($countRowFindSubsti > 0){
                                        // inform substitute guard
                                        $this->informSubstitute($userFindSubsti->email, 
                                                            $companySched, 
                                                            $comp_address, 
                                                            $timeinSched,
                                                            $timeoutSched,
                                                            $shiftSched,
                                                            $shiftSpanSched,
                                                            $leaveStart,
                                                            $expDateNew,
                                                            $substiPosition,
                                                            $substiPrice,
                                                            $substiOT
                                                            );

                                        $action = "Approve";
                                        $table_name = "Leave";
                                        $admindatetime = $this->getDateTime();
                                        $adminTime = $admindatetime['time'];
                                        $adminDate = $admindatetime['date'];
                                    
                                        $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";                    
                                        
                                        $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                        $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);

                                        $countRowAdminLog = $stmtAdminLog->rowCount();
                                        if($countRowAdminLog > 0){
                                            $msg = 'Approved Successfully';
                                            echo "<script>window.location.assign('./dashboard.php?message=$msg')</script>";
                                        } else {
                                            echo "<div class='error'>
                                                    <div class='icon-container'>
                                                        <span class='material-icons'>close</span>
                                                    </div>
                                                    <p>Approve Failed</p>
                                                    <div class='closeContainer'>
                                                        <span class='material-icons'>close</span>
                                                    </div>
                                                  </div>
                                                  <script>
                                                    let msgErr = document.querySelector('.error');
                                                    setTimeout(e => msgErr.remove(), 5000);
                                                  </script>";
                                        }
                                    } else {
                                        echo "<div class='error'>
                                                <div class='icon-container'>
                                                    <span class='material-icons'>close</span>
                                                </div>
                                                <p>No Available Guard Found</p>
                                                <div class='closeContainer'>
                                                    <span class='material-icons'>close</span>
                                                </div>
                                            </div>
                                            <script>
                                                let msgErr = document.querySelector('.error');
                                                setTimeout(e => msgErr.remove(), 5000);
                                            </script>";
                                    }
                                }
                            } else {
                                echo "<div class='error'>
                                        <div class='icon-container'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                        <p>Error Creating Schedule</p>
                                        <div class='closeContainer'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                    </div>
                                    <script>
                                        let msgErr = document.querySelector('.error');
                                        setTimeout(e => msgErr.remove(), 5000);
                                    </script>";
                            }
                        }
                    }
                }
            }
        }
    }

    public function rejectRequest($id, $adminFullname, $adminId)
    {
        if(isset($_POST['rejectRequest'])){
            $id = $_GET['id'];
            $email = $_POST['email'];
            $days = $_POST['days'];
            $leave_start = $_POST['leave_start'];
            $leave_end = $_POST['leave_end'];
            $reason = $_POST['reason'];

            // set timezone and get date and time
            $datetime = $this->getDateTime();
            $date = $datetime['date'];

            $sql = "UPDATE leave_request
                    SET status = ?,
                        date_admin = ?
                    WHERE id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute(['rejected', $date, $id]);
            $countRow = $stmt->rowCount();

            if($countRow > 0){
                $name = 'JTDV Incorporation';
                $body = "Your request has been rejected. <br/>
                        <br/>
                        Days: $days <br/>
                        From: $leave_start to $leave_end <br/>
                        Reason: $reason
                        ";

                if(!empty($email)){

                    $mail = new PHPMailer();

                    // smtp settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->e_username;  // gmail address
                    $mail->Password = $this->e_password;  // gmail password

                    $mail->Port = 465;
                    $mail->SMTPSecure = "ssl";

                    // email settings
                    $mail->isHTML(true);
                    $mail->setFrom($email, $name);              // Katabi ng user image
                    $mail->addAddress($email);                  // gmail address ng pagsesendan
                    $mail->Subject = ("$email");                // headline
                    $mail->Body = $body;                        // textarea

                    $mail->send();

                    $action = "Reject";
                    $table_name = "Leave";
                    $admindatetime = $this->getDateTime();
                    $adminTime = $admindatetime['time'];
                    $adminDate = $admindatetime['date'];

                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);

                    $countRowAdminLog = $stmtAdminLog->rowCount();
                    if($countRowAdminLog > 0){
                        $msg = 'Reject Successfully';
                        echo "<script>window.location.assign('./leave.php?message=$msg')</script>";
                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Reject Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                    }
                }
            } else {
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>Failed to reject</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            }
        }
    }

    // for dashboard
    public function rejectRequest2($id, $adminFullname, $adminId)
    {
        if(isset($_POST['rejectRequest'])){
            $id = $_GET['id'];
            $email = $_POST['email'];
            $days = $_POST['days'];
            $leave_start = $_POST['leave_start'];
            $leave_end = $_POST['leave_end'];
            $reason = $_POST['reason'];

            // set timezone and get date and time
            $datetime = $this->getDateTime();
            $date = $datetime['date'];

            $sql = "UPDATE leave_request
                    SET status = ?,
                        date_admin = ?
                    WHERE id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute(['rejected', $date, $id]);
            $countRow = $stmt->rowCount();

            if($countRow > 0){
                $name = 'JTDV Incorporation';
                $body = "Your request has been rejected. <br/>
                        <br/>
                        Days: $days <br/>
                        From: $leave_start to $leave_end <br/>
                        Reason: $reason
                        ";

                if(!empty($email)){

                    $mail = new PHPMailer();

                    // smtp settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->e_username;  // gmail address
                    $mail->Password = $this->e_password;  // gmail password

                    $mail->Port = 465;
                    $mail->SMTPSecure = "ssl";

                    // email settings
                    $mail->isHTML(true);
                    $mail->setFrom($email, $name);              // Katabi ng user image
                    $mail->addAddress($email);                  // gmail address ng pagsesendan
                    $mail->Subject = ("$email");                // headline
                    $mail->Body = $body;                        // textarea

                    $mail->send();

                    $action = "Reject";
                    $table_name = "Leave";
                    $admindatetime = $this->getDateTime();
                    $adminTime = $admindatetime['time'];
                    $adminDate = $admindatetime['date'];

                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);

                    $countRowAdminLog = $stmtAdminLog->rowCount();
                    if($countRowAdminLog > 0){
                        $msg = 'Rejected Successfully';
                        echo "<script>window.location.assign('./dashboard.php?message=$msg')</script>";
                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Reject Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                    }
                }
            } else {
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>Failed to reject</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            }
        }
    }

    //Code sa WEB VERSION
    public function viewListViolation() {
        $sql = "SELECT v.*,
                       e.empId,
                       e.isDeleted 
                FROM violationsandremarks v
                INNER JOIN employee e
                ON v.empId = e.empId
                WHERE v.remark IS NULL
                AND e.isDeleted = 0
                ORDER BY v.id DESC";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while ($row = $stmt->fetch()) {
                echo "<tr>
                        <td>$row->empId</td>
                        <td>$row->fine</td>
                        <td>$row->violation</td>
                        <td>$row->date_created</td>
                        <td><a href='?rid=$row->id'><span class='material-icons'>sticky_note_2</span></a></td>
                    </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }

    public function viewListRemarkedViolation() {
        $sql = "SELECT 
                    v.remark,
                    v.empId,
                    i.*,
                    e.firstname,
                    e.lastname,
                    i.date_created as date_created
                FROM violationsandremarks v

                INNER JOIN inbox i
                ON v.remark = i.id

                INNER JOIN employee e
                ON v.empId = e.empId

                WHERE v.remark IS NOT NULL 
                ORDER BY date_created DESC";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while ($row = $stmt->fetch()) {
                echo "<tr>
                        <td>$row->empId</td>
                        <td>$row->firstname $row->lastname</td>
                        <td>$row->subject</td>
                        <td>$row->date_created</td>
                        <td><a href='?lrid=$row->id'><span class='material-icons'>visibility</span></a></td>
                    </tr>";
            }
        } else {
            echo "<tr>
                    <td style='width: 150px;'>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }

    public function viewListRemarkedViolationSearch($search) {
        $sql = "SELECT 
                    v.remark,
                    v.empId,
                    i.*,
                    e.firstname,
                    e.lastname,
                    i.date_created as date_created
                FROM violationsandremarks v

                INNER JOIN inbox i
                ON v.remark = i.id

                INNER JOIN employee e
                ON v.empId = e.empId

                WHERE v.remark IS NOT NULL AND v.empId LIKE '%$search%' OR
                      v.remark IS NOT NULL AND e.firstname LIKE '%$search%' OR
                      v.remark IS NOT NULL AND e.lastname LIKE '%$search%' OR
                      v.remark IS NOT NULL AND i.subject LIKE '%$search%' OR
                      v.remark IS NOT NULL AND i.date_created LIKE '%$search%'
                ORDER BY date_created DESC";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while ($row = $stmt->fetch()) {
                echo "<tr>
                        <td>$row->empId</td>
                        <td>$row->firstname $row->lastname</td>
                        <td>$row->subject</td>
                        <td>$row->date_created</td>
                        <td><a href='?lrid=$row->id'><span class='material-icons'>visibility</span></a></td>
                    </tr>";
            }
        } else {
            echo "<tr>
                    <td style='width: 150px;'>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
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

                echo "<div class='modal-viewremarked'>
                        <div class='modal-holder'>
                            <div class='viewremarked-header'>
                                <h1>View Remarked</h1>
                                <span id='exit-modal-viewremarked' class='material-icons'>close</span>
                            </div>
                            <div class='viewremarked-content'>
                                <form method='POST'>
                                    <div>
                                        <label for='empid'>Employee ID</label>
                                        <input type='text' id='empid' name='empid' value='$usersView->empId' readonly/>
                                    </div>
                                    <div>
                                        <label for='fullname'>Fullname</label>
                                        <input type='text' id='fullname' name='fullname' value='$usersView->firstname $usersView->lastname' readonly/>
                                    </div>
                                    <div>
                                        <label for='subject'>Subject</label>
                                        <input type='text' id='subject' name='subject' value='$usersView->subject' readonly/>
                                    </div>
                                    <div>
                                        <label for='body'>Remark</label>
                                        <textarea id='body' name='body' maxlength='255' readonly>$usersView->body</textarea>
                                    </div>
                                    <div>
                                        <label for='date'>Date</label>
                                        <input type='text' name='date' id='date' value='$usersView->date_created' readonly/>
                                    </div>
                                </form>
                            </div>
                        </div>
                      </div>
                      <script>
                        // addguard modal exit btn
                        let exitModalViewRemarked = document.querySelector('#exit-modal-viewremarked')
                        exitModalViewRemarked.addEventListener('click', e => {
                            let viewremarkedModal = document.querySelector('.modal-viewremarked');
                            viewremarkedModal.style.display = 'none';
                        });
                      </script>";
            }
        }
    }

    public function addModalRemarks($adminFullname, $adminId) {
    
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

                echo "<div class='modal-setremarks'>
                        <div class='modal-holder'>
                            <div class='setremarks-header'>
                                <h1>Set Remarks</h1>
                                <span id='exit-modal-setremarks' class='material-icons'>close</span>
                            </div>
                            <div class='setremarks-content'>
                                <form method='POST' enctype='multipart/form-data'>
                                    <div>
                                        <label for='empid'>Employee ID</label>
                                        <input type='text' id='empid' name='empid' value='$usersRem->empId' readonly/>
                                    </div>
                                    <div>
                                        <label for='fullname'>Fullname</label>
                                        <input type='text' id='fullname' name='fullname' value='$usersRem->firstname $usersRem->lastname' readonly/>
                                    </div>
                                    <div>
                                        <label for='subject'>Subject</label>
                                        <input type='text' id='subject' name='subject' placeholder='Enter a subject' required/>
                                    </div>
                                    <div>
                                        <label for='body'>Remark</label>
                                        <textarea id='body' name='body' maxlength='255' placeholder='Max of 255 characters.' required>$autoRemark</textarea>
                                    </div>
                                    <div>
                                        <label for='file'><span></span> Choose File</label>
                                        <input type='file' name='file' id='file'>
                                    </div>
                                    <div>
                                        <button type='submit' name='submit'>Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                      </div>
                      <script>
                        // addguard modal exit btn
                        let exitModalSetRemarks = document.querySelector('#exit-modal-setremarks')
                        exitModalSetRemarks.addEventListener('click', e => {
                            let setremarksModal = document.querySelector('.modal-setremarks');
                            setremarksModal.style.display = 'none';
                        });
                      </script>";
            }

            if (isset($_POST['submit'])) {
                
                // Declaring Variables
                date_default_timezone_set('Asia/Manila');
                $location = "../inbox/";

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
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>File is too big. Maximum size is 10 MB</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
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

                            $action = "Add";
                            $table_name = "Remarks";
                            $admindatetime = $this->getDateTime();
                            $adminTime = $admindatetime['time'];
                            $adminDate = $admindatetime['date'];
                                                                
                            $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                            $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                            $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                                
                            $countRowAdminLog = $stmtAdminLog->rowCount();
                            if($countRowAdminLog > 0){
                                $msg = 'Submitted Successfully';
                                echo "<script>window.location.assign('remarks.php?message=$msg')</script>";
                            } else {
                            echo "<div class='error'>
                                        <div class='icon-container'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                        <p>Submit Failed</p>
                                        <div class='closeContainer'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                    </div>
                                    <script>
                                        let msgErr = document.querySelector('.error');
                                        setTimeout(e => msgErr.remove(), 5000);
                                    </script>";
                            }
                        }
                    }
                } else {
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Cannot upload this type of file.</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                }     
            }
        }
    }

    public function mostViolation()
    {
        $sql = "SELECT 
                        i.empId as iEmpId,
                        COUNT(i.empId) AS totalEmpId,
                        e.firstname as firstname,
                        e.lastname as lastname,
                        e.position as position
                FROM inbox i
                INNER JOIN employee e
                ON i.empId = e.empId
                GROUP BY iEmpId
                ORDER BY totalEmpId DESC
                LIMIT 1";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            $fullname = $user->lastname.", ".$user->firstname;
            echo "<div class='most-violation-header'>
                    <h1>Most Violation</h1>
                  </div>
                  <div class='most-violation-content'>
                    <div>
                        <h2>$fullname</h2>
                        <p>$user->position</p>
                    </div>
                    <div>
                        <p>Number of Violations</p>
                        <h1>$user->totalEmpId Penalties</h1>
                    </div>
                    <button><a href='remarks.php?mvId=$user->iEmpId'>See All</a></button>
                  </div>";
        } else {
            echo "<div class='most-violation-header'>
                    <h1>Most Violation</h1>
                  </div>
                  <div class='most-violation-content'>
                    <div>
                        <h2>No Violators Found</h2>
                        <p></p>
                    </div>
                    <div>
                        <p>Number of Violations</p>
                        <h1>0</h1>
                    </div>
                    <button style='background-color: #434343 !important;'><a href='#'>No Data</a></button>
                  </div>";
        }
    }

    // list of most violations
    public function viewMostViolation($id)
    {
        $sql = "SELECT 
                        empId,
                        subject,
                        body,
                        date_created
                FROM inbox
                WHERE empId = '$id'
                ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->empId</td>
                        <td>$row->subject</td>
                        <td>$row->body</td>
                        <td>$row->date_created</td>
                      </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }

    public function countNewGuardsWelcome($name)
    {
        $user = $name;

        $sql = "SELECT * FROM schedule 
                WHERE date_assigned BETWEEN CURRENT_DATE - 15 
                                        AND CURRENT_DATE";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){

            $guard = "";
            if($countRow > 1){
                $guard = "employees";
            } else {
                $guard = "employee";
            }

            echo "<h2>Welcome $name!</h2>
                  <p>You've assign new tasks to each of the $countRow $guard. To review all the tasks click the button below. </p>
                  <button><a href='./dashboard.php?reviewAll=true'>Review All</a></button>
                  ";
        } else {
            echo "<h2>Welcome $name!</h2>
                  <p>You've assign no task to each of the total employees.</p>
                  <button style='background-color:gray' disabled><a>Review All</a></button>
                  ";
        }
    }


    public function reviewAll()
    {
        $sql = "SELECT s.empId,
                       s.company as company,
                       s.date_assigned,
                       e.firstname,
                       e.lastname,
                       e.position as position,
                       c.comp_location
                       
                FROM schedule s
                INNER JOIN employee e
                ON s.empId = e.empId

                INNER JOIN company c
                ON s.company = c.company_name
                WHERE s.date_assigned BETWEEN CURRENT_DATE - 15
                                      AND CURRENT_DATE
                ORDER BY s.date_assigned DESC";
        $stmt = $this->con()->query($sql);
        while($row = $stmt->fetch()){
            $fullname = $row->firstname ." ".$row->lastname;
            echo "<tr>
                    <td>$fullname</td>
                    <td>$row->company</td>
                    <td>$row->comp_location</td>
                    <td>$row->position</td>
                    <td>$row->date_assigned</td>
                  </tr>";
        }
    }


    public function dashboardStatistics()
    {
        $sql = "SELECT * FROM employee WHERE position != ''";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            $totalEmployees = $countRow;
            
            $sqlCountEmp = "SELECT position, 
                                   COUNT(position) AS positions,
                                   ROUND(100. * count(*) / sum(count(*)) over (), 0) AS percentage,
                                   availability
                            FROM employee
                            WHERE position != 'NULL' AND availability = 'Unavailable'
                            GROUP BY position
                            ORDER BY percentage DESC
                            LIMIT 4;
                            ";
            $stmtCountEmp = $this->con()->query($sqlCountEmp);
            
            while($usersCountEmp = $stmtCountEmp->fetch()){
                $posName = $usersCountEmp->position;
                $posTotal = $usersCountEmp->positions;
                $posPercentage = $usersCountEmp->percentage . "%";
                echo "<div class='cards'>
                        <div>
                            <h1>$posTotal</h1>
                        </div>
                        <div>
                            <p>$posName</p>
                            <p>Position</p>
                        </div>
                        <div>
                            <div class='outstanding'>
                                <h3>$posPercentage</h3>
                            </div>
                        </div>
                      </div>";
            }
        } else {
            echo "<div class='cards'>
                    <div>
                      <h1>0</h1>
                    </div>
                    <div>
                      <p>No Data Found</p>
                    </div>
                  </div>";
        }
    }

    public function viewAllStatistics() 
    {
        $sql = "SELECT * FROM employee";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([]);
        $users = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            $totalEmployees = $countRow;
            
            $sqlCountEmp = "SELECT position, 
                                   COUNT(position) AS positions,
                                   ROUND(100. * count(*) / sum(count(*)) over (), 0) AS percentage,
                                   availability
                            FROM employee
                            WHERE position != 'NULL' AND availability = 'Unavailable'
                            GROUP BY position
                            ORDER BY percentage DESC;
                            ";
            $stmtCountEmp = $this->con()->query($sqlCountEmp);
            
            while($usersCountEmp = $stmtCountEmp->fetch()){
                $posName = $usersCountEmp->position;
                $posTotal = $usersCountEmp->positions;
                $posPercentage = $usersCountEmp->percentage . "%";

                echo "<tr>
                          <td>$posTotal</td>
                          <td>$posName</td>
                          <td>$posPercentage</td>
                      </tr>";
            }

        } else {
            echo "<tr>
                    <td>0</td>
                    <td>No data found</td>
                    <td>0%</td>
                  </tr>";
        }
    }

    public function dashboardRecentActivity()
    {
        $sql = "SELECT * FROM company 
                WHERE isDeleted = 0
                ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        // set timezone and get date and time
        $datetime = $this->getDateTime();
        $date = $datetime['date'];

        // if no data found
        if($countRow == 0){
            echo "<tr>
                    <td style='width: 200px;'>No data found</td>
                    <td style='width: 200px;'></td>
                    <td></td>
                    <td></td>
                  </tr>";
        } else {
            while($users = $stmt->fetch()){
                $findColor = $users->date;
                $status = '';
                
                if(strtotime($users->date) <= strtotime($date) && 
                   strtotime($users->date) >= strtotime($date.'-15 day')){
                    $status = 'recent';
    
                    $hiredGuards = $users->hired_guards == NULL || '' ? 0 : $users->hired_guards;

                    echo "<tr>
                            <td>$users->company_name</td>
                            <td>$users->comp_location</td>
                            <td>$hiredGuards</td>
                            <td>
                                <div class='circle-with-text'>
                                    <div class='circle $status'></div>
                                    <span>$users->date</span>
                                </div>
                            </td>
                          </tr>";
                } 
            }
        }
    }

    public function dashboardRecentActivityAll()
    {
        $sql = "SELECT * FROM company 
                WHERE isDeleted = 0
                ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        // set timezone and get date and time
        $datetime = $this->getDateTime();
        $date = $datetime['date'];

        // if no company found
        if($countRow == 0){
            echo "<tr>
                    <td>No user found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }

        while($users = $stmt->fetch()){
            $hiredGuards = $users->hired_guards == NULL ? 0 : $users->hired_guards;

            echo "<tr>
                    <td>$users->company_name</td>
                    <td>$users->comp_location</td>
                    <td>$hiredGuards</td>
                    <td>$users->date</td>
                  </tr>";
        }
    }

    public function dashboardNewGuards()
    {
        $sql = "SELECT * FROM employee WHERE availability = 'Available' AND isDeleted = 0 ORDER BY id DESC LIMIT 4";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<div class='guard-row'>
                    <div class='guard-row-text'>
                        <p>No Data Found</p>
                        <span> <b></b></span>
                    </div>
                    <div class='guard-row-button'>
                        <div class=''>
                            <a><span class='material-icons'></span></a>
                        </div>
                        <div class=''>
                            <a><span class='material-icons'></span></a>
                        </div>
                    </div>
                  </div>";
        } else {
            while($users = $stmt->fetch()){
                $fullname = $users->lastname.", ".$users->firstname;
    
                echo "<div class='guard-row'>
                            <div class='guard-row-text'>
                                <p>$fullname</p>
                                <span>Date added: <b>$users->date</b></span>
                            </div>
                            <div class='guard-row-button'>
                                <div class='btn-edit'>
                                    <a href='./dashboard.php?guardId=$users->id&editGuard=true&email=$users->email' class='btn-edit-icon'>
                                        <span class='material-icons'>edit</span>
                                    </a>
                                </div>
                                <div class='btn-delete'>
                                    <a href='./dashboard.php?guardId=$users->id&deleteGuard=true' class='btn-delete-icon'>
                                        <span class='material-icons'>delete</span>
                                    </a>
                                </div>
                                
                            </div>
                      </div>";
            }
        }
    }

    // modal only
    public function dashboardEditGuardsModal($id)
    {
        $sql = "SELECT * FROM employee WHERE id = ? AND isDeleted = 0";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            echo "<form method='post'>
                    <div>
                        <label for='firstname'>Firstname</label>
                        <input type='text' name='firstname' id='firstname' value='$user->firstname' onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' autocomplete='off' required/>
                    </div>
                    <div>
                        <label for='lastname'>Lastname</label>
                        <input type='text' name='lastname' id='lastname' value='$user->lastname' onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' autocomplete='off' required/>
                    </div>
                    <div>
                        <label for='address'>Address</label>
                        <input type='text' name='address' id='address' value='$user->address' placeholder='Please include the city' autocomplete='off' required/>
                    </div>
                    <div>
                        <label for='email'>Email</label>
                        <input type='email' name='email' id='email' value='$user->email' autocomplete='off' required/>
                    </div>
                    <div>
                        <label for='cpnumber'>Contact Number</label>
                        <input type='text' name='cpnumber' id='cpnumber' value='$user->cpnumber' maxlength='11' placeholder='09' onkeypress='validate(event)' autocomplete='off' required/>
                    </div>
                    <div>
                        <button type='submit' name='editGuard' class='btn_primary'>Edit Guard</button>
                    </div>
                  </form>";
        }
    }

    // new guard edit in modal info
    public function dashboardEditGuards($id, $existingEmail, $adminFullname, $adminId)
    {
        if(isset($_POST['editGuard'])){
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $address = $_POST['address'];
            $email = $_POST['email'];
            $cpnumber = $_POST['cpnumber'];

            if(empty($firstname) &&
               empty($lastname) &&
               empty($address) &&
               empty($email) &&
               empty($cpnumber)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input fields are required!</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {

                if($email == $existingEmail){

                        $sql = "UPDATE employee
                                SET firstname = ?,
                                    lastname = ?,
                                    address = ?,
                                    email = ?,
                                    cpnumber = ?
                                WHERE id = ?";
                        $stmt = $this->con()->prepare($sql);
                        $stmt->execute([$firstname, $lastname, $address, $email, $cpnumber, $id]);
                        $countRow = $stmt->rowCount();

                        if($countRow > 0){

                            $action = "Edit";
                            $table_name = "Available Employee";
                            $datetime = $this->getDateTime();
                            $adminTime = $datetime['time'];
                            $adminDate = $datetime['date'];

                            $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                            $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                            $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);

                            $countRowAdminLog = $stmtAdminLog->rowCount();
                            if($countRowAdminLog > 0){
                                $msg = 'Update Successfully';
                                echo "<script>window.location.assign('./dashboard.php?message=$msg');</script>";
                            } else {
                                echo "<div class='error'>
                                        <div class='icon-container'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                        <p>Update Failed</p>
                                        <div class='closeContainer'>
                                            <span class='material-icons'>close</span>
                                        </div>
                                    </div>
                                    <script>
                                        let msgErr = document.querySelector('.error');
                                        setTimeout(e => msgErr.remove(), 5000);
                                    </script>";
                            }
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Update Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                </div>
                                <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                </script>";
                        }

                } else {
                    
                        // update employee

                        $sqlFindEmail = "SELECT * FROM employee WHERE email = ?";
                        $stmtFindEmail = $this->con()->prepare($sqlFindEmail);
                        $stmtFindEmail->execute([$email]);
                        $userFindEmail = $stmtFindEmail->fetch();
                        $countRowFindEmail = $stmtFindEmail->rowCount();
                        if($countRowFindEmail > 0){
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Email is already exist!</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                </div>
                                <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                </script>";
                        } else {
                            $sql = "UPDATE employee
                                    SET firstname = ?,
                                        lastname = ?,
                                        address = ?,
                                        email = ?,
                                        cpnumber = ?
                                    WHERE id = ?";
                            $stmt = $this->con()->prepare($sql);
                            $stmt->execute([$firstname, $lastname, $address, $email, $cpnumber, $id]);
                            $countRow = $stmt->rowCount();

                            if($countRow > 0){

                                $sqlInform = "SELECT e.*, sd.secret_key as secret_key  
                                            FROM employee e
                                            INNER JOIN secret_diarye sd
                                            ON e.email = sd.e_id
                                            WHERE e.id = ?";
                                $stmtInform = $this->con()->prepare($sqlInform);
                                $stmtInform->execute([$id]);
                                $userInform = $stmtInform->fetch();
                                $countRowInform = $stmtInform->rowCount();
                                
                                if($countRowInform > 0){
                                    // send credentials in new email
                                    $this->sendEmail($userInform->email, $userInform->secret_key);

                                    $action = "Edit";
                                    $table_name = "Available Employee";
                                    $datetime = $this->getDateTime();
                                    $adminTime = $datetime['time'];
                                    $adminDate = $datetime['date'];

                                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);

                                    $countRowAdminLog = $stmtAdminLog->rowCount();
                                    if($countRowAdminLog > 0){
                                        $msg = 'Update Successfully';
                                        echo "<script>window.location.assign('./dashboard.php?message=$msg');</script>";
                                    } else {
                                        echo "<div class='error'>
                                                <div class='icon-container'>
                                                    <span class='material-icons'>close</span>
                                                </div>
                                                <p>Update Failed</p>
                                                <div class='closeContainer'>
                                                    <span class='material-icons'>close</span>
                                                </div>
                                            </div>
                                            <script>
                                                let msgErr = document.querySelector('.error');
                                                setTimeout(e => msgErr.remove(), 5000);
                                            </script>";
                                    }
                                }
                            }
                        }

                }
            }
        }
    }


    public function dashboardDeleteGuardsModal($id)
    {
        $sql = "SELECT * FROM employee WHERE id = ? AND isDeleted = 0";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            echo "<div class='modal-holder'>
                    <div class='deleteguard-header'>
                        <h1>Delete Employee</h1>
                        <span id='exit-modal-deleteguard' class='material-icons'>close</span>
                    </div>
                    <div class='deleteguard-content'>
                        <h1>Are you sure you want to delete this employee?</h1>
                        <form method='post'>
                            <input type='hidden' name='empDeleteId' value='$user->id' required/>
                            <button type='submit' name='deleteEmployee'>Delete</button>
                        </form>
                    </div>
                  </div>";
        }
    }

    public function dashboardDeleteGuards($adminFullname, $adminId)
    {
        if(isset($_POST['deleteEmployee'])){
            $empDeleteId = $_POST['empDeleteId'];
            if(empty($empDeleteId)){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>Id are required to delete</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {
                $sql = "SELECT * FROM employee WHERE id = ?";
                $stmt = $this->con()->prepare($sql);
                $stmt->execute([$empDeleteId]);
                $user = $stmt->fetch();
                $countRow = $stmt->rowCount();

                if($countRow > 0){

                    $action = "Delete";
                    $table_name = "Available Employee";
                    $datetime = $this->getDateTime();
                    $adminTime = $datetime['time'];
                    $adminDate = $datetime['date'];

                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);

                    $sqlEmp = "UPDATE employee SET isDeleted = 1 WHERE id = ?";
                    $stmtEmp = $this->con()->prepare($sqlEmp);
                    $stmtEmp->execute([$empDeleteId]);

                    $countRowEmp = $stmtEmp->rowCount();
                    if($countRowEmp > 0){

                        $msg = 'Deleted Successfully';
                        echo "<script>window.location.assign('./dashboard.php?message=$msg');</script>";

                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Delete Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                        }
                }
            }
        }
    }


    public function dashboardLeaveRequests()
    {
        $sql = "SELECT 
                        l.*,
                        l.id as finalId,
                        e.position AS position,
                        e.firstname AS firstname,
                        e.lastname AS lastname
                FROM leave_request l
                INNER JOIN employee e
                ON l.empId = e.empId
                WHERE status = 'pending'
                ORDER BY id DESC
                LIMIT 4";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow == 0){
            echo "<div class='request-row'>
                    <div class='request-row-text'>
                        <p>No Data Found</p>
                        <span><b></b></span>
                    </div>
                    <div class='request-row-button'>
                        <div>
                            <a><span class='material-icons'></span></a>
                        </div>
                        <div>
                            <a><span class='material-icons'></span></a>
                        </div>
                    </div>
                  </div>";
        } else {
            while($row = $stmt->fetch()){
                $fullname = $row->firstname ." ".$row->lastname;
                echo "<div class='request-row'>
                            <div class='request-row-text'>
                                <p>$fullname</p>
                                <span>Position to <b>$row->position</b></span>
                            </div>
                            <div class='request-row-button'>
                                <div class='btn-edit'>
                                    <a href='./dashboard.php?id=$row->finalId&act=approve' class='btn-edit-icon'>
                                        <span class='material-icons'>done</span>
                                    </a>
                                </div>
                                <div class='btn-delete'>
                                    <a href='./dashboard.php?id=$row->finalId&act=reject' class='btn-delete-icon'>
                                        <span class='material-icons'>close</span>
                                    </a>
                                </div>
                            </div>
                        </div>";
            }
        }
    }

    public function adminProfile($id)
    {
        $sql = "SELECT * FROM super_admin WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $rowCount = $stmt->rowCount();

        if($rowCount > 0){
            $fullname = $user->firstname." ".$user->lastname;

            $facebook = $user->facebook;
            $google = $user->google;
            $twitter = $user->twitter;
            $instagram = $user->instagram;

            if($facebook == 'https://'){
                $facebook = '';
            }

            if($google == 'https://'){
                $google = '';
            }

            if($twitter == 'https://'){
                $twitter = '';
            }

            if($instagram == 'https://'){
                $instagram = '';
            }

            echo "<script>
                      let image = document.querySelector('#image');
                      let firstname = document.querySelector('#firstname');
                      let lastname = document.querySelector('#lastname');
                      let address = document.querySelector('#address');
                      let cpnumber = document.querySelector('#cpnumber');
                      let email = document.querySelector('#email');

                      firstname.value = '$user->firstname';
                      lastname.value = '$user->lastname';
                      address.value = '$user->address';
                      cpnumber.value = '$user->cpnumber';
                      email.value = '$user->username';


                      // before modal
                      let userNameContainer = document.querySelector('.user-name');
                      let userH1 = userNameContainer.querySelector('h1');
                      let userP = userNameContainer.querySelector('p');
                      userH1.innerText = '$fullname';
                      userP.innerText = '$user->access';
                      
                      let aboutMeContainer = document.querySelector('.about-me-container');
                      let aboutP = aboutMeContainer.querySelector('p');
                      aboutP.innerText = '$user->address';

                      let mobEmailContainer = document.querySelector('.mob-email-container');
                      let mobEmailData = mobEmailContainer.querySelectorAll('p');

                      mobEmailData[0].innerText = '$user->cpnumber';
                      mobEmailData[1].innerText = '$user->username';

                      // create social media icons container
                      let socialMediaContainer = document.querySelector('.socialmedia-container');
                      let facebookText = '$facebook';
                      let googleText = '$google';
                      let twitterText = '$twitter';
                      let instagramText = '$instagram';


                      if(facebookText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'facebook-icon');
                        newA.setAttribute('href', facebookText);
                        socialMediaContainer.appendChild(newA);
                      }

                      if(googleText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'google-icon');
                        newA.setAttribute('title', googleText);
                        socialMediaContainer.appendChild(newA);
                      }

                      if(twitterText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'twitter-icon');
                        newA.setAttribute('href', twitterText);
                        socialMediaContainer.appendChild(newA);
                      }

                      if(instagramText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'instagram-icon');
                        newA.setAttribute('href', instagramText);
                        socialMediaContainer.appendChild(newA);
                      }
                      
                      let facebookInput = document.querySelector('#facebook');
                      let googleInput = document.querySelector('#google');
                      let twitterInput = document.querySelector('#twitter');
                      let instagramInput = document.querySelector('#instagram');

                      facebookInput.value = facebookText;
                      googleInput.value = googleText;
                      twitterInput.value = twitterText;
                      instagramInput.value = instagramText;
                  </script>";
        }
    }

    public function adminFeedback($id)
    {
        $sql = "SELECT * FROM super_admin WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $rowCount = $stmt->rowCount();

        if($rowCount > 0){
            $fullname = $user->firstname." ".$user->lastname;

            $facebook = $user->facebook;
            $google = $user->google;
            $twitter = $user->twitter;
            $instagram = $user->instagram;

            if($facebook == 'https://'){
                $facebook = '';
            }

            if($google == 'https://'){
                $google = '';
            }

            if($twitter == 'https://'){
                $twitter = '';
            }

            if($instagram == 'https://'){
                $instagram = '';
            }

            echo "<script>

                      // before modal
                      let userNameContainer = document.querySelector('.user-name');
                      let userH1 = userNameContainer.querySelector('h1');
                      let userP = userNameContainer.querySelector('p');
                      userH1.innerText = '$fullname';
                      userP.innerText = '$user->access';
                      
                      let aboutMeContainer = document.querySelector('.about-me-container');
                      let aboutP = aboutMeContainer.querySelector('p');
                      aboutP.innerText = '$user->address';

                      let mobEmailContainer = document.querySelector('.mob-email-container');
                      let mobEmailData = mobEmailContainer.querySelectorAll('p');

                      mobEmailData[0].innerText = '$user->cpnumber';
                      mobEmailData[1].innerText = '$user->username';

                      // create social media icons container
                      let socialMediaContainer = document.querySelector('.socialmedia-container');
                      let facebookText = '$facebook';
                      let googleText = '$google';
                      let twitterText = '$twitter';
                      let instagramText = '$instagram';


                      if(facebookText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'facebook-icon');
                        newA.setAttribute('href', facebookText);
                        socialMediaContainer.appendChild(newA);
                      }

                      if(googleText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'google-icon');
                        newA.setAttribute('title', googleText);
                        socialMediaContainer.appendChild(newA);
                      }

                      if(twitterText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'twitter-icon');
                        newA.setAttribute('href', twitterText);
                        socialMediaContainer.appendChild(newA);
                      }

                      if(instagramText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'instagram-icon');
                        newA.setAttribute('href', instagramText);
                        socialMediaContainer.appendChild(newA);
                      }
                      
                      let facebookInput = document.querySelector('#facebook');
                      let googleInput = document.querySelector('#google');
                      let twitterInput = document.querySelector('#twitter');
                      let instagramInput = document.querySelector('#instagram');

                      facebookInput.value = facebookText;
                      googleInput.value = googleText;
                      twitterInput.value = twitterText;
                      instagramInput.value = instagramText;
                  </script>";
        }
    }

    public function adminChange($id)
    {
        $sql = "SELECT * FROM super_admin WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $rowCount = $stmt->rowCount();

        if($rowCount > 0){
            $fullname = $user->firstname." ".$user->lastname;

            $facebook = $user->facebook;
            $google = $user->google;
            $twitter = $user->twitter;
            $instagram = $user->instagram;

            if($facebook == 'https://'){
                $facebook = '';
            }

            if($google == 'https://'){
                $google = '';
            }

            if($twitter == 'https://'){
                $twitter = '';
            }

            if($instagram == 'https://'){
                $instagram = '';
            }

            echo "<script>
                      let email = document.querySelector('#username');
                      email.value = '$user->username';

                      // before modal
                      let userNameContainer = document.querySelector('.user-name');
                      let userH1 = userNameContainer.querySelector('h1');
                      let userP = userNameContainer.querySelector('p');
                      userH1.innerText = '$fullname';
                      userP.innerText = '$user->access';
                      
                      let aboutMeContainer = document.querySelector('.about-me-container');
                      let aboutP = aboutMeContainer.querySelector('p');
                      aboutP.innerText = '$user->address';

                      let mobEmailContainer = document.querySelector('.mob-email-container');
                      let mobEmailData = mobEmailContainer.querySelectorAll('p');

                      mobEmailData[0].innerText = '$user->cpnumber';
                      mobEmailData[1].innerText = '$user->username';

                      // create social media icons container
                      let socialMediaContainer = document.querySelector('.socialmedia-container');
                      let facebookText = '$facebook';
                      let googleText = '$google';
                      let twitterText = '$twitter';
                      let instagramText = '$instagram';


                      if(facebookText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'facebook-icon');
                        newA.setAttribute('href', facebookText);
                        socialMediaContainer.appendChild(newA);
                      }

                      if(googleText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'google-icon');
                        newA.setAttribute('title', googleText);
                        socialMediaContainer.appendChild(newA);
                      }

                      if(twitterText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'twitter-icon');
                        newA.setAttribute('href', twitterText);
                        socialMediaContainer.appendChild(newA);
                      }

                      if(instagramText){
                        let newA = document.createElement('a');
                        newA.setAttribute('class', 'instagram-icon');
                        newA.setAttribute('href', instagramText);
                        socialMediaContainer.appendChild(newA);
                      }
                      
                      let facebookInput = document.querySelector('#facebook');
                      let googleInput = document.querySelector('#google');
                      let twitterInput = document.querySelector('#twitter');
                      let instagramInput = document.querySelector('#instagram');

                      facebookInput.value = facebookText;
                      googleInput.value = googleText;
                      twitterInput.value = twitterText;
                      instagramInput.value = instagramText;
                  </script>";
        }
    }

    public function viewAdminImage($id)
    {
        $sql = "SELECT image FROM admin_profile WHERE sa_id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            $myImage = base64_encode($user->image);
            echo "<img src='data:image/jpg;charset=utf8;base64,$myImage'/>";
        } else {
            echo "<img />";
        }
    }

    // for modal with id='output'
    public function viewAdminImage2($id)
    {
        $sql = "SELECT `image` FROM admin_profile WHERE sa_id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            $myImage = base64_encode($user->image);
            echo "<img src='data:image/jpg;charset=utf8;base64,$myImage' id='output'/>";
        } else {
            echo "<img id='output'/>";
        }
    }

    public function editAdminProfile($id, $adminFullname, $adminId)
    {
        if(isset($_POST["saveChanges"])){ 

            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $address = $_POST['address'];
            $cpnumber = $_POST['cpnumber'];
            $email = $_POST['email'];

            $facebook = $_POST['facebook'] != '' ? $_POST['facebook'] : 'https://';
            $google = $_POST['google'] != '' ? $_POST['google'] : 'https://';
            $twitter = $_POST['twitter'] != '' ? $_POST['twitter'] : 'https://';
            $instagram = $_POST['instagram'] != '' ? $_POST['instagram'] : 'https://';

            if(empty($firstname) ||
               empty($lastname) ||
               empty($address) ||
               empty($cpnumber) ||
               empty($email)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>Input fields are required to update!</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {

                // username is already exist in super_admin
                $sqlExist = "SELECT id, username
                             FROM super_admin 
                             WHERE username = ? AND id != ?";
                $stmtExist = $this->con()->prepare($sqlExist);
                $stmtExist->execute([$email, $id]);
                $userExist = $stmtExist->fetch();
                $countRowExist = $stmtExist->rowCount();
                if($countRowExist > 0){
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Email already exist!</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                } else {

                    // if nagiba email send credentials
                    $sqlSend = "SELECT sa.id, sa.username, sd.sa_id, sd.secret_key as secret_key 
                                FROM super_admin sa
                                INNER JOIN secret_diary sd
                                ON sa.username = sd.sa_id
                                WHERE sa.id = ? AND sa.username = ?";
                    $stmtSend = $this->con()->prepare($sqlSend);
                    $stmtSend->execute([$id, $email]);
                    $userSend = $stmtSend->fetch();
                    $countRowSend = $stmtSend->rowCount();

                    // fetch old data to get password
                    $sqlCre = "SELECT sa.username, sd.sa_id, sd.secret_key as secret_key 
                               FROM super_admin sa
                               INNER JOIN secret_diary sd
                               ON sa.username = sd.sa_id
                               WHERE sa.id = ?";
                    $stmtCre = $this->con()->prepare($sqlCre);
                    $stmtCre->execute([$id]);
                    $userCre = $stmtCre->fetch();

                    if($countRowSend <= 0){
                        $this->sendEmail($email, $userCre->secret_key);
                    }

                    // check image file type
                    $status = 'error'; 
                    if(!empty($_FILES["image"]["name"])) {
                        // Get file info 
                        $fileName = basename($_FILES["image"]["name"]); // sample.jpg
                        $fileType = pathinfo($fileName, PATHINFO_EXTENSION); // .jpg
                        
                        // Allow certain file formats 
                        $allowTypes = array('jpg','png','jpeg','gif'); 

                        // kapag jpg yung file or what
                        if(in_array($fileType, $allowTypes)){ 
                            $image = $_FILES['image']['tmp_name']; 
                            $imgContent = addslashes(file_get_contents($image)); 
                        
                            // Delete the existing image because it will fail if we update it
                            $sqlDel = "DELETE FROM admin_profile WHERE sa_id = ?";
                            $stmtDel = $this->con()->prepare($sqlDel);
                            $stmtDel->execute([$id]);

                            // Insert image content into database 
                            $sql = "INSERT INTO admin_profile (image, sa_id, created) 
                                    VALUES ('$imgContent', $id, NOW())";
                            $stmt = $this->con()->query($sql);

                        } else{  
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Sorry, only JPG, JPEG, PNG, & GIF files are allowed to upload.</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>"; 
                        } 
                    }


                    // update admin profile
                    $sqlAdmin = "UPDATE super_admin
                                SET firstname = ?,
                                    lastname = ?,
                                    address = ?,
                                    cpnumber = ?,
                                    username = ?,
                                    facebook = ?,
                                    google = ?,
                                    twitter = ?,
                                    instagram = ?
                                WHERE id = ?";
                    $stmtAdmin = $this->con()->prepare($sqlAdmin);
                    $stmtAdmin->execute([$firstname, $lastname, $address, $cpnumber, $email, $facebook, $google, $twitter, $instagram, $id]);
                    $countRowAdmin = $stmtAdmin->rowCount();
                    if($countRowAdmin > 0){

                        $action = 'Edit Profile';
                        $adminFullname = $firstname." ".$lastname;
                        $datetime = $this->getDateTime();
                        $adminTime = $datetime['time'];
                        $adminDate = $datetime['date'];

                        $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, time, date) VALUES(?, ?, ?, ?, ?)";
                        $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                        $stmtAdminLog->execute([$id, $adminFullname, $action, $adminTime, $adminDate]);
                        $countRowAdminLog = $stmtAdminLog->rowCount();

                        if($countRowAdminLog > 0){
                            $msg = 'Updated Successfully';
                            echo "<script>window.location.assign('profile.php?message=$msg');</script>";
                        } else {
                            echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Update Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                  </div>
                                  <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                  </script>";
                        }
                    }
                }
            }
        } 
    }


    public function adminChangePassword($id, $adminFullname, $adminId)
    {
        if(isset($_POST['saveChanges'])){
            $email = $_POST['email'];
            $current_password = $_POST['current_password'];
            $confirm_password = $_POST['confirm_password'];

            $checkPass = $this->generatedPassword($current_password);
            $encryptedPass = $this->generatedPassword($confirm_password);

            if(empty($email) ||
               empty($current_password) ||
               empty($confirm_password)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input fields are required!</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {

                $sqlFindUser = "SELECT * FROM super_admin 
                                WHERE username = ?
                                AND password = ?";
                $stmtFindUser = $this->con()->prepare($sqlFindUser);
                $stmtFindUser->execute([$email, $checkPass[0]]);
                $userFindUser = $stmtFindUser->fetch();
                $countRowFindUser = $stmtFindUser->rowCount();

                if($countRowFindUser > 0){
                    $currEmail = $userFindUser->username;

                    $sql = "UPDATE super_admin
                            SET password = ?
                            WHERE id = ?";
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute([$encryptedPass[0], $id]);

                    $sqlOrigPass = "UPDATE secret_diary
                                    SET secret_key = ?
                                    WHERE sa_id = ?";
                    $stmtOrigPass = $this->con()->prepare($sqlOrigPass);
                    $stmtOrigPass->execute([$confirm_password, $currEmail]);

                    $action = 'Change Password';
                    $adminFullname = $userFindUser->firstname." ".$userFindUser->lastname;
                    $datetime = $this->getDateTime();
                    $adminTime = $datetime['time'];
                    $adminDate = $datetime['date'];

                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, time, date) VALUES(?, ?, ?, ?, ?)";
                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                    $stmtAdminLog->execute([$userFindUser->id, $adminFullname, $action, $adminTime, $adminDate]);
                    $countRowAdminLog = $stmtAdminLog->rowCount();
                    if($countRowAdminLog > 0){
                        $msg = 'Updated Successfully';
                        echo "<script>window.location.assign('passInfo.php?message=$msg');</script>";
                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Change Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                    }


                    
                } else {
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Password are not match!</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                }  
            }
        }
    }

    // feedback
    public function sendFeedback($fullname)
    {
        if(isset($_POST['sendFeedbackbtn'])){
            $position = "Administrator";
            $category = $_POST['category'];
            $comment = $_POST['comment'];
            $datetime = $this->getDateTime();
            $date = $datetime['date'];

            if(empty($category) ||
               empty($comment) ||
               empty($fullname)
            ){
                echo "Input fields are required!";
            } else {
                
                $sql = "INSERT INTO feedback(fullname, position, category, comment, date_created)
                        VALUES(?, ?, ?, ?, ?)";
                $stmt = $this->con()->prepare($sql);
                $stmt->execute([$fullname, $position, $category, $comment, $date]);
                $countRow = $stmt->rowCount();

                if($countRow > 0){
                    $msg = "Sent Successfully";
                    echo "<script>window.location.assign('./feedback.php?message=$msg');</script>";
                } else {
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Send Failed</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>";
                }
            }

        }
    }



    // for new company
    // for newly added company
    public function newlyaddedcompany()
    {
        $sql = "SELECT * FROM company WHERE isDeleted = 0 ORDER BY date DESC LIMIT 4";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){

                $status = "";
                $datetime = $this->getDateTime();
                $date = $datetime['date'];
    
                if(strtotime($row->date) <= strtotime($date) && 
                   strtotime($row->date) >= strtotime($date.'-15 day')){
                    $status = 'recent';
                } elseif(strtotime($row->date) >= strtotime($date.'-30 day') && 
                         strtotime($row->date) <= strtotime($date.'-15 day')){
                    $status = 'late';
                } else {
                    $status = 'old';
                }
    
                echo "<div class='cards'>
                        <div class='circle $status'></div>
                        <h3>$row->company_name</h3>
                        <p>$row->date</p>
                      </div>";
            }
        } else {
            echo "<div class='cards'>
                    <div class='circle' style='background: #d2d2d2;'></div>
                    <h3>No <br/>Data Found</h3>
                    <p></p>
                  </div>";
        }   
    }

    // list of company 
    public function companylist()
    {
        $sql = "SELECT * FROM company WHERE isDeleted = 0 ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        while($row = $stmt->fetch()){

            $sqlFind = "SELECT company FROM schedule WHERE company = ?";
            $stmtFind = $this->con()->prepare($sqlFind);
            $stmtFind->execute([$row->company_name]);
            $userFind = $stmtFind->fetch();
            $countRowFind = $stmtFind->rowCount();

            $delete = "";

            if($countRowFind > 0){
                $delete .= "<a></a>";
            } else {
                $delete .= "<a href='./company.php?id=$row->id&action=delete'>
                                <span class='material-icons'>delete</span>
                            </a>";
            }

            $hiredGuards = $row->hired_guards == NULL || '' ? 0 : $row->hired_guards;

            echo "<tr>
                    <td>$row->company_name</td>
                    <td>$hiredGuards</td>
                    <td>$row->email</td>
                    <td>$row->date</td>
                    <td>
                        <a href='./company.php?id=$row->id&action=view'>
                            <span class='material-icons'>visibility</span>
                        </a>
                        <a href='./company.php?id=$row->id&action=edit'>
                            <span class='material-icons'>edit</span>
                        </a>
                        $delete
                    </td>
                  </tr>";
        }
    }


    public function companylistSearch($search)
    {
        $sql = "SELECT * FROM company 
                WHERE isDeleted = 0 AND company_name LIKE '%$search%' OR
                      isDeleted = 0 AND hired_guards LIKE '%$search%' OR
                      isDeleted = 0 AND email LIKE '%$search%' OR
                      isDeleted = 0 AND date LIKE '%$search%'
                ORDER BY id DESC";
        $stmt = $this->con()->query($sql);
        while($row = $stmt->fetch()){

            $sqlFind = "SELECT company FROM schedule WHERE company = ?";
            $stmtFind = $this->con()->prepare($sqlFind);
            $stmtFind->execute([$row->company_name]);
            $userFind = $stmtFind->fetch();
            $countRowFind = $stmtFind->rowCount();

            $delete = "";

            if($countRowFind > 0){
                $delete .= "<a></a>";
            } else {
                $delete .= "<a href='./company.php?id=$row->id&action=delete'>
                                <span class='material-icons'>delete</span>
                            </a>";
            }

            $hiredGuards = $row->hired_guards == NULL || '' ? 0 : $row->hired_guards;

            echo "<tr>
                    <td>$row->company_name</td>
                    <td>$hiredGuards</td>
                    <td>$row->email</td>
                    <td>$row->date</td>
                    <td>
                        <a href='./company.php?id=$row->id&action=view'>
                            <span class='material-icons'>visibility</span>
                        </a>
                        <a href='./company.php?id=$row->id&action=edit'>
                            <span class='material-icons'>edit</span>
                        </a>
                        $delete
                    </td>
                  </tr>";
        }
    }


    public function addcompany($adminFullname, $adminId)
    {
        if(isset($_POST['addcompany'])){
            $company_name = $_POST['company_name'];
            $cpnumber = $_POST['cpnumber'];
            $email = $_POST['email'];
            $comp_location = $_POST['comp_location'];
            $longitude = $_POST['longitude'];
            $latitude = $_POST['latitude'];
            $boundary_size = $_POST['boundary_size'];
            $shift = $_POST['shift'];
            $shift_span = $_POST['shift_span'];
            $day_start = $_POST['day_start'];

            $datetime = $this->getDateTime();
            $date = $datetime['date'];

            if(empty($company_name) ||
               empty($cpnumber) ||
               empty($email) ||
               empty($comp_location) ||
               empty($longitude) ||
               empty($latitude) ||
               empty($boundary_size) ||
               empty($shift) ||
               empty($shift_span) ||
               empty($day_start) ||
               empty($date)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input field are required to add company</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {

                // check if email already exists
                $sqlFindEmail = "SELECT email FROM company WHERE email = ?";
                $stmtFindEmail = $this->con()->prepare($sqlFindEmail);
                $stmtFindEmail->execute([$email]);
                $userFindEmail = $stmtFindEmail->fetch();
                $countRowFindEmail = $stmtFindEmail->rowCount();

                // check if company name already exists and not deleted
                $sqlFindAccNot = "SELECT * FROM company WHERE company_name = ? AND email = ? AND isDeleted = 0";
                $stmtFindAccNot = $this->con()->prepare($sqlFindAccNot);
                $stmtFindAccNot->execute([$company_name]);
                $userFindAccNot = $stmtFindAccNot->fetch();
                $countRowFindAccNot = $stmtFindAccNot->rowCount();

                // check if company name already exists and deleted
                $sqlFindAcc = "SELECT * FROM company WHERE company_name = ? AND email = ? AND isDeleted = 1";
                $stmtFindAcc = $this->con()->prepare($sqlFindAcc);
                $stmtFindAcc->execute([$company_name]);
                $userFindAcc = $stmtFindAcc->fetch();
                $countRowFindAcc = $stmtFindAcc->rowCount();

                if($countRowFindAccNot > 0){
                    $msg = "Company Already Exist!";
                    echo "<script>window.location.assign('company.php?message2=$msg');</script>";
                } elseif($countRowFindAcc > 0){
                    $msg = "Company Already Exist! Request Restoration";
                    echo "<script>window.location.assign('company.php?message2=$msg');</script>";
                } elseif($countRowFindEmail > 0){
                    $msg = "Email Already Exist!";
                    echo "<script>window.location.assign('company.php?message2=$msg');</script>";
                } else {
                    $sql = "INSERT INTO company(company_name,
                                        cpnumber, 
                                        email,
                                        comp_location,
                                        longitude,
                                        latitude,
                                        boundary_size,
                                        shifts, 
                                        shift_span,
                                        day_start,
                                        date) 
                            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute([$company_name, 
                                    $cpnumber,
                                    $email,
                                    $comp_location,
                                    $longitude,
                                    $latitude,
                                    $boundary_size,
                                    $shift,
                                    $shift_span,
                                    $day_start,
                                    $date
                                ]);
                    $countRow = $stmt->rowCount();

                    if($countRow > 0){

                        $lengthInput = $_POST['lengthInput'];
                        

                        for($i = 0; $i <= $lengthInput; $i++){
                            $pos = $_POST["position$i"];
                            $price = $_POST["price$i"];
                            $ot = $_POST["ot$i"];

                            $sqlPosition = "INSERT INTO `positions`(`company`, `position_name`, `price`, `overtime_rate`) VALUES (?, ?, ?, ?)";
                            $stmtPosition = $this->con()->prepare($sqlPosition);
                            $stmtPosition->execute([$company_name, 
                                                    $pos, 
                                                    $price, 
                                                    $ot]);
                            $countRowPosition = $stmtPosition->rowCount();
                        }

                        $action = "Add";
                        $table_name = "Company";
                        $admindatetime = $this->getDateTime();
                        $adminTime = $admindatetime['time'];
                        $adminDate = $admindatetime['date'];
                                                            
                        $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                        $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                        $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                            
                        $countRowAdminLog = $stmtAdminLog->rowCount();
                        if($countRowAdminLog > 0){
                            $msg = 'New data was added';
                            echo "<script>window.location.assign('./company.php?message=$msg');</script>";
                        } else {
                            $msg = "No Data Added";
                            echo "<script>window.location.assign('company.php?message2=$msg');</script>";
                        }
                    } else {
                        $msg = "No Data Added";
                        echo "<script>window.location.assign('company.php?message2=$msg');</script>";
                    }
                }
            }
        }

        // for add modal
        if(isset($_POST['addcompany2'])){
            $company_name = $_POST['company_name2'];
            $cpnumber = $_POST['cpnumber2'];
            $email = $_POST['email2'];
            $comp_location = $_POST['comp_location2'];
            $longitude = $_POST['longitude2'];
            $latitude = $_POST['latitude2'];
            $boundary_size = $_POST['boundary_size2'];
            $shift = $_POST['shift2'];
            $shift_span = $_POST['shift_span2'];
            $day_start = $_POST['day_start2'];

            $datetime = $this->getDateTime();
            $date = $datetime['date'];

            if(empty($company_name) ||
               empty($cpnumber) ||
               empty($email) ||
               empty($comp_location) ||
               empty($longitude) ||
               empty($latitude) ||
               empty($boundary_size) ||
               empty($shift) ||
               empty($shift_span) ||
               empty($day_start) ||
               empty($date)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input field are required to add company</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {

                // check if email exists
                $sqlFindEmail = "SELECT email FROM company WHERE email = ?";
                $stmtFindEmail = $this->con()->prepare($sqlFindEmail);
                $stmtFindEmail->execute([$email]);
                $userFindEmail = $stmtFindEmail->fetch();
                $countRowFindEmail = $stmtFindEmail->rowCount();

                // check if company name already exists
                $sqlFindAccNot = "SELECT * FROM company WHERE company_name = ? AND email = ? AND isDeleted = 0";
                $stmtFindAccNot = $this->con()->prepare($sqlFindAccNot);
                $stmtFindAccNot->execute([$company_name, $email]);
                $userFindAccNot = $stmtFindAccNot->fetch();
                $countRowFindAccNot = $stmtFindAccNot->rowCount();

                // check if company name already exists
                $sqlFindAcc = "SELECT * FROM company WHERE company_name = ? AND email = ? AND isDeleted = 1";
                $stmtFindAcc = $this->con()->prepare($sqlFindAcc);
                $stmtFindAcc->execute([$company_name, $email]);
                $userFindAcc = $stmtFindAcc->fetch();
                $countRowFindAcc = $stmtFindAcc->rowCount();

                if($countRowFindAccNot > 0){
                    $msg = "Company Already Exist!";
                    echo "<script>window.location.assign('company.php?message2=$msg');</script>";
                } elseif($countRowFindAcc > 0){
                    $msg = "Company Already Exist! Request Restoration";
                    echo "<script>window.location.assign('company.php?message2=$msg');</script>";
                } elseif($countRowFindEmail > 0){
                    $msg = "Email Already Exist!";
                    echo "<script>window.location.assign('company.php?message2=$msg');</script>";
                } else {
                    $sql = "INSERT INTO company(company_name,
                                        cpnumber, 
                                        email,
                                        comp_location,
                                        longitude,
                                        latitude,
                                        boundary_size,
                                        shifts, 
                                        shift_span,
                                        day_start,
                                        date) 
                            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute([$company_name, 
                                    $cpnumber,
                                    $email,
                                    $comp_location,
                                    $longitude,
                                    $latitude,
                                    $boundary_size,
                                    $shift,
                                    $shift_span,
                                    $day_start,
                                    $date
                                ]);
                    $countRow = $stmt->rowCount();

                    if($countRow > 0){

                        $lengthInput = $_POST['lengthInput2'];
                        
                        for($i = 0; $i <= $lengthInput; $i++){
                            $pos = $_POST["position$i"];
                            $price = $_POST["price$i"];
                            $ot = $_POST["ot$i"];

                            $sqlPosition = "INSERT INTO `positions`(`company`, `position_name`, `price`, `overtime_rate`) VALUES (?, ?, ?, ?)";
                            $stmtPosition = $this->con()->prepare($sqlPosition);
                            $stmtPosition->execute([$company_name, 
                                                    $pos, 
                                                    $price, 
                                                    $ot]);
                            $countRowPosition = $stmtPosition->rowCount();
                        }

                        $action = "Add";
                        $table_name = "Company";
                        $admindatetime = $this->getDateTime();
                        $adminTime = $admindatetime['time'];
                        $adminDate = $admindatetime['date'];
                                                            
                        $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                        $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                        $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                            
                        $countRowAdminLog = $stmtAdminLog->rowCount();
                        if($countRowAdminLog > 0){
                            $msg = 'New data was added';
                            echo "<script>window.location.assign('./company.php?message=$msg');</script>";
                        } else {
                            $msg = "No Data Added";
                            echo "<script>window.location.assign('company.php?message2=$msg');</script>";
                        }
                    } else {
                        $msg = "No Data Added";
                        echo "<script>window.location.assign('company.php?message2=$msg');</script>";
                    }
                }
            }
        }
    }


    // Modal for Viewing of Company Info
    public function viewcompanymodal($id)
    {
        $sql = "SELECT * FROM company WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){

            $company_name = $user->company_name;
            $cpnumber = $user->cpnumber;
            $email = $user->email;
            $comp_location = $user->comp_location;
            $longitude = $user->longitude;
            $latitude = $user->latitude;
            $boundary_size = $user->boundary_size;
            $shifts = $user->shifts;
            $shift_span = $user->shift_span;
            $day_start = $user->day_start;

            $output = "<div class='view-modal'>
                        <div class='modal-holder'>
                            <div class='view-modal-header'>
                                <h1>View Modal</h1>
                                <span class='material-icons' id='viewModalClose'>close</span>
                            </div>
                            <div class='view-modal-content'>
                                <form method='POST'>
                                    <div>
                                        <label for='company_name'>Company</label>
                                        <input type='text' value='$company_name' readonly/>
                                    </div>
                                    <div>
                                        <label for='cpnumber'>Contact Number</label>
                                        <input type='text' value='$cpnumber' readonly/>
                                    </div>
                                    <div>
                                        <label for='email'>Email</label>
                                        <input type='email' value='$email' readonly/>
                                    </div>
                                    <div>
                                        <div id='map-viewmodal'></div>
                                    </div>
                                    <div>
                                        <label for='comp_location'>Address</label>
                                        <input type='text' value='$comp_location' readonly/>
                                        <input type='hidden' id='longitude-viewmodal' value='$longitude' readonly/>
                                        <input type='hidden' id='latitude-viewmodal' value='$latitude' readonly/>
                                    </div>
                                    <div>
                                        <label for='boundary_size'>Boundary Size</label>
                                        <input type='text' value='$boundary_size' readonly/>
                                    </div>
                                    <div>
                                        <label for='shifts'>Shift</label>
                                        <select disabled>
                                            <option value='$shifts'>$shifts</option>
                                        </select>
                                    </div>       
                                    <div>
                                        <label for='shift_span'>Shift Span</label>
                                        <select disabled>
                                            <option value='$shift_span'>$shift_span</option>
                                        </select>
                                    </div>            
                                    <div>
                                        <label for='day_start'>Day Start</label>
                                        <select disabled>
                                            <option value='$day_start'>$day_start</option>
                                        </select>
                                    </div>         
                                    <div>
                                        <label>Positions</label>";

            $sqlPosition = "SELECT * FROM `positions` WHERE `company` = '$company_name'";
            $stmtPosition = $this->con()->query($sqlPosition);
            while($rowPosition = $stmtPosition->fetch()){
                            $output .= "<div class='positions_container'>
                                            <span>$rowPosition->position_name</span>
                                            <span>$rowPosition->price</span>
                                            <span>$rowPosition->overtime_rate</span>
                                        </div>";
            }
            $output .= "            </div>
                                </form>
                            </div>
                        </div>
                      </div>
                      <script>let currPositionView = [$longitude, $latitude];</script>
                      <script src='../scripts/comp-viewlocation.js'></script>";
            echo $output;
        } else {
            echo "<div class='error'>No user found</div>";
        }
    }

    // Modal for Editing Company Info
    public function editcompanymodal($id)
    {
        $sql = "SELECT * FROM company WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){

            $company_name = $user->company_name;
            $cpnumber = $user->cpnumber;
            $email = $user->email;
            $comp_location = $user->comp_location;
            $longitude = $user->longitude;
            $latitude = $user->latitude;
            $boundary_size = $user->boundary_size;
            $shifts = $user->shifts;
            $shiftDisabled = $shifts == 'Shift1' ? 'Shift2' : 'Shift1';

            $shift_span = $user->shift_span;
            $shift_spanDisabled = $shift_span == '8' ? '12' : '8';

            $day_start = $user->day_start;
            $day_startDisabled = $day_start == '06:00 am' ? '07:00 am' : '06:00 am';

            $sqlFind = "SELECT company FROM schedule WHERE company = ?";
            $stmtFind = $this->con()->prepare($sqlFind);
            $stmtFind->execute([$company_name]);
            $userFind = $stmtFind->fetch();
            $countRowFind = $stmtFind->rowCount();
            $isEnable = "";

            if($countRowFind > 0){
                $isEnable .= "  <div>
                                    <label for='shifts'>Shifts</label>
                                    <select name='shifts' required>
                                        <option value='$shifts' selected>$shifts</option>
                                        <option value='$shiftDisabled' disabled>$shiftDisabled</option>
                                    </select>
                                </div>
                                <div>
                                    <label for='shift_span'>Shift Span</label>
                                    <select name='shift_span' required>
                                        <option value='$shift_span' selected>$shift_span</option>
                                        <option value='$shift_spanDisabled' disabled>$shift_spanDisabled</option>
                                    </select>
                                </div>
                                <div>
                                    <label for='day_start'>Day Start</label>
                                    <select name='day_start' required>
                                        <option value='$day_start' selected>$day_start</option>
                                        <option value='$day_startDisabled' disabled>$day_startDisabled</option>
                                    </select>
                                </div>";
            } else {
                $isEnable .= "  <div>
                                    <label for='shifts'>Shifts</label>
                                    <select name='shifts' required>
                                        <option value='$shifts' selected>$shifts</option>
                                        <option value='$shiftDisabled'>$shiftDisabled</option>
                                    </select>
                                </div>
                                <div>
                                    <label for='shift_span'>Shift Span</label>
                                    <select name='shift_span' required>
                                        <option value='$shift_span' selected>$shift_span</option>
                                        <option value='$shift_spanDisabled'>$shift_spanDisabled</option>
                                    </select>
                                </div>
                                <div>
                                    <label for='day_start'>Day Start</label>
                                    <select name='day_start' required>
                                        <option value='$day_start' selected>$day_start</option>
                                        <option value='$day_startDisabled'>$day_startDisabled</option>
                                    </select>
                                </div>";
            }
            $output = "<div class='edit-modal'>
                        <div class='modal-holder'>
                            <div class='edit-modal-header'>
                                <h1>Edit Modal</h1>
                                <span class='material-icons' id='editModalClose'>close</span>
                            </div>
                            <div class='edit-modal-content'>
                                <form method='POST'>
                                    <div>
                                        <input type='hidden' name='companyId' value='$id' required/>
                                        <label for='company_name'>Company</label>
                                        <input type='text' name='company_name' value='$company_name' autocomplete='off' required/>
                                    </div>
                                    <div>
                                        <label for='cpnumber3'>Contact Number</label>
                                        <input type='text' name='cpnumber' id='cpnumber3' value='$cpnumber' placeholder='09' maxlength='11' onkeypress='validate(event)' autocomplete='off' required/>
                                    </div>
                                    <div>
                                        <label for='email'>Email</label>
                                        <input type='email' name='email' value='$email' autocomplete='off' required/>
                                    </div>
                                    <div>
                                        <div id='map-editmodal'></div>
                                    </div>
                                    <div>
                                        <label for='comp_location'>Address</label>
                                        <input type='text' name='comp_location' value='$comp_location' autocomplete='off' required/>
                                        <input type='hidden' name='longitude' id='longitude-editmodal' value='$longitude' />
                                        <input type='hidden' name='latitude' id='latitude-editmodal' value='$latitude' />
                                    </div>
                                    <div>
                                        <label for='boundary_size'>Boundary Size</label>
                                        <div id='map_b-editmodal'></div>
                                        <input type='hidden' name='boundary_size' class='map_b_size-editmodal' value='$boundary_size' autocomplete='off' required/>
                                    </div>
                                    $isEnable
                                    <div>
                                        <div class='positions_container'>
                                            <button type='button'><a href='./company.php?company=$company_name'>View Positions</a></button>
                                        </div>
                                    </div>
                                    <div>
                                        <button type='submit' name='editCompanyInfo' class='btn_primary3'>Edit Company</button> 
                                    </div>
                                </form>
                            </div>
                        </div>
                      </div>
                      <script>let currPositionEdit = [$longitude, $latitude];</script>
                      <script src='../scripts/comp-editlocation.js'></script>
                      <script>
                        // check if contact number equal to 11 EDIT MODAL
                        let btnPrimary3 = document.querySelector('.btn_primary3');
                        let mobilePrimary3 = document.querySelector('#cpnumber3');
                        let minLength3 = 11;
                        btnPrimary3.addEventListener('click', validateMobileModal);

                        function validateMobileModal(event) {
                            if (mobilePrimary3.value.length < minLength3) {
                                event.preventDefault();

                                // create error message box
                                let errorDiv = document.createElement('div');
                                errorDiv.classList.add('error');
                                let iconContainerDiv = document.createElement('div');
                                iconContainerDiv.classList.add('icon-container');
                                let spanIcon = document.createElement('span');
                                spanIcon.classList.add('material-icons');
                                spanIcon.innerText = 'done';
                                let pError = document.createElement('p');
                                pError.innerText = 'Contact Number must be ' + minLength3 + ' digits.'; 
                                let closeContainerDiv = document.createElement('div');
                                closeContainerDiv.classList.add('closeContainer');
                                let spanClose = document.createElement('span');
                                spanClose.classList.add('material-icons');
                                spanClose.innerText = 'close';

                                // destructure
                                iconContainerDiv.appendChild(spanIcon);
                                closeContainerDiv.appendChild(spanClose);

                                errorDiv.appendChild(iconContainerDiv);
                                errorDiv.appendChild(pError);
                                errorDiv.appendChild(closeContainerDiv);
                                document.body.appendChild(errorDiv);

                                // remove after 5 mins
                                setTimeout(e => errorDiv.remove(), 5000);
                            }
                        }
                      </script>";
            echo $output;
        } else {
            echo "<div class='error'>No user found</div>";
        }
    }

    // Action for Editing Company Info
    public function editcompanymodalinfo($adminFullname, $adminId)
    {  
        if(isset($_POST['editCompanyInfo']))
        {
            $companyId = $_POST['companyId'];
            $company_name = $_POST['company_name'];
            $cpnumber = $_POST['cpnumber'];
            $email = $_POST['email'];
            $comp_location = $_POST['comp_location'];
            $longitude = $_POST['longitude'];
            $latitude = $_POST['latitude'];
            $boundary_size = $_POST['boundary_size'];
            $shifts = $_POST['shifts'];
            $shift_span = $_POST['shift_span'];
            $day_start = $_POST['day_start'];

            if(empty($companyId) ||
               empty($company_name) ||
               empty($cpnumber) ||
               empty($email) ||
               empty($comp_location) ||
               empty($longitude) ||
               empty($latitude) ||
               empty($boundary_size) ||
               empty($shifts) ||
               empty($shift_span) ||
               empty($day_start)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input fields are required to edit</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {
                $sql = "UPDATE company
                        SET company_name = ?,
                            cpnumber = ?,
                            email = ?,
                            comp_location = ?,
                            longitude = ?,
                            latitude = ?,
                            boundary_size = ?,
                            shifts = ?,
                            shift_span = ?,
                            day_start = ?
                        WHERE id = ?";
                $stmt = $this->con()->prepare($sql);
                $stmt->execute([$company_name, 
                                $cpnumber,
                                $email, 
                                $comp_location,
                                $longitude,
                                $latitude,
                                $boundary_size,
                                $shifts,
                                $shift_span,
                                $day_start,
                                $companyId]);
                $countRow = $stmt->rowCount();

                if($countRow > 0){
                    $sqlInform = "SELECT s.company, e.email as e_email 
                                  FROM schedule s
                                  INNER JOIN employee e
                                  ON s.empId = e.empId

                                  WHERE s.company = '$company_name'";
                    $stmtInform = $this->con()->query($sqlInform);

                    while($rowInform = $stmtInform->fetch()){
                        $this->informEmployeeInComp($rowInform->e_email,
                                                    $company_name,
                                                    $cpnumber,
                                                    $email,
                                                    $comp_location,
                                                    $longitude,
                                                    $latitude,
                                                    $boundary_size
                        );
                    }

                    $action = "Edit";
                    $table_name = "Company";
                    $admindatetime = $this->getDateTime();
                    $adminTime = $admindatetime['time'];
                    $adminDate = $admindatetime['date'];
                                                        
                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                        
                    $countRowAdminLog = $stmtAdminLog->rowCount();
                    if($countRowAdminLog > 0){
                        $msg = 'Updated Successfully';
                        echo "<script>window.location.assign('./company.php?message=$msg');</script>";
                    } else {
                    echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Update Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                            </div>
                            <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                            </script>";
                    }
                } else {
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Updating failed</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                }
            }
        }
    }

    // Modal For Positions
    public function editpositions($company)
    {
        $sql = "SELECT * FROM `positions` WHERE company = '$company'";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();
        while($row = $stmt->fetch()){

            echo "<tr>
                    <td>$row->id</td>
                    <td>$row->position_name</td>
                    <td>$row->price</td>
                    <td>$row->overtime_rate</td>
                    <td>
                        <a href='./company.php?idPos=$row->id&actionPos=edit'>
                            <span class='material-icons'>edit</span>
                        </a>
                        <a href='./company.php?idPos=$row->id&actionPos=delete'>
                            <span class='material-icons'>delete</span>
                        </a>
                    </td>
                  </tr>";
        }
    }

    // Action For Adding of New Position
    public function addnewpos($company, $adminFullname, $adminId)
    {
        if(isset($_POST['addnewpos-btn'])){
            $position_name = $_POST['position_name'];
            $price = $_POST['price'];
            $ot = $_POST['ot'];

            if(empty($position_name) ||
               empty($price) ||
               empty($ot)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input fields are required</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {

                // when position name exist don't add
                $sqlFind = "SELECT position_name FROM `positions` WHERE position_name = ? AND company = ?";
                $stmtFind = $this->con()->prepare($sqlFind);
                $stmtFind->execute([$position_name, $company]);
                $userFind = $stmtFind->fetch();
                $countRowFind = $stmtFind->rowCount();

                if($countRowFind > 0){
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Position name already exists</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                } else {
                    $sql = "INSERT INTO `positions`(company, position_name, price, overtime_rate)
                            VALUES(?, ?, ?, ?)";
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute([$company, $position_name, $price, $ot]);
                    $countRow = $stmt->rowCount();

                    if($countRow > 0){

                        // get all email first
                        $sqlEmail = "SELECT e.email as email
                                      FROM schedule s 
                                      INNER JOIN employee e
                                      ON s.empId = e.empId
                                      WHERE s.company = '$company'";
                        $stmtEmail = $this->con()->query($sqlEmail);
                        
                        while($rowEmail = $stmtEmail->fetch()){
                            // inform here
                            $this->informEmployeeInCompAddPos($rowEmail->email, $position_name, $price, $ot);
                        }

                        $action = "Add";
                        $table_name = "Company Position";
                        $admindatetime = $this->getDateTime();
                        $adminTime = $admindatetime['time'];
                        $adminDate = $admindatetime['date'];
                                                            
                        $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                        $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                        $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                            
                        $countRowAdminLog = $stmtAdminLog->rowCount();
                        if($countRowAdminLog > 0){
                            $msg = 'Added Successfully';
                            echo "<script>window.location.assign('./company.php?message=$msg');</script>";
                        } else {
                        echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Add Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                </div>
                                <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                </script>";
                        }
                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>No Position Added</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                    }
                }
            }
        }
    }

    // Modal For Edit Specific Position
    public function editSpecificPositionModal($id)
    {
        $sql = "SELECT * FROM `positions` WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            echo "<div class='editpos-modal'>
                    <div class='modal-holder'>
                        <div class='editpos-header'>
                            <h1>Edit Position</h1>
                            <span class='material-icons' id='editposModalClose'>close</span>
                        </div>
                        <div class='editpos-content'>
                            <form method='POST'>
                                <div>
                                    <input type='hidden' value='$user->id' name='position_id' required/>
                                    <label for=''>Position</label>
                                    <input type='text' name='position_name' id='detectOIC' value='$user->position_name' onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' autocomplete='off' required/>
                                </div>
                                <div>
                                    <label for=''>Rates per hour</label>
                                    <input type='text' name='price' value='$user->price' placeholder='00.00' onkeypress='validate(event)' autocomplete='off' required/>
                                </div>
                                <div>
                                    <label for=''>Overtime Rate</label>
                                    <input type='text' name='overtime_rate' value='$user->overtime_rate' placeholder='00.00' onkeypress='validate(event)' autocomplete='off' required/>
                                </div>
                                <div>
                                    <button type='submit' name='editposBtn'>Edit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                  </div>
                  <script>
                    // close modal
                    let editposModalClose = document.querySelector('#editposModalClose');
                    editposModalClose.onclick = () => {
                        let editposModal = document.querySelector('.editpos-modal');
                        editposModal.style.display = 'none';
                    }

                    let detectOIC = document.querySelector('#detectOIC');
                    if(detectOIC.value == 'Officer in Charge'){
                        detectOIC.readOnly = true;
                    }
                  </script>";
        } else {
            echo "<div class='error'>No position detected</div>";
        }
    }

    // Action for Editing Specific Position
    public function editSpecificPosition($adminFullname, $adminId)
    {
        if(isset($_POST['editposBtn'])){
            $positionId = $_POST['position_id'];
            $position_name = $_POST['position_name'];
            $price = $_POST['price'];
            $overtime_rate = $_POST['overtime_rate'];

            if(empty($positionId) ||
               empty($position_name) ||
               empty($price) ||
               empty($overtime_rate)
            ){
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>All input fields are required</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            } else {

                // before update get the old data
                $sqlOld = "SELECT * FROM `positions` WHERE id = ?";
                $stmtOld = $this->con()->prepare($sqlOld);
                $stmtOld->execute([$positionId]);
                $userOld = $stmtOld->fetch();

                $sql = "UPDATE `positions`
                        SET position_name = ?,
                            price = ?,
                            overtime_rate = ?
                        WHERE id = ?";
                $stmt = $this->con()->prepare($sql);
                $stmt->execute([$position_name, $price, $overtime_rate, $positionId]);
                $countRow = $stmt->rowCount();

                if($countRow > 0){

                    // get all email first
                    $sqlEmail = "SELECT e.email as email
                                 FROM schedule s 
                                 INNER JOIN employee e
                                 ON s.empId = e.empId
                                 WHERE s.company = '$userOld->company'";
                    $stmtEmail = $this->con()->query($sqlEmail);

                    $position_name2 = $userOld->position_name;
                    $price2 = $userOld->price;
                    $overtime_rate2 = $userOld->overtime_rate;

                    while($rowEmail = $stmtEmail->fetch()){
                        // inform here
                        $this->informEmployeeInCompEditPos($rowEmail->email, $position_name, $price, $overtime_rate
                                                                           , $position_name2, $price2, $overtime_rate2
                                                          );
                    }

                    $action = "Edit";
                    $table_name = "Company Position";
                    $admindatetime = $this->getDateTime();
                    $adminTime = $admindatetime['time'];
                    $adminDate = $admindatetime['date'];
                                                        
                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                        
                    $countRowAdminLog = $stmtAdminLog->rowCount();
                    if($countRowAdminLog > 0){
                        $msg = 'Updated Successfully';
                        echo "<script>window.location.assign('./company.php?message=$msg');</script>";
                    } else {
                    echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Update Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                            </div>
                            <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                            </script>";
                    }
                } else {
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Update Failed</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                }
            }
        }
    }

    // Modal For Delete Specific Position
    public function deleteSpecificPositionModal($id)
    {
        $sql = "SELECT * FROM `positions` WHERE id = ?";
        $stmt = $this->con()->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            echo "<div class='deletepos-modal'>
                    <div class='modal-holder'>
                        <div class='deletepos-header'>
                            <h1>Delete Position</h1>
                            <span class='material-icons' id='deleteposModalClose'>close</span>
                        </div>
                        <div class='deletepos-content'>
                            <form method='POST'>
                                <input type='hidden' name='posId' value='$user->id' required/>
                                <h1>Are you sure you want to delete this position?</h1>
                                <div>
                                    <button type='submit' name='deletePos'>Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>
                  </div>
                  <script>
                    // close modal
                    let deleteposModalClose = document.querySelector('#deleteposModalClose');
                    deleteposModalClose.onclick = () => {
                        let deleteposModal = document.querySelector('.deletepos-modal');
                        deleteposModal.style.display = 'none';
                    }
                  </script>";
        }
    }

    // Action for Deleting Specific Position
    public function deleteSpecificPosition($adminFullname, $adminId)
    {
        if(isset($_POST['deletePos'])){
            $posId = $_POST['posId'];

            // select company and position name
            $sqlFindPos = "SELECT * FROM `positions` WHERE id = ?";
            $stmtFindPos = $this->con()->prepare($sqlFindPos);
            $stmtFindPos->execute([$posId]);
            $userFindPos = $stmtFindPos->fetch();
            $countRowFindPos = $stmtFindPos->rowCount();

            if($countRowFindPos > 0){
                $sqlFindEmp = "SELECT s.*, e.*
                               FROM schedule s
                               INNER JOIN employee e
                               ON s.empId = e.empId
                               WHERE s.company = ? AND e.position = ?";
                $stmtFindEmp = $this->con()->prepare($sqlFindEmp);
                $stmtFindEmp->execute([$userFindPos->company, $userFindPos->position_name]);
                $userFindEmp = $stmtFindEmp->fetch();
                $countRowFindEmp = $stmtFindEmp->rowCount();

                if($countRowFindEmp > 0){
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Delete Failed. Employee Exists</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                } else {
                    $sql = "DELETE FROM `positions` WHERE id = ?";
                    $stmt = $this->con()->prepare($sql);
                    $stmt->execute([$posId]);
                    $countRow = $stmt->rowCount();
        
                    if($countRow > 0){

                        $action = "Delete";
                        $table_name = "Company Position";
                        $admindatetime = $this->getDateTime();
                        $adminTime = $admindatetime['time'];
                        $adminDate = $admindatetime['date'];
                                                            
                        $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                        $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                        $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                            
                        $countRowAdminLog = $stmtAdminLog->rowCount();
                        if($countRowAdminLog > 0){
                            $msg = 'Deleted Successfully';
                            echo "<script>window.location.assign('./company.php?message=$msg');</script>";
                        } else {
                        echo "<div class='error'>
                                    <div class='icon-container'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                    <p>Delete Failed</p>
                                    <div class='closeContainer'>
                                        <span class='material-icons'>close</span>
                                    </div>
                                </div>
                                <script>
                                    let msgErr = document.querySelector('.error');
                                    setTimeout(e => msgErr.remove(), 5000);
                                </script>";
                        }
                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Delete Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                              </div>
                              <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                              </script>";
                    }
                }
            } 
        }
    }

    // Modal for Deleting Company Info
    public function deletecompanymodal($id)
    {
        echo "<div class='delete-modal'>
                <div class='modal-holder'>
                    <div class='delete-header'>
                        <h1>Delete Position</h1>
                        <span class='material-icons' id='deleteModalClose'>close</span>
                    </div>
                    <div class='delete-content'>
                        <form method='POST'>
                            <input type='hidden' value='$id' name='companyId' required/>
                            <h1>Are you sure you want to delete this company?</h1>
                            <div>
                                <button type='submit' name='deletecompany'>Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
              </div>
              <script>
                // close modal
                let deleteModalClose = document.querySelector('#deleteModalClose');
                deleteModalClose.onclick = () => {
                    let deleteModal = document.querySelector('.delete-modal');
                    deleteModal.style.display = 'none';
                }
              </script>";
    }

    // Action for Deleting the Company Info
    public function deleteCompanyFinal($adminFullname, $adminId)
    {
        if(isset($_POST['deletecompany'])){
            $companyId = $_POST['companyId'];

            // select company first to get company_name
            $sql = "SELECT company_name FROM company WHERE id = ?";
            $stmt = $this->con()->prepare($sql);
            $stmt->execute([$companyId]);
            $user = $stmt->fetch();
            $countRow = $stmt->rowCount();

            if($countRow > 0){
                // kapag may company, may access na sa company_name
                // $sqlPos = "DELETE FROM `positions` WHERE company = ?";
                // $stmtPos = $this->con()->prepare($sqlPos);
                // $stmtPos->execute([$user->company_name]);
                // $countRowPos = $stmtPos->rowCount();

                // magcecreate yan ng oic kaya need tong if
                // delete mo naman si company
                $sqlComp = "UPDATE company SET isDeleted = 1 WHERE id = ?";
                $stmtComp = $this->con()->prepare($sqlComp);
                $stmtComp->execute([$companyId]);
                $countRowComp = $stmtComp->rowCount();

                if($countRowComp > 0){

                    $action = "Delete";
                    $table_name = "Company";
                    $admindatetime = $this->getDateTime();
                    $adminTime = $admindatetime['time'];
                    $adminDate = $admindatetime['date'];
                                                        
                    $sqlAdminLog = "INSERT INTO admin_log(admin_id, name, action, table_name, time, date) VALUES(?, ?, ?, ?, ?, ?)";
                    $stmtAdminLog = $this->con()->prepare($sqlAdminLog);
                    $stmtAdminLog->execute([$adminId, $adminFullname, $action, $table_name, $adminTime, $adminDate]);
                                                        
                    $countRowAdminLog = $stmtAdminLog->rowCount();
                    if($countRowAdminLog > 0){
                        $msg = 'Deleted Successfully';
                        echo "<script>window.location.assign('./company.php?message=$msg');</script>";
                    } else {
                        echo "<div class='error'>
                                <div class='icon-container'>
                                    <span class='material-icons'>close</span>
                                </div>
                                <p>Delete Failed</p>
                                <div class='closeContainer'>
                                    <span class='material-icons'>close</span>
                                </div>
                            </div>
                            <script>
                                let msgErr = document.querySelector('.error');
                                setTimeout(e => msgErr.remove(), 5000);
                            </script>";
                    }
                } else {
                    echo "<div class='error'>
                            <div class='icon-container'>
                                <span class='material-icons'>close</span>
                            </div>
                            <p>Delete Failed</p>
                            <div class='closeContainer'>
                                <span class='material-icons'>close</span>
                            </div>
                          </div>
                          <script>
                            let msgErr = document.querySelector('.error');
                            setTimeout(e => msgErr.remove(), 5000);
                          </script>";
                }
            } else {
                echo "<div class='error'>
                        <div class='icon-container'>
                            <span class='material-icons'>close</span>
                        </div>
                        <p>No Company Exists</p>
                        <div class='closeContainer'>
                            <span class='material-icons'>close</span>
                        </div>
                      </div>
                      <script>
                        let msgErr = document.querySelector('.error');
                        setTimeout(e => msgErr.remove(), 5000);
                      </script>";
            }
        }
    }

    // company basic information
    public function informEmployeeInComp($email, $eCompanyName, 
                                                 $eCpNumber,
                                                 $eEmail,
                                                 $eCompLocation,
                                                 $eLongitude,
                                                 $eLatitude,
                                                 $eBoundarySize
    ){
        $name = 'JTDV Incorporation';
        $body = "Company Details has been updated. <br/>
                 <br/>
                 Company Name: $eCompanyName <br/>
                 Contact Number: $eCpNumber <br/>
                 Company Email: $eEmail <br/>
                 Company Location: $eCompLocation <br/>
                 Longitude: $eLongitude <br/>
                 Latitude: $eLatitude <br/>
                 Boundary: $eBoundarySize <br/>
                ";

        if(!empty($email)){

            $mail = new PHPMailer();

            // smtp settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = $this->e_username;  // gmail address
            $mail->Password = $this->e_password;  // gmail password

            $mail->Port = 465;
            $mail->SMTPSecure = "ssl";

            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email, $name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ("$email");                // headline
            $mail->Body = $body;                        // textarea

            $mail->send();
        }
    }

    // company positions, price and rates
    public function informEmployeeInCompAddPos($email, $pos, $rate, $ot)
    {
        $name = 'JTDV Incorporation';
        $body = "Company Details has been updated. <br/>
                 <br/>
                 New Position in company has been added:<br/>
                 Position Name: $pos <br/>
                 Rate per hour: $rate <br/>
                 Overtime Rate: $ot
                ";

        if(!empty($email)){

            $mail = new PHPMailer();

            // smtp settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = $this->e_username;  // gmail address
            $mail->Password = $this->e_password;  // gmail password

            $mail->Port = 465;
            $mail->SMTPSecure = "ssl";

            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email, $name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ("$email");                // headline
            $mail->Body = $body;                        // textarea

            $mail->send();
        }
    }



    // company positions, price and rates
    public function informEmployeeInCompEditPos($email, $pos, $rate, $ot, $pos2, $rate2, $ot2)
    {
        $name = 'JTDV Incorporation';
        $body = "Company Details has been updated. <br/>
                 <br/>
                 1 Position has been updated<br/>
                 From<br/>
                 Position Name: $pos2 <br/>
                 Rate per hour: $rate2 <br/>
                 Overtime Rate: $ot2 <br/><br/>

                 To<br/>
                 Position Name: $pos <br/>
                 Rate per hour: $rate <br/>
                 Overtime Rate: $ot <br/>
                ";

        if(!empty($email)){

            $mail = new PHPMailer();

            // smtp settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = $this->e_username;  // gmail address
            $mail->Password = $this->e_password;  // gmail password

            $mail->Port = 465;
            $mail->SMTPSecure = "ssl";

            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email, $name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ("$email");                // headline
            $mail->Body = $body;                        // textarea

            $mail->send();
        }
    }

    // company positions, price and rates
    public function informEmployeeInCompDeletePos($email, $pos, $rate, $ot)
    {
        $name = 'JTDV Incorporation';
        $body = "Company Details has been updated. <br/>
                 <br/>
                 1 Position has been updated<br/>
                 From<br/>
                 Position Name: $pos2 <br/>
                 Rate per hour: $rate2 <br/>
                 Overtime Rate: $ot2 <br/><br/>

                 To<br/>
                 Position Name: $pos <br/>
                 Rate per hour: $rate <br/>
                 Overtime Rate: $ot <br/>
                ";

        if(!empty($email)){

            $mail = new PHPMailer();

            // smtp settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = $this->e_username;  // gmail address
            $mail->Password = $this->e_password;  // gmail password

            $mail->Port = 465;
            $mail->SMTPSecure = "ssl";

            // email settings
            $mail->isHTML(true);
            $mail->setFrom($email, $name);              // Katabi ng user image
            $mail->addAddress($email);                  // gmail address ng pagsesendan
            $mail->Subject = ("$email");                // headline
            $mail->Body = $body;                        // textarea

            $mail->send();
        }
    }

    // activity logs
    public function employeeLogs()
    {
        $sql = "SELECT * 
                FROM admin_log 
                WHERE table_name = 'Available Employee' || 
                    table_name = 'Unavailable Employee' || 
                    table_name = 'Available Employee QR' ||
                    table_name = 'Unavailable Employee QR'
                ORDER BY date DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->name</td>
                        <td>$row->action</td>
                        <td>$row->table_name</td>
                        <td>$row->time</td>
                        <td>$row->date</td>
                     </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }

    public function companyLogs()
    {
        $sql = "SELECT * 
                FROM admin_log 
                WHERE table_name = 'Company' || 
                      table_name = 'Company Position'
                ORDER BY date DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->name</td>
                        <td>$row->action</td>
                        <td>$row->table_name</td>
                        <td>$row->time</td>
                        <td>$row->date</td>
                     </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }

    public function secretaryLogs2()
    {
        $sql = "SELECT * 
                FROM admin_log 
                WHERE table_name = 'Secretary'
                ORDER BY date DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->name</td>
                        <td>$row->action</td>
                        <td>$row->table_name</td>
                        <td>$row->time</td>
                        <td>$row->date</td>
                     </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }

    public function leaveLogs()
    {
        $sql = "SELECT * 
                FROM admin_log 
                WHERE table_name = 'Leave'
                ORDER BY date DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->name</td>
                        <td>$row->action</td>
                        <td>$row->table_name</td>
                        <td>$row->time</td>
                        <td>$row->date</td>
                     </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }

    public function remarksLogs()
    {
        $sql = "SELECT * 
                FROM admin_log 
                WHERE table_name = 'Remarks'
                ORDER BY date DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->name</td>
                        <td>$row->action</td>
                        <td>$row->table_name</td>
                        <td>$row->time</td>
                        <td>$row->date</td>
                     </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }

    public function adminLogs()
    {
        $sql = "SELECT * 
                FROM admin_log 
                WHERE table_name = 'Login' || 
                      table_name = 'Profile'
                ORDER BY date DESC";
        $stmt = $this->con()->query($sql);
        $countRow = $stmt->rowCount();

        if($countRow > 0){
            while($row = $stmt->fetch()){
                echo "<tr>
                        <td>$row->name</td>
                        <td>$row->action</td>
                        <td>$row->table_name</td>
                        <td>$row->time</td>
                        <td>$row->date</td>
                     </tr>";
            }
        } else {
            echo "<tr>
                    <td>No Data Found</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>";
        }
    }
}
$payroll = new Payroll();
?>