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
<link rel="stylesheet" href="/js/highlight/styles/monokai_sublime.css">
<!-- Script -->
<script async src="/js/script.js"></script>
<script type="text/x-mathjax-config">
MathJax.Hub.Config({
	extensions: ["tex2jax.js"],
	jax: ["input/TeX", "output/HTML-CSS"],
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
<script src="/js/MathJax/MathJax.js"></script>
<script src="/js/highlight/highlight.pack.js"></script>
<script>
	hljs.initHighlightingOnLoad();
</script>
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