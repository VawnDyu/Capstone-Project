<?php
require_once('../class.php');
$sessionData = $payroll->getSessionData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'], 2);
$payroll->addcompany($sessionData['fullname'], $sessionData['id']);
$payroll->maintenance();

// for success action
$msg = '';
if(isset($_GET['message'])){
    $msg = $_GET['message'];
}

// for error action
$msg2 = '';
if(isset($_GET['message2'])){
    $msg2 = $_GET['message2'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company</title>
    <link rel="icon" href="../styles/img/icon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://api.tiles.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.tiles.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />
    <script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.7.0/mapbox-gl-geocoder.min.js"></script>
    <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.7.0/mapbox-gl-geocoder.css" type="text/css" />
    <link rel="stylesheet" href="../styles/mincss/company.min.css">
</head>
<body>
    <?php 
        // for entire company info
        $payroll->editcompanymodalinfo($sessionData['fullname'], $sessionData['id']); 
        $payroll->deleteCompanyFinal($sessionData['fullname'], $sessionData['id']);
        // for position only 
        $payroll->editSpecificPosition($sessionData['fullname'], $sessionData['id']);
        $payroll->deleteSpecificPosition($sessionData['fullname'], $sessionData['id']);
    ?>
    <div class='main-container'>
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
                            <li><a href="../employee/employee.php">Employee</a></li>
                            <li><a href="./company.php">Company</a></li>
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
                <h1>Company</h1>
            </div>
            <div class="welcome-info">
                <div class="welcome-box">
                    <h2><?= $sessionData['fullname']; ?></h2>
                    <!-- <p>Let's keep things organized and maintainable</p> -->
                    <p>Begin to manage the process of creating a new company that will allow employees to work for.</p>
                </div>
                <div class="welcome-svg">
                    <object data="../styles/SVG_modified/company.svg" type="image/svg+xml"></object>
                </div>
            </div>
            <div class="newlyadded-info">
                <div class="newlyadded-header">
                    <h2>Newly Added</h2>
                    <div class='color-coding'>
                        <div class='recent-container'>
                            <div title='Recent'></div>
                            <span>Recent</span>
                        </div>
                        <div class='late-container'>
                            <div title='Late'></div>
                            <span>Late</span>
                        </div>
                        <div class='old-container'>
                            <div title='Old'></div>
                            <span>Old</span>
                        </div>
                    </div>
                </div>
                <div class="newlyadded-cards">
                    <?php $payroll->newlyaddedcompany(); ?>
                </div>
            </div>
            <div class="companylist-container">
                <div class="companylist-header">
                    <h2>List of Company</h2>
                </div>
                <div class="table-container">
                    <div class="table-header">
                        <h2>Company</h2>
                        <form method="GET">
                            <input type="text" id="search" name="search" placeholder="Search.." autocomplete="off"/>
                            <button type="submit" name="companysearch"></button>
                        </form>
                    </div>
                    <div class="table-content">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Employee</th>
                                    <th>Email</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    if(isset($_GET['search'])){
                                        $payroll->companylistSearch($_GET['search']);
                                    } else {
                                        $payroll->companylist();
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="rightbar">
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
            <div class="form-container">
                <div class="form-header">
                    <h2>Add Company</h2>
                    <!-- <a id='addmodal-show'>modal</a> -->
                    <button type='button' id='open-modal'>modal</button>
                </div>
                <div class="form-contents">
                    <form id="myForm" method="post">
                        <div>
                            <label for="company_name">Company</label>
                            <input type="text" name="company_name" autocomplete="off" required/>
                        </div>
                        <div>
                            <label for="cpnumber">Contact Number</label>
                            <input type="text" name="cpnumber" id='cpnumber' autocomplete="off" maxlength="11" placeholder="09" onkeypress='validate(event)' required/>
                        </div>
                        <div>
                            <label for="email">Email</label>
                            <input type="email" name="email" autocomplete="off" required/>
                        </div>
                        <div>
                            <label for="">Trace Location</label>
                            <div id="map" class="trace"></div>
                        </div>
                        <div>
                            <label for="location_name">Address</label>
                            <input type="text" id="location_name" name="comp_location" autocomplete="off" required/>
                            <input type="hidden" id="longitude" name="longitude" placeholder="Longitude" required/>
                            <input type="hidden" id="latitude" name="latitude" placeholder="Latitude" required/>
                        </div>
                        <div>
                            <label for="">Set Boundary</label>
                            <div id="map_b"></div>
                            <input type="hidden" name="boundary_size" placeholder="Boundary size" class="map_b_size" required/>
                        </div>
                        <div>
                            <label for="">Shift</label>
                            <select name="shift" required>
                                <option value="">Select shift</option>
                                <option value="Shift1">Shift1</option>
                                <option value="Shift2">Shift2</option>
                            </select>
                        </div>
                        <div>
                            <label for="">Shift Span</label>
                            <select name="shift_span" required>
                                <option value="">Select span</option>
                                <option value="8">8 hrs</option>
                                <option value="12">12 hrs</option>
                            </select>
                        </div>
                        <div>
                            <label for="">Day Start</label>
                            <select name="day_start" required>
                                <option value="">Select day start</option>
                                <option value="06:00 am">06:00 AM</option>
                                <option value="07:00 am">07:00 AM</option>
                            </select>
                        </div>
                        <div class="addhere">
                            <label for="">Position</label>
                            <input type="number" style="display:none;" id="lengthInput" name="lengthInput" value="0" />
                            <section>
                                <input type="text" name="position0" value="Officer in Charge" onkeydown="return /^[a-zA-Z\s]*$/i.test(event.key)" autocomplete="off" readonly/>
                                <input type="text" name="price0" placeholder="00.00" onkeypress='validate(event)' autocomplete="off"/>
                                <input type="text" name="ot0" placeholder="00.00" onkeypress='validate(event)' autocomplete="off"/>
                            </section>
                        </div>
                        <div class="addnew-container">
                            <button type="button" id="addnew">+ Add new</button>
                        </div>
                        <button type="submit" name="addcompany" class='btn_primary'>Add Company</button><br/>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- for success action -->
    <input type='hidden' id='msg' value='<?= $msg; ?>' /> <!-- success -->
    <input type='hidden' id='msg2' value='<?= $msg2; ?>' /> <!-- error -->

    <!-- add modal -->
    <div class="modal-viewcompany">
        <div class="modal-holder">
            <div class="viewcompany-header">
                <h1>Add Company</h1>
                <span id="exit-modal-viewcompany" class="material-icons">close</span>
            </div>
            <div class="viewcompany-content">
                <form id="myForm" method="post">
                    <div>
                        <label for="company_name2">Company</label>
                        <input type="text" name="company_name2" autocomplete="off" required/>
                    </div>
                    <div>
                        <label for="cpnumber2">Contact Number</label>
                        <input type="text" name="cpnumber2" id='cpnumber2' placeholder='09' maxlength="11" onkeypress='validate(event)' autocomplete="off" required/>
                    </div>
                    <div>
                        <label for="email2">Email</label>
                        <input type="email" name="email2" autocomplete="off" required/>
                    </div>
                    <div>
                        <label>Trace Location</label>
                        <div id="map-addmodal" class="trace-addmodal"></div>
                    </div>
                    <div>
                        <label for="location_name">Address</label>
                        <input type="text" id="location_name" name="comp_location2" autocomplete="off" required/>
                        <input type="hidden" id="longitude-addmodal" name="longitude2" placeholder="Longitude" required/>
                        <input type="hidden" id="latitude-addmodal" name="latitude2" placeholder="Latitude" required/>
                    </div>
                    <div>
                        <label>Set Boundary</label>
                        <div id="map_b-addmodal"></div>
                        <input type="hidden" name="boundary_size2" placeholder="Boundary size" class="map_b_size-addmodal" required/>
                    </div>
                    <div>
                        <label for="">Shift</label>
                        <select name="shift2" required>
                            <option value="">Select shift</option>
                            <option value="Shift1">Shift1</option>
                            <option value="Shift2">Shift2</option>
                        </select>
                    </div>
                    <div>
                        <label for="">Shift Span</label>
                        <select name="shift_span2" required>
                            <option value="">Select span</option>
                            <option value="8">8 hrs</option>
                            <option value="12">12 hrs</option>
                        </select>
                    </div>
                    <div>
                        <label for="">Day Start</label>
                        <select name="day_start2" required>
                            <option value="">Select day start</option>
                            <option value="06:00 am">06:00 AM</option>
                            <option value="07:00 am">07:00 AM</option>
                        </select>
                    </div>
                    <div class="addhere-addmodal">
                        <label for="">Position</label>
                        <input type="number" id="lengthInput-addmodal" name="lengthInput2" style="display:none;" value="0" />
                        <section>
                            <input type="text" name="position0" value="Officer in Charge" onkeydown='return /^[a-zA-Z\s]*$/i.test(event.key)' autocomplete="off" readonly/>
                            <input type="text" name="price0" placeholder="00.00" onkeypress='validate(event)' autocomplete="off" required/>
                            <input type="text" name="ot0" placeholder="00.00" onkeypress='validate(event)' autocomplete="off" required/>
                        </section>
                    </div>
                    <div class="addnew-container">
                        <button type="button" id="addnew-addmodal">+ Add new</button>
                    </div>
                    <div>
                        <button type="submit" name="addcompany2" class='btn_primary2'>Add Company</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

     <!-- crud functionality for company -->
     <?php if(isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'view'){ 
        $payroll->viewcompanymodal($_GET['id']);
    } ?>

    <?php if(isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'edit'){ 
        $payroll->editcompanymodal($_GET['id']);
    } ?>

    <?php if(isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'delete'){ 
        $payroll->deletecompanymodal($_GET['id']);
    } ?>


    <!-- when user wants to view the company positions -->
    <?php if(isset($_GET['company'])){?>
        <div class="editpositions-modal">
            <div class="modal-holder">
                <div class="editpositions-header">
                    <h1>Company Position</h1>
                    <span class="material-icons" id='editpositionsModalClose'>close</span>
                </div>
                <div class="editpositions-content">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Position</th>
                                <th>Rates per hr</th>
                                <th>Overtime Rate</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $payroll->editpositions($_GET['company']); ?> 
                        </tbody>
                    </table>
                    <div>
                        <button>
                            <a href="./company.php?company=<?=$_GET['company'];?>&action=addnewpos">Add Position</a>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // close modal
            let editpositionsModalClose = document.querySelector('#editpositionsModalClose');
            editpositionsModalClose.onclick = () => {
                let editpositionsModal = document.querySelector('.editpositions-modal');
                editpositionsModal.style.display = 'none';
            }
        </script>
    <?php } ?>

    <?php if(isset($_GET['company']) && isset($_GET['action']) && $_GET['action'] == 'addnewpos'){ ?>
        <div class='addnewpos-modal'>
            <div class="modal-holder">
                <div class="addnewpos-header">
                    <h1>Add New Position</h1>
                    <span class="material-icons" id="addnewposModalClose">close</span>
                </div>
                <div class="addnewpos-content">
                    <form method="POST">
                        <div>
                            <label for="">Position</label>
                            <input type="text" name='position_name' onkeydown="return /^[a-zA-Z\s]*$/i.test(event.key)" autocomplete="off" required/>
                        </div>
                        <div>
                            <label for="">Rates per hour</label>
                            <input type="text" name='price' placeholder='00.00' placeholder='00.00' onkeypress='validate(event)' autocomplete="off" required/>
                        </div>
                        <div>
                            <label for="">Overtime Rate</label>
                            <input type="text" name='ot' placeholder='00.00' placeholder='00.00' onkeypress='validate(event)' autocomplete="off" required/>
                        </div>
                        <div>
                            <button type='submit' name='addnewpos-btn'>Add Position</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            let addnewposModalClose = document.querySelector('#addnewposModalClose');
            addnewposModalClose.onclick = () => {
                let addnewpos = document.querySelector('.addnewpos-modal');
                addnewpos.style.display = 'none';
            }
        </script>
    <?php $payroll->addnewpos($_GET['company'], $sessionData['fullname'], $sessionData['id']); // action: add
    } ?>

    <!-- when user wants to edit specific position -->
    <?php if(isset($_GET['idPos']) && isset($_GET['actionPos']) && $_GET['actionPos'] == 'edit'){ 
        $payroll->editSpecificPositionModal($_GET['idPos']);
    } ?>

    <!-- when user wants to delete specific position -->
    <?php if(isset($_GET['idPos']) && isset($_GET['actionPos']) && $_GET['actionPos'] == 'delete'){ 
        $payroll->deleteSpecificPositionModal($_GET['idPos']);
    } ?>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <script>
        const addnew = document.querySelector('#addnew');
        addnew.onclick = () => {

            let addhere = document.querySelector('.addhere');
            let inputLength = document.querySelector('#lengthInput');

            // convert to int
            let totalInput = parseInt(inputLength.value);
            inputLength.value = parseInt(totalInput + 1);

            // create elements
            let div = document.createElement('div');
            let pos = document.createElement('input');
            let price = document.createElement('input');
            let ot = document.createElement('input');
            let eks = document.createElement('span');

            pos.setAttribute('name', `position${inputLength.value}`);
            pos.setAttribute('placeholder', 'position');
            pos.setAttribute('type', 'text');
            pos.setAttribute('onkeydown', "return /^[a-zA-Z\\s]*$/i.test(event.key)");
            pos.setAttribute('autocomplete', 'off');
            price.setAttribute('name', `price${inputLength.value}`);
            price.setAttribute('placeholder', '00.00');
            price.setAttribute('type', 'text');
            price.setAttribute('onkeypress', 'validate(event)');
            price.setAttribute('autocomplete', 'off');
            ot.setAttribute('name', `ot${inputLength.value}`);
            ot.setAttribute('placeholder', '00.00');
            ot.setAttribute('type', 'text');
            ot.setAttribute('onkeypress', 'validate(event)');
            ot.setAttribute('autocomplete', 'off');
            eks.setAttribute('onclick', 'getParentElement(this)');
            eks.setAttribute('class', 'eks material-icons');
            eks.innerText = 'close';

            // place to created div
            div.appendChild(pos);
            div.appendChild(price);
            div.appendChild(ot);
            div.appendChild(eks);

            // add to existing parent element
            addhere.appendChild(div);
        }

        function getParentElement(e){

            let lengthInput = document.querySelector('#lengthInput');
            lengthInput.value = parseInt(lengthInput.value) - parseInt(1);
            // lengthInput.value = parseInt(lengthInput.value);

            e.parentElement.children[0].value = ''; //position
            e.parentElement.children[1].value = ''; //price
            e.parentElement.children[2].value = ''; //ot

            let myparent = e.parentElement; // div na walang att


            let addhere = e.parentElement.parentElement; // addhere
            let mydiv = addhere.querySelectorAll('div'); // object

            const mydivArray = Object.values(mydiv); // array

            // object
            let filteredDiv = mydivArray.filter( div => { return div != myparent; });
            const filteredDivArray = Object.values(filteredDiv); // array
            console.log(filteredDiv);
            for(let i = 0; i < filteredDivArray.length; i++){
                filteredDivArray[i].children[0].setAttribute('name', `position${i+1}`);
                filteredDivArray[i].children[1].setAttribute('name', `price${i+1}`);
                filteredDivArray[i].children[2].setAttribute('name', `ot${i+1}`);
            }

            myparent.remove();
        }

        // open add modal
        const openModalBtn = document.querySelector('#open-modal');
        openModalBtn.onclick = () => {
            let addModal = document.querySelector('.modal-viewcompany');
            addModal.style.display = 'flex';
        }

        // close add modal
        let exitModalViewCompany = document.querySelector("#exit-modal-viewcompany");
        exitModalViewCompany.addEventListener('click', e => {
            let viewcompanyModal = document.querySelector('.modal-viewcompany');
            viewcompanyModal.style.display = "none";
        });

        // for add modal
        const addnewAddModal = document.querySelector('#addnew-addmodal');
        addnewAddModal.onclick = () => {

            let addhere = document.querySelector('.addhere-addmodal');
            let inputLength = document.querySelector('#lengthInput-addmodal');

            // convert to int
            let totalInput = parseInt(inputLength.value);
            inputLength.value = parseInt(totalInput + 1);

            // create elements
            let div = document.createElement('div');
            let pos = document.createElement('input');
            let price = document.createElement('input');
            let ot = document.createElement('input');
            let eks = document.createElement('span');

            pos.setAttribute('name', `position${inputLength.value}`);
            pos.setAttribute('placeholder', 'position');
            pos.setAttribute('type', 'text');
            pos.setAttribute('onkeydown', 'return /^[a-zA-Z\\s]*$/i.test(event.key)');
            pos.setAttribute('autocomplete', 'off');
            price.setAttribute('name', `price${inputLength.value}`);
            price.setAttribute('placeholder', '00.00');
            price.setAttribute('type', 'text');
            price.setAttribute('onkeypress', 'validate(event)');
            price.setAttribute('autocomplete', 'off');
            ot.setAttribute('name', `ot${inputLength.value}`);
            ot.setAttribute('placeholder', '00.00');
            ot.setAttribute('type', 'text');
            ot.setAttribute('onkeypress', 'validate(event)');
            ot.setAttribute('autocomplete', 'off');
            eks.setAttribute('onclick', 'getParentElement2(this)');
            eks.setAttribute('class', 'eks material-icons');
            eks.innerText = 'close';
            


            // place to created div
            div.appendChild(pos);
            div.appendChild(price);
            div.appendChild(ot);
            div.appendChild(eks);

            // add to existing parent element
            addhere.appendChild(div);
        }

        function getParentElement2(e){

            let lengthInput = document.querySelector('#lengthInput-addmodal');
            lengthInput.value = parseInt(lengthInput.value) - parseInt(1);
            // lengthInput.value = parseInt(lengthInput.value);

            e.parentElement.children[0].value = ''; //position
            e.parentElement.children[1].value = ''; //price
            e.parentElement.children[2].value = ''; //ot

            let myparent = e.parentElement; // div na walang att


            let addhere = e.parentElement.parentElement; // addhere
            let mydiv = addhere.querySelectorAll('div'); // object

            const mydivArray = Object.values(mydiv); // array

            // object
            let filteredDiv = mydivArray.filter( div => { return div != myparent; });
            const filteredDivArray = Object.values(filteredDiv); // array
            console.log(filteredDiv);
            for(let i = 0; i < filteredDivArray.length; i++){
                filteredDivArray[i].children[0].setAttribute('name', `position${i+1}`);
                filteredDivArray[i].children[1].setAttribute('name', `price${i+1}`);
                filteredDivArray[i].children[2].setAttribute('name', `ot${i+1}`);
            }

            myparent.remove();
        }

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

        // success message
        let msg = document.querySelector('#msg');
        if(msg.value != ''){
            let successDiv = document.createElement('div');
            successDiv.classList.add('success');
            let iconContainerDiv = document.createElement('div');
            iconContainerDiv.classList.add('icon-container');
            let spanIcon = document.createElement('span');
            spanIcon.classList.add('material-icons');
            spanIcon.innerText = 'done';
            let pSuccess = document.createElement('p');
            pSuccess.innerText = msg.value; // set to $_GET['msg']
            let closeContainerDiv = document.createElement('div');
            closeContainerDiv.classList.add('closeContainer');
            let spanClose = document.createElement('span');
            spanClose.classList.add('material-icons');
            spanClose.innerText = 'close';

            // destructure
            iconContainerDiv.appendChild(spanIcon);
            closeContainerDiv.appendChild(spanClose);

            successDiv.appendChild(iconContainerDiv);
            successDiv.appendChild(pSuccess);
            successDiv.appendChild(closeContainerDiv);
            document.body.appendChild(successDiv);

            // remove after 5 mins
            setTimeout(e => successDiv.remove(), 5000);
        }

        // error message
        let msg2 = document.querySelector('#msg2');
        if(msg2.value != ''){
            let errorDiv = document.createElement('div');
            errorDiv.classList.add('error');
            let iconContainerDiv = document.createElement('div');
            iconContainerDiv.classList.add('icon-container');
            let spanIcon = document.createElement('span');
            spanIcon.classList.add('material-icons');
            spanIcon.innerText = 'done';
            let pError = document.createElement('p');
            pError.innerText = msg2.value; // set to $_GET['msg2']
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


        // check if contact number equal to 11 ADD MODAL
        let btnPrimary2 = document.querySelector('.btn_primary2');
        let mobilePrimary2 = document.querySelector('#cpnumber2');
        let minLength2 = 11;
        btnPrimary2.addEventListener('click', validateMobileModal);

        function validateMobileModal(event) {
            if (mobilePrimary2.value.length < minLength2) {
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
                pError.innerText = 'Contact Number must be ' + minLength2 + ' digits.'; 
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


    </script>
    <script src='../scripts/comp-location.js'></script>
</body>
</html>