﻿<?php
session_start();
include_once("config.php");
include_once("functions.php");

if(ENABLE_AUTHENTICATION){
	if(!isAuthenticated() && !isset($_POST['user'], $_POST['pass'])){
		include_once("login.php");
		exit;
	} elseif(!isAuthenticated() && isset($_POST['user'], $_POST['pass'])){
		if(!checkAuthentication(trim(strval($_POST['user'])), trim(strval($_POST['pass'])))){
			echo "Wrong username of password...";
			exit;
		}
	}
}

// Clean GET vars for filter
$title 			= (isset($_GET["title"]) && !empty($_GET["title"])) ? mysql_real_escape_string(trim(strval($_GET["title"]))) : "";
$genre 			= (isset($_GET["genre"]) && !empty($_GET["genre"])) ? mysql_real_escape_string(trim(strval($_GET["genre"]))) : "";
$year 			= (isset($_GET["year"]) && !empty($_GET["year"])) ? intval($_GET["year"]) : "";
$realisator 	= (isset($_GET["realisator"]) && !empty($_GET["realisator"])) ? mysql_real_escape_string(trim(strval($_GET["realisator"]))) : "";
$nationality 	= (isset($_GET["nationality"]) && !empty($_GET["nationality"])) ? mysql_real_escape_string(trim(strval($_GET["nationality"]))) : "";
$offset 		= (isset($_GET["offset"]) && trim(strval($_GET["offset"])) != "" && intval($_GET["offset"]) >= 0) ? intval($_GET["offset"]) : 0;

// Compute SQL filters
$filters = array();
if(!empty($title)){
	$filters[] = "(c00 LIKE '%" . $title . "%' OR c16 LIKE '%" . $title . "%' OR c01 LIKE '%" . $title . "%')";
}
if(!empty($genre)){
	$filters[] = "c14 LIKE '%" . $genre . "%'";
}
if(!empty($year)){
	$filters[] = "c07='" . $year . "'";
}
if(!empty($realisator)){
	$filters[] = "(c06 LIKE '%" . $realisator . "%' OR c15 LIKE '%" . $realisator . "%')";
}
if(!empty($nationality)){
	$filters[] = "c21 LIKE '%" . $nationality . "%'";
}

if(count($filters) > 0){
	$sql = "SELECT * FROM " . NAX_MOVIE_VIEW . " WHERE ";
	$multi = false;
	foreach($filters as $filter){
		if($multi)
			$sql .= "AND";
		$sql .= " " . $filter . " ";
		$multi = true;
	}
	$sql .= "ORDER BY c00 ASC LIMIT $offset," . DEFAULT_ENTRIES_DISPLAY . ";";
} else {
	$sql = "SELECT * FROM " . NAX_MOVIE_VIEW . " ORDER BY dateAdded DESC LIMIT $offset," . DEFAULT_ENTRIES_DISPLAY . ";"; 
}

//echo $sql;

if(isset($_GET["id"]) && isset($_GET["action"]) && $_GET["action"] == "detail"){
	$id = intval($_GET["id"]);
	if($id > 0)
		getDetailsEntryMovie($id);
	exit;
}

if(isset($_GET["offset"])){
	$offset = intval($_GET["offset"]);
	if($offset < 0)
		$offset = 0;
	sleep(1);
	getEntriesMovies($sql);
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Cache-Control" content="no-cache" />
	<meta http-equiv="Pragma-directive" content="no-cache" />
	<meta http-equiv="Cache-Directive" content="no-cache" />
	<meta name="robots" content="noindex,follow" />
	<title>KodiWebPortal <?php echo KODI_WEB_PORTAL_VERSION; ?></title>
	<link rel="stylesheet" href="style.css">
	<script type="text/javascript" src="https://code.jquery.com/jquery.min.js"></script>
	<script type="text/javascript" src="script.js"></script>
</head>

<body>
<a href="index.php" alt="Movies / Films" title="See all movies"><img class="image-nav" src="./images/cinema-logo.png" style="margin-top:30px;" /></a>
<a href="tvshow.php" alt="TvShow / Séries" title="See all TVshow"><img class="image-nav image-nav-move" src="./images/tvshow-logo.png" style="margin-top:160px;" /></a>
<div id="opacity"></div>

<div id="search">

<form action="" method="GET">
<?php
	$countreq = mysql_query("SELECT DISTINCT COUNT(*) FROM " . NAX_MOVIE_VIEW . ";");
	$count = mysql_fetch_array($countreq);
	echo "<b>[ " . $count[0] . " movies ]</b> - ";
?>
	<label for="title">Title</label>
	<input id="title" type="text" name="title" placeholder="Indiana Jones" value="<?php echo htmlentities(stripcslashes($title)); ?>" />

	<label for="genre">Genre</label>
	<select id="genre" name="genre">
		<option value="">*</option>
<?php
	$gs = getAllGenres("movies");
	foreach($gs as $g){
		if(stripcslashes($genre) == $g)
			echo "<option value='" . $g . "' selected>" . $g . "</option>";
		else
			echo "<option value='" . $g . "'>" . $g . "</option>";
	}
?>
	</select>
	
	<label for="year">Year</label>
	<select id="year" name="year">
		<option value="">*</option>
<?php
	$ys = getAllYears();
	foreach($ys as $y){
		if($year == $y)
			echo "<option value='" . $y . "' selected>" . $y . "</option>";
		else
			echo "<option value='" . $y . "'>" . $y . "</option>";
	}
?>
	</select>
	
	<label for="realisator">Realized by</label>
	<input id="realisator" type="text" name="realisator" placeholder="Spielberg" value="<?php echo htmlentities(stripcslashes($realisator)); ?>" />

	<label for="nationality">Nationality</label>
	<select id="nationality" name="nationality">
		<option value="">*</option>
<?php
	$ns = getAllNationalities();
	foreach($ns as $n){
		if(stripcslashes($nationality) == $n)
			echo "<option value='" . $n . "' selected>" . $n . "</option>";
		else
			echo "<option value='" . $n . "'>" . $n . "</option>";
	}
?>
	</select>

	<input type="submit" value="Search" />
	<input type="button" onclick="document.location=document.location.href.replace(document.location.search, '');" value="Reset" />
</form>
</div>

<div id="entries">

<script type="text/javascript">getEntries();</script>

</div>
</body>
</html>