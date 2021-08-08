<?php
session_start();
if ($_SESSION['valid'] != true) {
    die("failure: you must be logged in to request to join a team.");
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

    <title>Request to join a team</title>
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
//connect to database
$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('eventcoord_new411');

//get GET values
$team_name = htmlspecialchars($_GET['team_name']);
$event_name = htmlspecialchars($_GET['event_name']);
$event_start = htmlspecialchars($_GET['event_start']);
$event_end = htmlspecialchars($_GET['event_end']);
$user_id = $_SESSION['id'];

//get team info
$sql="SELECT * FROM Team WHERE name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
$res=mysql_query($sql);
if (mysql_num_rows($res) == 0) {
    die("No such team ($team_name, $event_name, $event_start, $event_end) exists.");
}

$data=mysql_fetch_assoc($res);
$team_leader_id = $data['leader_id'];
$team_desc = $data['description'];
//get event info
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

//security checks
if ($status > 1) {
    die("you cannot request to join a team for an expired event");
}

//disallow team creation if the user is too skilled
$user_min_skill = 0;
$sql = "SELECT MIN(score) FROM Has_Skill WHERE user_id = '{$_SESSION['id']}'";
$res = mysql_query($sql);
if (mysql_num_rows($res) == 0) {
    die("Catastrophic failure: Failed to get skill list");
}
//
$data=mysql_fetch_assoc($res);
$user_min_skill = $data['MIN(score)'];
if ($user_min_skill > $event_max_skill || $status === 2 || $_SESSION['id'] == $event_organizer_id) {
    die("you are prohibited from participating in this event.");
}

//testing soft death i.e. still prints out all the necessary navigation UI: please use this instead in the future for errors that you 'expect' to be triggered
$sql="SELECT * FROM Join_Request WHERE user_id = '$user_id' AND team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
$res=mysql_query($sql);
if (mysql_num_rows($res) > 0) {
    print("You already have a pending request to join this team. If you wish to request to join in a different role, please cancel your original request.");
    print("<br/>");
    print("<button class=\"cancelbutton\" onclick=\"window.location.href='team_page.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end'\">Back</button>");
    die();
}
?>

<div class="container">
    <?php
    print("<h1>Request to join team: $team_name</h1>");
    print("<form action=\"team_request_submit.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end&user_id=$user_id\" method=\"post\" target=\"_top\">");
    ?>
        <label style=color:black;font-family:verdana><b>Role</b></label>
        <select name="roleName">
            <?php
            //query available roles
            $sql = "SELECT DISTINCT skill_name AS name
                FROM Has_Skill
                WHERE score <= $event_max_skill AND user_id = '$user_id'";
            $res = mysql_query($sql);
            //REMEMBER THAT THE CHECK FOR MAX LEVEL WAS ALSO SUPPOSED TO BE DONE IN THE SEARCHING STAGE. THAT IS, THERE SHOULD ALWAYS BE AT LEAST 1 ROLE THAT IS LESS THAN EVENT_MAX_SKILL.
            if (mysql_num_rows($res) == 0) {
                die("Catastrophic failure: You cannot join this team.");
            }

            //populate drop down list
            while ($data = mysql_fetch_assoc($res)) {
                print("<option value = \"{$data['name']}\">{$data['name']}</option>");
            }
            ?>
        </select>
        <label style=color:black;font-family:verdana><b>Message</b></label>
        <input type="text" class="input1" placeholder="A message of request." name="description" required>

        <button class="button10" type="submit">Request to Join</button>
    </form>
</div>

<div class="container" style="background-color:#EEEEEE">
    <?php
    print("<button class=\"cancelbutton\" onclick=\"window.location.href='team_page.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end'\">Back</button>");
    ?>
</div>


</body>
</html>