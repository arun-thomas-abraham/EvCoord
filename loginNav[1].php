<style>
    .navbar .nav > li > a,
    .navbar .nav > li.current-menu-item > a,
    .navbar .nav > li.current-menu-ancestor > a {
        font-size: 0.5em;
        padding: -5px;
        padding-right: 0.5cm;
    }
</style>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<link href="dashboard.css" rel="stylesheet">

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">EvCoord</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="index.php">Home</a></li>
                <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Search for Events<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <div class="d">
                            <input class="d2" type="text"/>
                            <button class="button7" onclick="window.location.href='event_search.php'">Advanced Search
                            </button>

                            <div class="search">
                                <button class="button3">Search</button>
                            </div>
                        </div>
                    </ul>
                </li>
                <li><a href="sign_up.php">Sign Up</a></li>
                <li><a href="sign_in.php">Login</a></li>
            </ul>

        </div>
    </div>
</nav>