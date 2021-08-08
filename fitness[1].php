<?php 
ini_set("include_path", '/home/eventcoord/php:' . ini_get("include_path") );


function open_db(){
    $link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
	if (!$link) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db('eventcoord_new411');
}


function print_array_elements($array){
    foreach( $array as $key => $value ){
        print($key."\t=>\t".$value."\n");
    }
}

//returns an array of skills to be used for ordering items within skill vectors
function get_skills(){
    $skill_array = array();
    
    $sql = "SELECT * FROM Skill";
    $res = mysql_query($sql);
    while($data = mysql_fetch_assoc($res)){
        $skill_name = $data['name'];
        $skill_array[] = $skill_name;
    }
    // so either the array's not getting updated or it's not being printed properly
    return $skill_array;
}

//returns an array of traits to be used for ordering items within trait vectors 
function get_traits(){
    $trait_array = array();
    
    $sql = "SELECT * FROM Trait";
    $res = mysql_query($sql);
    while($data = mysql_fetch_assoc($res)){
        $trait_name = $data['name'];
        $trait_array[] = $trait_name;
    }
    return $trait_array;
}



//things to consider: 
//add option for only using skills, only using traits, using both together, or using both separately. 
//Add option to weight different factors differently (i. e. traits have less weight than skills)
//how do users get awards? how do their skills/ traits increase?

//returns a user's traits as an associative array: trait name => trait level
function get_user_trait_array($user_id){
    $traits = array();
    $sql = "SELECT * FROM Has_Trait WHERE user_id='$user_id'";
    $res = mysql_query($sql);
    while($data = mysql_fetch_assoc($res)){
        $trait_name = $data['trait_name'];
        $trait_level = $data['score'];
        $traits[$trait_name] = (int)$trait_level;
    }
    //print("traits for '$user_id'");
    //var_dump($traits);
    return $traits;
}

//returns a user's skills as an associative array: skill name => skill level
function get_user_skill_array($user_id){
    $skills = array();
    $sql = "SELECT * FROM Has_Skill WHERE user_id='$user_id'";
    $res = mysql_query($sql);
    while($data = mysql_fetch_assoc($res)){
        $skill_name = $data['skill_name'];
        $skill_level = $data['score'];
        $skills[$skill_name] = (int)$skill_level;
    }
    //print("skills for '$user_id'");
    //var_dump($skills);
    return $skills;
}

//$is_skill is true for skill array, false for trait array
function get_user_array($user_id, $is_skill){
    if($is_skill){
        return get_user_skill_array($user_id);
    }
    else{
        return get_user_trait_array($user_id);
    }
}



function get_team_sum_array($team_name, $event_name, $event_start, $event_end, $is_skill){
    //print("$team_name $event_name $event_start $event_end $is_skill");
    
    $team_member_ids = array();
    $sql = "SELECT DISTINCT user_id FROM Member WHERE team_name='$team_name' AND event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end'";
    $res = mysql_query($sql);
    while($data = mysql_fetch_assoc($res)){
        $team_member_ids[] = $data['user_id'];
    }
    
    //now iterate through the team members and get their trait vectors. (call get_user_trait_vector)
    $team_values = array();
    foreach($team_member_ids as $id){
        $member_array = get_user_array($id, $is_skill);
        foreach($member_array as $name=>$level){
            $team_values[$name] += $level;
        }
    }
    
    return $team_values;
}


function get_team_average_array($team_name, $event_name, $event_start, $event_end, $is_skill){
    $sum_array = get_team_sum_array($team_name, $event_name, $event_start, $event_end, $is_skill);
    $team_size = get_number_users_in_team($team_name, $event_name, $event_start, $event_end);
    $average_array = array();
    foreach($sum_array as $name=>$level){
        $average_array[$name] = $level/$team_size;
    }
    return $average_array;
}

function get_team_average_array_with_new_user($user_id, $team_name, $event_name, $event_start, $event_end, $is_skill){
    $sum_array = get_team_sum_array($team_name, $event_name, $event_start, $event_end, $is_skill);
    $team_size = get_number_users_in_team($team_name, $event_name, $event_start, $event_end);
    $new_user_array = get_user_array($user_id, $is_skill);
    foreach($new_user_array as $name=>$level){
        $sum_array[$name] += $level;
    }
    $average_array = array();
    foreach($sum_array as $name=>$level){
        $average_array[$name] = $level/ ($team_size+1);
    }
    return $average_array;
}



//return the number of users in a team
function get_number_users_in_team($team_name, $event_name, $event_start, $event_end){
    $sql = "SELECT COUNT(DISTINCT user_id) AS team_size FROM Member WHERE team_name='$team_name' AND event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end'";
    $res = mysql_query($sql);
    $data = mysql_fetch_assoc($res);
    $team_size = $data['team_size'];
    return $team_size;
}


function get_event_sum_array($event_name, $event_start, $event_end, $is_skill){
    $event_participant_ids = array();
    $sql = "SELECT DISTINCT user_id FROM Member WHERE event_name ='$event_name' AND event_start='$event_start' AND event_end='$event_end'";
    $res = mysql_query($sql);
    while($data = mysql_fetch_assoc($res)){
        $event_participant_ids[] = $data['user_id'];
    }

    $event_sum_array = array();
    foreach($event_participant_ids as $id){
        $participant_array = get_user_array($id, $is_skill);
        foreach($participant_array as $name=>$level){
            $event_sum_array[$name] += $level;
        }
    }
    
    return $event_sum_array;
}

function get_event_average_array($event_name, $event_start, $event_end, $is_skill){
    $event_sum_array = $get_event_sum_array($is_skill);
    $event_average_array = array();
    $num_participants = get_num_users_in_event($event_name, $event_start, $event_end, $is_skill);
    
    foreach($event_sum_array as $name=>$level){
        $event_average_array[$name] = $level/$num_participants;
    }
    return $event_average_array;
}

function get_event_average_array_with_new_user($user_id, $event_name, $event_start, $event_end, $is_skill){
    $num_participants = get_number_users_in_event($event_name, $event_start, $event_end);
    
    $event_sum_array = get_event_sum_array($event_name, $event_start, $event_end, $is_skill);
    
    $new_user_array = get_user_array($user_id, $is_skill);
    foreach($new_user_array as $name => $level){
        $event_sum_array[$name] += $level;
    }
    
    $event_average_array = array();
    foreach($event_sum_array as $name => $level){
        $event_average_array[$name] = $level / ($num_participants + 1);
    }
    
    return $event_average_array;
}


//return the number of users in an event
function get_number_users_in_event($event_name, $event_start, $event_end){
    $sql = "SELECT COUNT(DISTINCT user_id) AS event_size FROM Member WHERE event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end'";
    $res = mysql_query($sql);
    $data = mysql_fetch_assoc($res);
    return $data['event_size'];
}


function get_fitness_with_new_user($user_id, $team_name, $event_name, $event_start, $event_end, $use_skills, $use_traits){
    $team_array =array();
    $event_array = array();
    

    if ($use_traits){
        $team_trait_array = get_team_average_array_with_new_user($user_id, $team_name, $event_name, $event_start, $event_end, False);
        $team_array = array_merge($team_array, $team_trait_array);

        $event_trait_array = get_event_average_array_with_new_user($user_id, $event_name, $event_start, $event_end, False);
        $event_array = array_merge($event_array, $event_trait_array);
    }
    if ($use_skills){
        $team_skill_array = get_team_average_array_with_new_user($user_id, $team_name, $event_name, $event_start, $event_end, True);
        $team_array = array_merge($team_array, $team_skill_array);
        $event_skill_array = get_event_average_array_with_new_user($user_id, $event_name, $event_start, $event_end, True);
        $event_array = array_merge($event_array, $event_skill_array);
    }
    
    $diff = array();
    foreach($event_array as $name => $level){
        $diff[$name] = $team_array[$name] - $event_array[$name];
    }
    
    
    
    $distance = euclidean_norm($diff);
    
    
    // fitness will be: 100% minus the percentage of the event norm that distance makes up.
    $event_norm = euclidean_norm($event_array);
    $fitness = 100*(1 - $distance/$event_norm);
    
    /*
    print("team array");
    var_dump($team_array);
    print("event array");
    var_dump($event_array);
    print('diff');
    var_dump('diff');
    print("distance '$distance'");
    print("event_norm '$event_norm'"); */
    
    return $fitness;
}

//how close the two users are to forming a well-rounded team
function get_roundedness_between_two_users($id1, $id2, $use_skills, $use_traits){
    $user_list = array();
    $user_list[] = $id1;
    $user_list[] = $id2;
    return get_roundedness_among_users($user_list, $use_skills, $use_traits);
}

function get_roundedness_among_team($team_name, $event_name, $event_start, $event_end, $use_skills, $use_traits){
    $user_list = array();
    $sql = "SELECT user_id FROM Member WHERE team_name='$team_name' AND event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end'";
    $res = mysql_query($sql);
    while($data = mysql_fetch_assoc($res)){
        $user_list[] = $data['user_id'];
    }
    return get_roundedness_among_users($user_list, $use_skills, $use_traits);
}

function get_roundedness_for_team_and_new_user($user_id, $team_name, $event_name, $event_start, $event_end, $use_skills, $use_traits){
    $user_list = array();
    $user_list[] = $user_id;
    $sql = "SELECT user_id FROM Member WHERE team_name='$team_name' AND event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end'";
    $res = mysql_query($sql);
    while($data = mysql_fetch_assoc($res)){
        $user_list[] = $data['user_id'];
    }
    return get_roundedness_among_users($user_list, $use_skills, $use_traits);
}

function get_roundedness_among_users($user_list, $use_skills, $use_traits){
    $round_vector = array();
    $users_vector = array();
    
    if($use_skills){
        foreach($user_list as $user_id){
            $user_skill_vector = get_user_skill_array($user_id);
            foreach($user_skill_vector as $name=>$level){
                $users_vector[$name] += $level;
            }
        }
        $skill_list = get_skills();
        foreach($skill_list as $skill){
            $round_vector[$skill] = 1;
        }
    }
    if($use_traits){
        foreach($user_list as $user_id){
            $user_trait_vector = get_user_trait_array($user_id);
            foreach($user_trait_vector as $name=>$level){
                $users_vector[$name] +=$level;
            }
        }
        
        $trait_list = get_traits();
        foreach($trait_list as $trait){
            $round_vector[$trait] = 1;
        }
    }
    var_dump($users_vector);
    var_dump($round_vector);
    
    $round_norm = euclidean_norm($round_vector);
    $normalized_round = vector_divide_by_const($round_vector, $round_norm);
    
    $users_norm = euclidean_norm($users_vector);
    $normalized_users_vector = vector_divide_by_const($users_vector, $users_norm);
    
    $diff = array();
    foreach($normalized_users_vector as $name=>$value){
        $diff[$name] = $normalized_users_vector - $normalized_round;   
    }
    
    $distance = euclidean_norm($diff);
    
    return 100 * (1 - $distance);
}


// returns p-norm of an array (p>=1)
function p_norm($array, $p){
    $summation = 0;
    foreach($array as $value){
        $summation += pow(abs($value), $p);
    }
    return pow($summation, 1/$p);
}

function euclidean_norm($array){
    return p_norm($array, 2);
}


function vector_divide_by_const($array, $divisor){
    $res = array();
    foreach($array as $key=>$value){
        $res[$key] =$value/$divisor;
    }
    return $res;
}

function add_vectors($array1, $array2){
    $sum = array();
    foreach($array1 as $key=>$value){
        $sum[$key] += $array2[$key];
    }
    return $sum;
}



function get_team_used_skill_avg($team_name, $event_name, $event_start, $event_end){
    $member_sql = "SELECT user_id, skill_name FROM Member WHERE team_name='$team_name' AND event_name='$event_name' AND event_start='$event_start' AND event_end='$event_end'";
    $avg_skill_sql = "SELECT AVG(score) as average_skill FROM ($member_sql) AS member_list NATURAL JOIN Has_Skill;";
    $avg_skill_res = mysql_query($avg_skill_sql);
    $avg_skill_data = mysql_fetch_assoc($avg_skill_res);
    $average_skill = $avg_skill_data['average_skill'];
    return $average_skill;
}


?>