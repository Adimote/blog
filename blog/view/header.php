<?php
	include_once '../../includes/sql-manager.php';
	function getTitle() {
		$title = Conf::Title." | ";
		if ($GLOBALS['pageTitle']) {
			$title = $title.$GLOBALS['pageTitle'];
		} else {
			$title = $title."Undefined Page";
		}
		return $title;
	}
	if (isset($_GET['admin'])) {
		$GLOBALS['admin'] = true;
	}
?>
<!DOCTYPE html>
<meta charset='utf-8'>
<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css"></link>
<link rel="stylesheet" type="text/css" href="/css/styles.css"></link>
<title><?php
	echo getTitle();
?></title>

<header>
	<a href="/">
	<div>
		<h1><?php echo Conf::Title; ?></h1>
		<small><p><?php echo Conf::SubTitle; ?></p></small>
	</div>
	</a>
</header>
<ul class="nav nav-tabs container">
	<li class="active"><a href="#">Blog</a></li>
	<li><a href="#">About Me</a></li>
</ul>