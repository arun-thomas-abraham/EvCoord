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

    <title>Edit User</title>

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
            margin: 8px 0;
            border: none;
            cursor: pointer;
            color: white;
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
if ($_SESSION['valid'] === false) {
    die("Not logged in.");
}
//connect to database
$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('eventcoord_new411');

//get and verify user
$sql="SELECT * FROM User WHERE id = '{$_SESSION['id']}'";
$res=mysql_query($sql);
if (mysql_num_rows($res) == 0) {
    die("No user exists by the id {$_SESSION['id']}.");
}
$data=mysql_fetch_assoc($res);
$user_id = $data['id'];
$user_name = $data['name'];
$user_description = $data['description'];

?>
<div class="container">
    <form action="user_edit_submit.php" method="post" target="_top">
        <label style=color:black;font-family:verdana><b>New Password</b></label>
        <?php
        print("<input type=\"password\" class=\"input2\" placeholder=\"Enter New Password\" name=\"password\" required>");
        ?>

        <label style=color:black;font-family:verdana><b>Display Name</b></label>
        <?php
        print("<input type=\"text\" class=\"input1\" value=\"$user_name\" name=\"name\" required>");
        ?>

        <label style=color:black;font-family:verdana><b>Personal description (Max. 2000 characters)</b></label>
        <?php
        print("<input type=\"text\" class=\"input1\" value=\"$user_description\" name=\"description\" required>");
        ?>
        <button class="button10" type="submit">Submit</button>
    </form>
</div>

<div class="container" style="background-color:#EEEEEE">
    <button class="cancelbutton" onclick="window.location.href='user_page.php'">Back</button>
</div>


</body>
</html>
