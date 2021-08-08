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

    <title>View Invitations</title>

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
            cursor: pointer
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
        die("you must be logged in to join a team");
    }
    ?>
</h2>


<?php
$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('eventcoord_new411');

//get all relevant values
//I assume they'll return null if it's not found in the get list
$user_id = htmlspecialchars($_GET['user_id']);
$team_name = htmlspecialchars($_GET['team_name']);
$event_name = htmlspecialchars($_GET['event_name']);
$event_start = htmlspecialchars($_GET['event_start']);
$event_end = htmlspecialchars($_GET['event_end']);

//if no user is specified and nobody is logged in, throw an error.
if ($user_id == null && $_SESSION['valid'] == false) {
    die("nothing to see here!");
}

//security check: disregard team data if we're not authorized
//not logged in
if ($_SESSION['valid'] == false) {
    $team_name = null;
    $event_name = null;
    $event_start = null;
    $event_end = null;
} else {
    //set default user if logged in session is valid
    if ($user_id == null) {
        $user_id = $_SESSION['id'];
    }
    //check if our id is the leader
    $sql = "SELECT * FROM Team WHERE leader_id = '{$_SESSION['id']}' AND name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
    $res = mysql_query($sql);
    if (mysql_num_rows($res) == 0) {
        $team_name = null;
        $event_name = null;
        $event_start = null;
        $event_end = null;
    }
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
    $user_min_skill = 0;
    $sql = "SELECT MIN(score) FROM Has_Skill WHERE user_id = '$user_id'";
    $res = mysql_query($sql);
    if (mysql_num_rows($res) == 0) {
        die("Catastrophic failure: Failed to get skill list from $user_id");
    }
    $data=mysql_fetch_assoc($res);
    $user_min_skill = $data['MIN(score)'];
}
?>
    <div class="row">
        <!-- sidebar -->
        <div class="col-sm-3 col-md-2 sidebar">
            <!-- If not user who owns this page only and we have a 'teamname', 'eventname' and 'eventstart' and 'eventend' (meaning, we have a team to invite him to) on our GET-->
            <!-- also, we are not allowed to invite the event organizer. For obvious reasons-->
            <?php
            if ($_SESSION['valid'] && $user_id != null && $team_name != null && $event_name != null && $event_start != null && $event_end != null) {
                if ($user_id != $event_organizer_id && $user_min_skill <= $event_max_skill) {
                    print('<ul class="nav nav-sidebar">');
                    print("<li><a href=\"user_invite.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end&user_id=$user_id\">Invite to Team</a></li>");
                    print('</ul>');
                }
                else {
                    print('<ul class="nav nav-sidebar">');
                    print("<li><a>You cannot invite this user.</a></li>");
                    print('</ul>');
                }
            }

            //Viewable to anyone
            print("<ul class=\"nav nav-sidebar\">");
            $user_id_get = urldecode($user_id);
            print("<li><a href=\"user_page.php?$user_id_get\">User Overview<span class=\"sr-only\">(current)</span></a></li>");
            print("</ul>");

            //Viewable to user who owns page
            if ($_SESSION['valid'] === true && $_SESSION['id'] === $user_id) {
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"user_edit.php\">Edit Profile </a></li>");
                print('</ul>');
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"user_teams.php?user_id=$user_id_get\">View Teams</a></li>");
                print("<li><a href=\"user_events.php?user_id=$user_id_get\">View Events</a></li>");
                print("<li class=\"active\"><a href=\"user_view_invites.php?user_id=$user_id_get\">View Invitations</a></li>");
                print('</ul>');
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"vis.php?user_id=$user_id_get\">View Data Visualization</a></li>");
                print('</ul>');
            }
            ?>
        </div>

<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
    <h2>Pending Invitations</h2>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Event Name</th>
                <th>Event Start</th>
                <th>Event End</th>
                <th>Team Name</th>
                <th>Role</th>
                <th>Description</th>
                <th>Accept</th>
                <th>Reject</th>
            </tr>
            </thead>
            <tbody>
            <?php
                $person_id = $_SESSION['id'];
                $sql ="SELECT * FROM Invite WHERE user_id = '$person_id'";
                $res = mysql_query($sql);
                    if (mysql_num_rows($res) == 0) {
                        die("You have no pending invitations");
                    }
                while ($data = mysql_fetch_assoc($res)){
                    $event_name = $data['event_name'];
                    $event_start = $data['event_start'];
                    $event_end = $data['event_end'];
                    $team_name = $data['team_name'];
                    $requested_skill = $data['skill_requested'];
                    $description = $data['description'];
                    print("<tr>");
                    print("<th>$event_name</th>");
                    print("<th>$event_start</th>");
                    print("<th>$event_end</th>");
                    print("<th>$team_name</th>");
                    print("<th>$requested_skill</th>");
                    print("<th>$description</th>");
                    print("<th><a href=\"user_invite_accept.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end&skill_name=$requested_skill\">accept</a></th>");
                    print("<th><a href=\"user_invite_reject.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end&skill_name=$requested_skill\">reject</a></th>");
                    print("</tr>");
                } 
            ?>
            </tbody>
        </table>
    </div>
</div>
<?php
    mysql_close();
?>
<br/>
</body>
</html>