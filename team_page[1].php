<!--PURPOSE: MAIN TEAM PAGE. ONLY REACHABLE WITH AN (team_name, event_name, event_start, event_end) argument.-->
<?php
session_start();
include 'matrix.php';
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

    <title>Team Page</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="../../assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">
    <style>
        .heatmap_table {
            width : 90%;
            border-style : solid;
        }
    </style>

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]>
    <script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="../../assets/js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
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
//get GET variables
$team_name = htmlspecialchars($_GET['team_name']);
$event_name = htmlspecialchars($_GET['event_name']);
$event_start = htmlspecialchars($_GET['event_start']);
$event_end = htmlspecialchars($_GET['event_end']);
//query for our team and populate all releant variables
$sql="SELECT * FROM Team WHERE name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
$res=mysql_query($sql);
if (mysql_num_rows($res) == 0) {
    die("No such team ($team_name, $event_name, $event_start, $event_end) exists.");
}
$data=mysql_fetch_assoc($res);
$team_leader_id = $data['leader_id'];
$team_desc = $data['description'];

$sql="SELECT * FROM Event WHERE name = '$event_name' AND start = '$event_start' AND end = '$event_end'";
$res=mysql_query($sql);
if (mysql_num_rows($res) == 0) {
    die("No such event ($event_name, $event_start, $event_end) exists.");
}
$data=mysql_fetch_assoc($res);
$event_desc = $data['description'];
$event_organizer_id = $data['organizer_id'];
$event_max_skill = $data['max_skill'];

$status_str = ["Upcoming", "Active", "Expired"];
$status = 0;
$sql="SELECT (start < NOW()) AS active, (end < NOW()) AS expired FROM Event WHERE name = '$event_name' AND start = '$event_start' AND end = '$event_end'";
$res=mysql_query($sql);
if (!$res) {
    print("event status query failure.");
}
if (mysql_num_rows($res) == 0) {
    print("No such event ($event_name, $event_start, $event_end) exists.");
}
$data=mysql_fetch_assoc($res);
if ($data['active'] == "1") {
    $status = 1;
}
if ($data['expired'] == "1") {
    $status = 2;
}


?>
<?php
//update posts heatmap array
//day: 0-23 (in hours)
//week: 0-6 (in days of the week)
$hrs = ["Mid", "1AM", "2AM", "3AM", "4AM", "5AM", "6AM", "7AM", "8AM", "9AM", "10AM", "11AM", "Noon", "1PM", "2PM", "3PM", "4PM", "5PM", "6PM", "7PM", "8PM", "9PM", "10PM", "11PM"];
$dotw = ["Sun", "Mon", "Tues", "Wed", "Thurs", "Fri", "Sat"];
$heat_color = [130, 255, 150];
$sql = "SELECT *
        FROM Team_Update
        WHERE team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'
        ORDER BY posted_time DESC";
$res = mysql_query($sql);
$day_heat = array();
$week_heat = array();
for ($i = 0; $i < 24; $i++) {
    $day_heat[$i] = 0;
}
for ($i = 0; $i < 6; $i++) {
    $week_heat[$i] = 0;
}
while ($data = mysql_fetch_assoc($res)) {
    $time = strtotime($data['posted_time']);
    $hour = (int)date("G", $time);
    $day_heat[(int)date("G", $time)]++;
    $week_heat[(int)date("w", $time)]++;
}
$day_max = max($day_heat);
$week_max = max($week_heat);
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <?php
            //should only appear if a user is logged in and is not already a part of this team and can actually join
            if ($_SESSION['valid'] === true) {
                $sql="SELECT * FROM Member WHERE user_id = '{$_SESSION['id']}' AND team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
                $res=mysql_query($sql);
                if (mysql_num_rows($res) == 0) {
                    $user_min_skill = 0;
                    $sql = "SELECT MIN(score) FROM Has_Skill WHERE user_id = '{$_SESSION['id']}'";
                    $res = mysql_query($sql);
                    if (mysql_num_rows($res) == 0) {
                        die("Catastrophic failure: Failed to get skill list");
                    }
                    $data=mysql_fetch_assoc($res);
                    $user_min_skill = $data['MIN(score)'];
                    if ($user_min_skill <= $event_max_skill && $_SESSION['id'] !== $event_organizer_id && $status < 2) {
                        print("<ul class=\"nav nav-sidebar\">");
                        print("<li><a href=\"team_request.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Request to join</a></li>");
                        print("</ul>");
                    }
                }
                else {
                    print("<ul class=\"nav nav-sidebar\">");
                    print("<li><a>You cannot join this team.</a></li>");
                    print("</ul>");
                }
            }
            ?>
            <ul class="nav nav-sidebar">
                <?php
                print("<li class=\"active\"><a href=\"team_page.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Team Overview<span class=\"sr-only\">(current)</span></a></li>");
                ?>
            </ul>
            <ul class="nav nav-sidebar">
                <?php
                print("<li><a href=\"team_members.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Current Members</a></li>");
                ?>
            </ul>

            <!-- Leader functions only -->
            <ul class="nav nav-sidebar">
                <?php
                if ($_SESSION['valid'] === true) {
                    $sql="SELECT * FROM Member WHERE user_id = '{$_SESSION['id']}' AND team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
                    $res=mysql_query($sql);
                    //leader functions. also, to edit the team, the event must still be active.
                    if ($_SESSION['id'] === $team_leader_id && $status < 2) {
                        print("<li><a href=\"user_search.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Search For New Members</a></li>");
                        print("<li><a href=\"team_edit.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Edit Team</a></li>");
                        print("<li><a href=\"team_view_join_requests.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">View Requests to join</a></li>");
                        print("<li><a href=\"team_view_recommended_members.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">View recommended members</a></li>");

                    }
                }
                ?>
            </ul>
            <!--event admin functions only-->
            <ul class="nav nav-sidebar">
                <?php
                //if the event has expired, we can assign bonuses to teams.
                if ($_SESSION['valid'] === true && $_SESSION['id'] === $event_organizer_id) {
                    if ($status === 2) {
                        print("<li><a href=\"team_reward.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Reward Team</a></li>");
                    }
                    else{
                        print("<li><a>You cannot reward this team.</a></li>");
                    }
                }
                ?>
            </ul>
        </div>
        <!--MAIN CONTENT-->
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <?php
            //search for user traits first, because our layout depends on it
            $team_trait_vector = array();
            $trait_name_vector = array();
            $name_color = "rgba(0, 0, 0, 0)";
            $name_desc = "";
            $i = 0;
            //get average trait values of team
            $sql1 = "SELECT * FROM Has_Trait";
            $sql = "SELECT trait_name, SUM(score) FROM Member NATURAL JOIN ($sql1) AS S1 WHERE team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end' GROUP BY trait_name ORDER BY trait_name ASC";
            $res = mysql_query($sql);
            if (!$res) {
                die("Team trait search failed!");
            }
            if (mysql_num_rows($res) == 0) {
                die("traits for team $team_name not found!");
            }
            while ($data = mysql_fetch_assoc($res)) {
                $team_trait_vector[$i] = $data['score'];
                $trait_name_vector[$i] = $data['trait_name'];
                $i++;
            }
            //if user isn't me, compare it
            $sql="SELECT * FROM Member WHERE user_id = '{$_SESSION['id']}' AND team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
            $res=mysql_query($sql);
            if ($_SESSION['valid'] && mysql_num_rows($res) == 0) {
                //get my user skills
                $my_trait_vector = array();
                $avg_trait_vector = array();
                $i = 0;
                $sql = "SELECT * FROM Has_Trait WHERE user_id = '{$_SESSION['id']}' ORDER BY trait_name ASC";
                $res = mysql_query($sql);
                if (mysql_num_rows($res) == 0) {
                    die("User skills for {$_SESSION['id']} not found!");
                }
                while ($data = mysql_fetch_assoc($res)) {
                    $my_trait_vector[$i] = $data['score'];
                    $avg_trait_vector[$i] = 1;
                    $i++;
                }
                //compare them
                $user_trait_vector_normalized = vec_normalized($team_trait_vector);
                $user_trait_vector_normalized_new = vec_normalized(vec_add($team_trait_vector, $my_trait_vector));
                $avg_trait_vector_normalized = vec_normalized($avg_trait_vector);

                $distance_old = vec_norm(vec_sub($avg_trait_vector_normalized, $user_trait_vector_normalized));
                $distance_new = vec_norm(vec_sub($avg_trait_vector_normalized, $user_trait_vector_normalized_new));

                if ($distance_new > $distance_old) {
                    $name_color = "rgba(255, 0, 0, 0.2)";
                    $name_desc = "This team may not be a good fit for you.";
                }
                else if ($distance_new < $distance_old) {
                    $name_color = "rgba(0, 255, 0, 0.2)";
                    $name_desc = "This team may be a good fit for you.";
                }
            }
            print("<div style='background-color:$name_color' title='$name_desc'><h1 class=\"page-header\">$team_name</h1></div>");
            print("<h3 class=\"sub-header\">Participating in: <a href='event_page.php?event_name=$event_name&event_start=$event_start&event_end=$event_end'>$event_name</a></h3>");
            print("<p>$team_desc</p>");
            ?>
            <!--posting form-->
            <?php
            if ($_SESSION['valid']) {
                $sql="SELECT * FROM Member WHERE user_id = '{$_SESSION['id']}' AND team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
                $res=mysql_query($sql);
                if (mysql_num_rows($res) > 0) {
                    print("<form action=\"team_update_submit.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\" method=\"post\" target=\"_top\">");
                        print("<input style=\"width:100%\" type=\"text\" placeholder=\"Update your friends!\" name=\"post_description\" required>");
                        print("<button type=\"submit\" class='button'>Post it</button>");
                    print("</form>");
                }
            }
            ?>
            <h2 class="sub-header">Activity Map</h2>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <?php
                        for ($i = 0; $i < 24; $i++) {
                            print("<th style='width: 4.166667%; text-align:center'>{$hrs[$i]}</th>");
                        }
                        ?>
                    </tr>
                    <tr>
                        <?php
                        for ($i = 0; $i < 24; $i++) {
                            $alpha = ($day_heat[$i]/$day_max);
                            print("<td style='width: 4.166667%;color: #696969; background-color: rgba({$heat_color[0]}, {$heat_color[1]}, {$heat_color[2]}, $alpha); text-align:center'>{$day_heat[$i]}</td>");
                        }
                        ?>
                    </tr>
                </table>
                <table class="table table-bordered">
                    <tr>
                        <?php
                        for ($i = 0; $i < 7; $i++) {
                            print("<th style='width: 14.285714%; text-align:center'>$dotw[$i]</th>");
                        }
                        ?>
                    </tr>
                    <tr>
                        <?php
                        for ($i = 0; $i < 7; $i++) {
                            $alpha = ($week_heat[$i]/$week_max);
                            print("<td style='width: 14.285714%; color: #696969; background-color: rgba({$heat_color[0]}, {$heat_color[1]}, {$heat_color[2]}, $alpha); text-align:center'>{$week_heat[$i]}</td>");
                        }
                        ?>
                    </tr>
                </table>
            </div>
            <h2 class="sub-header">Details</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Time</th>
                        <th>Poster</th>
                        <th>Post</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    //query for event updates
                    /*
                    $sql = "SELECT *
                            FROM Team_Update
                            WHERE team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'
                            ORDER BY posted_time DESC";
                     */
                    $sql1 = "SELECT id AS poster_id, name AS poster_name FROM User";
                    $sql = "SELECT *
                            FROM Team_Update NATURAL JOIN ($sql1) AS S1
                            WHERE team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'
                            ORDER BY posted_time DESC";
                    $res = mysql_query($sql);
                    while ($data = mysql_fetch_assoc($res)) {
                        print("<tr>");
                        print("<td>{$data['posted_time']}</td>");
                        print("<td><a href=\"user_page.php?user_id={$data['poster_id']}\">{$data['poster_name']}</a></td>");
                        print("<td>{$data['description']}</td>");
                        print("</tr>");
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="../../dist/js/bootstrap.min.js"></script>
<!-- Just to make our placeholder images work. Don't actually copy the next line! -->
<script src="../../assets/js/vendor/holder.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>