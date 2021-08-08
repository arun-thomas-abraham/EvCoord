<?php
	session_start();
	if ($_SESSION['valid'] != true) {
		die("failure: user not logged in.");
	}
	$ID = $_SESSION['ID'];
	$team_name = $_POST["team_name"];
	$event_name = $_POST["event_name"];
	$event_start = $_POST["event_start"];
	$event_end = $_POST["event_end"];
	$requested_role = $_POST["requested_role"];

	$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
	if (!$link) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db('eventcoord_new411');
		
	//delete join request
	$sql = "DELETE FROM Join_Request WHERE Requested_Person_ID = '$ID' AND Team_Name = '$team_name' AND Event_Name = '$event_name' AND Event_Start = '$event_start' AND Event_End = '$event_end' AND Requested_Role = '$requested_role'"
	$res = mysql_query($sql);	
		
	print("query performed.");

	mysql_close();
?>