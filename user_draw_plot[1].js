function toPlotSpace(x, y) {
    var ret_x = 50+(x*100);
    var ret_y = 300-(50+(y*100));
    return [ret_x, ret_y]

}
function getLocalMousePos(canvas, e) {
    var rect = canvas.getBoundingClientRect();
    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
}

function draw() {
    ctx.clearRect(0, 0, 600, 300);
    ctx.strokeStyle = '#7f7f7f';
    //draw axis lines
    ctx.moveTo(0, 250);
    ctx.lineTo(600, 250);
    ctx.stroke();

    ctx.moveTo(50, 0);
    ctx.lineTo(50, 300);
    ctx.stroke();

    //draw points
    ctx.fillStyle = '#5fc6ff';
    for(i = 0; i < user_data_split.length-1; i++) {
        //get point
        //it'd be i, user_data_split[i][4]
        var point_plot_coord = toPlotSpace(i, user_data_split[i][4]);
        ctx.beginPath();
        ctx.arc(point_plot_coord[0], point_plot_coord[1], radius, 0, 2*Math.PI);
        ctx.fill();
        ctx.stroke();
    }
    //get best fit line
    //it looks better to fill the whole screen, so we send it -0.5 to 4.5 coordinates
    ctx.strokeStyle = '#ff7100';
    var x_int = parseFloat(user_data_split[user_data_split.length-1][0]);
    var slope = parseFloat(user_data_split[user_data_split.length-1][1]);
    console.log(x_int);
    console.log(slope);
    var y0 = (x_int)+(-0.5*slope);
    var y1 = (x_int)+(4.5*slope);
    var line_coord_1 = toPlotSpace(-0.5, y0);
    var line_coord_2 = toPlotSpace(4.5, y1);
    console.log(line_coord_1);
    console.log(line_coord_2);
    ctx.moveTo(line_coord_1[0], line_coord_1[1]);
    ctx.lineTo(line_coord_2[0], line_coord_2[1]);
    ctx.stroke();

    //average line
    ctx.strokeStyle = '#7f7f7f';
    var avg = parseFloat(user_data_split[user_data_split.length-1][2]);
    var avg_line_coord1 = toPlotSpace(-0.5, avg);
    var avg_line_coord2 = toPlotSpace(4.5, avg);
    ctx.moveTo(avg_line_coord1[0], avg_line_coord1[1]);
    ctx.lineTo(avg_line_coord2[0], avg_line_coord2[1]);
    ctx.stroke();
}

function on_mousemove (e) {
    var localMouse = getLocalMousePos(c, e);

    draw();
    ctx.fillStyle = '#000000';
    document.body.style.cursor = "";
    activeLink = "";

    for(i = 0; i < user_data_split.length-1; i++) {
        //get point
        //it'd be i, user_data_split[i][4]
        var point_plot_coord = toPlotSpace(i, user_data_split[i][4]);

        if (Math.sqrt(Math.pow(point_plot_coord[0]-localMouse.x, 2)+Math.pow(point_plot_coord[1]-localMouse.y, 2)) < radius) {
            document.body.style.cursor = "pointer";
            activeLink = "team_page.php?team_name="+user_data_split[i][0]+"&event_name="+user_data_split[i][1]+"&event_start="+user_data_split[i][2]+"&event_end="+user_data_split[i][3];
            ctx.fillText(user_data_split[i][0], point_plot_coord[0], point_plot_coord[1]-(radius*2));

        }
    }
}

//if the link has been clicked, go to link
function on_click(e) {
    if (activeLink !== "") {
        window.location = activeLink;
    }
}


//get user data
var user_data_string = document.getElementById("user_bonus_array").textContent;
console.log(user_data_string);
//split into array
var user_data_split = user_data_string.split(";");

for (var i = 0; i < user_data_split.length; i++) {
    user_data_split[i] = user_data_split[i].split(",");
}
console.log(user_data_split);

var c = document.getElementById("user_bonus_plot");
var ctx = c.getContext("2d");

ctx.font = "12px Arial";
ctx.textAlign="center";

var radius = 10;
var activeLink = "";

c.addEventListener("mousemove", on_mousemove, false);
c.addEventListener("click", on_click, false);

draw();


