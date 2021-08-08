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

    <title>Event Update</title>

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
        die("you must be logged in to create a post.");
    }
    ?>
</h2>
<?php
$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('eventcoord_new411');

$event_name = htmlspecialchars($_GET['event_name']);
$event_start = htmlspecialchars($_GET['event_start']);
$event_end = htmlspecialchars($_GET['event_end']);
//query for our user
$sql="SELECT * FROM Event WHERE name = '$event_name' AND start = '$event_start' AND end = '$event_end'";
$res=mysql_query($sql);
if (mysql_num_rows($res) == 0) {
    die("No such event ($event_name, $event_start, $event_end) exists.");
}
$data=mysql_fetch_assoc($res);
$event_desc = $data['description'];
$event_organizer_id = $data['organizer_id'];

if ($_SESSION['id'] !== $event_organizer_id) {
    die("you do not have permission to post on this event.");
}
$post_description = mysql_real_escape_string($_POST["post_description"]);
$sql = "INSERT INTO Event_Update(event_name, event_start, event_end, posted_time, description)
        VALUES ('$event_name', '$event_start', '$event_end', NOW(), '$post_description')";
$res = mysql_query($sql);
if (!$res) {
    print ("Post submission of [$post_description] failed.");
}
else {
    print("Post successful!");
}
mysql_close();
?>
<br/>
<div class="container" style="background-color:#EEEEEE">
    <?php
    print("<button class=\"cancelbutton\" onclick=\"window.location.href='event_page.php?event_name=$event_name&event_start=$event_start&event_end=$event_end'\">Return to Event</button>");
    ?>
</div>
</body>
</html>