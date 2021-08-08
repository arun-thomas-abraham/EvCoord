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

    <title>Team Search</title>

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
        <h2 class="page-header"> Can't find a team you are looking for? Use the search bar below!
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
//get GET variables
$event_name = htmlspecialchars($_GET['event_name']);
$event_start = htmlspecialchars($_GET['event_start']);
$event_end = htmlspecialchars($_GET['event_end']);

//get POST variables
$search_name=mysql_real_escape_string($_POST["name"]);

?>
<div style="width: 100%; border: 1px solid gray">
    <?php
        print("<h1>Search for teams in $event_name [$event_start - $event_end]</h1>");
        print("<form action=\"team_search.php?event_name=$event_name&event_start=$event_start&event_end=$event_end\" method=\"post\" target=\"_top\">");
    ?>
        <div style="padding: 1em;">
            <label>Team Name</label>
            <?php
            print("<input type=\"text\" value=\"$search_name\" name=\"name\">");
            ?>
            <button type="submit" class="button10">Search</button>
        </div>
    </form>
</div>
<div style="width: 100%; border: 1px solid gray;">
    <h2>Search Results</h2>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Name</th>
                <th>Leader</th>
                <th>Average Used Skill Level</th>
                <th>Fitness</th>
            </tr>
            </thead>
            <tbody>
            <?php
            require_once('fitness.php');
            //search for current teams
            //$sql = "SELECT * FROM Team WHERE name LIKE '%$search_name%' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
            $sql1 = "SELECT id AS leader_id, name AS leader_name FROM User";
            $sql = "SELECT * FROM Team NATURAL JOIN ($sql1) AS S1 WHERE name LIKE '%$search_name%' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
            $res = mysql_query($sql);
            while ($data = mysql_fetch_assoc($res)) {
                //TODO: need to get skill level by getting the maximum score of the has_skill of the member assigned to that role.
                $team_name = $data['name'];
                $leader_id = $data['leader_id'];
                $leader_name = $data['leader_name'];
                $fitness = get_fitness_with_new_user($_SESSION['id'], $team_name, $event_name, $event_start, $event_end, true, false);
                
                //skill level is average level for the skills that members are actually using on the team
                
                $member_sql = "SELECT user_id, skill_name FROM Member WHERE team_name='$team_name' AND event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end'";
                $avg_skill_sql = "SELECT AVG(score) as average_skill FROM ($member_sql) AS member_list NATURAL JOIN Has_Skill;";
                $avg_skill_res = mysql_query($avg_skill_sql);
                $avg_skill_data = mysql_fetch_assoc($avg_skill_res);
                $average_skill = get_team_used_skill_avg($team_name, $event_name, $event_start, $event_end);


                print("<tr>");
                print("<td><a href=\"team_page.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\">$team_name</a></td>");
                print("<td><a href=\"user_page.php?user_id=$leader_id\">$leader_name</a></td>");
                print("<td><p>$average_skill</p></td>");
                print("<td><p>$fitness</p></td>");
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
