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

    <title>User Page</title>

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
</head>

<body>
<!-- top navigation header? -->
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
<div class="container-fluid">
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
            print("<li class=\"active\"><a href=\"user_page.php?$user_id_get\">User Overview<span class=\"sr-only\">(current)</span></a></li>");
            print("</ul>");

            //Viewable to user who owns page
            if ($_SESSION['valid'] === true && $_SESSION['id'] === $user_id) {
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"user_edit.php\">Edit Profile </a></li>");
                print('</ul>');
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"user_teams.php?user_id=$user_id_get\">View Teams</a></li>");
                print("<li><a href=\"user_events.php?user_id=$user_id_get\">View Events</a></li>");
                print("<li><a href=\"user_view_invites.php?user_id=$user_id_get\">View Invitations</a></li>");
                print('</ul>');
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"vis.php?user_id=$user_id_get\">View Data Visualization</a></li>");
                print('</ul>');
            }
            ?>
        </div>

        <!-- main page -->
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <?php
            //after this point, we're guaranteed to have a user id.
            //get this user's info
            $user_name = "";
            $user_description = "";
            //search for user details
            $sql = "SELECT * FROM User WHERE id = '$user_id'";
            $res = mysql_query($sql);
            if (mysql_num_rows($res) == 0) {
                die("User $user_id not found!");
            }
            while ($data = mysql_fetch_assoc($res)) {
                $user_name = $data['name'];
                $user_description = $data['description'];
            }
            //search for user traits first, because our layout depends on it
            $user_trait_vector = array();
            $trait_name_vector = array();
            $name_color = "rgba(0, 0, 0, 0)";
            $name_desc = "";
            $i = 0;
            $sql = "SELECT * FROM Has_Trait WHERE user_id = '$user_id'";
            $res = mysql_query($sql);
            if (mysql_num_rows($res) == 0) {
                die("User traits for $user_id not found!");
            }
            while ($data = mysql_fetch_assoc($res)) {
                $user_trait_vector[$i] = $data['score'];
                $trait_name_vector[$i] = $data['trait_name'];
                $i++;
            }
            //if user isn't me, compare it
            if ($user_id != $_SESSION['id']) {
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
                $user_trait_vector_normalized = vec_normalized($user_trait_vector);
                $user_trait_vector_normalized_new = vec_normalized(vec_add($user_trait_vector, $my_trait_vector));
                $avg_trait_vector_normalized = vec_normalized($avg_trait_vector);

                $distance_old = vec_norm(vec_sub($avg_trait_vector_normalized, $user_trait_vector_normalized));
                $distance_new = vec_norm(vec_sub($avg_trait_vector_normalized, $user_trait_vector_normalized_new));

                if ($distance_new > $distance_old) {
                    $name_color = "rgba(255, 0, 0, 0.2)";
                    $name_desc = "This member may not be a good fit for you.";
                }
                else if ($distance_new < $distance_old) {
                    $name_color = "rgba(0, 255, 0, 0.2)";
                    $name_desc = "This member may be a good fit for you.";
                }
            }
            print("<div style='background-color:$name_color' title='$name_desc'><h1 class=\"page-header\">$user_name</h1></div>");
            print("<p>$user_description</p>");

            print("<h2 class=\"sub-header\">Who am I?</h2>");
            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th colspan="4" style="text-align: center">Skills</th>
                        <th colspan="4" style="text-align: center">Traits</th>
                    </tr>
                    <tr>
                        <th style="text-align: center">Artist</th>
                        <th style="text-align: center">Designer</th>
                        <th style="text-align: center">Misc</th>
                        <th style="text-align: center">Programmer</th>
                        <th style="text-align: center">Friendly</th>
                        <th style="text-align: center">Hardworking</th>
                        <th style="text-align: center">Leader</th>
                        <th style="text-align: center">Teacher</th>

                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        //search for user skills
                        $sql = "SELECT * FROM Has_Skill WHERE user_id = '$user_id' ORDER BY skill_name ASC";
                        $res = mysql_query($sql);
                        if (mysql_num_rows($res) == 0) {
                            die("User skills for $user_id not found!");
                        }
                        while ($data = mysql_fetch_assoc($res)) {
                            print("<td  style=\"text-align: center\">{$data['score']}</td>");
                        }
                        for ($i = 0; $i < count($trait_name_vector); $i++) {
                            print("<td  style=\"text-align: center\">{$user_trait_vector[$i]}</td>");
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!--EVENT BONUS TREND CALCULATION-->
            <?php
            //event bonus trend
            //get each expired team
            $user_events = array();
            $i = 0;
            $sql = "SELECT DISTINCT team_name, event_name, event_start, event_end, 0 AS score
                    FROM Member
                    WHERE user_id = '$user_id' AND event_end < NOW()
                    ORDER BY event_end DESC, event_start DESC, event_name ASC, team_name ASC
                    LIMIT 5";
            $res = mysql_query($sql);
            if (!$res) {
                die("Member query failed");
            }
            if (mysql_num_rows($res) < 3) {
                $num = mysql_num_rows($res);
                print("<p>You must have at least 3 graded events. You have $num</p>");
            }
            else {
                $user_events = array();
                $i = 0;
                while ($data = mysql_fetch_assoc($res)) {
                    $user_events[$i] = array();
                    $user_events[$i]['team_name'] = $data['team_name'];
                    $user_events[$i]['event_name'] = $data['event_name'];
                    $user_events[$i]['event_start'] = $data['event_start'];
                    $user_events[$i]['event_end'] = $data['event_end'];
                    $user_events[$i]['score'] = $data['score'];
                    $i = $i + 1;
                }
                //accumulate the score for each members teams
                $sql = "SELECT DISTINCT team_name, event_name, event_start, event_end, score
                    FROM Event_Bonus
                    WHERE (team_name, event_name, event_start, event_end) IN (SELECT team_name, event_name, event_start, event_end FROM Member WHERE user_id = '$user_id' AND event_end < NOW())
                    ORDER BY event_end DESC";
                $res = mysql_query($sql);
                if (!$res) {
                    die("uh oh");
                }
                while ($data = mysql_fetch_assoc($res)) {
                    for ($i = 0; $i < count($user_events); $i++) {
                        if ($user_events[$i]['team_name'] == $data['team_name'] &&
                            $user_events[$i]['event_name'] == $data['event_name'] &&
                            $user_events[$i]['event_start'] == $data['event_start'] &&
                            $user_events[$i]['event_end'] == $data['event_end']
                        ) {
                            $user_events[$i]['score'] = $data['score'];
                        }
                    }
                }
                $n = count($user_events);
                if ($_SESSION['valid'] && $user_id == $_SESSION['id']) {
                    //assemble score matrix
                    //notice the $b[$n-$i] because we want the latest event to be the rightmost "last" point, which is the opposite of what we stored in sorted form.
                    $b = mat_zeros($n, 1);
                    for ($i = 0; $i < $n; $i++) {
                        $b[$n - $i - 1][0] = (int)$user_events[$i]['score'];
                    }
                    //set up interpolation matrix
                    $A = mat_zeros($n, 2);
                    for ($i = 0; $i < $n; $i++) {
                        $A[$i][0] = 1;
                        $A[$i][1] = $i;
                    }
                    //solve for fitted line
                    $At = mat_transpose($A);
                    $AtA = mat_dot($At, $A);
                    $Atb = mat_dot($At, $b);
                    list($P, $L, $U) = mat_lu($AtA);
                    $x = mat_plu_solve($P, $L, $U, $Atb);

                    $user_bonus_avg = 0;
                    for ($i = 0; $i < $n; $i++) {
                        $user_bonus_avg += $user_events[$i]['score'];
                    }
                    $user_bonus_avg /= $n;
                    $user_bonus_slope = $x[1][0];
                    //feed it into a document so js can pick up on it
                    //goes team_name,event_name,event_start,event_end,score
                    //then the last value is the intercept, slope, avg for the line.
                    print("<div id=\"user_bonus_array\" style=\"display: none;\">");
                    for ($i = $n-1; $i >= 0; $i--) {
                        print("{$user_events[$i]['team_name']},{$user_events[$i]['event_name']},{$user_events[$i]['event_start']},{$user_events[$i]['event_end']},{$user_events[$i]['score']};");
                    }
                    print("{$x[0][0]},{$x[1][0]},$user_bonus_avg");
                    print("</div>");
                    print("<h2 class=\"sub-header\">Your Trends</h2>");
                    print("<canvas id=\"user_bonus_plot\" width = \"500\" height = 300 style=\"border:1px solid #000000;\">Your browser does not support the HTML5 canvas tag.</canvas>");
                    //""""analysis""""
                    //average
                    if ($user_bonus_avg > 1.33) {
                        print("<p style='color:#7f7f7f;'>On average, you are likely to win: consider looking for more difficult events in the future.</p>");
                    }
                    else if ($user_bonus_avg < 0.67) {
                        print("<p style='color:#7f7f7f;'>On average, you are unlikely to win: consider looking for less difficult events in the future.</p>");
                    }
                    else {
                        print("<p style='color:#7f7f7f;'>On average, you are equally likely and unlikely to win.</p>");
                    }
                    if ($user_bonus_slope > 0.1725) {
                        print("<p style='color:#ff7100;'>Things are looking good: you are likely to win your next event.</p>");
                    }
                    else if ($user_bonus_slope < -0.1725) {
                        print("<p style='color:#ff7100;'>Things are looking poor: you are unlikely to win your next event.</p>");
                    }
                    else {
                        print("<p style='color:#ff7100;'>Things are looking alright: You have been progressing steadily.</p>");
                    }
                }
            }
            ?>
            <h2 class="sub-header">Trophy List</h2>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Event</th>
                        <th>Team</th>
                        <th>Award</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $achievement_str = array();
                    $achievement_str[1] = "[Honorable Mention]";
                    $achievement_str[2] = "[Winner]";
                    $n = count($user_events);
                    for ($i = 0; $i < $n; $i++) {
                        if ($user_events[$i]['score'] > 0) {
                            print("<tr>");
                            print("<td><a href=\"team_page.php?team_name={$user_events[$i]['team_name']}&event_name={$user_events[$i]['event_name']}&event_start={$user_events[$i]['event_start']}&event_end={$user_events[$i]['event_end']}\">{$user_events[$i]['team_name']}</a></td>");
                            print("<td><a href=\"event_page.php?event_name={$user_events[$i]['event_name']}&event_start={$user_events[$i]['event_start']}&event_end={$user_events[$i]['event_end']}\">{$user_events[$i]['event_name']}</a></td>");
                            $winner_str = $achievement_str[$user_events[$i]['score']];
                            print("<td>$winner_str</td>");
                            print("</tr>");
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
<script src="user_draw_plot.js"></script>
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