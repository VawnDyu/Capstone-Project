<?php

require_once('../secclass.php');
$sessionData = $payroll->getSessionSecretaryData();
$payroll->verifyUserAccess($sessionData['access'], $sessionData['fullname'],2);
$fullname = $sessionData['fullname'];
$access = $sessionData['access'];
$id = $sessionData['id'];
if(isset($_GET['message'])){
    $mess=$_GET['message'];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Online Feedback Form</title>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <style>
      html, body {
      min-height: 100%;
      }
      body, div, form, input, p { 
      padding: 0;
      margin: 0;
      outline: none;
      font-family: Roboto, Arial, sans-serif;
      font-size: 14px;
      color: #666;
      line-height: 22px;
      }
      h1 {
      font-weight: 400;
      }
      h4 {
      margin: 22px 0 4px;
      color: #095484;
      }
      .testbox {
      display: flex;
      justify-content: center;
      align-items: center;
      height: inherit;
      padding: 3px;
      }
      form {
      width: 100%;
      padding: 20px;
      background: #fff;
      box-shadow: 0 2px 5px #ccc; 
      }
      input {
      width: calc(100% - 10px);
      padding: 5px;
      border: 1px solid #ccc;
      border-radius: 3px;
      vertical-align: middle;
      }
      input:hover, textarea:hover {
      outline: none;
      border: 1px solid #095484;
      }
      .first-name {
      margin-bottom: 22px;
      }
      span {
      color: red;
      }
      th, td {
      width: 21%;
      padding: 15px 0;
      border-bottom: 1px solid #ccc;
      text-align: center;
      vertical-align: unset;
      line-height: 18px;
      font-weight: 400;
      word-break: break-all;
      }
      .first-col {
      width: 16%;
      text-align: left;
      }
      table {
      width: 100%;
      }
      textarea {
      width: calc(100% - 6px);
      }
      .btn-block {
      margin-top: 20px;
      text-align: center;
      }
      button {
      width: 150px;
      padding: 10px;
      border: none;
      -webkit-border-radius: 5px; 
      -moz-border-radius: 5px; 
      border-radius: 5px; 
      background-color: #095484;
      font-size: 16px;
      color: #fff;
      cursor: pointer;
      }
      button:hover {
      background-color: #0666a3;
      }
      @media (min-width: 568px) {
      th, td {
      word-break: keep-all;
      }
      }
    </style>
  </head>
  <body>
    <div class="testbox">
      <form method="post">
      <a href="secdashboard.php">Back</a>
        <h1>Online Feedback Form</h1>
        <p>Thank you for taking the time to fill in our online feedback form. By providing us your feedback, you are helping us understand what we do well and what improvements we need to implement.</p>
        <h4>Select Category<span>*</span></h4>
        <table>
          <tr>
            <th class="first-col"></th>
            <th>Report Bug</th>
            <th>Suggestion</th>
          </tr>
          <tr>
            <td class="first-col"></td>
            <td><input type="radio" value="Report Bug" name="satisfaction" /></td>
            <td><input type="radio" value="Suggestion" name="satisfaction" /></td>

          </tr>
        </table>
        <h4>Description</h4>
        <input type="textarea" rows="5" name="comment"></input>
        <small>* The information given within the Feedback Form will be used for service improvement only and are strictly confidential.</small>
        <div class="btn-block">
          <?php $payroll->feedback($id,$fullname); ?>
          <button type="submit" name="feedback">Send Feedback</button>
        </div>
      </form>
    </div>
  </body>
</html>