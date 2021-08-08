<?php
	//always prepend logged-in interactions with this
	session_start();
	
	//grab variables passed from our html forms
	$loginUser=$_POST["username"];
	$loginPass=$_POST["password"];
	//connect to database, replace with CS411 credentials
    $link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
    if (!$link) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db('eventcoord_new411');

	if ($_SESSION['valid'] == true) {
		header('Refresh: 3,url=user_page.php');
		die("you are already logged in, {$_SESSION['id']}");
	}
	else {
		//query for our user
		$sql="SELECT * FROM User WHERE id = '$loginUser' AND pw = '$loginPass'";
		$res=mysql_query($sql);
		
		//if such a user exists, let him through
		if (mysql_num_rows($res) > 0) {
			$_SESSION['valid'] = true;
			$data=mysql_fetch_assoc($res);
			$_SESSION['id'] = $data['id'];
			$_SESSION['name'] = $data['name'];
			header('Refresh: 0;url=user_page.php');
		}
		else {
			header('Refresh: 3;url=sign_in.php');
			print("<p>Username and/or password might be wrong. Sending you back to login page in 3 seconds.</p>");
		}
		mysql_close();
	}
?>
<?php
session_start();
$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('eventcoord_new411');
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

    <title>Sign In Submit</title>

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
        include 'loginNav.php';
    }
    ?>
</h2>
</body>
</html>