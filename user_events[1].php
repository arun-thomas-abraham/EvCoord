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

    <title>Your Events</title>

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
if ($_SESSION['valid'] === false || $user_id !== $_SESSION['id']) {
    die("unauthorized to view $user_id's profile!");
}
?>
<div class="container-fluid">
    <div class="row">
        <!-- sidebar -->
        <div class="col-sm-3 col-md-2 sidebar">
            <!-- If not user who owns this page only and we have a 'teamname', 'eventname' and 'eventstart' and 'eventend' (meaning, we have a team to invite him to) on our GET-->
            <?php
            //Viewable to anyone
            print("<ul class=\"nav nav-sidebar\">");
            $user_id_get = urldecode($user_id);
            print("<li><a href=\"user_page.php?$user_id_get\">User Overview<span class=\"sr-only\">(current)</span></a></li>");
            print("</ul>");

            //Viewable to user who owns page
            if ($_SESSION['valid'] === true && $_SESSION['id'] === $user_id) {
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"user_edit.php?\">Edit Profile </a></li>");
                print('</ul>');
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"user_teams.php?user_id=$user_id_get\">View Teams</a></li>");
                print("<li class=\"active\"><a href=\"user_events.php?user_id=$user_id_get\">View Events</a></li>");
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
            print("<h1 class=\"page-header\">$user_name</h1>");
            print("<p>$user_description</p>");
            ?>
            <h2 class="sub-header">Active Events</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Event</th>
                        <th>Start</th>
                        <th>End</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    //search for current teams
                    $sql = "SELECT * FROM Event WHERE organizer_id = '$user_id' AND end >= NOW()";
                    $res = mysql_query($sql);
                    while ($data = mysql_fetch_assoc($res)) {
                        $event_name = $data['name'];
                        $event_start = $data['start'];
                        $event_end = $data['end'];
                        print("<tr>");
                        print("<td><a href=\"event_page.php?&event_name=$event_name&event_start=$event_start&event_end=$event_end\">$event_name</a></td>");
                        print("<td>$event_start</td>");
                        print("<td>$event_end</td>");
                        print("</tr>");
                        print("</a>");
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <h2 class="sub-header">Expired Events</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Event</th>
                        <th>Start</th>
                        <th>End</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    //search for current teams
                    $sql = "SELECT * FROM Event WHERE organizer_id = '$user_id' AND end < NOW()";
                    $res = mysql_query($sql);
                    while ($data = mysql_fetch_assoc($res)) {
                        $event_name = $data['name'];
                        $event_start = $data['start'];
                        $event_end = $data['end'];
                        print("<tr>");
                        print("<td><a href=\"event_page.php?&event_name=$event_name&event_start=$event_start&event_end=$event_end\">$event_name</a></td>");
                        print("<td>$event_start</td>");
                        print("<td>$event_end</td>");
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