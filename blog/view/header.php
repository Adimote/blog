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
	if ($_GET['admin']) {
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
	<h1><a href="/"><?php echo Conf::Title; ?></a></h1>
	<small><p>Subtitle goes here</p></small>
</header>
