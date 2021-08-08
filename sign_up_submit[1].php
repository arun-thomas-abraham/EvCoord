<?php
	session_start();

	//connect to database
    $link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
    if (!$link) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db('eventcoord_new411');
	//grab variables from our html forms
	$newName=mysql_real_escape_string($_POST["name"]);
	$newId=mysql_real_escape_string($_POST["username"]);
	$newPass=mysql_real_escape_string($_POST["password"]);
	$newDesc=mysql_real_escape_string($_POST["description"]);

	//search for id conflict
	$sql="SELECT * FROM User WHERE id = '$newId'";
	$res=mysql_query($sql);
	if (mysql_num_rows($res)) {
	    header('Refresh: 3,url=sign_up.php');
		print("Your user ID is already registered. Sending you back to the signup page...");
	}
	
	else {
		$sql="INSERT INTO User(id, pw, name, description) VALUES ('$newId', '$newPass', '$newName', '$newDesc')";
		$res=mysql_query($sql);
		
		//assign all default roles to 0
		$sql = "SELECT DISTINCT name FROM Skill";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) == 0) {
			die("Catastrophic failure: Failed to get skill list");
		}
		while($data = mysql_fetch_assoc($res)) {
			$sql_1="INSERT INTO Has_Skill(user_id, skill_name, score) VALUES ('$newId', '{$data['name']}', 0)";
			$res_1=mysql_query($sql_1);
		}
		//assign all default traits to 0
		$sql = "SELECT DISTINCT name FROM Trait";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) == 0) {
			die("Catastrophic failure: Failed to get trait list");
		}
		while($data = mysql_fetch_assoc($res)) {
			$sql_1="INSERT INTO Has_Trait(user_id, trait_name, score) VALUES ('$newId', '{$data['name']}', 0)";
			$res_1=mysql_query($sql_1);
		}
		
		//log into our new session and send the user back
		$_SESSION['valid'] = true;
		$_SESSION['id'] = $newId;
		$_SESSION['name'] = $newName;
		header('Refresh: 3,url=user_page.php');
		print("Welcome, $newName, you are now registered under $newId. Sending you to your new dashboard...");
	}
	mysql_close();
?>