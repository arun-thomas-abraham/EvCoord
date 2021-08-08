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

    <title>User Search</title>

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
  
  <link rel="stylesheet" href="styles.css">
<style>
.button8 {
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
        
       
        
          <h2 class="page-header"> Can't find a user you are looking for? Use the search bar below !
              <?php
			if ($_SESSION['valid'] == true) {
				//array access must be encapsulated in brackets ONLY if they're referenced inside quotes
                           include 'logoutNav.php';

			}
			else {
				//both single quote ' and double quotes " work fine as print string qualifiers. choose whichever lets you get away with less escape characters.
				//EXCEPT when you need to format variables inside your strings. In that case, only double quotes " are allowed.
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
$team_name = htmlspecialchars($_GET['team_name']);
$event_name = htmlspecialchars($_GET['event_name']);
$event_start = htmlspecialchars($_GET['event_start']);
$event_end = htmlspecialchars($_GET['event_end']);

//get POST variables
$search_id = mysql_real_escape_string($_POST["search_id"]);
$search_name=mysql_real_escape_string($_POST["search_name"]);
$search_position = mysql_real_escape_string($_POST["search_position"]);
$search_trait = mysql_real_escape_string($_POST["search_trait"]);

?>
	
	
<div style = "width: 100%; border: 1px solid gray;">
	<!-- User controls. -->
	
</div>
<div style = "width: 100%; border: 1px solid gray">
	<h1>User Search</h1>
	
	<?php
    print("<form action=\"user_search.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\" method=\"post\" target=\"_top\">");
    ?>
    
    
    	<div style = "padding: 1em;">
	        <label>User ID</label>
	        <input type="text" value="" name='search_id' style = "float:center">
	    
		    <label>User Name</label>
		    <input type="text" value="" name='search_name' style="float:center">
        </div>

   
        <div style = "padding: 1em;">
		    <label>Position</label>
		    <input type="text" value="" name='search_position' style="float:center">
		
	        <label>Skill</label>
		    <input type="text" value="" name='search_trait' style="float:center">
        </div>
    <button class="button8" type="submit">Search</button>
    </form>
   
   
	<div style = "padding: 1em;">
		<label>Filter By Experience Level</label>
	    <div class="btn-group" role="group" aria-label="...">
	    
  <button type="button" class="btn btn-default" style=padding:8px;cursor:pointer;min-width:6%;margin-right:10px;display:inline;>1</button>
  <button type="button" class="btn btn-default" style=padding:8px;cursor:pointer;min-width:6%;margin-right:10px;display:inline;>2</button>
  <button type="button" class="btn btn-default" style=padding:8px;cursor:pointer;min-width:6%;margin-right:10px;display:inline;>3</button>
  <button type="button" class="btn btn-default" style=padding:8px;cursor:pointer;min-width:6%;margin-right:10px;display:inline;>4</button>
  <button type="button" class="btn btn-default" style=padding:8px;cursor:pointer;min-width:6%;margin-right:10px;display:inline;>5</button>
  <button type="button" class="btn btn-default" style=padding:8px;cursor:pointer;min-width:6%;margin-right:10px;display:inline;>6</button>
 
  <button type="button" class="btn btn-default" style=padding:8px;cursor:pointer;min-width:6%;margin-right:10px;display:inline;>7</button>
  <button type="button" class="btn btn-default" style=padding:8px;cursor:pointer;min-width:6%;margin-right:10px;display:inline;>8</button>
  <button type="button" class="btn btn-default" style=padding:8px;cursor:pointer;min-width:6%;margin-right:10px;display:inline;>9</button>
  <button type="button" class="btn btn-default" style=padding:8px;cursor:pointer;min-width:6%;margin-right:10px;display:inline;>10</button>
  </div>
  
	
</div>



</div>
<div style = "width: 100%; border: 1px solid gray;">
	<!-- Comments page. Expected to only be used on the team page. -->

	<h2>Search Results</h2>
	<table style="width:100%">
	<tr>
	    <th>User ID</th>
		<th>User Name</th>
		<th>Best Position</th>
		<th>Best Trait</th>
		<th>Experience Level</th>
		<th>Relevance</th>
		<th>Invite to team</th>
	</tr>
	
	<?php	
	    //TODO: FIX SEARCH AND BEST TRAIT IDENTIFICATION.
	    //for now this is just linking up to invitation page
	    
	    require_once('fitness.php');
	    
	    
	    $user_name = array();
	    $user_best_skill =array();
	    $user_best_trait = array();
	    $user_level = array();
	    $user_relevance = array();
	    $sql = "SELECT * FROM User WHERE id LIKE '%$search_id' AND name LIKE '%$search_name%'";
        $res = mysql_query($sql);
        while ($data = mysql_fetch_assoc($res)) {
            $user_id = $data["id"];
            $name = $data['name'];
    
            # 
            $skill_sql = "SELECT skill_name, score from Has_Skill WHERE user_id = '$user_id' AND score = (SELECT MAX(score) FROM Has_Skill WHERE user_id = '$user_id')";
            $skill_res = mysql_query($skill_sql);
            $best_skill = "";
            $level = 0;
            while($skill_data = mysql_fetch_assoc($skill_res)){
                $best_skill = $best_skill . " " . $skill_data['skill_name'];
                $level = $skill_data['score'];
            }
                
            $trait_sql = "SELECT trait_name, score from Has_Trait WHERE user_id = '$user_id' AND score = (SELECT MAX(score) FROM Has_Trait WHERE user_id = '$user_id')";
            $trait_res = mysql_query($trait_sql);
            $best_trait = "";
            while($trait_data = mysql_fetch_assoc($trait_res)){
                $best_trait = $best_trait . " " . $trait_data['trait_name'];
            }
            
            $user_name[$user_id] = $name;
            $user_best_skill[$user_id] = $best_skill;
            $user_best_trait[$user_id] = $best_trait;
            $user_level[$user_id] = $level;
            
            $fitness = get_fitness_with_new_user($user_id, $team_name, $event_name, $event_start, $event_end, true, false);
            #$fitness = get_roundedness_for_team_and_new_user($user_id, $team_name, $event_name, $event_start, $event_end, True, True);
            $user_relevance[$user_id] = $fitness;
        }
        arsort($user_relevance);
         foreach($user_relevance as $user_id => $relevance){    
            $name = $user_name[$user_id];
            $best_skill = $user_best_skill[$user_id]; 
            $best_trait = $user_best_trait[$user_id];
            $level = $user_level[$user_id];
            
            if($search_position){
                $search_lower = strtolower($search_position);
                $skills_lower = strtolower($best_skill);
                if(strpos($skills_lower, $search_lower) ==false){
                    continue;
                }
            }
            if($search_trait){
                $search_lower = strtolower($search_trait);
                $traits_lower = strtolower($best_trait);
                if(strpos($traits_lower, $search_lower) ==false){
                    continue;
                }
            }
            
            print("<tr>");
            print("<th><a href=user_page.php?user_id=$user_id>$user_id </a></th>");
            print("<th>$name</th>");
            print("<th>$best_skill</th>");
            print("<th>$best_trait</th>");
            print("<th>$level</th>");
            print("<th>$relevance</th>");
            print("<th><a href=\"user_invite.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end&user_id=$user_id\">Invite</a></th>");
            print("</tr>");
        }
    ?>
    
	</table>
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
