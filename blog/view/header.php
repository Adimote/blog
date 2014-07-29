<?php
include_once '../../includes/sql-manager.php';
include_once '../common.php';
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
<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="/feed.atom">
<link type="text/css" rel="stylesheet" href="/css/bootstrap.min.css" />
<link type="text/css" rel="stylesheet" href="/css/styles.css" />
<link type="text/css" rel="stylesheet" href="/js/highlight/styles/monokai_sublime.css" />
<script async src="/js/MathJax/MathJax.js?config=TeX-AMS-MML_SVG"></script>
<script async src="/js/script.js"></script>
<script async src="/js/highlight/highlight.pack.js" onload="hljs.initHighlightingOnLoad();"></script>
<!-- End Script -->
<?php
if (isset($GLOBALS['canonical'])) {
	echo "<link rel=\"canonical\" href=\"".Conf::URL.$GLOBALS['canonical']."\" />";
}
?>
<!-- Hide elements so they fade in if javascript is disabled -->
<style> article[anim="1"] {opacity:0;}</style>
<noscript>
<style> article[anim="1"] {opacity:1;}</style>
</noscript>
<title><?php
	echo getTitle();
?></title>
<?php
//IE Detection
if(preg_match('/(?i)msie [1-8]/',$_SERVER['HTTP_USER_AGENT']))
{
    // if IE
    $message = new Message("warning","This Website is not optimised for Internet Explorer");
    echo $message->formatMessage(true);
}
?>