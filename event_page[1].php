<!--PURPOSE: MAIN EVENT PAGE. ONLY REACHABLE WITH AN (event_name, event_start, event_end) argument.-->
<?php
session_start();
//set up arguments
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

    <title>Event Page</title>

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
<!--header-->
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
$event_name = htmlspecialchars($_GET['event_name']);
$event_start = htmlspecialchars($_GET['event_start']);
$event_end = htmlspecialchars($_GET['event_end']);
//query for our event and populate all releant variables
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
        <!--sidebar-->
        <div class="col-sm-3 col-md-2 sidebar">
            <!--EVENT OVERVIEW-->
            <ul class="nav nav-sidebar">
                <?php
                print("<li class=\"active\"><a href=\"event_page.php?event_name=$event_name&event_start=$event_start&event_end=$event_end\">Event Overview <span class=\"sr-only\">(current)</span></a></li>");
                ?>
            </ul>
            <!--CREATE TEAM-->
            <?php
            if ($_SESSION['valid'] === true && $status < 2 && $event_organizer_id !== $_SESSION['id']) {
                //disallow team creation if the user is too skilled
                $user_min_skill = 0;
                $sql = "SELECT MIN(score) FROM Has_Skill WHERE user_id = '{$_SESSION['id']}'";
                $res = mysql_query($sql);
                if (mysql_num_rows($res) == 0) {
                    die("Catastrophic failure: Failed to get skill list");
                }
                $data=mysql_fetch_assoc($res);
                $user_min_skill = $data['MIN(score)'];
                if ($user_min_skill <= $event_max_skill) {
                    print("<ul class=\"nav nav-sidebar\">");
                        print("<li><a href=\"team_create.php?event_name=$event_name&event_start=$event_start&event_end=$event_end\">Create Team</a></li>");
                    print("</ul>");
                }
            }
            ?>
            <!--SEARCH TEAMS-->
            <ul class="nav nav-sidebar">
                <?php
                print("<li><a href=\"team_search.php?event_name=$event_name&event_start=$event_start&event_end=$event_end\">Search Participating Teams</a></li>");
                ?>
            </ul>
            <?php
            if ($_SESSION['valid'] === true && $status == 0 && $event_organizer_id === $_SESSION['id']) {
                print("<ul class=\"nav nav-sidebar\">");
                print("<li><a href=\"event_edit.php?event_name=$event_name&event_start=$event_start&event_end=$event_end\">Edit Event</a></li>");
                print("</ul>");
            }
            ?>
        </div>
        <!--main content-->
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <?php
            print("<h1 class=\"page-header\">$event_name</h1>");
            print("<p>{$status_str[$status]} event from: $event_start to $event_end</p>");
            print("<br/>");
            print("<p>$event_desc</p>");
            ?>
            <h2 class="sub-header">Event Timeline</h2>
            <?php
            if ($_SESSION['valid'] && $_SESSION['id'] === $event_organizer_id) {
                print("<form action=\"event_update_submit.php?event_name=$event_name&event_start=$event_start&event_end=$event_end\" method=\"post\" target=\"_top\">");
                    print("<input type=\"text\" placeholder=\"put yer post in\" name=\"post_description\" required>");
                    print("<button type=\"submit\">Post it</button>");
                print("</form>");
            }
            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Time</th>
                        <th>Post</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    //query for event updates
                    $sql = "SELECT *
                            FROM Event_Update
                            WHERE event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'
                            ORDER BY posted_time DESC";
                    $res = mysql_query($sql);
                    while ($data = mysql_fetch_assoc($res)) {
                        print("<tr>");
                            print("<td>{$data['posted_time']}</td>");
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
