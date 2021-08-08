<?php
session_start();
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

    <title>Team Creation</title>

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
        include 'loginNav.php';
    }
    ?>
</h2>
<?php
session_start();
//connect to database
$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('eventcoord_new411');

//grab variables from our html forms
$teamDesc = mysql_real_escape_string($_POST["teamDesc"]);
$teamName = mysql_real_escape_string($_POST["teamName"]);
$roleName = $_POST["roleName"];
$roleDesc = mysql_real_escape_string($_POST["roleDesc"]);

//get GET variables
$event_name = htmlspecialchars($_GET['event_name']);
$event_start = htmlspecialchars($_GET['event_start']);
$event_end = htmlspecialchars($_GET['event_end']);
//query for our event
 $sql="SELECT * FROM Event WHERE name = '$event_name' AND start = '$event_start' AND end = '$event_end'";
    $res=mysql_query($sql);
    if (mysql_num_rows($res) > 0) {
        $data = mysql_fetch_assoc($res);
        $event_desc = $data['description'];
        $event_organizer_id = $data['organizer_id'];
        $event_max_skill = $data['max_skill'];

        $status_str = ["Upcoming", "Active", "Expired"];
        $status = 0;
        $event_start_formatted = new DateTime($event_start);
        $event_end_formatted = new DateTime($event_end);
        $now_datetime = new DateTime();
        if ($now_datetime >= $event_start_formatted) {
            $status = 1;
        }
        if ($now_datetime > $event_end_formatted) {
            $status = 2;
        }
    }
//disallow team creation if the user is too skilled
$user_max_skill = 0;
$sql = "SELECT MAX(score) FROM Has_Skill WHERE user_id = '{$_SESSION['id']}'";
$res = mysql_query($sql);
if (mysql_num_rows($res) == 0) {
    die("Catastrophic failure: Failed to get skill list");
}
$data=mysql_fetch_assoc($res);
$user_max_skill = $data['MAX(score)'];
if (($user_max_skill > $event_max_skill) || $status === 2 || $_SESSION['id'] == $event_organizer_id) {
    die("you are prohibited from participating in this event.");
}

//validate role
$sql = "SELECT * FROM Skill WHERE name = '$roleName'";
$res = mysql_query($sql);
if (mysql_num_rows($res) == 0) {
    die("you have specified an unsupported role.");
}

$sql = "SELECT * FROM Team WHERE name = '$teamName' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
$res = mysql_query($sql);
if (mysql_num_rows($res) > 0) {
    die("Your team was already taken.");
}
$sql = "INSERT INTO Team(name, event_name, event_start, event_end, description, leader_id) VALUES ('$teamName', '$event_name', '$event_start', '$event_end', '$teamDesc', '{$_SESSION['id']}')";
$res2 = mysql_query($sql);
if (!$res2) {
    die("Team addition failed");
}
$sql = "INSERT INTO Member(user_id, team_name, event_name, event_start, event_end, skill_name, skill_desc) VALUES ('{$_SESSION['id']}', '$teamName', '$event_name', '$event_start', '$event_end', '$roleName', '$roleDesc')";
$res3 = mysql_query($sql);
if (!$res3) {
    die("Member addition failed");
}

$Color = "black";
echo '<p class="container" style="color:black;">' . $_SESSION['name'] . ', you have successfully formed the team:</p> ';
echo '<div class="container" style="Color:' . $Color . ';font-size:20px">' . $teamName . '</div>';

mysql_close();
?>
<br/>
<div class="container" style="background-color:#EEEEEE">
    <?php
    print("<button class=\"cancelbutton\" onclick=\"window.location.href='event_page.php?event_name=$event_name&event_start=$event_start&event_end=$event_end'\">Back</button>");
    ?>
</div>
</body>
</html>