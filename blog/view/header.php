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
<link type="text/css" rel="stylesheet" href="/js/highlight/styles/monokai_sublime.css" />
<link type="text/css" rel="stylesheet" href="/min/g=css" />
<!-- Script -->
<script type="text/x-mathjax-config">
MathJax.Hub.Config({
	extensions: ["tex2jax.js"],
	jax: ["input/TeX", "output/SVG"],
tex2jax: {
	inlineMath: [ ['$','$'], ["\\(","\\)"] ],
	displayMath: [ ["\\[","\\]"] ],
	processEscapes: true
},
TeX: {
	extensions: ["AMSmath.js", "AMSsymbols.js"]
},
"HTML-CSS": { availableFonts: ["TeX"] }
});
</script>
<script async src="/js/MathJax/MathJax.js"></script>
<script async src="/min/g=js" onload="hljs.initHighlightingOnLoad();"></script>
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