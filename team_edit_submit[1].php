<?php
session_start();
if ($_SESSION['valid'] != true) {
    die("failure: user not logged in.");
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

    <title>Edit Team</title>

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
    if ($_SESSION['valid'] === true) {
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

//get POST values
$team_new_name = mysql_real_escape_string($_POST['name']);
$team_new_desc = mysql_real_escape_string($_POST['description']);

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

//get user info
$user_id = $_SESSION['id'];
?>
<div class="container">
    <?php
    //verify user for ownership
    if ($_SESSION['id'] !== $team_leader_id) {
        die("You do not have permission to edit this team.");
    }
    if ($status == 2) {
        die("You cannot edit an expired team.");
    }
    if ($team_new_name !== $team_name) {
        //get team info
        $sql="SELECT * FROM Team WHERE name = '$team_new_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
        $res=mysql_query($sql);
        if (mysql_num_rows($res) > 0) {
            die("The requested name is already taken.");
        }
    }
    else {
        if ($team_new_name !== $team_name) {
            $sql = "UPDATE Team SET name = '$team_new_name' WHERE name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
            $res = mysql_query($sql);
            if (mysql_affected_rows() == 0) {
                die("Name update returned 0 rows.");
            }
        }
        if ($team_new_desc !== $team_desc) {
            $sql = "UPDATE Team SET description = '$team_new_desc' WHERE name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
            $res = mysql_query($sql);
            if (mysql_affected_rows() == 0) {
                die("Description update returned 0 rows.");
            }
        }
        print("<p>Update successful!</p>");
        //log it in the comments
        $post_description = "Changed team details.";
        $sql = "INSERT INTO Team_Update(team_name, event_name, event_start, event_end, posted_time, poster_id, description)
                VALUES ('$team_new_name', '$event_name', '$event_start', '$event_end', NOW(), '$user_id', '$post_description')";
        $res = mysql_query($sql);
        if (!$res) {
            print ("<p>Post submission of [$post_description] by $user_id failed.</p>");
        } else {
            print("<p>Post successful!</p>");
        }
    }
    mysql_close();
    ?>
</div>

<div class="container" style="background-color:#EEEEEE">
    <?php
    print("<button class=\"cancelbutton\" onclick=\"window.location.href='team_page.php?team_name=$team_new_name&event_name=$event_name&event_start=$event_start&event_end=$event_end'\">Back</button>");
    ?>
</div>


</body>
</html>
