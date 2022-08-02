<?php
require_once('../class.php');
$sessionData = $payroll->getSessionData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'], 2);
$payroll->setUnavailableGuards($sessionData['fullname'], $sessionData['id']);
$payroll->maintenance();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Guards</title>
    <link rel="icon" href="../styles/img/icon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../styles/mincss/selectedGuards.min.css">
</head>
<body>
    <div class="main-container">
        <div class="leftbar">
            <div class="logo-container">
                <div class="logo"></div>
                <h1>JTDV</h1>
            </div>
            <div class="links-container">
                <ul>
                    <li><a href="../dashboard.php">Dashboard</a></li>
                    <li class="active-parent">Records
                        <ul>
                            <li><a href="./employee.php">Employee</a></li>
                            <li><a href="../company/company.php">Company</a></li>
                            <li><a href="../secretary/secretary.php">Secretary</a></li>
                        </ul>
                    </li>
                    <li>Manage Report
                        <ul>
                            <li><a href="../leave/leave.php">Leave</a></li>
                            <li><a href="../remarks/remarks.php">Remarks</a></li>
                        </ul>
                    </li>
                    <li><a href="../activity/activity.php">Activities</a></li>
                </ul>
                <div>
                    <a href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
        <div class="centerbar">
            <div class="header-info">
                <h1>Employee</h1>
                <div class="profile-container">
                    <div class="profile-setter">
                        <h3><?= $sessionData['fullname']; ?></h3>
                        <a href="../admin/profile.php">
                            <div class="image-container">
                                <?= $payroll->viewAdminImage($sessionData['id']); ?>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="select-info">
                <div class="select-header">
                    <h1>Select Company</h1>
                    <!-- <form method="GET">
                        <input type="text" id="search" name="search" placeholder="Search.." autocomplete="off"/>
                        <button type="submit" name="searchbtn"></button>
                    </form> -->
                </div>
                <div class="select-content">
                    <form method="POST">
                        <div class="left">
                            <div>
                                <label for="companyname">Company Name</label>
                                <select name="companyname" onchange="populate(this)" id="companyname" required>
                                    <option value="">Select One</option>
                                    <!-- get all company name -->
                                    <?= $payroll->dropdownCompanyDetails(); ?>
                                </select>
                            </div>
                
                            <div>
                                <label for="location">Location</label>
                                <input type="text" id="location" placeholder="Auto fill" disabled/>
                            </div>
                
                            <div>
                                <label>Duration</label>
                                <div>
                                    <div class="duration-row">
                                        <label for="year">Year</label>
                                        <select name="year" onchange="setYear(this)" id="year" required>
                                            <option value="0">0</option>
                                            <option value="1" selected>1</option>
                                            <option value="2">2</option>
                                        </select>
                                    </div>
                                    <div class="duration-row">
                                        <label for="month">Month</label>
                                        <select name="month" onchange="setMonthDay(this)" id="month">
                                            <option value="0">0</option>
                                            <option value="1" selected>1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                        </select>
                                    </div>
                                    <div class="duration-row">
                                        <label for="day">Day</label>
                                        <select name="day" onchange="setMonthDay(this)" id="day">
                                            <option value="0">0</option>
                                            <option value="1" selected>1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                            <option value="21">21</option>
                                            <option value="22">22</option>
                                            <option value="23">23</option>
                                            <option value="24">24</option>
                                            <option value="25">25</option>
                                            <option value="26">26</option>
                                            <option value="27">27</option>
                                            <option value="28">28</option>
                                            <option value="29">29</option>
                                            <option value="30">30</option>
                                            <option value="31">31</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <br/>
                            <button type="submit" name="assignguards">Assign Employees</button>
                        </div>
                
                        <div class="right">
                            <table>

                                <colgroup>
                                    <col span="1" style="width:35%"/>
                                    <col span="1" style="width:16%"/>
                                    <col span="1" style="width:19%"/>
                                    <col span="1" style="width:7%"/>
                                </colgroup>

                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Email</th>
                                        <th>Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= $payroll->selectguardsAddCompany($_GET['ids']); ?>
                                </tbody>
                                
                            </table>
                            <div class="addmore-emp">
                                <span class="material-icons">add</span>
                                <h3>Add New Employee</h3>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- modal add more employee -->
    <div class="addmore-modal">
        <div class="modal-holder">
            <div class="table-header">
                <h1>Add New Employee</h1>
                <!-- <form method="POST">
                    <input type="text" id="addnew-search" name="addnew-search" placeholder="Search.." autocomplete="off"/>
                    <button type="submit" name="addnew-btn"></button>
                </form> -->
            </div>
            <div class="table-content">
                <div class="table-holder">
                    <table>
                        <colgroup>

                            <col span="1" style="width:5%;" />
                            <col span="1" style="width:15%" />
                            <col span="1" style="width:15%" />
                            <col span="1" style="width:36%" />
                        </colgroup>

                        <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= $payroll->addNewSelectedGuard($_GET['ids']); ?>
                        </tbody>
                    </table>
                </div>
                <form method="POST">
                    <input type='hidden' name='ids' id='ids'/>
                    <button type="button" id="exit-addmore-modal">Cancel</button>
                    <button type="button" onclick="redirectAgain()">Select Guards</button>
                </form>
            </div>
        </div> 
    </div>
    <script src="../scripts/selectedGuards.js"></script>
</body>
</html>