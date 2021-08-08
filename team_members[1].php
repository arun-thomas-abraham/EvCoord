<!--PURPOSE: MAIN TEAM PAGE. ONLY REACHABLE WITH AN (team_name, event_name, event_start, event_end) argument.-->
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

    <title>Team Members</title>

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
                print("<li><a href=\"team_page.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Team Overview<span class=\"sr-only\">(current)</span></a></li>");
                ?>
            </ul>
            <ul class="nav nav-sidebar">
                <?php
                print("<li class=\"active\"><a href=\"team_members.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Current Members</a></li>");
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
            print("<h1 class=\"page-header\">$team_name</h1>");
            print("<h3 class=\"sub-header\">Participating in: <a href='event_page.php?event_name=$event_name&event_start=$event_start&event_end=$event_end'>$event_name</a></h3>");
            print("<p>$team_desc</p>");
            ?>
            <h2 class="sub-header">Members</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Member</th>
                        <th>Role</th>
                        <th>Role Description</th>
                        <?php
                            if($status !=2){
                             print("<th>Actions</th>");
                            }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    //query for team members
                    /**
                    $sql = "SELECT *
                    FROM Member
                    WHERE team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
                     */
                    $sql1 = "SELECT id AS user_id, name AS user_name FROM User";
                    $sql = "SELECT *
                            FROM Member NATURAL JOIN ($sql1) AS S1
                            WHERE team_name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
                    $res = mysql_query($sql);
                    while ($data = mysql_fetch_assoc($res)) {
                        $user_id = $data['user_id'];
                        $user_name = $data['user_name'];
                        $skill_name = $data['skill_name'];
                        $skill_desc = $data['skill_desc'];
                        print("<tr>");
                        print("<td><a href=\"user_page.php?user_id=$user_id\">$user_name</td></a>");
                        if ($user_id == $team_leader_id) {
                            print("<td>Leader ($skill_name)</td>");
                        }
                        else {
                            print("<td>$skill_name</td>");
                        }
                        print("<td>$skill_desc</td>");
                        
                        //actions
                        if($status !=2){
                            print("<td>");
                            //actions available to leader
                            if($_SESSION['id'] == $team_leader_id){
                                if($user_id != $_SESSION['id']){
                                    print("<a href=\"team_assign_leadership.php?user_id=$user_id&team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Assign Leadership </a>");
                                    print("<a href=\"team_dismiss_member.php?user_id=$user_id&team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Dismiss </a>");
                                }
                                else {
                                    print("<a href=\"team_member_edit.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Edit</a>");
                                }
                            }
                            //actions available to only to non-leaders
                            if($_SESSION['id'] != $team_leader_id){
                                if($user_id == $_SESSION['id']){
                                    print("<a href=\"team_member_edit.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">Edit</a>");
                                }
                            }
                            print("</td>");
                        }
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