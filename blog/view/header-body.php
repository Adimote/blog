<?php
include_once 'rendering.php';
if (isset($GLOBALS['tab'])) {
	$tab = $GLOBALS['tab'];
} else {
	$tab = 0;
}
?>
<header id="pagehead">
	<h1><?php echo Conf::Title; ?></h1>
	<small><p><?php echo Conf::SubTitle; ?></p></small>
</header>
<a class="spacer" href="/">
</a>
<div>
<ul class="nav nav-tabs container">
	<li <?php if ($tab == 0) echo "class=\"active\""?>><a href="/">Blog</a></li>
	<li <?php if ($tab == 1) echo "class=\"active\""?>><a href="/about">About Me</a></li>
</ul>
<main class="container perspective">
<?php
if (isset($GLOBALS['breadcrumbs'])) {
	echo renderBreadcrumbs($GLOBALS['breadcrumbs']);
}
?>