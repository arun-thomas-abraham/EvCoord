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

    <title>Team Edited</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="../../assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
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
  <h2><?php
			if ($_SESSION['valid'] == true) {
				//array access must be encapsulated in brackets ONLY if they're referenced inside quotes
                           include 'logoutNav.php';

			}
			else {
				//both single quote ' and double quotes " work fine as print string qualifiers. choose whichever lets you get away with less escape characters.
				//EXCEPT when you need to format variables inside your strings. In that case, only double quotes " are allowed.
                           include 'loginNav.php';

			}
		?></h2>
<?php
	session_start();
	if ($_SESSION['valid'] != true) {
		die("failure: user not logged in.");
	}
	$personlist=explode(',', $_POST["personlist"], 2);
        $userID = $personlist[0];
        $userRating = $personlist[1];
	$teamName = $_POST["team_name"];
	$eventName = $_POST["event_name"];
	$eventStart = $_POST["event_start"];
	$eventEnd = $_POST["event_end"];
        $role = $_POST["skill_name"];

	$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
	if (!$link) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db('eventcoord_new411');
        $sql="SELECT * FROM Skill WHERE Name = '$role'";
	$res=mysql_query($sql);
	if (mysql_num_rows($res) == 0) {
		die("you have specified an unsupported role.");
	}

        $sql = "INSERT INTO Join_Request(Team_Name, Event_Name, Event_Start, Event_End, Requested_Person_ID, Requested_Role) VALUES ('$teamName', '$eventName', '$eventStart', '$eventEnd', '$userID', '$role')";
        $res=mysql_query($sql);

        if (!res) {
		print ("Member invite failed.");
        }
	else {
        $Color = "orange";
		echo '<p class="container" style="color:black;">'.$_SESSION['Name'].', you have successfully invited:</p> ';
        echo '<div class="container" style="Color:'.$Color.';font-size:20px">'.$userID.'</div>';
	}

	mysql_close();
?>
    <br></br>
      <div class="container" style="background-color:#EEEEEE">

    <button class="cancelbutton" onclick="window.location.href='index.php'">Return to Home</button>
    </div>
</body>
</html>