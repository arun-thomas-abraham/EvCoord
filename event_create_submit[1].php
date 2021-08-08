<?php
session_start();
$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('eventcoord_new411');
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

    <title>Event Creation</title>

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
            color: white;
            margin: 8px 0;
            border: none;
            cursor: pointer;
        }

        .container {
            padding: 16px;
        }


    </style>
</head>
<body>
<h2>
    <?php
    if ($_SESSION['valid'] == true) {
        include 'logoutNav.php';

    } else {
        die("you must be logged in to create an event.");
    }
    ?>
</h2>
<?php
session_start();

//grab variables from our html forms
$eventName = mysql_real_escape_string($_POST["EventName"]);
$startDate = $_POST["StartDate"];
$endDate = $_POST["EndDate"];
$eventDesc = mysql_real_escape_string($_POST["EventDesc"]);
$maxLevel = $_POST["MaxLevel"];

//format them as strings
$startDate = date('Y-m-d', strtotime($startDate));
$endDate = date('Y-m-d', strtotime($endDate));
$startString = explode('-', $startDate, 3);
$startYear = $startString[0];
$startMonth = $startString[1];
$startDay = $startString[2];
$endString = explode('-', $endDate, 3);
$endYear = $endString[0];
$endMonth = $endString[1];
$endDay = $endString[2];

//validate the two dates, pretty sure this isn't needed if the source is a proper date object
$d = checkdate($startMonth, $startDay, $startYear);
if ($d != True) {
    die("you have specified an invalid date [$startDate] [$startMonth] [$startDay] [$startYear] ");
}
$d = checkdate($endMonth, $endDay, $endYear);
if ($d != True) {
    die("you have specified an invalid date [$endDate] [$endMonth] [$endDay] [$endYear]");
}

//check for date correctness
$startDate_formatted = new DateTime($startDate);
$endDate_formatted = new DateTime($endDate);
if ($startDate > $endDate) {
    die("your starting date [$startDate] must be earlier than your ending date [$endDate].");
}

$sql = "SELECT * FROM Event WHERE name = '$eventName' AND start = '$startDate' AND end = '$endDate'";
$res = mysql_query($sql);
if (mysql_num_rows($res) != 0) {
    die("Event of the same name and start date already exists");
} else {
    $sql = "INSERT INTO Event(name, start, end, description, organizer_id, max_skill) VALUES ('$eventName', '$startDate', '$endDate', '$eventDesc', '{$_SESSION['id']}', '$maxLevel')";
    $res2 = mysql_query($sql);

    if (!$res2) {
        print ("Event Creation failed.");
    } else {
        $Color = "orange";
        print("<p class=\"container\" style=\"color:black;\">'{$_SESSION['name']}', you have successfully created the event:</p>");
        print("<div class=\"container\" style=\"Color:$Color\';font-size:20px\">$eventName, active from $startDate to $endDate</div>");
    }
}
mysql_close();
?>
<br/>
<div class="container" style="background-color:#EEEEEE">
    <button class="cancelbutton" onclick="window.location.href='user_page.php'">Return to Home</button>
</div>
</body>
</html>