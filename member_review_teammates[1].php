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

    <title>Team Reward Submit</title>

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


//get team info
$sql="SELECT * FROM Team WHERE name = '$team_name' AND event_name = '$event_name' AND event_start = '$event_start' AND event_end = '$event_end'";
$res=mysql_query($sql);
if (mysql_num_rows($res) == 0) {
    die("No such team ($team_name, $event_name, $event_start, $event_end) exists.");
}
$data=mysql_fetch_assoc($res);
$team_leader_id = $data['leader_id'];
$team_desc = $data['description'];

//get list of traits
$trait_array = array();
$sql = "SELECT DISTINCT name FROM Trait";
$res = mysql_query($sql);
while($data = mysql_fetch_assoc($res)){
    $trait_array[] = $data['name'];
}


$teammate_ids = array();
//get teammate info
$sql = "SELECT user_id FROM Member WHERE team_name = '$team_name' AND event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end'";
$res = mysql_query($sql);
while($data = mysql_fetch_assoc($res)){
    $teammate_id = $data['user_id'];
    if($teammate_id != $_SESSION['id']){
        $teammate_ids[] = $teammate_id;
    }
}

//get user info
$user_id = $_SESSION['id'];

//now check for which reviews have already been done
$existing_reviews = array(); // user_id to selected best trait
$sql = "SELECT * FROM Teammate_Review WHERE team_name='$team_name' AND event_name='$event_name' AND event_start = '$event_start' AND event_end = '$event_end' AND reviewer_id='$user_id'";
$res = mysql_query($sql);
while($data = mysql_fetch_assoc($res)){
    $teammate_id = $data['reviewee_id'];
    $best_trait = $data['best_trait'];
    $existing_reviews[$teammate_id] = $best_trait;
}



//if a teammate isn't in Has_Trait: add to database with a score of one


//now obtain POST values using team member names
//use those values to:
//remove outdated reviews and fix HasTrait
//add updated reviews and fix HasTrait.
foreach($teammate_ids as $teammate_id){
    $trait_index = 'best_trait' . $teammate_id;
    $best_trait = $_POST[$trait_index];
    
    if($best_trait){
        $best_trait = mysql_real_escape_string($best_trait);
        //remove previous review for this teammate
        //using delete and not update because there might not be a previous review.
        $sql = "DELETE FROM Teammate_Review WHERE team_name='$team_name' AND event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end' AND reviewer_id='$user_id' AND reviewee_id='$teammate_id'";
        $res = mysql_query($sql);
        
        //add review from post
        $sql = "INSERT INTO Teammate_Review(team_name, event_name, event_start, event_end, reviewer_id, reviewee_id, best_trait)
                    VALUES('$team_name', '$event_name', '$event_start', '$event_end', '$user_id', '$teammate_id', '$best_trait')";
        $res = mysql_query($sql);
        
        
        
        $prev_trait = $existing_reviews[$teammate_id];
        if($prev_trait){
            // decrement score for prev_trait
            $sql = "UPDATE Has_Trait SET score = score-1 WHERE user_id='$teammate_id' AND trait_name='$prev_trait'";
            $res = mysql_query($sql);
        }
        
        //increment score for $best_trait
        $sql = "UPDATE Has_Trait SET score = score+1 WHERE user_id='$teammate_id' AND trait_name='$best_trait'";
        $res = mysql_query($sql);
        
        //fix previous review for this teammate
        $existing_reviews[$teammate_id] = $best_trait;
    }
    
    $description_index = "description" . $teammate_id;
    $description = $_POST[$description_index];
    if($description){
        $description = mysql_real_escape_string($description);
        
        
        $sql = "UPDATE Teammate_Review SET description='$description' WHERE team_name='$team_name' AND event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end' AND reviewer_id='$user_id' AND reviewee_id='$teammate_id'";
        $res = mysql_query($sql);
        
    }
    
    
}


?>
<div class="container">
    <?php
        //build a form with an drop-down input field for each team member. display current value and allow it to be changed.
        print("<form action=\"member_review_teammates.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end\" method=\"post\" target=\"_top\">");
    ?>
    
        <?php
            foreach($teammate_ids as $idx=>$teammate_id){
                $has_current_best_trait = array_key_exists($teammate_id, $existing_reviews);
                $current_best_trait = ($has_current_best_trait ? $existing_reviews[$teammate_id] : "not set");
                $field_name = "best_trait" . $teammate_id;
                print("<label>$teammate_id's best trait (currently $current_best_trait) </label>");
                print("<select name=\"$field_name\"> ");
                
                //populate dropdown
                print("<option value=\"\" selected></option>");
                foreach($trait_array as $trait_name){
                    print("<option value =\"$trait_name\">$trait_name</option>");
                }
                print("</select>");
                
                $description_field_name = "description" . $teammate_id;
                print("<label>Describe $teammate_id (max 2047 characters)</label>");
                print("<input type=\"text\" name=\"$description_field_name\"></input>");
                
                print("<br>");
            }
            
        ?>

        
    <?php
        print("<button class=\"button10\" type=\"submit\">Submit Reviews</button>");
        print("</form>");
    ?>
        
    <?php
        mysql_close();
    ?>
</div>

<div class="container" style="background-color:#EEEEEE">
    <?php
    $user_id = $_SESSION['id'];
    print("<button class=\"cancelbutton\" onclick=\"window.location.href='user_teams.php?&user_id=$user_id'\">Return to list of your teams</button>");
    ?>
</div>


</body>
</html>