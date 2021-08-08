<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<style>
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    padding-top: 100px; /* Location of the box */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 90%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0); /* Black w/ opacity */
}
/* Modal Content */
.modal-content {
    position: relative;
    background-color: #fefefe;
    margin: auto;
    padding: 0;
    border: 1px solid #888;
    width: 80%;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
    -webkit-animation-name: animatetop;
    -webkit-animation-duration: 0.4s;
    animation-name: animatetop;
    animation-duration: 0.4s
}
/* Add Animation */
@-webkit-keyframes animatetop {
    from {top:-300px; opacity:0} 
    to {top:0; opacity:1}
}
@keyframes animatetop {
    from {top:-300px; opacity:0}
    to {top:0; opacity:1}
}
/* The Close Button */
.close {
    color: white;
    float: right;
    font-size: 28px;
    font-weight: bold;
}
.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}
.modal-header {
    padding: 2px 16px;
    background-color: #0066ff;
    color: white;
}
.modal-body {padding: 2px 16px;}
.modal-footer {
    padding: 2px 16px;
    background-color: #0066ff;
    color: white;
}
canvas {
    padding-left: 300px;
}
table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 10%;
}
td, th {
    border: 2px solid #000000;
    text-align: left;
    padding: 6px;
}
</style>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Data Visualization</title>

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
</head>
<body>
<!-- top navigation header? -->
<h2>
    <?php
    if ($_SESSION['valid'] == true) {
        include 'logoutNav.php';

    } else {
        include 'loginNav.php';
    }
    ?>
</h2>
<h3 align="center" style=padding-left:150px>See Who You Work Best With (click on a node!)</h3>
<?php
$link = mysql_connect('webhost.engr.illinois.edu', 'eventcoord_cs411', 'cs411');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('eventcoord_new411');

$user_id = htmlspecialchars($_GET['user_id']);
if ($_SESSION['valid'] === false || $user_id !== $_SESSION['id']) {
    die("unauthorized to view $user_id's data!");
}
$N = 1;
$sql = "SELECT * FROM Member WHERE user_id = '$user_id' ORDER BY event_end DESC LIMIT 10";
$res = mysql_query($sql);
if (mysql_num_rows($res) == 0) {
    die("User has no team history");
}
else {
    $Yourtraits = array();
    $traitcount = array();
    $sql = "SELECT * FROM Has_Trait WHERE user_id = '$user_id'";
    $resT = mysql_query($sql);
    if (mysql_num_rows($resT) != 0) {
        $numtraits = mysql_num_rows($resT);
        while ($rowT = mysql_fetch_array($resT, MYSQL_NUM)) {
            $Yourtraits[] = $rowT[1];
            $traitcount[] = $rowT[2];
        }
    }
    date_default_timezone_set('America/Chicago');
    $today = date("Y-m-d");
    $theirtraits = array();
    $theirtraitscore = array();
    $N += mysql_num_rows($res);
    $T = mysql_num_rows($res);
    $TMcountarr = array();
    $TMarr = array();
    $Tarr = array();
    $TEventarr = array();
    $TStartarr = array();
    $TEndarr = array();
    $TMflag = array();
    $Traitarr = array();
    $Winarr = array();
    $is_skill = 0;
    while ($row = mysql_fetch_array($res, MYSQL_NUM)) {
        $Tarr[] = $row[1];
        $TEventarr[] = $row[2];
        $TStartarr[] = $row[3];
        $TEndarr[] = $row[4];
        $sql = "SELECT * FROM Event_Bonus WHERE team_name = '$row[1]' AND event_name = '$row[2]' AND event_start = '$row[3]' AND event_end = '$row[4]'";
        $resWin = mysql_query($sql);
        if (mysql_num_rows($resWin) != 0) {
            $rowWin = mysql_fetch_array($resWin, MYSQL_NUM);
            $Winarr[] = $rowWin[5];
        }
        else {
            $Winarr[] = 0;
        }
        $sql = "SELECT * FROM Member WHERE team_name = '$row[1]' AND event_name = '$row[2]' AND event_start = '$row[3]' AND event_end = '$row[4]' AND user_id != '$user_id'";
        $res2 = mysql_query($sql);
        if (mysql_num_rows($res2) != 0) {
            $TMcountarr[] = mysql_num_rows($res2);
            $N += mysql_num_rows($res2);
            while ($row2 = mysql_fetch_array($res2, MYSQL_NUM)) {
                $TMarr[] = $row2[0];
            }
        }
    }
    $arru = array_unique($TMarr);
    $arrkeys = array_keys($arru);
    $max = sizeof($arru);
    $arru2 = array();
    for ($p = 0; $p < $max; $p++) {
        $arru2[] = $arru[$arrkeys[$p]];
    }
    $teamNumArr = array();
    for ($q = 0; $q < $max; $q++) {
        $count = 0;
        $temp = array();
        for ($p = 0; $p < $T; $p++) {
            for ($pq = 0; $pq < $TMcountarr[$p]; $pq++) {
                if (strcmp($arru2[$q],$TMarr[$count]) === 0) {
                    $temp[] = $p+1;
                }
                $count += 1;
            }
        }
        $teamNumArr[] = $temp;
    }
    $N -= sizeof($TMarr) - $max;

    for ($q = 0; $q < $max; $q++) {
        $sql = "SELECT * FROM Has_Trait WHERE user_id = '$arru2[$q]'";
        $resT = mysql_query($sql);
        if (mysql_num_rows($resT) != 0) {
            while ($rowT = mysql_fetch_array($resT, MYSQL_NUM)) {
                $theirtraitscore[] = $rowT[2];
            }
        }
    }
    $idx = 0;
    $assignedcount = array();
    for($p = 0; $p < $max; $p++) {
        $temp = array();
        $TMflag[] = 0;
            $sql = "SELECT * FROM Teammate_Review WHERE reviewer_id = '$user_id' AND reviewee_id = '$arru2[$p]'";
            $res3 = mysql_query($sql);
            if (mysql_num_rows($res3) != 0) {
                $TMflag[$idx] = 1;
                while ($row3 = mysql_fetch_array($res3, MYSQL_NUM)) {
                    $temp[] = $row3[6];
                }
            }
        $tsize = sizeof($temp);
        $uni = array_unique($temp);
        $akeys = array_keys($uni);
        $size = sizeof($uni);
        $uni2 = array();
        for ($q = 0; $q < $size; $q++) {
            $uni2[] = $uni[$akeys[$q]];
        }
        $count = 0;
        for ($q = 0; $q < $size; $q++) {
            for ($m = 0; $m < $tsize; $m++) {
                if (strcmp($uni2[$q], $temp[$m]) === 0) {
                    $count += 1;
                }
            }
            $assignedcount[] = $count;
            $count = 0;
        }
        $Traitarr[] = $uni2;
        $idx += 1;
    }

}
?>
<script>
var assignedcount = <?php echo json_encode($assignedcount, JSON_NUMERIC_CHECK);?>;
var max = <?php echo $max; ?>;
var arru2 = <?php echo json_encode($arru2); ?>;
var teamNumArr = <?php echo json_encode($teamNumArr); ?>;
var today = <?php echo json_encode($today); ?>;
var N = <?php echo $N; ?>;
var T = <?php echo $T; ?>;
var TMcountarr = <?php echo json_encode($TMcountarr, JSON_NUMERIC_CHECK);?>;
var TMflag = <?php echo json_encode($TMflag, JSON_NUMERIC_CHECK);?>;
var TMarr = <?php echo json_encode($TMarr);?>;
var Tarr = <?php echo json_encode($Tarr);?>;
var TEventarr = <?php echo json_encode($TEventarr);?>;
var TStartarr = <?php echo json_encode($TStartarr);?>;
var TEndarr = <?php echo json_encode($TEndarr);?>;
var Traitarr = <?php echo json_encode($Traitarr);?>;
var traitcount = <?php echo json_encode($traitcount, JSON_NUMERIC_CHECK);?>;
var theirtraitscore = <?php echo json_encode($theirtraitscore, JSON_NUMERIC_CHECK);?>;
var Yourtraits = <?php echo json_encode($Yourtraits);?>;
var userID = "<?php echo $user_id; ?>";
var numtraits = <?php echo $numtraits; ?>;
var Winarr = <?php echo json_encode($Winarr, JSON_NUMERIC_CHECK);?>;
var nodeList = [];
var edgeList = [];
var matrix = [];
var sum = T+1;
for (var i = 0; i < T; i++) {
    edgeList.push([0,i+1]);
}
for (var i = 0; i < max; i++) {
    for (var j = 0; j < teamNumArr[i].length; j++) {
        edgeList.push([teamNumArr[i][j], sum]);
    }
    sum += 1;
}
// Get the modal
var modal = document.getElementById('myModal');
// Get the button that opens the modal
var btn = document.getElementById("myBtn");
// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];
// When the user clicks the button, open the modal 
btn.onclick = function() {
    modal.style.display = "block";
    curr = 0;
};
// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
};
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};
</script>

<!-- Trigger/Open The Modal -->
<button id="myBtn" style="display:none">0</button>
<!-- The Modal -->
<div id="myModal" class="modal">
  <!-- Modal content -->
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">&times;</span>
      <h2 id="entName"><input type="text" style=background-color:#0066ff;border:none;display:block;margin:auto;text-align:center;font-size:80% size="65" id="mytext" readonly></h2>

    </div>
    <div class="modal-body">
      <p><a id="ahref"><input type="text" style=border:none;cursor:pointer;display:block;margin:auto;text-align:center;font-size:150% size="75" id="mytext2" readonly></a></p>
      <p><input type="text" style=border:none;display:block;margin:auto;text-align:center size="120" id="mytext3" readonly></p>
    </div>
    <div class="modal-footer">
      <h3><input type="text" style=border:none;background-color:#0066ff;display:block;margin:auto;text-align:center size="65" id="mytext4" readonly></h3>
    </div>
  </div>
</div>
<table align = "right">
  <tr>
    <td bgcolor="gray"></td>
    <td style=width:85%>You</td>
    
  </tr>
  <tr>
    <td bgcolor="red"></td>
    <td style=width:85%>Past Teams</td>
 
  </tr>
  <tr>
    <td bgcolor="white"></td>
    <td style=width:85%>Past Team Members</td>
  </tr>
  <tr>
    <td bgcolor="#53e241"></td>
    <td style=width:85%>Positive Review</td>
 
  </tr>
 
</table>
<canvas id="myCanvas" width="800" height="600"></canvas>
<script type="text/javascript">
var node = {
	x: 0,
	y: 0,
	radius: 10,
	inLink: false,
	createNode: function () {
		return Object.create(node);
	}
};

var Vector = {
	x: 0,
	y: 0,
	createVec: function(x, y) {
		var vec = Object.create(Vector);
		vec.x = x;
		vec.y = y;
		return vec;
	},
	vecLength: function () {
		return Math.sqrt(this.x * this.x + this.y * this.y);
	},
	vecAdd: function (v) {
		return Vector.createVec(this.x + v.x, this.y + v.y);
	}
};
// Get the modal
var modal = document.getElementById('myModal');
// Get the button that opens the modal
var btn = document.getElementById("myBtn");
// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];
// When the user clicks the button, open the modal 
btn.onclick = function() {
    modal.style.display = "block";
};
// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
};
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};

var canvas = document.getElementById("myCanvas");
var ctx = canvas.getContext("2d");
var linkHeight=10;
var linkWidth;

function initialize() {
	for (var i = 0; i < N; i++){
		var n = node.createNode();
		a = Math.random();
		if(a < 0.3) {
		    a += 0.3;
		}
		if (a > 0.7) {
		    a -= 0.3;
		}
		b = Math.random();
		if (b < 0.3) {
		    b += 0.325
		}
		if (b > 0.6) {
		    b -= 0.325;
		}
		n.x = a * canvas.width;
		n.y = b * canvas.height;
		if (i === 0) {
            n.radius = 25;
        }
        else if (i < T+1) {
            n.radius = 15;
        }
		nodeList.push(n);
	}
	
	matrix= new Array();
	for (var i = 0; i < N; i++)
		matrix[i] = new Array(N);
	
	for (var i = 0;i < edgeList.length; i++){
		matrix[edgeList[i][0]][edgeList[i][1]] = true;
		matrix[edgeList[i][1]][edgeList[i][0]] = true;
	}
}

var drawNode = function(ctx, x, y, rad, i) {
    if (i == 0) {
        ctx.fillStyle = "gray"
    }
    else if (i < T+1) {
        ctx.fillStyle = "red";
    }
    else {
        if (TMflag[i-(T+1)] != 1) {
            ctx.fillStyle = "white";
        }
        else {
            ctx.fillStyle = "#00ff00";
        }
    }
    ctx.strokeStyle = "black";
    ctx.beginPath();
    ctx.arc(x,y,rad,0,Math.PI*2);
    ctx.fill();
    ctx.stroke();
};

var drawEdge = function(ctx, x0, y0, x1, y1) {
    ctx.beginPath();
    ctx.moveTo(x0,y0);
    ctx.lineTo(x1,y1);
    ctx.stroke();
};

function draw(){
  canvas = document.getElementById("myCanvas");
  // check if supported
  if(canvas.getContext){

    ctx=canvas.getContext("2d");

	ctx.lineWidth = 2;
    ctx.strokeStyle = "#000000";
    for (var i = 0; i < edgeList.length; i++) {
        drawEdge(ctx, nodeList[edgeList[i][0]].x, nodeList[edgeList[i][0]].y, nodeList[edgeList[i][1]].x, nodeList[edgeList[i][1]].y);
    }	
	for (var i = 0; i < N; i++){
	    drawNode(ctx, nodeList[i].x, nodeList[i].y, nodeList[i].radius, i);
	    //draw the link
        ctx.font='10px sans-serif';
        ctx.fillStyle = "#0000ff";
        if (i < 10) {
            ctx.fillText(i.toString(),nodeList[i].x-3,nodeList[i].y+3);
        }
        else {
            ctx.fillText(i.toString(),nodeList[i].x-6,nodeList[i].y+3);
        }
        linkWidth=ctx.measureText(i.toString()).width;

        //add mouse listeners
        canvas.addEventListener("mousemove", on_mousemove, false);
        canvas.addEventListener("click", on_click, false);
	}
  }

}

function getLocalMousePos(canvas, ev) {
    var rect = canvas.getBoundingClientRect();
    return {
        x: ev.clientX - rect.left,
        y: ev.clientY - rect.top
    };
}
function on_mousemove (ev) {
    var localMouse = getLocalMousePos(canvas, ev);
    document.body.style.cursor = "";
    var xoff = 300;
    for (var i = 0; i < nodeList.length; i++) {
        if (Math.sqrt(Math.pow(nodeList[i].x-localMouse.x+xoff, 2)+Math.pow(nodeList[i].y-localMouse.y, 2)) < nodeList[i].radius) {
            document.body.style.cursor = "pointer";
            nodeList[i].inLink = true;
            console.log("collision with node "+i);
        }
        else {
            nodeList[i].inLink = false;
        }
    }
}

//if the link has been clicked, go to link
function on_click(e) {
    for (var i = 0; i < nodeList.length; i++) {
        if (nodeList[i].inLink) {
            if (i == 0) {
                var test = userID;
                var test2 = "link to your page";
                var link = "http://eventcoord.web.engr.illinois.edu/user_page.php?user_id=" + userID;
                var test3 = "";
                var test4 = "Your traits: ";
                for (var k = 0; k < numtraits; k++) {
                    test4 += Yourtraits[k] + ": " + traitcount[k]
                    if (k < numtraits-1) {
                        test4 += ", "
                    }
                }
            }
            else if (i < T+1) {
                var test = "Team Name: " + Tarr[i-1] + " - Event: " + TEventarr[i-1];
                var test2 = "link to " + Tarr[i-1] + "'s page";
                var link = "http://eventcoord.web.engr.illinois.edu/team_page.php?team_name=" + Tarr[i-1] + "&event_name=" + TEventarr[i-1] + "&event_start=" + TStartarr[i-1] + "&event_end=" + TEndarr[i-1];
                var test3 = "From " + TStartarr[i-1] + " to " + TEndarr[i-1];
//                window.alert(today);

                if (TEndarr[i-1] < today) {
                    test3 += " (expired)";
                }
                var test4 = "Event Result: " + Winarr[i-1].toString();
                if (Winarr[i-1] === 0) {
                    if (TStartarr[i-1] > today) {
                        test4 += " (upcoming)";
                    }
                    else if (TEndarr[i-1] < today) {
                        test4 += " (did not win)";
                    }
                    else {
                        test4 += " (in progress)"
                    }
                }
                else if(Winarr[i-1] === 1) {
                    test4 += " (honorable mention)";
                }
                else {
                    test4 += " (winner)";
                }
            }
            else {
                var test = arru2[i-(T+1)];
                var test2 = "link to " + arru2[i-(T+1)] + "'s page";
                var link = "http://eventcoord.web.engr.illinois.edu/user_page.php?user_id=" + arru2[i-(T+1)];
                if (TMflag[i-(T+1)] != 0) {
                    var test3 = "Traits you endorsed: ";
                    var s = Traitarr[i-(T+1)].length;
                    for (var j = 0; j < Traitarr[i-(T+1)].length; j++) {
                        if (j < s-1) {
                            test3 += Traitarr[i-(T+1)][j] + " (" + assignedcount[j] + "), ";
                        }
                        else {
                            test3 += Traitarr[i-(T+1)][j] + " (" + assignedcount[j] + ")";
                        }
                    }
                }
                else {
                    var test3 = "No traits endorsed.";
                }
                var test4 = arru2[i-(T+1)] + "'s traits: ";
                for (var k = 0; k < numtraits; k++) {
                    test4 += Yourtraits[k] + ": " + theirtraitscore[numtraits*(i-(T+1))+k];
                    if (k < numtraits-1) {
                        test4 += ", "
                    }
                }
            }
            document.getElementById("mytext4").value = test4;
            document.getElementById("mytext3").value = test3;
            document.getElementById("mytext2").value = test2;
            document.getElementById("ahref").href = link;
            document.getElementById("mytext").value = test;
            document.getElementById("myBtn").click();

        }
    }
}

//Hooke's Law spring attraction 
function HookeAttract(i, j) { // the result can be direct added to i
	var K = 0.1; //arbitrary spring constant, corresponds with Coulomb's constant
	var L = 20; //displaced (max) length 
	if(!matrix[i][j]) {
        return Vector.createVec(0, 0);
	}
	var diff = Vector.createVec(nodeList[i].x - nodeList[j].x, nodeList[i].y - nodeList[j].y);
	var len = diff.vecLength(); //distance between nodes or resting position of spring
	var norm = Vector.createVec(diff.x/len, diff.y/len);
	return Vector.createVec(norm.x * (K * (L - len)), norm.y * (K * (L - len)));
}

//Coulomb's Law - electrical repulsion between nodes
function CoulombRepel(i, j) {   // the result can be direct added to i
    if (i == 0 || j == 0) {
        var K = 35000; //arbitrary Coulomb's constant equivalent, corresponds with spring constant
    }
    else if (i < 5 || j < 5) {
	    var K = 25000; //arbitrary Coulomb's constant equivalent, corresponds with spring constant
    }
    else {
	    var K = 18000; //arbitrary Coulomb's constant equivalent, corresponds with spring constant
    }
	var diff = Vector.createVec(nodeList[i].x - nodeList[j].x, nodeList[i].y - nodeList[j].y); 
	var len = diff.vecLength();
	var norm = Vector.createVec(diff.x/len, diff.y/len); //distance between nodes
	return  Vector.createVec(norm.x * (K / Math.pow(len,2)), norm.y * (K / Math.pow(len,2)));
}


function redraw(){
	ctx.clearRect(0, 0, 800, 600);
	var move = new Array(N);
	for (var i = 0; i < N; i++) {
	    move[i] = Vector.createVec(0, 0);
	}
	for (var i = 0; i < N; i++) {
	    for (var j = i + 1; j < N; j++){
			var v1= HookeAttract(i, j).vecAdd(CoulombRepel(i, j)); //endpoint attraction and node repulsion combined
			move[i] = move[i].vecAdd(v1); //attract
			var neg = Vector.createVec(-(v1.x), -(v1.y));
			move[j] = move[j].vecAdd(neg); //repel
		}	
	}
	for (var i = 0; i < N; i++){
		nodeList[i].x += move[i].x;
		nodeList[i].y += move[i].y;
	}
	draw();
}

initialize();
setInterval(redraw,50);
</script>
  <div class="col-sm-3 col-md-2 sidebar">
            <!-- If not user who owns this page only and we have a 'teamname', 'eventname' and 'eventstart' and 'eventend' (meaning, we have a team to invite him to) on our GET-->
            <!-- also, we are not allowed to invite the event organizer. For obvious reasons-->
            <?php
            if ($user_id != null && $team_name != null && $event_name != null && $event_start != null && $event_end != null && $user_id != $event_organizer_id) {
                //TODO: link
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"user_invite.php?team_name=$team_name&event_name=$event_name&event_start=$event_start&event_end=$event_end&user_id=$user_id\">Invite to Team</a></li>");
                print('</ul>');
            }

            //Viewable to anyone
            print("<ul class=\"nav nav-sidebar\">");
            $user_id_get = urldecode($user_id);
            print("<li><a href=\"user_page.php?$user_id_get\">User Overview<span class=\"sr-only\">(current)</span></a></li>");
            print("</ul>");

            //Viewable to user who owns page
            if ($_SESSION['valid'] === true && $_SESSION['id'] === $user_id) {
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"user_edit.php\">Edit Profile </a></li>");
                print('</ul>');
                print('<ul class="nav nav-sidebar">');
                print("<li><a href=\"user_teams.php?user_id=$user_id_get\">View Teams</a></li>");
                print("<li><a href=\"user_events.php?user_id=$user_id_get\">View Events</a></li>");
                print("<li><a href=\"user_view_invites.php?user_id=$user_id_get\">View Invitations</a></li>");
                print('</ul>');
                print('<ul class="nav nav-sidebar">');
                print("<li class=\"active\"><a href=\"vis.php?user_id=$user_id_get\">View Data Visualization</a></li>");
                print('</ul>');
            }
            ?>
    </div>



<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="../../dist/js/bootstrap.min.js"></script>
<!-- Just to make our placeholder images work. Don't actually copy the next line! -->
<script src="../../assets/js/vendor/holder.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>