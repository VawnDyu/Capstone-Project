<?php
require_once('../secclass.php');
$sessionData = $payroll->getSessionSecretaryData();
$id = $sessionData['id'];
$fullname = $sessionData['fullname'];
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
$payroll->AutomaticGenerateSalary($fullname,$id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Salary</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../seccss/main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.js"></script>
</head>
<style>
.no-js #loader { display: none;  }
.js #loader { display: block; position: absolute; left: 100px; top: 0; }
.se-pre-con {
	position: fixed;
	left: 0px;
	top: 0px;
	width: 100%;
	height: 100%;
	z-index: 9999;
	background: url(https://smallenvelop.com/wp-content/uploads/2014/08/Preloader_11.gif) center no-repeat #fff;
}
</style>
<body>
<div class="main-containter">
    <div class="modal">
        <form method="post" class="modal__delete" id="hide" style="display:block">
            <div class="modal__delete__header1">
                <h1>Create Salary</h1>
            </div>
                <div class="modal__delete__content">
                    <h1>Create salary to this employee/s?</h1>
                                <script>
                                    function dothis()
                                    {
                                        var x =  document.getElementById('createsalary').value 
                                        if(x == "create")
                                        {   
                                            document.getElementById("show").style.display = "block";
                                            document.getElementById("hide").style.display = "none";
                                            $(window).load(function() {
                                                $('.se-pre-con').fadeOut('slow');;
                                                });
                                        }
                                    }
                                </script>
                        <button class="btn_success" type="submit" name="createsalary" id="createsalary" value="create" onclick="dothis()">
                            Create
                        </button>

                    <button class="cancel" type="submit" name="cancel">
                        Back
                    </button>
                </div>
        </form>
                <div class ="show" style="display:none" id="show">
                    <div class="main-containter">
                        <div class="modal">
                            <form action="" method="post" class=modal__form>
                                <div class="modal__form__content">     
                                    <div class="se-pre-con">
                                        <div class="modal__form__content__spaces">
                                        </div>
                                        <div class="modal__form__content__spaces">
                                        </div>
                                        <div class="modal__form__content__spaces">
                                        </div>
                                        <div class="modal__form__content__spaces">
                                        </div>
                                        <center>
                                            <img src="../img/icon.png" style="height:200px; width:200px">
                                                <div class="modal__form__content__spaces">
                                                </div>
                                                    <h1>Please wait while loading.</h1>
                                                    <p>a pdf file will automatically download<br>if not, download the file <a href="filemodal.php">here</a></p>
                                                    <br>
                                                        <div class="modal__form__content__spaces">
                                                            </div>
                                                    <button class="btn_success" type="submit" name="generatededuction">
                                                        <a href="automaticpayroll.php" style="color:white;">Click if done</a>
                                                    </button>
                                        </center>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
    </div>
</div>
</body>
</html>