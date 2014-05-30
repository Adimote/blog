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
<?php
if (isset($GLOBALS['canonical'])) {
	echo "<link rel=\"canonical\" href=\"".Conf::URL.$GLOBALS['canonical']."\" />";
}
?>
<script async src="/js/script.js"></script>
<!-- Hide elements so they fade in if javascript is disabled -->
<style> article[anim="1"] {opacity:0;}</style>
<noscript>
<style> article[anim="1"] {opacity:1;}</style>
</noscript>

<title><?php
	echo getTitle();
?></title>