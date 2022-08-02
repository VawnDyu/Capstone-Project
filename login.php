<?php
require_once('class.php');
$payroll->login();
$payroll->maintenance();

// for error action
$msg2 = '';
if(isset($_GET['message2'])){
    $msg2 = $_GET['message2'];
}

// for error message when logging in
$errorMessage = '';

if(isset($_GET['errormessage'])){
    $errorMessage = $_GET['errormessage'];
} else {
    $errorMessage = '';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to JTDV</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link rel="stylesheet" href="./styles/css/login.css">
    <link rel="icon" href="./styles/img/icon.png">
</head>
<body>
    <div class="main-container">
        <div class="leftbar">
            <nav>
                <div class="logo-container">
                    <div class="logo"></div>
                    <h3>JTDV</h2>
                </div>
            </nav>
            <div class="content">
                <div class="content-svg">
                    <object data="./styles/SVG_modified/login.svg" type="image/svg+xml"></object>
                </div>
                <div class="content-info">
                    <div class="center">
                        <h2>Engage with people you work with.</h1>
                        <p>Security system will help you manage your people with better user experience. Sign in now.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rightbar">
            <div class="welcome-container">
                <h1>Welcome Back</h1>
                <p>Sign in your account</p>
            </div>
            <form method="POST" id="form">
                <input type="hidden" id='errormessage' value='<?= $errorMessage; ?>' />
                <!-- insert here -->

                <div class="input-container1">
                    <label for="username">Username</label>
                    <div class="icon1">
                        <input type="email" id="username" name="username" placeholder="Enter username" autofocus autocomplete="off" required/>
                    </div>
                </div>

                <div class="input-container2">
                    <label for="password">Password</label>
                    <div class="icon2">
                        <input type="password" id="password" name="password" placeholder="Enter password" autocomplete="off" required/>
                    </div>
                </div>
                <button type="submit" name="login">Sign in</button>
            </form>
        </div>
    </div>

    <input type='hidden' id='msg2' value='<?= $msg2; ?>' /> <!-- error -->

    <script>
        window.onload = () => {
            let myForm = document.querySelector('#form');
            let errorMessage = document.querySelector('#errormessage');

            if(errorMessage.value != ''){
                // create error message elements
                let myDiv = document.createElement('div');
                myDiv.setAttribute('class', 'message error');
                let myP = document.createElement('p');
                myP.innerText = errorMessage.value;

                // append it inside div
                myDiv.appendChild(myP);
                myForm.prepend(myDiv);
            }
        }

        // error
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
    </script>
</body>
</html>