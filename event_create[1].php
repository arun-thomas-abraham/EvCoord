<!--PURPOSE: FORM SUBMISSION TO CREATE A PAGE. REACHABLE FROM ANY PAGE. LEADS TO THE SUBMISSION-CODE PAGE.-->
<?php
session_start();
if ($_SESSION['valid'] != true) {
    die("failure: user not logged in.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Create Event</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/resources/demos/style.css">

    <!-- Bootstrap core CSS -->
    <link href="bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="../../assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]>
    <script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="../../assets/js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>

        .input1[type=text], .input2[type=password] {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .button10 {
            background-color: #E35E1B;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .cancelbutton {
            width: auto;
            padding: 10px 18px;
            background-color: #f44336;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            color: white;
        }

        .container {
            padding: 16px;
        }


    </style>
</head>
<body>
<h2><?php
    if ($_SESSION['valid'] == true) {
        //array access must be encapsulated in brackets ONLY if they're referenced inside quotes
        include 'logoutNav.php';

    } else {
        //both single quote ' and double quotes " work fine as print string qualifiers. choose whichever lets you get away with less escape characters.
        //EXCEPT when you need to format variables inside your strings. In that case, only double quotes " are allowed.
        include 'loginNav.php';

    }
    ?></h2>
<div class="container">
    <form action="event_create_submit.php" method="post" target="_top">

        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script>
            $(function () {
                $("#datepicker1").datepicker();
            });
            $(function () {
                $("#datepicker2").datepicker();
            });
        </script>

        <label style=color:black;font-family:verdana><b>Event Name</b></label>
        <input type="text" class="input1" placeholder="Enter Event Name" name="EventName" required>

        <label style=color:black;font-family:verdana><b>Start Date</b></label>
        <input type="text" class="input1" id="datepicker1" placeholder="Enter Valid Start Date" name="StartDate" required>

        <label style=color:black;font-family:verdana><b>End Date</b></label>
        <input type="text" class="input1" id="datepicker2" placeholder="Enter Valid End Date" name="EndDate" required>
        
        <br>
        <label style=color:black;font-family:verdana><b>Maximum Level</b></label>
        <input type="number" step="1" min="-1" placeholder="Maximum level at which participants are allowed to join event, or -1 for no restrictions" name="MaxLevel" required>
        <br>

        <label style=color:black;font-family:verdana><b>Event Description</b></label>
        <input type="text" class="input1" placeholder="Describe Your Event!" name="EventDesc" required>

        <button class="button10" type="submit">Submit</button>
    </form>
</div>

<div class="container" style="background-color:#EEEEEE">
    <button class="cancelbutton" onclick="window.location.href='index.php'">Cancel</button>
</div>


</body>
</html>