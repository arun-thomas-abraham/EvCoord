<?php
session_start();
?>
<html lang="en-us">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Event Search</title>

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

    <link rel="stylesheet" href="styles.css">
    <style>
        .button10 {
            background-color: #E35E1B;
            color: white;
            padding: 8px;
            border: none;
            cursor: pointer;
            min-width: 6%;
            margin-right: 15px;
            display: inline;
        }

        aside {
            float: left;
            margin: 0;
            padding: 1em;
        }

        main {
            text-align: center;
            border-left: 1px solid gray;
            padding: 1em;
            overflow: hidden;
            min-height: 500px;
        }

        .comment {
            border-top: 1px solid gray;
            padding: 1em;
        }

        .box {
            margin: auto;
            padding: 10px;
            min-height: 300px;
            border: 1px solid white;
        }

        .box_inner {
            margin: auto;
            padding: 10px;
            border: 1px solid white;
        }

        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(1) {
            background-color: #152438;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <h2 class="page-header"> Can't find an event you are looking for? Use the search bar below!
            <?php
            if ($_SESSION['valid'] == true) {
                include 'logoutNav.php';

            } else {
                include 'loginNav.php';
            }
            ?>
        </h2>
    </div>
</div>

<?php
//connect to database
$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('eventcoord_new411');
//get POST variables
$search_name=mysql_real_escape_string($_POST["name"]);
$search_start=mysql_real_escape_string($_POST["date_start"]);
$search_end=mysql_real_escape_string($_POST["date_end"]);

$user_min_skill = 0;
if ($_SESSION['valid'] === true) {
    $sql = "SELECT MIN(score) FROM Has_Skill WHERE user_id = '{$_SESSION['id']}'";
    $res = mysql_query($sql);
    if (mysql_num_rows($res) == 0) {
        die("Catastrophic failure: Failed to get skill list");
    }
    $data = mysql_fetch_assoc($res);
    $user_min_skill = $data['MIN(score)'];
}
?>
<div style="width: 100%; border: 1px solid gray">
    <h1>Event Search</h1>
    <form action="event_search.php" method="post" target="_top">
        <div style="padding: 1em;">
            <label>Event Name</label>
            <?php
            print("<input type=\"text\" value=\"$search_name\" name=\"name\">");
            ?>
        </div>
        <div style="padding: 1em;">
            <label>Filter By Date </label>
            <?php
            if ($search_start == "") {
                print("<input type=\"text\" placeholder=\"YYYY-MM-DD\" name=\"date_start\">");
            }
            else {
                print("<input type=\"text\" value=\"$search_start\" name=\"date_start\">");
            }
            ?>

            <label>to</label>
            <?php
            if ($search_end == "") {
                print("<input type=\"text\" placeholder=\"YYYY-MM-DD\" name=\"date_end\">");
            }
            else {
                print("<input type=\"text\" value=\"$search_end\" name=\"date_end\">");
            }
            ?>
            <button type="submit">Search</button>
        </div>
    </form>
</div>
<div style="width: 100%; border: 1px solid gray;">
    <h2>Search Results</h2>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Event Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Organizer</th>
            </tr>
            </thead>
            <tbody>
            <?php
            //ok, just hack it
            if ($search_start == "") {
                $search_start = "0000-01-01";
            }
            if($search_end == "") {
                $search_end = "9999-12-30";
            }
            //search for events
            //$sql = "SELECT * FROM Event WHERE name LIKE '%$search_name%' AND end > '$search_start' AND end < '$search_end' AND max_skill >= $user_min_skill";
            $sql1 = "SELECT id AS organizer_id, name AS organizer_name FROM User";
            $sql = "SELECT * FROM Event NATURAL JOIN ($sql1) AS S1 WHERE name LIKE '%$search_name%' AND end > '$search_start' AND end < '$search_end' AND max_skill >= $user_min_skill";
            $res = mysql_query($sql);
            while ($data = mysql_fetch_assoc($res)) {
                $event_name = $data['name'];
                $event_start = $data['start'];
                $event_end = $data['end'];
                $organizer_id = $data['organizer_id'];
                $organizer_name = $data['organizer_name'];
                print("<tr>");
                print("<td><a href=\"event_page.php?event_name=$event_name&event_start=$event_start&event_end=$event_end\">$event_name</a></td>");
                print("<td><p>$event_start</p></td>");
                print("<td><p>$event_end</p></td>");
                print("<td><a href=\"user_page.php?user_id=$organizer_id\">$organizer_name</a></td>");
                print("</tr>");
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="../../dist/js/bootstrap.min.js"></script>
<!-- Just to make our placeholder images work. Don't actually copy the next line! -->
<script src="../../assets/js/vendor/holder.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>
