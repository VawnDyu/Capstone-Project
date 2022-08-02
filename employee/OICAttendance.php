<?php 

require_once('../classemp.php');

    $sql = "SELECT status FROM maintenance WHERE module='Guards'";
    $stmt = $payroll->con()->prepare($sql);
    $stmt->execute();
        
    $row = $stmt->fetchColumn();
        
    if ($row == 1) {
        header('location: ../m_maintenance.php');
    } else {
        $sessionData = $payroll->getSessionOICData();
        $getDateTime = $payroll->getDateTime();
        
        $sessionName = $sessionData['fullname'];
        $SessionCompany = $sessionData['company'];
        
        $getTimeNow = $getDateTime['time'];
        $getDateNow = $getDateTime['date'];
        // $getempId = $sessionData['empId'];
        
        $payroll->MobileVerifyUserAccess($sessionData['access'], $sessionData['fullname'], $sessionData['position']);
        
        $payroll->submitOICAttendance();
        $payroll->TimeOutAttendance();
        $sessionLocation = $payroll->getLocation($SessionCompany);
        
        $Availability = $sessionData['availability'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="icon" href="../img/icon.png" type="image/png">
    <script src='https://unpkg.com/@turf/turf/turf.min.js'></script>
    <script src='https://api.mapbox.com/mapbox-gl-js/v1.11.0/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v1.11.0/mapbox-gl.css' rel='stylesheet' />
    <title>OIC | Attendance</title>
</head>
<body>
    <div class="main-container">
        <div class="nav-bar-left">
            <div class="logo-header">
                <img src="../img/icon.png">
                <header>JTDV</header>
            </div>
        </div>
        <div class="nav-bar-right">
            <div class="navigator">
                <ul>
                    <li><a href="OICProfile.php"><span class="material-icons-outlined">person</span></a></li>
                    <li><a href="OIC.php"><span class="material-icons-outlined">other_houses</span></a></li>
                    <li><a href="OICInbox.php" class='notification'>
                            <span class="material-icons-outlined" style="transform: translateY(6%);">mail</span>
                            <?php $payroll->notificationBadge() ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="attendance-header">
            <header>Attendance</header>
        </div>
        <div class="attendance-view">
        <a href="OICAttendanceViewAll.php"><span class="material-icons-outlined">account_circle</span>View</a>
        </div>

        <div class="first-attendance-title">
            <header><span class="material-icons-outlined">history_toggle_off</span>Time-in / Time-out</header>
        </div>

        <div class="schedule-text">
            <header>Schedule: <?php echo date("H:i", strtotime($sessionData['scheduleTimeIn'])) ?> - <?php echo date("H:i", strtotime($sessionData['scheduleTimeOut']))?></header>
        </div>

        <div class="attendance-form">
            <form method="post">
                <div>
                    <label for="fullname"><span class="material-icons-outlined">perm_identity</span>Fullname</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo $sessionName ?>" readonly>
                </div>

                <div>
                    <label for="timenow"><span class="material-icons-outlined">access_time</span>Time</label>
                    <input type="text" name="timenow" id="timenow" value="<?php echo date("H:i:s", strtotime($getTimeNow)) ?>" readonly>
                </div>

                <div>
                    <label for="datenow"><span class="material-icons-outlined">today</span>Date</label>
                    <input type="text" name="datenow" id="datenow" value="<?php echo $getDateNow ?>" readonly>
                </div>
                
                <div>
                    <label for="location"><span class="material-icons-outlined">location_on</span>Location</label>
                    <input type="text" name="location" id="location" value="Please turn on your GPS." readonly>
                </div>
                <?php
                    if ($Availability == 'Absent') {
                        echo "<input type='button' value='Marked as Absent' style='color: #FFFFFF; background: #464646; opacity: 0.6;' disabled>";
                        // echo "Absent";
                    } else {
                        $payroll->alreadyLogin();
                    }
                ?>
            </form>
        </div>
    </div>
    <div class="view-modal-error">  
        <div class="modal-error">
            <header class='error-header'>Error</header>
            <?php $payroll->getErrorModalMsg() ?>
            <button type="button" id="btnOkay">Okay</button>
        </div>
    </div>

    <div class="view-modal-success">  
        <div class="modal-success">
            <header class='success-header'>Success</header>
            <?php $payroll->getSuccessModalMsg() ?>
            <button type="button" id="btnOkaySuccess">Okay</button>
        </div>
    </div>

    <?php $payroll->showMsgModal() ?>

    <script>
        var click = document.getElementById("btnOkay");
        var clicksuccess = document.getElementById("btnOkaySuccess");
        var errormodal = document.getElementsByClassName('view-modal-error');
        var successmodal = document.getElementsByClassName('view-modal-success');

        click.addEventListener("click", function() {
            for (var i=0;i<errormodal.length;i+=1) {
                errormodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OICAttendance.php");
            }
        });

        clicksuccess.addEventListener("click", function() {
            for (var i=0;i<successmodal.length;i+=1) {
                successmodal[i].style.display = 'none';

                window.history.pushState("Closing the modal", "Refresh", "OICAttendance.php");
            }
        });


        //Location Script

        let currPosition = []

        navigator.geolocation.getCurrentPosition(pos => {
        currPosition.push(pos.coords.longitude);
        currPosition.push(pos.coords.latitude);

        // mapboxgl.accessToken = "pk.eyJ1Ijoidm9uMzlnYW1pbmciLCJhIjoiY2wwZm0yMnFkMGlmbTNkcXR4YTM0cW9odSJ9.XN2rqXL7L9KMdXFHb8U6oQ";
        // var map = new mapboxgl.Map({
        //     container: 'map', // container id
        //     style: 'mapbox://styles/mapbox/streets-v11',
        //     center: currPosition,
        //     zoom: 18
        // });

        //YOUR TURN: Replace var to = [lng, lat] with the lng/lat for Madison, WI [-89.384, 43.101] 
        //YOUR TURN: Replace var to = [lng, lat] with the lng/lat for Chicago, Il [-87.627, 41.919] 

        //   var to = [myLong, myLat] //lng, lat
        var from = [<?php echo $sessionLocation['longitude'] ?>, <?php echo $sessionLocation['latitude'] ?>] //lng, lat 

        // var greenMarker = new mapboxgl.Marker({
        //     color: 'green'
        //     })
        //     .setLngLat(currPosition) // marker position using variable 'to'
        //     .addTo(map); //add marker to map

        // var purpleMarker = new mapboxgl.Marker({
        //     color: 'purple'
        //     })
        //     .setLngLat(from) // marker position using variable 'from'
        //     .addTo(map); //add marker to map

        var options = {
            units: 'kilometers'
        }; // units can be degrees, radians, miles, or kilometers, just be sure to change the units in the text box to match. 

        var distance = turf.distance(currPosition, from, options);
        var boundary = parseFloat(<?php echo rtrim($sessionLocation['boundary'], "km") ?>);
        var value = document.getElementById('location');
        var getTimeInBtn = document.getElementById('time-in-button');

        
        if (distance > boundary) {
            var diff = (distance - boundary).toFixed(2);
            let steps =  (diff * 1312.33595801).toFixed();
            
            let message = "You are " + diff + " km far / " + steps + " more steps.";
            value.value = message;
            getTimeInBtn.disabled = true;
            getTimeInBtn.style.opacity = 0.6;
        } else {
            let message = '<?php echo $sessionLocation['comp_location']?>';
            value.value = message;
            getTimeInBtn.disabled = false;
            getTimeInBtn.style.removeProperty('opacity');
        }

        // value.innerHTML = "Distance: " + distance.toFixed([2]) + " kilometers<br>Boundary: " + boundary + "<br>" + message;

        console.log(currPosition, from, distance);
        });

    </script>
</body>
</html>