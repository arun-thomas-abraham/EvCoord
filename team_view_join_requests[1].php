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

    <title>View Join Requests</title>

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
    if ($_SESSION['valid'] == true) {
        include 'logoutNav.php';

    } else {
        die("you must be logged in to accept a user");
    }
    ?>
</h2>


<?php
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
?>

<div style="width: 100%; border: 1px solid gray;">
    <h2>Pending Invitations</h2>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>User ID</th>
                <th>Role</th>
                <th>Description</th>
                <th>Accept</th>
                <th>Reject</th>
            </tr>
            </thead>
            <tbody>
            <?php
                $sql ="SELECT * FROM Join_Request WHERE team_name = '$team_name' AND event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end'";
                $res = mysql_query($sql);
                    if (mysql_num_rows($res) == 0) {
                        die("No one has requested to join your team.");
                    }
                while ($data = mysql_fetch_assoc($res)){
                    $user_id = $data['user_id'];
                    $requested_skill = $data['skill_requested'];
                    $description = $data['description'];
                    print("<tr>");
                    print("<th>$user_id</th>");
                    print("<th>$requested_skill</th>");
                    print("<th>$description</th>");
                    print("<th><a href=\"team_request_accept.php?user_id=$user_id&team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end&skill_name=$requested_skill\">accept</a></th>");
                    print("<th><a href=\"team_request_reject.php?user_id=$user_id&team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end&skill_name=$requested_skill\">reject</a></th>");
                    print("</tr>");
                } 
            ?>
            </tbody>
        </table>
    </div>
</div>
<?php
    print("<button class=\"cancelbutton\" onclick=\"window.location.href='team_page.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end'\">Back to page for $team_name </button>");
    mysql_close();
?>
<br/>
</body>
</html>