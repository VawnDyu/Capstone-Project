<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <div class="myModal">
        <label for="deductionname" id="deductionname"> Name :</label> <br>
        <input type="text" id="dedname" name="name" >
        <label for="amount"  id="name"> Amount :</label> <br>
        <input type="number" id="amount" name="amount" >
        <select name="unitamount" id="unitamount">
        <option value="">Select Amount Measurement</option>
        <option value="percentage">Percentage</option>
        <option value="total">Total Amount</option>
        </select>
        <input type="number" step="0.001" id="otherpercentage" name="otherpercentage" >
        </div>
</body>
</html>